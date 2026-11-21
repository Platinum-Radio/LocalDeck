[CmdletBinding()]
param(
    [Parameter(Mandatory)]
    [ValidatePattern('^\d+\.\d+\.\d+(?:-[0-9A-Za-z.-]+)?$')]
    [string]$Version,

    [Parameter(Mandatory)]
    [ValidateScript({ Test-Path -LiteralPath $_ -PathType Leaf })]
    [string]$SetupPath,

    [Parameter(Mandatory)]
    [ValidateScript({ Test-Path -LiteralPath $_ -PathType Leaf })]
    [string]$ZipPath,

    [Parameter(Mandatory)]
    [ValidatePattern('^https://')]
    [string]$BaseUrl,

    [ValidateSet('stable', 'beta')]
    [string]$Channel = 'stable',

    [string]$Title,
    [string]$TitleEn,
    [string]$Notes = 'Nieuwe verbeteringen en onderhoudsupdates voor LocalDeck.',
    [string]$NotesEn = 'New improvements and maintenance updates for LocalDeck.',
    [string[]]$Changes = @(),
    [string[]]$ChangesEn = @(),

    [ValidateSet('full', 'application-only', 'component-aware')]
    [string]$UpdateStrategy = 'component-aware',

    [bool]$RuntimeChanged = $false,

    [object[]]$Components = @(),

    [switch]$AllowUnsigned
)

$ErrorActionPreference = 'Stop'
$scriptDirectory = Split-Path -Parent $PSCommandPath
$websiteRoot = Split-Path -Parent $scriptDirectory
$downloadsDirectory = Join-Path $websiteRoot 'downloads'
$releaseDirectory = Join-Path $downloadsDirectory (Join-Path 'releases' $Version)
$catalogPath = Join-Path $downloadsDirectory 'releases.json'
$feedPath = Join-Path $downloadsDirectory $(if ($Channel -eq 'beta') { 'beta.json' } else { 'windows.json' })
$BaseUrl = $BaseUrl.TrimEnd('/')
$Title = if ($Title) { $Title } else { "LocalDeck $Version" }
$TitleEn = if ($TitleEn) { $TitleEn } else { "LocalDeck $Version" }

if (-not (Test-Path -LiteralPath $catalogPath -PathType Leaf)) {
    throw 'releases.json ontbreekt.'
}

$catalog = Get-Content -LiteralPath $catalogPath -Raw | ConvertFrom-Json
if (@($catalog.releases | Where-Object version -eq $Version | Where-Object published).Count -gt 0) {
    throw "Versie $Version is al gepubliceerd. Een gepubliceerde release wordt nooit stil overschreven."
}

$sourceArtifacts = @(
    [pscustomobject]@{ Id = 'setup-x64'; Path = (Resolve-Path -LiteralPath $SetupPath).Path; Label = 'LocalDeck installeren — EXE' },
    [pscustomobject]@{ Id = 'zip-x64'; Path = (Resolve-Path -LiteralPath $ZipPath).Path; Label = 'Zonder installatie — ZIP uitpakken' }
)

$signatureResults = foreach ($source in $sourceArtifacts | Where-Object Id -eq 'setup-x64') {
    $signature = Get-AuthenticodeSignature -LiteralPath $source.Path
    if (-not $AllowUnsigned -and $signature.Status -ne 'Valid') {
        throw "$($source.Path) heeft geen geldige Authenticode-handtekening. Status: $($signature.Status)."
    }
    if ($AllowUnsigned -and $signature.Status -notin @('Valid', 'NotSigned')) {
        throw "$($source.Path) heeft een onveilige of beschadigde handtekeningstatus: $($signature.Status)."
    }
    [pscustomobject]@{ Path = $source.Path; Status = [string] $signature.Status; Subject = [string] $signature.SignerCertificate.Subject }
}
$releaseSigned = @($signatureResults | Where-Object Status -ne 'Valid').Count -eq 0
if (-not $releaseSigned) {
    Write-Warning 'De websitepublicatie bevat ongetekende Windows-bestanden. Windows SmartScreen of Smart App Control kan deze blokkeren.'
}

