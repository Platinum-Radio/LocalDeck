[CmdletBinding()]
param(
  [switch]$Finalize,
  [Parameter(Mandatory=$true)][ValidatePattern('^https://')][string]$DownloadBaseUrl,
  [string]$Publisher,
  [switch]$AllowUnsigned
)

Set-StrictMode -Version Latest
$ErrorActionPreference='Stop'
$root=[IO.Path]::GetFullPath($PSScriptRoot)
if(!$Finalize){throw 'Publicatie is niet gestart. Gebruik -Finalize uitsluitend wanneer de nieuwe versie echt gereed is.'}
if(!$AllowUnsigned){
  if([string]::IsNullOrWhiteSpace($env:CSC_LINK)){throw 'CSC_LINK ontbreekt. Gebruik een codecertificaat of kies bewust -AllowUnsigned.'}
  if([string]::IsNullOrWhiteSpace($Publisher)){throw 'De exacte Authenticode-uitgever ontbreekt.'}
}else{
  $Publisher='Niet digitaal ondertekend'
  Write-Warning 'Er wordt een expliciet ONGETEKENDE release gebouwd. Windows SmartScreen of Smart App Control kan deze downloads blokkeren.'
}
$DownloadBaseUrl=$DownloadBaseUrl.TrimEnd('/')
$node=Join-Path $root 'tools\node\node.exe';if(!(Test-Path -LiteralPath $node)){$node=(Get-Command node.exe -ErrorAction Stop).Source}
$version=(Get-Content -LiteralPath (Join-Path $root 'package.json') -Raw|ConvertFrom-Json).version
if($version-notmatch '^\d+\.\d+\.\d+(?:[-+][0-9A-Za-z.-]+)?$'){throw "Ongeldig versienummer: $version"}
$releaseChannel=if($version-match '-'){'beta'}else{'stable'}
$downloads=Join-Path $root 'downloads';$published=Join-Path $downloads $version
if(Test-Path -LiteralPath $published){throw "Release $version bestaat al. Verhoog eerst de versie; bestaande downloads worden nooit overschreven."}
$buildId="$version-$(Get-Date -Format 'yyyyMMdd-HHmmss')";$work=Join-Path $root ".release-work\$buildId";$stage=Join-Path $work 'stage';$release=Join-Path $work 'release';$candidate=Join-Path $work 'candidate';$nativeRuntime=Join-Path $work 'native-runtime'
New-Item -ItemType Directory -Path $stage,$release,$candidate -Force|Out-Null

$builderCache=Join-Path $root '.build-cache\electron-builder'
$winCodeSignCache=Join-Path $builderCache 'winCodeSign\winCodeSign-2.6.0'
$winCodeSignRequired=@('rcedit-x64.exe','windows-10\x64\signtool.exe')
if(@($winCodeSignRequired|Where-Object{-not(Test-Path -LiteralPath (Join-Path $winCodeSignCache $_))}).Count){
  $candidateRoots=@(
    (Join-Path $builderCache 'winCodeSign'),
    (Join-Path $env:LOCALAPPDATA 'electron-builder\Cache\winCodeSign')
  )
  $cachedTools=$candidateRoots|Where-Object{Test-Path -LiteralPath $_}|ForEach-Object{Get-ChildItem -LiteralPath $_ -Directory -ErrorAction SilentlyContinue}|Where-Object{(Test-Path -LiteralPath (Join-Path $_.FullName 'rcedit-x64.exe')) -and (Test-Path -LiteralPath (Join-Path $_.FullName 'windows-10\x64\signtool.exe'))}|Select-Object -First 1
  if($cachedTools){New-Item -ItemType Directory -Path (Split-Path -Parent $winCodeSignCache) -Force|Out-Null;Copy-Item -LiteralPath $cachedTools.FullName -Destination $winCodeSignCache -Recurse}
}
if(@($winCodeSignRequired|Where-Object{-not(Test-Path -LiteralPath (Join-Path $winCodeSignCache $_))}).Count){throw 'De lokale electron-builder Windows-toolcache is niet compleet. Voer eerst een interne Windows-build uit met Windows Developer Mode ingeschakeld.'}

function Invoke-Node([string[]]$Arguments,[string]$Label){Write-Host "[release] $Label" -ForegroundColor Cyan;& $node @Arguments;if($LASTEXITCODE-ne0){throw "$Label is mislukt met code $LASTEXITCODE."}}

