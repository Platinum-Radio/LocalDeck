[CmdletBinding()]
param()

Set-StrictMode -Version Latest
$ErrorActionPreference='Stop'
Add-Type -AssemblyName System.IO.Compression.FileSystem
$root=[IO.Path]::GetFullPath($PSScriptRoot)
$node=Join-Path $root 'tools\node\node.exe'
if(!(Test-Path -LiteralPath $node)){$node=(Get-Command node.exe -ErrorAction Stop).Source}
$requiredPackages=@('apache.zip','php.zip','php-8.2.zip','php-8.3.zip','php-8.4.zip','mysql.zip','phpmyadmin.zip','mailpit.zip','redis.zip','WinSW.NET4.exe','composer.phar','vc_redist.x64.exe','mkcert.exe')

function Invoke-Node([string[]]$Arguments,[string]$Label){
  Write-Host "[controle] $Label" -ForegroundColor Cyan
  & $node @Arguments
  if($LASTEXITCODE-ne0){throw "$Label is mislukt met code $LASTEXITCODE."}
}

function Assert-CompactZip([string]$Name,[string[]]$ForbiddenPatterns,[string[]]$RequiredMarkers){
  $archiveFile=Join-Path (Join-Path $root 'runtime-packages') $Name
  $archive=[IO.Compression.ZipFile]::OpenRead($archiveFile)
  try{
    $entries=@($archive.Entries|ForEach-Object{$_.FullName.Replace('\','/')})
    foreach($pattern in $ForbiddenPatterns){
      $unexpected=$entries|Where-Object{$_-match$pattern}|Select-Object -First 1
      if($unexpected){throw "Runtimepakket $Name bevat uitgesloten distributiebestand: $unexpected"}
    }
    foreach($marker in $RequiredMarkers){
      if(!($entries|Where-Object{$_.EndsWith($marker,[StringComparison]::OrdinalIgnoreCase)}|Select-Object -First 1)){
        throw "Runtimepakket $Name mist verplicht bestand: $marker"
      }
    }
  }finally{$archive.Dispose()}
}

Push-Location $root
try{
  $packageDirectory=Join-Path $root 'runtime-packages'
  $manifestFile=Join-Path $packageDirectory 'offline-runtime.json'
  if(!(Test-Path -LiteralPath $manifestFile)){throw 'Het offline-runtime-manifest ontbreekt.'}
  $manifest=Get-Content -LiteralPath $manifestFile -Raw|ConvertFrom-Json
  if($manifest.packages.Count-ne$requiredPackages.Count){throw "Het runtime-manifest bevat $($manifest.packages.Count) in plaats van $($requiredPackages.Count) pakketten."}
  foreach($name in $requiredPackages){
    $entry=$manifest.packages|Where-Object{$_.name-eq$name}|Select-Object -First 1
    $file=Join-Path $packageDirectory $name
    if(!$entry-or!(Test-Path -LiteralPath $file)){throw "Runtimepakket ontbreekt: $name"}
    if((Get-FileHash -LiteralPath $file -Algorithm SHA256).Hash-ne$entry.sha256){throw "SHA-256-controle mislukt voor $name"}
  }
  Write-Host '[controle] 13 offline runtimepakketten zijn intact' -ForegroundColor Green
  if(!$manifest.PSObject.Properties['optimization']-or$manifest.optimization.profile-ne'windows-x64-runtime-only'){throw 'Het runtime-manifest mist het compacte Windows-runtimeprofiel.'}
  Assert-CompactZip 'mysql.zip' @('(?i)\.pdb$','(?i)\.lib$','(?i)(^|/)include/','(?i)/lib/plugin/debug/','(?i)/bin/[^/]+-debug\.dll$') @('bin/mysqld.exe','bin/mysql.exe','bin/mysqladmin.exe','bin/mysqldump.exe')
  Assert-CompactZip 'redis.zip' @('(?i)\.pdb$') @('redis-server.exe','redis-cli.exe')
  Assert-CompactZip 'apache.zip' @('(?i)/(include|lib)/') @('bin/httpd.exe','modules/mod_ssl.so','modules/mod_proxy_fcgi.so')
  Write-Host '[controle] Runtimepakketten bevatten alleen uitvoerbare Windows-onderdelen' -ForegroundColor Green

  $packageConfig=Get-Content -LiteralPath (Join-Path $root 'package.json') -Raw|ConvertFrom-Json
  $productionDependencies=@($packageConfig.dependencies.PSObject.Properties.Name|Sort-Object)
  if(($productionDependencies-join',')-ne'qrcode,yaml'){throw "Onverwachte productie-afhankelijkheden worden in de app verpakt: $($productionDependencies-join', ')"}
  $electronLanguages=@($packageConfig.build.electronLanguages|Sort-Object)
  if(($electronLanguages-join',')-ne'en-GB,en-US,nl'){throw "Onverwachte Electron-talen in de Windows-build: $($electronLanguages-join', ')"}
  if($packageConfig.build.compression-ne'maximum'){throw 'De Windows-build gebruikt niet de maximale distributiecompressie.'}
  Write-Host '[controle] App-afhankelijkheden, talen en compressie zijn distributiecompact' -ForegroundColor Green

  foreach($script in Get-ChildItem -LiteralPath (Join-Path $root 'scripts') -Filter '*.ps1' -File){
    $tokens=$null;$parseErrors=$null
    [System.Management.Automation.Language.Parser]::ParseFile($script.FullName,[ref]$tokens,[ref]$parseErrors)|Out-Null
    if($parseErrors.Count){throw "PowerShell-syntaxfout in $($script.Name): $($parseErrors[0].Message)"}
  }
  Write-Host '[controle] Alle PowerShell-scripts hebben geldige syntax' -ForegroundColor Green

  $php=Join-Path $root 'LocalDeck-Startklaar\runtime\php\php.exe'
  $websiteRoot=Join-Path $root 'LocalDeck-Website'
  if(!(Test-Path -LiteralPath $php)){throw 'De meegeleverde PHP-runtime voor de websitetests ontbreekt.'}
  $phpFiles=Get-ChildItem -LiteralPath $websiteRoot -Filter '*.php' -Recurse -File
  foreach($phpFile in $phpFiles){
    $lintOutput=& $php -l $phpFile.FullName 2>&1
    if($LASTEXITCODE-ne0){throw "PHP-syntaxfout in $($phpFile.FullName): $lintOutput"}
  }
  foreach($testFile in Get-ChildItem -LiteralPath (Join-Path $websiteRoot 'tests') -Filter '*.test.php' -File){
    & $php $testFile.FullName
    if($LASTEXITCODE-ne0){throw "Websitetest $($testFile.Name) is mislukt met code $LASTEXITCODE."}
  }
  Write-Host "[controle] $($phpFiles.Count) websitebestanden en alle websitetests zijn geldig" -ForegroundColor Green

  Invoke-Node @('node_modules/typescript/bin/tsc','-p','tsconfig.electron.json') 'Electron-hoofdproces en gedeelde types'
  Invoke-Node @('node_modules/typescript/bin/tsc','--noEmit') 'TypeScript-interface'
  Invoke-Node @('node_modules/vitest/vitest.mjs','run') 'Automatische tests'
  Invoke-Node @('node_modules/vite/bin/vite.js','build') 'Dashboard-build'

  $validationDirectory=Join-Path $root '.validation';New-Item -ItemType Directory -Path $validationDirectory -Force|Out-Null
  $version=(Get-Content -LiteralPath (Join-Path $root 'package.json') -Raw|ConvertFrom-Json).version
  [ordered]@{version=$version;validatedAt=(Get-Date).ToString('o');packages=$requiredPackages.Count;status='passed'}|ConvertTo-Json|Set-Content -LiteralPath (Join-Path $validationDirectory 'last-success.json') -Encoding UTF8
  Write-Host "`nLocalDeck $version is intern gevalideerd. Er zijn geen downloadbestanden gemaakt." -ForegroundColor Green
}finally{Pop-Location}
