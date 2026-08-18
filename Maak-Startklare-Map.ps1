[CmdletBinding()]
param([switch]$SkipValidation)

Set-StrictMode -Version Latest
$ErrorActionPreference='Stop'
$root=[IO.Path]::GetFullPath($PSScriptRoot)
$target=[IO.Path]::GetFullPath((Join-Path $root 'LocalDeck-Startklaar'))
$work=[IO.Path]::GetFullPath((Join-Path $root '.run-work'))
$website=[IO.Path]::GetFullPath((Join-Path $root 'LocalDeck-Website'))
$node=Join-Path $root 'tools\node\node.exe'
if(!(Test-Path -LiteralPath $node)){throw 'De lokale Node.js-runtime ontbreekt uit de mastermap.'}
if(!$target.StartsWith($root.TrimEnd('\')+'\',[StringComparison]::OrdinalIgnoreCase)-or!$work.StartsWith($root.TrimEnd('\')+'\',[StringComparison]::OrdinalIgnoreCase)){throw 'De testmappen vallen buiten LocalDeck-Master.'}
if($website.StartsWith($target.TrimEnd('\')+'\',[StringComparison]::OrdinalIgnoreCase)){throw 'De afzonderlijke websitebron mag nooit binnen de te resetten startmap staan.'}

function Invoke-Node([string[]]$Arguments,[string]$Label){Write-Host "[startklaar] $Label" -ForegroundColor Cyan;& $node @Arguments;if($LASTEXITCODE-ne0){throw "$Label is mislukt met code $LASTEXITCODE."}}

Push-Location $root
$previousBuilderCache=$env:ELECTRON_BUILDER_CACHE
try{
  if(!$SkipValidation){& powershell.exe -NoLogo -NoProfile -NonInteractive -ExecutionPolicy Bypass -File (Join-Path $root 'Validate-LocalDeck.ps1');if($LASTEXITCODE-ne0){throw 'De bronvalidatie is mislukt.'}}
  $running=Get-Process -Name LocalDeck -ErrorAction SilentlyContinue|Where-Object{try{$_.Path-eq(Join-Path $target 'LocalDeck.exe')}catch{$false}}|Select-Object -First 1
  if($running){throw 'Sluit LocalDeck-Startklaar eerst volledig af via het systeemvak.'}
  if(Test-Path -LiteralPath $work){Remove-Item -LiteralPath $work -Recurse -Force}
  if(Test-Path -LiteralPath $target){Remove-Item -LiteralPath $target -Recurse -Force}
  New-Item -ItemType Directory -Path $work -Force|Out-Null
  Invoke-Node @('node_modules/typescript/bin/tsc','-p','tsconfig.electron.json') 'Electron-hoofdproces compileren'
  Invoke-Node @('node_modules/vite/bin/vite.js','build') 'dashboard bouwen'
  $env:ELECTRON_BUILDER_CACHE=Join-Path $root '.build-cache\electron-builder'
  Invoke-Node @('node_modules/electron-builder/out/cli/cli.js','--win','dir','--x64',"--config.directories.output=$work",'--config.win.signAndEditExecutable=false') 'draagbare Windows-app bouwen'
  $unpacked=Join-Path $work 'win-unpacked';if(!(Test-Path -LiteralPath (Join-Path $unpacked 'LocalDeck.exe'))){throw 'De startbare Windows-app ontbreekt.'}
  $unexpectedBuildTools=@(Get-ChildItem -LiteralPath $unpacked -File -Recurse|Where-Object{$_.FullName-match'[\\/]node_modules[\\/]@(esbuild|rollup)[\\/]'});if($unexpectedBuildTools.Count){throw 'De Windows-app bevat nog Vite-buildbinaries.'}
  $localeNames=@(Get-ChildItem -LiteralPath (Join-Path $unpacked 'locales') -File|Select-Object -ExpandProperty Name|Sort-Object);if(($localeNames-join',')-ne'en-GB.pak,en-US.pak,nl.pak'){throw "De Windows-app bevat onverwachte Electron-talen: $($localeNames-join', ')"}
  $asar=Get-Item -LiteralPath (Join-Path $unpacked 'resources\app.asar');if($asar.Length-gt10MB){throw "De verpakte app bevat onverwacht veel productiecode ($([math]::Round($asar.Length/1MB,1)) MB)."}
  Write-Host '[startklaar] app-bundel is compact: geen buildtools, drie talen en minimale runtimecode' -ForegroundColor Green
  New-Item -ItemType Directory -Path $target -Force|Out-Null
  Copy-Item -Path (Join-Path $unpacked '*') -Destination $target -Recurse -Force
  $policyCompatibleHost=Join-Path $root 'node_modules\electron\dist\electron.exe';if(!(Test-Path -LiteralPath $policyCompatibleHost)){throw 'De ongewijzigde Electron-host ontbreekt.'};Copy-Item -LiteralPath $policyCompatibleHost -Destination (Join-Path $target 'LocalDeck.exe') -Force
  [ordered]@{schemaVersion=1;mode='folder';runtimeDirectory='runtime';projectsDirectory='websites';description='Houd LocalDeck, runtime, databases en websites samen in deze map.'}|ConvertTo-Json|Set-Content -LiteralPath (Join-Path $target 'localdeck-folder.json') -Encoding UTF8
  Copy-Item -LiteralPath (Join-Path $root 'docs\STARTKLAAR-LEESMIJ.txt') -Destination (Join-Path $target 'LEESMIJ-EERST.txt') -Force
  $websites=Join-Path $target 'websites';$example=Join-Path $websites 'voorbeeld-website';New-Item -ItemType Directory -Path $websites,$example -Force|Out-Null
  foreach($name in 'index.html','style.css','favicon.svg'){if(!(Test-Path -LiteralPath (Join-Path $websites $name))){Copy-Item -LiteralPath (Join-Path $root "scripts\startpage\$name") -Destination (Join-Path $websites $name)}}
  if(!(Test-Path -LiteralPath (Join-Path $example 'index.php'))){Copy-Item -LiteralPath (Join-Path $root 'assets\example-website\index.php') -Destination (Join-Path $example 'index.php')}
  $runtime=Join-Path $target 'runtime';if(!(Test-Path -LiteralPath (Join-Path $runtime 'runtime.json'))){Write-Host "[startklaar] Alle ingebouwde programma's volledig offline uitpakken" -ForegroundColor Cyan;& powershell.exe -NoLogo -NoProfile -NonInteractive -WindowStyle Hidden -ExecutionPolicy Bypass -File (Join-Path $target 'resources\installer\install-runtime.ps1') -InstallDir $runtime -PackageDir (Join-Path $target 'resources\installer\packages') -SkipCertificateTrust;if($LASTEXITCODE-ne0){throw 'De ingebouwde runtime kon niet worden voorbereid.'}}
  $stateFile=Join-Path $runtime 'state.json';if(Test-Path -LiteralPath $stateFile){$initialState=Get-Content -LiteralPath $stateFile -Raw|ConvertFrom-Json}else{$initialState=[pscustomobject]@{}}
  if(!$initialState.PSObject.Properties['settings']){$initialState|Add-Member -NotePropertyName settings -NotePropertyValue ([pscustomobject]@{})}
  $testSettings=[ordered]@{firstRunComplete=$true;runtimeMode='application';serviceAutoStart=$false;minimizeToTray=$true;autoUpdateCheck=$false;securityScanOnStart=$false}
  foreach($setting in $testSettings.GetEnumerator()){$initialState.settings|Add-Member -NotePropertyName $setting.Key -NotePropertyValue $setting.Value -Force}
  $initialState|ConvertTo-Json -Depth 20|Set-Content -LiteralPath $stateFile -Encoding UTF8
  Invoke-Node @('scripts/test-startklare-map.cjs',$target) 'echte startklare map testen'
  Write-Host '[startklaar] verpakte LocalDeck.exe starten en dashboard renderen' -ForegroundColor Cyan
  $smokeScreenshot=Join-Path $target 'app-smoke.png'
  $appProcess=Start-Process -FilePath (Join-Path $target 'LocalDeck.exe') -WorkingDirectory $target -ArgumentList @('--localdeck-screenshot=app-smoke.png','--localdeck-screenshot-delay=1800','--localdeck-width=1280','--localdeck-height=720') -WindowStyle Hidden -PassThru
  if(!$appProcess.WaitForExit(20000)){Stop-Process -Id $appProcess.Id -Force -ErrorAction SilentlyContinue;throw 'De verpakte LocalDeck-app sloot de dashboardtest niet tijdig af.'}
  if($appProcess.ExitCode-ne0){throw "De verpakte LocalDeck-app stopte tijdens de dashboardtest met code $($appProcess.ExitCode)."}
  if(!(Test-Path -LiteralPath $smokeScreenshot)-or(Get-Item -LiteralPath $smokeScreenshot).Length-lt20KB){throw 'De verpakte LocalDeck-app heeft geen geldige dashboardscreenshot gemaakt.'}
  Remove-Item -LiteralPath $smokeScreenshot -Force
  Write-Host '[startklaar] verpakte app en dashboard zijn startbaar' -ForegroundColor Green
  Write-Host '[startklaar] Testgegevens verwijderen en eerste start herstellen' -ForegroundColor Cyan
  foreach($file in @('state.json','session.json','web-control-token.txt','mailpit.db','mailpit.db-shm','mailpit.db-wal','apache-error.log')){
    $path=Join-Path $runtime $file
    if(Test-Path -LiteralPath $path){Remove-Item -LiteralPath $path -Force}
  }
  foreach($directory in @('automation','backups','certs','dev-pages','downloads','logs','snapshots')){
    $path=Join-Path $runtime $directory
    if(Test-Path -LiteralPath $path){Remove-Item -LiteralPath $path -Recurse -Force}
    New-Item -ItemType Directory -Path $path -Force|Out-Null
  }
  foreach($configuration in @('apache\conf\localdeck-vhosts.conf','apache\conf\localdeck-fastcgi.conf')){
    $path=Join-Path $runtime $configuration
    if(Test-Path -LiteralPath $path){Remove-Item -LiteralPath $path -Force}
  }
  [ordered]@{version=(Get-Content -LiteralPath (Join-Path $root 'package.json') -Raw|ConvertFrom-Json).version;preparedAt=(Get-Date).ToString('o');status='passed';downloadArtifact=$false}|ConvertTo-Json|Set-Content -LiteralPath (Join-Path $target 'STARTKLAAR-TEST.json') -Encoding UTF8
  Write-Host "`nLocalDeck kan zonder installatie worden gestart via: $(Join-Path $target 'LocalDeck.exe')" -ForegroundColor Green
}finally{$env:ELECTRON_BUILDER_CACHE=$previousBuilderCache;Pop-Location}
