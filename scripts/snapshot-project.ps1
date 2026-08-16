[CmdletBinding()]
param(
  [Parameter(Mandatory)][string]$RuntimeDir,
  [Parameter(Mandatory)][string]$ProjectDir,
  [Parameter(Mandatory)][string]$ProjectId,
  [Parameter(Mandatory)][string]$ProjectName,
  [Parameter(Mandatory)][string]$SnapshotId,
  [string]$DatabaseName = '',
  [string]$Description = ''
)
$ErrorActionPreference='Stop'
$RuntimeDir=[IO.Path]::GetFullPath($RuntimeDir);$ProjectDir=[IO.Path]::GetFullPath($ProjectDir)
if(!(Test-Path -LiteralPath $ProjectDir -PathType Container)){throw 'Projectmap bestaat niet.'}
$snapshots=Join-Path $RuntimeDir 'snapshots';New-Item -ItemType Directory -Path $snapshots -Force|Out-Null
$stage=Join-Path ([IO.Path]::GetTempPath()) "LocalDeck-snapshot-$SnapshotId";New-Item -ItemType Directory -Path (Join-Path $stage 'project') -Force|Out-Null
try{
  & robocopy.exe $ProjectDir (Join-Path $stage 'project') /E /R:1 /W:1 /XD .git node_modules .localdeck-cache /XF '*.log' | Out-Null
  if($LASTEXITCODE-ge8){throw "Projectbestanden kopiëren is mislukt (robocopy $LASTEXITCODE)."}
  $includesDatabase=$false
  if($DatabaseName-and(Test-Path (Join-Path $RuntimeDir 'mysql\bin\mysqldump.exe'))){
    $secretScript=Join-Path $PSScriptRoot 'get-secret.ps1';$password=& $secretScript -SecretsFile (Join-Path $RuntimeDir 'secrets.json');$env:MYSQL_PWD=$password
    $dump=Join-Path $RuntimeDir 'mysql\bin\mysqldump.exe';$output=Join-Path $stage 'database.sql'
    $process=Start-Process -FilePath $dump -ArgumentList @("--defaults-file=$(Join-Path $RuntimeDir 'my.ini')",'-u','root','-h','127.0.0.1','--single-transaction','--routines','--events',$DatabaseName) -WindowStyle Hidden -RedirectStandardOutput $output -Wait -PassThru
    if($process.ExitCode-ne0){throw 'Database-snapshot is mislukt.'};$includesDatabase=$true
  }
  $includesConfiguration=$false;$includesMail=$false
  $stateFile=Join-Path $RuntimeDir 'state.json'
  if(Test-Path -LiteralPath $stateFile){
    $state=Get-Content -LiteralPath $stateFile -Raw -Encoding UTF8|ConvertFrom-Json
    $project=$state.projects|Where-Object{$_.id-eq$ProjectId}|Select-Object -First 1
    $addresses=@($state.mailAddresses|Where-Object{!$_.projectId-or$_.projectId-eq$ProjectId})
    [ordered]@{schemaVersion=1;project=$project;servicePorts=@($state.services|Select-Object id,port,version);settings=[ordered]@{defaultPhpVersion=$state.settings.defaultPhpVersion;localDomainSuffix=$state.settings.localDomainSuffix;dnsMode=$state.settings.dnsMode;mailMode=$state.settings.mailMode;smtpPort=$state.settings.smtpPort};mailAddresses=$addresses}|ConvertTo-Json -Depth 20|Set-Content (Join-Path $stage 'configuration.json') -Encoding UTF8
    $includesConfiguration=$true
  }
  $mailDatabase=Join-Path $RuntimeDir 'mailpit.db'
  if(Test-Path -LiteralPath $mailDatabase -PathType Leaf){Copy-Item -LiteralPath $mailDatabase -Destination (Join-Path $stage 'mailpit.db') -Force;$includesMail=$true}
  $manifest=[ordered]@{schemaVersion=2;id=$SnapshotId;projectId=$ProjectId;projectName=$ProjectName;projectDir=$ProjectDir;databaseName=$DatabaseName;description=$Description;createdAt=(Get-Date).ToString('o');includesDatabase=$includesDatabase;includesConfiguration=$includesConfiguration;includesMail=$includesMail}
  $manifest|ConvertTo-Json -Depth 4|Set-Content (Join-Path $stage 'snapshot.json') -Encoding UTF8
  $archive=Join-Path $snapshots "$SnapshotId.localdeck-snapshot.zip";Compress-Archive -Path (Join-Path $stage '*') -DestinationPath $archive -CompressionLevel Optimal -Force
  $metadata=[ordered]@{id=$SnapshotId;projectId=$ProjectId;projectName=$ProjectName;file=$archive;size="{0:N1} MB"-f((Get-Item $archive).Length/1MB);createdAt=(Get-Date).ToString('o');includesDatabase=$includesDatabase;includesConfiguration=$includesConfiguration;includesMail=$includesMail;description=$Description}
  $metadataFile=Join-Path $snapshots "$SnapshotId.snapshot.json";$metadata|ConvertTo-Json|Set-Content $metadataFile -Encoding UTF8
  Write-Output $metadataFile
}finally{
  $resolved=[IO.Path]::GetFullPath($stage);$tempRoot=[IO.Path]::GetFullPath([IO.Path]::GetTempPath());if($resolved.StartsWith($tempRoot,[StringComparison]::OrdinalIgnoreCase)-and(Test-Path -LiteralPath $resolved)){Remove-Item -LiteralPath $resolved -Recurse -Force}
}
