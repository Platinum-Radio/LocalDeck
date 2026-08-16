[CmdletBinding()]
param(
  [Parameter(Mandatory=$true)][string]$PackageDirectory,
  [Parameter(Mandatory=$true)][hashtable]$Versions
)

Set-StrictMode -Version Latest
$ErrorActionPreference='Stop'
$packageRoot=[IO.Path]::GetFullPath($PackageDirectory)
$manifestFile=Join-Path $packageRoot 'offline-runtime.json'
if(!(Test-Path -LiteralPath $manifestFile)){throw 'offline-runtime.json ontbreekt in de opgegeven pakketmap.'}
$manifest=Get-Content -LiteralPath $manifestFile -Raw|ConvertFrom-Json
foreach($package in $manifest.packages){
  $file=Join-Path $packageRoot $package.name
  if(!(Test-Path -LiteralPath $file)){throw "Pakket ontbreekt: $($package.name)"}
  if(!$Versions.ContainsKey([string]$package.id)){throw "Exacte versie ontbreekt voor $($package.id)."}
  $package.version=[string]$Versions[[string]$package.id]
  $package.sha256=(Get-FileHash -LiteralPath $file -Algorithm SHA256).Hash
  $package.bytes=(Get-Item -LiteralPath $file).Length
}
$manifest.schemaVersion=2
$manifest.createdAt=(Get-Date).ToString('o')
$manifest|ConvertTo-Json -Depth 8|Set-Content -LiteralPath $manifestFile -Encoding UTF8
Write-Host "Runtime-manifest bijgewerkt zonder downloads: $manifestFile" -ForegroundColor Green

