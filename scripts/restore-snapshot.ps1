[CmdletBinding()]
param(
  [Parameter(Mandatory)][string]$RuntimeDir,
  [Parameter(Mandatory)][string]$SnapshotFile,
  [Parameter(Mandatory)][string]$ProjectDir,
  [string]$DatabaseName = ''
)
$ErrorActionPreference='Stop'
$RuntimeDir=[IO.Path]::GetFullPath($RuntimeDir);$SnapshotFile=[IO.Path]::GetFullPath($SnapshotFile);$ProjectDir=[IO.Path]::GetFullPath($ProjectDir)
if(!(Test-Path -LiteralPath $SnapshotFile -PathType Leaf)){throw 'Snapshotarchief bestaat niet.'}
$stage=Join-Path ([IO.Path]::GetTempPath()) "LocalDeck-restore-$([guid]::NewGuid())";New-Item -ItemType Directory -Path $stage -Force|Out-Null
try{
  Expand-Archive -LiteralPath $SnapshotFile -DestinationPath $stage -Force
  $source=Join-Path $stage 'project';if(!(Test-Path -LiteralPath $source)){throw 'Snapshot bevat geen projectbestanden.'}
  New-Item -ItemType Directory -Path $ProjectDir -Force|Out-Null;& robocopy.exe $source $ProjectDir /E /R:1 /W:1 | Out-Null
  if($LASTEXITCODE-ge8){throw "Projectherstel is mislukt (robocopy $LASTEXITCODE)."}
  $sql=Join-Path $stage 'database.sql'
  if($DatabaseName-and(Test-Path $sql)-and(Test-Path (Join-Path $RuntimeDir 'mysql\bin\mysql.exe'))){
    $password=& (Join-Path $PSScriptRoot 'get-secret.ps1') -SecretsFile (Join-Path $RuntimeDir 'secrets.json');$env:MYSQL_PWD=$password
    Get-Content -LiteralPath $sql -Raw|& (Join-Path $RuntimeDir 'mysql\bin\mysql.exe') "--defaults-file=$(Join-Path $RuntimeDir 'my.ini')" -u root -h 127.0.0.1 $DatabaseName
    if($LASTEXITCODE-ne0){throw 'Databaseherstel is mislukt.'}
  }
  $mailDatabase=Join-Path $stage 'mailpit.db'
  if(Test-Path -LiteralPath $mailDatabase -PathType Leaf){Copy-Item -LiteralPath $mailDatabase -Destination (Join-Path $RuntimeDir 'mailpit.db') -Force}
  $configuration=Join-Path $stage 'configuration.json'
  if(Test-Path -LiteralPath $configuration -PathType Leaf){Copy-Item -LiteralPath $configuration -Destination (Join-Path $RuntimeDir 'last-restored-configuration.json') -Force}
}finally{
  $resolved=[IO.Path]::GetFullPath($stage);$tempRoot=[IO.Path]::GetFullPath([IO.Path]::GetTempPath());if($resolved.StartsWith($tempRoot,[StringComparison]::OrdinalIgnoreCase)-and(Test-Path -LiteralPath $resolved)){Remove-Item -LiteralPath $resolved -Recurse -Force}
}
