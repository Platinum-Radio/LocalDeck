[CmdletBinding()]
param(
    [Parameter(Mandatory = $true)][string]$SourceDir,
    [Parameter(Mandatory = $true)][string]$ManifestFile,
    [Parameter(Mandatory = $true)][string]$CertificateThumbprint,
    [Parameter(Mandatory = $true)][string]$OutputFile
)

$ErrorActionPreference = 'Stop'
Set-StrictMode -Version Latest
Add-Type -AssemblyName System.IO.Compression.FileSystem
Add-Type -AssemblyName System.Security

$source = [System.IO.Path]::GetFullPath($SourceDir)
$manifestPath = [System.IO.Path]::GetFullPath($ManifestFile)
$output = [System.IO.Path]::GetFullPath($OutputFile)
if (-not [System.IO.Directory]::Exists($source)) { throw 'De componentbronmap bestaat niet.' }
if (-not [System.IO.File]::Exists($manifestPath)) { throw 'Het componentmanifest bestaat niet.' }
if ([System.IO.Path]::GetExtension($output) -ine '.ldpack') { throw 'Het uitvoerbestand moet de extensie .ldpack hebben.' }
if ([System.IO.File]::Exists($output)) { throw 'Het uitvoerbestand bestaat al.' }

$manifest = (Get-Content -LiteralPath $manifestPath -Raw -Encoding UTF8) | ConvertFrom-Json
if ([int]$manifest.schemaVersion -ne 1) { throw 'Gebruik schemaVersion 1.' }
$files = @()
Get-ChildItem -LiteralPath $source -File -Recurse | Sort-Object FullName | ForEach-Object {
    $relative = $_.FullName.Substring($source.TrimEnd('\').Length + 1).Replace('\', '/')
    $files += [pscustomobject]@{ path = $relative; sha256 = (Get-FileHash -LiteralPath $_.FullName -Algorithm SHA256).Hash.ToUpperInvariant() }
}
if($manifest.PSObject.Properties.Name -contains 'files'){$manifest.files=$files}else{$manifest|Add-Member -NotePropertyName files -NotePropertyValue $files}
$manifestBytes = [System.Text.UTF8Encoding]::new($false).GetBytes(($manifest | ConvertTo-Json -Depth 20))
$certificate = Get-ChildItem -Path Cert:\CurrentUser\My, Cert:\LocalMachine\My | Where-Object { ($_.Thumbprint -replace '\s', '') -ceq ($CertificateThumbprint -replace '\s', '') } | Select-Object -First 1
if ($null -eq $certificate -or -not $certificate.HasPrivateKey) { throw 'Het codecertificaat met privésleutel is niet gevonden.' }
$content = New-Object System.Security.Cryptography.Pkcs.ContentInfo -ArgumentList (, $manifestBytes)
$signed = New-Object System.Security.Cryptography.Pkcs.SignedCms -ArgumentList $content, $true
$signer = New-Object System.Security.Cryptography.Pkcs.CmsSigner -ArgumentList $certificate
$signer.DigestAlgorithm = New-Object System.Security.Cryptography.Oid '2.16.840.1.101.3.4.2.1'
$signed.ComputeSignature($signer)
$signatureBytes = $signed.Encode()

[System.IO.Directory]::CreateDirectory([System.IO.Path]::GetDirectoryName($output)) | Out-Null
$archive = [System.IO.Compression.ZipFile]::Open($output, [System.IO.Compression.ZipArchiveMode]::Create)
try {
    $manifestEntry = $archive.CreateEntry('manifest.json', [System.IO.Compression.CompressionLevel]::Optimal)
    $stream = $manifestEntry.Open()
    try { $stream.Write($manifestBytes, 0, $manifestBytes.Length) } finally { $stream.Dispose() }
    $signatureEntry = $archive.CreateEntry('manifest.p7s', [System.IO.Compression.CompressionLevel]::NoCompression)
    $stream = $signatureEntry.Open()
    try { $stream.Write($signatureBytes, 0, $signatureBytes.Length) } finally { $stream.Dispose() }
    foreach ($file in $files) {
        $sourceFile = Join-Path $source ([string]$file.path).Replace('/', '\')
        [System.IO.Compression.ZipFileExtensions]::CreateEntryFromFile($archive, $sourceFile, ('payload/' + [string]$file.path), [System.IO.Compression.CompressionLevel]::Optimal) | Out-Null
    }
}
finally { $archive.Dispose() }

Write-Output $output
