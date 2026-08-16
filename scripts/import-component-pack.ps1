[CmdletBinding()]
param(
    [Parameter(Mandatory = $true)][string]$PackFile,
    [Parameter(Mandatory = $true)][string]$RuntimeDir,
    [Parameter(Mandatory = $true)][string]$TrustedPublisher
)

$ErrorActionPreference = 'Stop'
Set-StrictMode -Version Latest
Add-Type -AssemblyName System.IO.Compression.FileSystem
Add-Type -AssemblyName System.Security

function Read-ZipEntryBytes {
    param([System.IO.Compression.ZipArchiveEntry]$Entry)
    $stream = $Entry.Open()
    $memory = New-Object System.IO.MemoryStream
    try {
        $stream.CopyTo($memory)
        return $memory.ToArray()
    }
    finally {
        $stream.Dispose()
        $memory.Dispose()
    }
}

$packPath = [System.IO.Path]::GetFullPath($PackFile)
$runtimePath = [System.IO.Path]::GetFullPath($RuntimeDir)
if (-not [System.IO.File]::Exists($packPath)) { throw 'Het .ldpack-bestand bestaat niet.' }
if ([System.IO.Path]::GetExtension($packPath) -ine '.ldpack') { throw 'Alleen .ldpack-componentpakketten worden geaccepteerd.' }
if ([string]::IsNullOrWhiteSpace($TrustedPublisher)) { throw 'Een vertrouwde certificaatuitgever of certificaatthumbprint is verplicht.' }

