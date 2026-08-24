[CmdletBinding()]
param([Parameter(Mandatory=$true)][string]$OutputDir)

Set-StrictMode -Version Latest
$ErrorActionPreference='Stop'
$ProgressPreference='SilentlyContinue'
$root=[IO.Path]::GetFullPath((Join-Path $PSScriptRoot '..'))
$OutputDir=[IO.Path]::GetFullPath($OutputDir)
$sourceLockFile=Join-Path $root 'runtime-packages\runtime-sources.json'
if(!(Test-Path -LiteralPath $sourceLockFile)){throw 'De vastgezette runtimebronlijst ontbreekt.'}
$sourceLock=Get-Content -LiteralPath $sourceLockFile -Raw|ConvertFrom-Json
if($sourceLock.schemaVersion-ne1-or@($sourceLock.packages).Count-ne13){throw 'De runtimebronlijst heeft een onbekend formaat of onjuist pakketaantal.'}
New-Item -ItemType Directory -Path $OutputDir -Force|Out-Null

$existingManifestFile=Join-Path $OutputDir 'offline-runtime.json'
$existingManifest=if(Test-Path -LiteralPath $existingManifestFile){Get-Content -LiteralPath $existingManifestFile -Raw|ConvertFrom-Json}else{$null}

function New-PackageRecord($Spec,[string]$Sha256,[long]$Bytes){
  $record=[ordered]@{
    id=[string]$Spec.id
    displayName=[string]$Spec.displayName
    version=[string]$Spec.version
    required=$true
    name=[string]$Spec.name
    sha256=$Sha256
    bytes=$Bytes
    source=[string]$Spec.url
    sourceSha256=[string]$Spec.sha256
    sourceBytes=[long]$Spec.bytes
    license=[string]$Spec.license
    licenseUrl=[string]$Spec.licenseUrl
    projectUrl=[string]$Spec.projectUrl
    sourceCodeUrl=$Spec.sourceCodeUrl
  }
  if($Spec.PSObject.Properties['systemLibrary']){$record.systemLibrary=[bool]$Spec.systemLibrary}
  if($Spec.PSObject.Properties['optimizationProfile']){$record.packaging=[string]$Spec.optimizationProfile}
  return [pscustomobject]$record
}

function Save-PinnedPackage($Spec){
  $target=Join-Path $OutputDir ([string]$Spec.name)
  if(Test-Path -LiteralPath $target){
    $existingHash=(Get-FileHash -LiteralPath $target -Algorithm SHA256).Hash
    $existingBytes=(Get-Item -LiteralPath $target).Length
    $prepared=if($existingManifest){$existingManifest.packages|Where-Object{$_.id-eq$Spec.id}|Select-Object -First 1}else{$null}
    $matchesSource=$existingHash-eq[string]$Spec.sha256-and$existingBytes-eq[long]$Spec.bytes
    $matchesPrepared=$prepared-and$existingHash-eq[string]$prepared.sha256-and$existingBytes-eq[long]$prepared.bytes
    if(!$matchesSource-and!$matchesPrepared){throw "Bestaand runtimepakket wijkt af van zowel de bron-lock als het voorbereide manifest: $($Spec.name)"}
    Write-Host "[aanwezig] $($Spec.name)" -ForegroundColor DarkGray
    return New-PackageRecord $Spec $existingHash $existingBytes
  }

  $partial=Join-Path $OutputDir ('.'+[string]$Spec.name+'.download-'+[guid]::NewGuid().ToString('N')+'.tmp')
  try{
    Write-Host "[download] $($Spec.name) $($Spec.version)" -ForegroundColor Cyan
    Invoke-WebRequest -UseBasicParsing -UserAgent 'LocalDeck-Runtime-Builder/1.0' -Uri ([string]$Spec.url) -OutFile $partial
    $actualBytes=(Get-Item -LiteralPath $partial).Length
    $actualHash=(Get-FileHash -LiteralPath $partial -Algorithm SHA256).Hash
    if($actualBytes-ne[long]$Spec.bytes){throw "Bronlengte wijkt af voor $($Spec.name): $actualBytes in plaats van $($Spec.bytes)."}
    if($actualHash-ne[string]$Spec.sha256){throw "Bronhash wijkt af voor $($Spec.name): $actualHash."}
    Move-Item -LiteralPath $partial -Destination $target
    return New-PackageRecord $Spec $actualHash $actualBytes
  }catch{
    if(Test-Path -LiteralPath $partial){Remove-Item -LiteralPath $partial -Force}
    throw
  }
}

$packages=@($sourceLock.packages|ForEach-Object{Save-PinnedPackage $_})
[ordered]@{
  schemaVersion=2
  createdAt=(Get-Date).ToString('o')
  sourceLock='runtime-sources.json'
  packages=$packages
}|ConvertTo-Json -Depth 8|Set-Content -LiteralPath $existingManifestFile -Encoding UTF8

$targetSourceLock=Join-Path $OutputDir 'runtime-sources.json'
if([IO.Path]::GetFullPath($targetSourceLock)-ne[IO.Path]::GetFullPath($sourceLockFile)){
  Copy-Item -LiteralPath $sourceLockFile -Destination $targetSourceLock -Force
}

& (Join-Path $PSScriptRoot 'Optimize-RuntimePackages.ps1') -PackageDirectory $OutputDir
if($LASTEXITCODE-ne0){throw 'De offline runtime kon niet compact worden gemaakt.'}
Write-Host "Offline runtime gereed uit 13 vastgezette en gecontroleerde bronnen: $OutputDir" -ForegroundColor Green