New-Item -ItemType Directory -Force -Path $releaseDirectory | Out-Null
$artifacts = @()
foreach ($source in $sourceArtifacts) {
    $destinationName = switch ($source.Id) {
        'setup-x64' { "LocalDeck-$Version-Windows-Setup-x64.exe" }
        'zip-x64' { "LocalDeck-$Version-Windows-x64.zip" }
        default { throw "Onbekend releasebestand: $($source.Id)" }
    }
    $destination = Join-Path $releaseDirectory $destinationName
    if (Test-Path -LiteralPath $destination) {
        throw "Doelbestand bestaat al: $destination"
    }
    Copy-Item -LiteralPath $source.Path -Destination $destination
    $hash = (Get-FileHash -LiteralPath $destination -Algorithm SHA256).Hash.ToLowerInvariant()
    $sizeMb = [math]::Round((Get-Item -LiteralPath $destination).Length / 1MB, 1)
    $artifacts += [pscustomobject]@{
        id = $source.Id
        label = $source.Label
        file = "downloads/releases/$Version/$destinationName"
        sizeLabel = "$sizeMb MB"
        sha256 = $hash
        signed = $releaseSigned
    }
}

$artifacts |
    ForEach-Object { "$($_.sha256)  $([IO.Path]::GetFileName($_.file))" } |
    Set-Content -LiteralPath (Join-Path $releaseDirectory 'SHA256SUMS.txt') -Encoding ascii

$release = [pscustomobject]@{
    version = $Version
    channel = $Channel
    releasedAt = (Get-Date).ToUniversalTime().ToString('o')
    published = $true
    signed = $releaseSigned
    title = $Title
    titleEn = $TitleEn
    notes = $Notes
    notesEn = $NotesEn
    changes = @($Changes)
    changesEn = @($ChangesEn)
    packageBytes = [int64] (Get-Item -LiteralPath $SetupPath).Length
    runtimeChanged = $RuntimeChanged
    updateStrategy = $UpdateStrategy
    components = @($Components)
    warning = if ($Channel -eq 'beta' -and -not $releaseSigned) { 'Windows SmartScreen of Smart App Control kan bij deze testversie waarschuwen of blokkeren.' } else { $null }
    artifacts = $artifacts
}
$remaining = @($catalog.releases | Where-Object version -ne $Version)
$catalog.releases = @($release) + $remaining

$setup = $artifacts | Where-Object id -eq 'setup-x64' | Select-Object -First 1
$historyCandidates = @($catalog.releases | Where-Object published)
if ($Channel -eq 'stable') {
    $historyCandidates = @($historyCandidates | Where-Object { [string]::IsNullOrWhiteSpace([string]$_.channel) -or [string]$_.channel -eq 'stable' })
}
$history = @($historyCandidates | Select-Object -First 25 | ForEach-Object {
    [ordered]@{
        version = $_.version
        title = $_.title
        titleEn = $_.titleEn
        notes = $_.notes
        notesEn = $_.notesEn
        changes = @($_.changes)
        changesEn = @($_.changesEn)
        publishedAt = $_.releasedAt
        channel = $_.channel
    }
})
$previous = $historyCandidates | Where-Object version -ne $Version | Select-Object -First 1
$previousSetup = if ($previous) { $previous.artifacts | Where-Object id -eq 'setup-x64' | Select-Object -First 1 } else { $null }
$feed = [ordered]@{
    schemaVersion = 2
    version = $Version
    title = $Title
    titleEn = $TitleEn
    notes = $Notes
    notesEn = $NotesEn
    downloadUrl = "$BaseUrl/download.php?version=$Version&artifact=setup-x64"
    releaseNotesUrl = "$BaseUrl/updates.php"
    publishedAt = $release.releasedAt
    channel = $Channel
    sha256 = $setup.sha256
    signed = $releaseSigned
    publisher = if ($releaseSigned) { $signatureResults[0].Subject } else { 'Niet digitaal ondertekend' }
    packageBytes = $release.packageBytes
    runtimeChanged = $release.runtimeChanged
    updateStrategy = $release.updateStrategy
    components = @($release.components)
    history = $history
}
if ($previous -and $previousSetup -and $previousSetup.sha256) {
    $feed.rollbackVersion = $previous.version
    $feed.rollbackUrl = "$BaseUrl/download.php?version=$($previous.version)&artifact=setup-x64"
    $feed.rollbackSha256 = $previousSetup.sha256
}

$catalogTemporary = "$catalogPath.tmp"
$feedTemporary = "$feedPath.tmp"
$catalog | ConvertTo-Json -Depth 12 | Set-Content -LiteralPath $catalogTemporary -Encoding UTF8
$feed | ConvertTo-Json -Depth 8 | Set-Content -LiteralPath $feedTemporary -Encoding UTF8
Move-Item -LiteralPath $catalogTemporary -Destination $catalogPath -Force
Move-Item -LiteralPath $feedTemporary -Destination $feedPath -Force

Write-Host "LocalDeck $Version is aan de website-releasebron toegevoegd."
Write-Host "Updatefeed: $feedPath"
Write-Host "Controleer de downloadpagina en updatecontrole vóór je de website publiceert."