Push-Location $root
$previousBuilderCache=$env:ELECTRON_BUILDER_CACHE;$previousNativeRuntime=$env:LOCALDECK_NATIVE_RUNTIME;$previousCscIdentityAutoDiscovery=$env:CSC_IDENTITY_AUTO_DISCOVERY
try{
  & powershell.exe -NoLogo -NoProfile -NonInteractive -ExecutionPolicy Bypass -File (Join-Path $root 'Validate-LocalDeck.ps1')
  if($LASTEXITCODE-ne0){throw 'De interne validatie is mislukt.'}

  $env:ELECTRON_BUILDER_CACHE=$builderCache
  $directoryBuildArguments=@('node_modules/electron-builder/out/cli/cli.js','--win','dir','--x64',"--config.directories.output=$stage")
  if($AllowUnsigned){$env:CSC_IDENTITY_AUTO_DISCOVERY='false';$directoryBuildArguments+='--config.win.verifyUpdateCodeSignature=false'}else{$directoryBuildArguments+=@('--config.win.verifyUpdateCodeSignature=true',"--config.win.publisherName=$Publisher")}
  Invoke-Node $directoryBuildArguments $(if($AllowUnsigned){'Ongetekende Windows-app samenstellen'}else{'Ondertekende Windows-app samenstellen'})
  $unpacked=Join-Path $stage 'win-unpacked';$appExe=Join-Path $unpacked 'LocalDeck.exe'
  $appSignature=Get-AuthenticodeSignature -LiteralPath $appExe
  if($AllowUnsigned){if($appSignature.Status-ne'NotSigned'){throw "De ongetekende app heeft een onverwachte handtekeningstatus: $($appSignature.Status)."}}elseif($appSignature.Status-ne'Valid'-or$appSignature.SignerCertificate.Subject-notlike"*$Publisher*"){throw "De app is niet geldig ondertekend door $Publisher."}

  $installerRoot=Join-Path $unpacked 'resources\installer';$packageDirectory=Join-Path $installerRoot 'packages'
  Write-Host '[release] Exact verpakte runtime volledig offline installeren' -ForegroundColor Cyan
  & powershell.exe -NoLogo -NoProfile -NonInteractive -WindowStyle Hidden -ExecutionPolicy Bypass -File (Join-Path $installerRoot 'install-runtime.ps1') -InstallDir $nativeRuntime -PackageDir $packageDirectory -SkipCertificateTrust
  if($LASTEXITCODE-ne0){throw 'De volledige offline runtime-installatie is mislukt.'}
  $env:LOCALDECK_NATIVE_RUNTIME=$nativeRuntime
  Invoke-Node @('node_modules/vitest/vitest.mjs','run','electron/runtime.test.ts','-t','start en stopt alle echte','--reporter','verbose') 'Echte Windows-services, localhost en database testen'

  $packageBuildArguments=@('node_modules/electron-builder/out/cli/cli.js','--prepackaged',$unpacked,'--win','nsis','--x64',"--config.directories.output=$release")
  if($AllowUnsigned){$packageBuildArguments+='--config.win.verifyUpdateCodeSignature=false'}else{$packageBuildArguments+=@('--config.win.verifyUpdateCodeSignature=true',"--config.win.publisherName=$Publisher")}
  Invoke-Node $packageBuildArguments $(if($AllowUnsigned){'Ongetekende installatie-EXE bouwen'}else{'Ondertekende installatie-EXE bouwen'})
  $setupName="LocalDeck-$version-Windows-Setup-x64.exe";$folderName="LocalDeck-$version-Windows-x64";$zipName="$folderName.zip"
  $setup=Join-Path $release $setupName
  if(!(Test-Path -LiteralPath $setup)){throw 'De definitieve Windows-installatie ontbreekt.'}
  $signature=Get-AuthenticodeSignature -LiteralPath $setup;if($AllowUnsigned){if($signature.Status-ne'NotSigned'){throw "Onverwachte handtekeningstatus voor ${setup}: $($signature.Status)"}}elseif($signature.Status-ne'Valid'-or$signature.SignerCertificate.Subject-notlike"*$Publisher*"){throw "Ongeldige Authenticode-handtekening: $setup"}

  $shot=Join-Path $work 'portable-smoke.png';$profile=Join-Path $work 'portable-profile'
  $portableProcess=Start-Process -FilePath $appExe -ArgumentList "--user-data-dir=`"$profile`"","--localdeck-screenshot=`"$shot`"","--localdeck-screenshot-delay=1600" -WindowStyle Hidden -PassThru -Wait
  if($portableProcess.ExitCode-ne0-or!(Test-Path -LiteralPath $shot)){throw 'De portable eindtest is mislukt.'}

  Copy-Item -LiteralPath $setup -Destination (Join-Path $candidate $setupName)
  $zipStage=Join-Path $work 'zip-stage';New-Item -ItemType Directory -Path $zipStage -Force|Out-Null
  Copy-Item -LiteralPath $unpacked -Destination (Join-Path $zipStage $folderName) -Recurse
  & tar.exe -a -cf (Join-Path $candidate $zipName) -C $zipStage $folderName
  if($LASTEXITCODE-ne0){throw 'De uitpakbare Windows-ZIP kon niet worden gemaakt.'}

  $runtimeManifest=Get-Content -LiteralPath (Join-Path $zipStage "$folderName\resources\installer\packages\offline-runtime.json") -Raw|ConvertFrom-Json
  if($runtimeManifest.packages.Count-ne13){throw 'De kandidaat bevat niet alle 13 runtimepakketten.'}
  foreach($package in $runtimeManifest.packages){$file=Join-Path $zipStage "$folderName\resources\installer\packages\$($package.name)";if((Get-FileHash -LiteralPath $file -Algorithm SHA256).Hash-ne$package.sha256){throw "Definitieve pakketcontrole mislukt voor $($package.name)"}}
  $setupHash=(Get-FileHash -LiteralPath (Join-Path $candidate $setupName) -Algorithm SHA256).Hash;$zipHash=(Get-FileHash -LiteralPath (Join-Path $candidate $zipName) -Algorithm SHA256).Hash;$publishedAt=(Get-Date).ToString('o')
  $feed=[ordered]@{version=$version;channel=$releaseChannel;title="LocalDeck $version is beschikbaar";notes=$(if($AllowUnsigned){'Ongetekende Windows-release met SHA-256 en volledig offline gecontroleerde runtime. Windows kan een SmartScreen-waarschuwing tonen.'}else{'Ondertekende Windows-release met volledig offline gecontroleerde runtime.'});downloadUrl="$DownloadBaseUrl/$setupName";publishedAt=$publishedAt;sha256=$setupHash;signed=(-not $AllowUnsigned);publisher=$Publisher}
  $previous=Get-ChildItem -LiteralPath $downloads -Directory -ErrorAction SilentlyContinue|Where-Object{$_.Name-ne$version-and$_.Name-match'^\d+\.\d+\.\d+$'}|Sort-Object{[version]$_.Name}|Select-Object -Last 1
  if(!$AllowUnsigned-and$previous){$previousSetup=Get-ChildItem -LiteralPath $previous.FullName -Filter 'LocalDeck-*-Windows-Setup-x64.exe'|Select-Object -First 1;if($previousSetup){$feed.rollbackVersion=$previous.Name;$feed.rollbackUrl="$DownloadBaseUrl/$($previous.Name)/$($previousSetup.Name)";$feed.rollbackSha256=(Get-FileHash -LiteralPath $previousSetup.FullName -Algorithm SHA256).Hash}}
  $feed|ConvertTo-Json|Set-Content -LiteralPath (Join-Path $candidate 'update-feed.json') -Encoding UTF8
  [ordered]@{version=$version;channel=$releaseChannel;publishedAt=$publishedAt;packages=13;validation='passed';signed=(-not $AllowUnsigned);publisher=$Publisher;warning=$(if($AllowUnsigned){'Windows SmartScreen of Smart App Control kan deze ongetekende bestanden blokkeren.'}else{$null});artifacts=@([ordered]@{type='installer-exe';file=$setupName;sha256=$setupHash},[ordered]@{type='portable-zip';file=$zipName;sha256=$zipHash});sourceCommit=(git -C $root rev-parse HEAD 2>$null);updateFeed='update-feed.json'}|ConvertTo-Json -Depth 5|Set-Content -LiteralPath (Join-Path $candidate 'release.json') -Encoding UTF8

  New-Item -ItemType Directory -Path $downloads -Force|Out-Null
  Move-Item -LiteralPath $candidate -Destination $published
  Write-Host "`nRelease $version is volledig gecontroleerd en staat nu in: $published" -ForegroundColor Green
}finally{$env:ELECTRON_BUILDER_CACHE=$previousBuilderCache;$env:LOCALDECK_NATIVE_RUNTIME=$previousNativeRuntime;$env:CSC_IDENTITY_AUTO_DISCOVERY=$previousCscIdentityAutoDiscovery;Pop-Location}
