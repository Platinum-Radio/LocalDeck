[CmdletBinding(SupportsShouldProcess)]
param(
  [string]$InstallDir=(Join-Path $env:APPDATA 'LocalDeck\runtime'),
  [ValidateSet('Keep','Remove')][string]$DataAction='Keep',
  [string]$Confirmation=''
)

Set-StrictMode -Version Latest
$ErrorActionPreference='Stop'
$runtime=[IO.Path]::GetFullPath($InstallDir)
if(!(Test-Path -LiteralPath $runtime)){Write-Host 'Geen LocalDeck-runtime gevonden.';exit 0}
if($DataAction-eq'Keep'){Write-Host "LocalDeck-gegevens veilig bewaard in: $runtime";exit 0}
if($Confirmation-ne'VERWIJDER LOCALDECK-GEGEVENS'){throw 'Gegevens zijn niet verwijderd. Gebruik exact de vereiste bevestiging.'}
$root=[IO.Path]::GetPathRoot($runtime)
if($runtime-eq$root-or[IO.Path]::GetFileName($runtime)-ne'runtime'-or$runtime-notmatch'(?i)[\\/]LocalDeck[\\/]runtime$'){throw "Veiligheidsstop: onverwachte runtime-map ($runtime)."}
if($PSCmdlet.ShouldProcess($runtime,'LocalDeck runtime, websites, databases, back-ups en certificaten definitief verwijderen')){
  Remove-Item -LiteralPath $runtime -Recurse -Force
  Write-Host 'LocalDeck-gegevens zijn definitief verwijderd.'
}
