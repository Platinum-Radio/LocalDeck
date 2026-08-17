[CmdletBinding()]
param([switch]$CodeOnly)

Set-StrictMode -Version Latest
$ErrorActionPreference = 'Stop'

$websiteRoot = [IO.Path]::GetFullPath((Split-Path -Parent $PSScriptRoot))
$masterRoot = [IO.Path]::GetFullPath((Split-Path -Parent $websiteRoot))
$deploymentRoot = [IO.Path]::GetFullPath((Join-Path $masterRoot '.deployment'))
$deploymentName = if ($CodeOnly) { 'localdeck-site-1.0.0-code' } else { 'localdeck-site-1.0.0' }
$archiveName = if ($CodeOnly) { 'LocalDeck-Website-1.0.0-Code.zip' } else { 'LocalDeck-Website-1.0.0.zip' }
$stage = [IO.Path]::GetFullPath((Join-Path $deploymentRoot $deploymentName))
$archive = [IO.Path]::GetFullPath((Join-Path $deploymentRoot $archiveName))
$allowedPrefix = $masterRoot.TrimEnd('\') + '\'

if (-not $deploymentRoot.StartsWith($allowedPrefix, [StringComparison]::OrdinalIgnoreCase) -or
    -not $stage.StartsWith($allowedPrefix, [StringComparison]::OrdinalIgnoreCase) -or
    -not $archive.StartsWith($allowedPrefix, [StringComparison]::OrdinalIgnoreCase)) {
    throw 'De website-deploymentpaden vallen buiten LocalDeck-Master.'
}

if (Test-Path -LiteralPath $stage) {
    Remove-Item -LiteralPath $stage -Recurse -Force
}
if (Test-Path -LiteralPath $archive) {
    Remove-Item -LiteralPath $archive -Force
}
New-Item -ItemType Directory -Path $stage -Force | Out-Null

foreach ($directory in @('api', 'assets', 'downloads', 'inc', 'private')) {
    Copy-Item -LiteralPath (Join-Path $websiteRoot $directory) -Destination (Join-Path $stage $directory) -Recurse -Force
}
foreach ($file in @('.htaccess', 'robots.txt')) {
    Copy-Item -LiteralPath (Join-Path $websiteRoot $file) -Destination (Join-Path $stage $file) -Force
}

# Alle publieke PHP-ingangspunten in de websiteroot horen bij dezelfde deployment.
# Zo kan een gedeelde renderer zoals guide.php niet ongemerkt uit het archief vallen.
Get-ChildItem -LiteralPath $websiteRoot -Filter '*.php' -File |
    ForEach-Object {
        Copy-Item -LiteralPath $_.FullName -Destination (Join-Path $stage $_.Name) -Force
    }

foreach ($requiredFile in @('guide.php', 'guides.php')) {
    if (-not (Test-Path -LiteralPath (Join-Path $stage $requiredFile) -PathType Leaf)) {
        throw "Verplicht websitebestand ontbreekt in deployment: $requiredFile"
    }
}

foreach ($developmentFile in @(
    'downloads\README.md',
    'downloads\releases\0.10.0\PLAATS-HIER-NOG-GEEN-RELEASE.txt',
    'downloads\releases\1.0.0-rc.1\PLAATS-HIER-NOG-GEEN-RELEASE.txt'
)) {
    $path = Join-Path $stage $developmentFile
    if (Test-Path -LiteralPath $path) {
        Remove-Item -LiteralPath $path -Force
    }
}

if ($CodeOnly) {
    Get-ChildItem -LiteralPath (Join-Path $stage 'downloads\releases') -Recurse -Force -File -ErrorAction SilentlyContinue |
        Remove-Item -Force
    $sessionPath = Join-Path $stage 'private\sessions'
    if (Test-Path -LiteralPath $sessionPath) {
        Remove-Item -LiteralPath $sessionPath -Recurse -Force
    }
    foreach ($dynamicFile in @('private\community-submissions.json', 'private\download-stats.json')) {
        $path = Join-Path $stage $dynamicFile
        if (Test-Path -LiteralPath $path) {
            Remove-Item -LiteralPath $path -Force
        }
    }
}

& tar.exe -a -cf $archive -C $stage .
if ($LASTEXITCODE -ne 0 -or -not (Test-Path -LiteralPath $archive -PathType Leaf)) {
    throw 'Het website-deploymentarchief kon niet worden gemaakt.'
}

[pscustomobject]@{
    archive = $archive
    files = @(Get-ChildItem -LiteralPath $stage -Recurse -Force -File).Count
    bytes = (Get-Item -LiteralPath $archive).Length
    sha256 = (Get-FileHash -LiteralPath $archive -Algorithm SHA256).Hash
}
