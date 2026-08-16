[CmdletBinding(SupportsShouldProcess)]param([string]$InstallDir=(Join-Path $env:APPDATA 'LocalDeck\runtime'))
if(!(Test-Path $InstallDir)){Write-Host 'Geen LocalDeck-runtime gevonden.';exit 0}
if($PSCmdlet.ShouldProcess($InstallDir,'LocalDeck runtime en databases verwijderen')){Remove-Item -LiteralPath $InstallDir -Recurse -Force;Write-Host 'LocalDeck-runtime verwijderd.'}