$archive = [System.IO.Compression.ZipFile]::OpenRead($packPath)
$staging = $null
try {
    $entries = @($archive.Entries)
    $manifestEntry = $entries | Where-Object { $_.FullName -ceq 'manifest.json' } | Select-Object -First 1
    $signatureEntry = $entries | Where-Object { $_.FullName -ceq 'manifest.p7s' } | Select-Object -First 1
    if ($null -eq $manifestEntry -or $null -eq $signatureEntry) { throw 'manifest.json of manifest.p7s ontbreekt.' }

    $manifestBytes = Read-ZipEntryBytes -Entry $manifestEntry
    $signatureBytes = Read-ZipEntryBytes -Entry $signatureEntry
    $content = New-Object System.Security.Cryptography.Pkcs.ContentInfo -ArgumentList (, $manifestBytes)
    $signed = New-Object System.Security.Cryptography.Pkcs.SignedCms -ArgumentList $content, $true
    $signed.Decode($signatureBytes)
    $signed.CheckSignature($true)
    if ($signed.SignerInfos.Count -ne 1) { throw 'Het pakket moet exact één certificaatondertekening bevatten.' }
    $certificate = $signed.SignerInfos[0].Certificate
    if ($null -eq $certificate) { throw 'Het ondertekeningscertificaat ontbreekt.' }
    if ($certificate.NotBefore -gt (Get-Date) -or $certificate.NotAfter -lt (Get-Date)) { throw 'Het ondertekeningscertificaat is niet geldig op dit moment.' }
    $trusted = $TrustedPublisher.Trim()
    $thumbprint = ($certificate.Thumbprint -replace '\s', '').ToUpperInvariant()
    $trustedThumbprint = ($trusted -replace '\s', '').ToUpperInvariant()
    $publisherMatches = $trustedThumbprint -ceq $thumbprint -or $trusted -ceq $certificate.Subject -or $trusted -ceq $certificate.GetNameInfo([System.Security.Cryptography.X509Certificates.X509NameType]::SimpleName, $false)
    if (-not $publisherMatches) { throw "Pakketuitgever '$($certificate.Subject)' komt niet overeen met de ingestelde vertrouwde uitgever." }

    $manifestText = [System.Text.Encoding]::UTF8.GetString($manifestBytes)
    $manifest = $manifestText | ConvertFrom-Json
    if ([int]$manifest.schemaVersion -ne 1) { throw 'Deze componentpackversie wordt niet ondersteund.' }
    if ([string]$manifest.id -notmatch '^[a-z0-9][a-z0-9._-]{1,63}$') { throw 'Ongeldige component-id.' }
    if ([string]$manifest.version -notmatch '^[0-9A-Za-z][0-9A-Za-z._+-]{0,63}$') { throw 'Ongeldige componentversie.' }
    if ([string]$manifest.category -notin @('webserver', 'database', 'mail', 'tool')) { throw 'Ongeldige componentcategorie.' }
    if ($null -eq $manifest.files -or @($manifest.files).Count -eq 0) { throw 'Het componentmanifest bevat geen bestanden.' }

    $stagingRoot = Join-Path $runtimePath 'component-staging'
    [System.IO.Directory]::CreateDirectory($stagingRoot) | Out-Null
    $staging = Join-Path $stagingRoot ([guid]::NewGuid().ToString('N'))
    [System.IO.Directory]::CreateDirectory($staging) | Out-Null
    $seen = New-Object 'System.Collections.Generic.HashSet[string]' ([System.StringComparer]::OrdinalIgnoreCase)
    foreach ($file in @($manifest.files)) {
        $relative = ([string]$file.path).Replace('/', [System.IO.Path]::DirectorySeparatorChar)
        if ([string]::IsNullOrWhiteSpace($relative) -or [System.IO.Path]::IsPathRooted($relative)) { throw 'Een componentbestand heeft een ongeldig pad.' }
        $destination = [System.IO.Path]::GetFullPath((Join-Path $staging $relative))
        $stagingPrefix = $staging.TrimEnd('\') + '\'
        if (-not $destination.StartsWith($stagingPrefix, [System.StringComparison]::OrdinalIgnoreCase)) { throw 'Padmanipulatie in het componentmanifest is geblokkeerd.' }
        if (-not $seen.Add($relative)) { throw "Dubbel bestand in manifest: $relative" }
        $zipName = 'payload/' + ([string]$file.path).Replace('\', '/')
        $entry = $entries | Where-Object { $_.FullName -ceq $zipName } | Select-Object -First 1
        if ($null -eq $entry -or $entry.FullName.EndsWith('/')) { throw "Bestand ontbreekt in pakket: $zipName" }
        [System.IO.Directory]::CreateDirectory([System.IO.Path]::GetDirectoryName($destination)) | Out-Null
        $inputStream = $entry.Open()
        $outputStream = [System.IO.File]::Create($destination)
        try { $inputStream.CopyTo($outputStream) }
        finally { $inputStream.Dispose(); $outputStream.Dispose() }
        $actualHash = (Get-FileHash -LiteralPath $destination -Algorithm SHA256).Hash.ToUpperInvariant()
        if ($actualHash -cne ([string]$file.sha256).ToUpperInvariant()) { throw "SHA-256-controle mislukt voor $relative." }
    }

    [System.IO.File]::WriteAllBytes((Join-Path $staging 'manifest.json'), $manifestBytes)
    [ordered]@{schemaVersion=1;verified=$true;verifiedAt=(Get-Date).ToString('o');publisher=$certificate.Subject;certificateThumbprint=$thumbprint;manifestSha256=([System.BitConverter]::ToString([System.Security.Cryptography.SHA256]::Create().ComputeHash($manifestBytes)).Replace('-', ''));packSha256=(Get-FileHash -LiteralPath $packPath -Algorithm SHA256).Hash.ToUpperInvariant()}|ConvertTo-Json|Set-Content -LiteralPath (Join-Path $staging 'verification.json') -Encoding UTF8
    $destinationRoot = Join-Path (Join-Path (Join-Path $runtimePath 'components') ([string]$manifest.id)) ([string]$manifest.version)
    if ([System.IO.Directory]::Exists($destinationRoot)) { throw 'Deze componentversie is al geïnstalleerd.' }
    [System.IO.Directory]::CreateDirectory([System.IO.Path]::GetDirectoryName($destinationRoot)) | Out-Null
    [System.IO.Directory]::Move($staging, $destinationRoot)
    $staging = $null

    [pscustomobject]@{
        id = [string]$manifest.id
        name = [string]$manifest.name
        description = [string]$manifest.description
        category = [string]$manifest.category
        status = 'installed'
        requiresAdmin = [bool]$manifest.requiresAdmin
        license = [string]$manifest.license
        version = [string]$manifest.version
        publisher = $certificate.Subject
        verified = $true
        path = $destinationRoot
    } | ConvertTo-Json -Compress
}
finally {
    $archive.Dispose()
    if ($null -ne $staging -and [System.IO.Directory]::Exists($staging)) {
        [System.IO.Directory]::Delete($staging, $true)
    }
}
