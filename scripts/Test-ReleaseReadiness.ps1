[CmdletBinding()]
param([string]$Publisher)

Set-StrictMode -Version Latest
$ErrorActionPreference='Stop'
$root=[IO.Path]::GetFullPath((Join-Path $PSScriptRoot '..'))
$manifest=Get-Content -LiteralPath (Join-Path $root 'runtime-packages\offline-runtime.json') -Raw|ConvertFrom-Json
$results=@()
function Add-Result([string]$Name,[bool]$Ok,[string]$Detail){$script:results+=[pscustomobject]@{Check=$Name;Status=$(if($Ok){'OK'}else{'FOUT'});Detail=$Detail}}
Add-Result 'Windows x64' ($env:PROCESSOR_ARCHITECTURE-eq'AMD64') "$([Environment]::OSVersion.VersionString) · $env:PROCESSOR_ARCHITECTURE"
Add-Result 'Runtimepakket-aantal' ($manifest.packages.Count-eq13) "$($manifest.packages.Count)/13"
foreach($package in $manifest.packages){$file=Join-Path $root "runtime-packages\$($package.name)";$ok=(Test-Path -LiteralPath $file)-and((Get-FileHash -LiteralPath $file -Algorithm SHA256).Hash-eq$package.sha256);Add-Result "Pakket $($package.id)" $ok $package.version}
Add-Result 'Publicatiescript' (Test-Path -LiteralPath (Join-Path $root 'Publish-LocalDeck.ps1')) 'Fail-closed Authenticode-pijplijn'
Add-Result 'Uitgever' (![string]::IsNullOrWhiteSpace($Publisher)) $(if($Publisher){$Publisher}else{'Nog niet ingesteld'})
$results|Format-Table -AutoSize
if($results.Status-contains'FOUT'){exit 1}

