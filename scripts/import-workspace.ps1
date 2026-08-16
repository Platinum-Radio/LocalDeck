[CmdletBinding()]
param(
  [Parameter(Mandatory=$true)][string]$Archive,
  [Parameter(Mandatory=$true)][string]$Destination
)

Set-StrictMode -Version Latest
$ErrorActionPreference='Stop'
$archiveFile=[IO.Path]::GetFullPath($Archive);$target=[IO.Path]::GetFullPath($Destination)
if(!(Test-Path -LiteralPath $archiveFile)){throw 'Het werkruimtearchief bestaat niet.'}
if(Test-Path -LiteralPath $target){if((Get-ChildItem -LiteralPath $target -Force|Select-Object -First 1)){throw 'Kies voor veilig herstel een lege doelmap.'}}else{New-Item -ItemType Directory -Path $target -Force|Out-Null}
$temp=Join-Path ([IO.Path]::GetTempPath()) ("LocalDeck-import-"+[guid]::NewGuid().ToString('N'))
try{
  Expand-Archive -LiteralPath $archiveFile -DestinationPath $temp
  $payload=Join-Path $temp 'LocalDeck-Workspace';$manifestFile=Join-Path $payload 'workspace-manifest.json'
  if(!(Test-Path -LiteralPath $manifestFile)){throw 'Dit is geen geldig LocalDeck-werkruimtearchief.'}
  $manifest=Get-Content -LiteralPath $manifestFile -Raw|ConvertFrom-Json
  if($manifest.product-ne'LocalDeck'-or$manifest.schemaVersion-ne1){throw 'Deze werkruimteversie wordt niet ondersteund.'}
  foreach($entry in $manifest.files){$file=Join-Path $payload $entry.path;if(!(Test-Path -LiteralPath $file)-or(Get-FileHash -LiteralPath $file -Algorithm SHA256).Hash-ne$entry.sha256){throw "Integriteitscontrole mislukt voor $($entry.path)."}}
  Copy-Item -Path (Join-Path $payload '*') -Destination $target -Recurse -Force
}catch{if(Test-Path -LiteralPath $target){Get-ChildItem -LiteralPath $target -Force|Remove-Item -Recurse -Force};throw}finally{if(Test-Path -LiteralPath $temp){Remove-Item -LiteralPath $temp -Recurse -Force}}
Write-Host $target

