[CmdletBinding()]
param([Parameter(Mandatory=$true)][string]$RuntimeDir)
$ErrorActionPreference='Stop'
$RuntimeDir=[IO.Path]::GetFullPath($RuntimeDir)
$mysql=Join-Path $RuntimeDir 'mysql\bin\mysql.exe'
$dump=Join-Path $RuntimeDir 'mysql\bin\mysqldump.exe'
$ini=Join-Path $RuntimeDir 'my.ini'
if(!(Test-Path $mysql)-or!(Test-Path $dump)){exit 0}
$secretScript=Join-Path $RuntimeDir 'automation\get-secret.ps1'
$secrets=Join-Path $RuntimeDir 'secrets.json'
if(Test-Path $secretScript){$env:MYSQL_PWD=(& $secretScript -SecretsFile $secrets).Trim()}
try{
  $state=Get-Content (Join-Path $RuntimeDir 'state.json') -Raw|ConvertFrom-Json
  $port=($state.services|Where-Object id-eq'mysql'|Select-Object -First 1).port
  $names=& $mysql "--defaults-file=$ini" -u root -h 127.0.0.1 -P $port -N -e "SELECT SCHEMA_NAME FROM information_schema.SCHEMATA WHERE SCHEMA_NAME NOT IN ('mysql','information_schema','performance_schema','sys')"
  if($LASTEXITCODE-ne0){exit 0}
  $dir=Join-Path $RuntimeDir 'backups';New-Item -ItemType Directory -Force $dir|Out-Null
  $stamp=(Get-Date).ToUniversalTime().ToString('yyyy-MM-ddTHH-mm-ssZ')
  foreach($name in $names){if($name){
    $output=Join-Path $dir "$name-$stamp.sql"
    $arguments=@("--defaults-file=$ini",'-u','root','-h','127.0.0.1','-P',"$port",'--single-transaction','--routines','--events',"$name")
    $process=Start-Process -FilePath $dump -ArgumentList $arguments -WindowStyle Hidden -RedirectStandardOutput $output -Wait -PassThru
    if($process.ExitCode-ne0-and(Test-Path $output)){Remove-Item -LiteralPath $output -Force}
  }}
  $retention=[Math]::Max(1,[int]$state.settings.backupRetention)
  foreach($name in $names){Get-ChildItem $dir -Filter "$name-*.sql"|Sort-Object LastWriteTime -Descending|Select-Object -Skip $retention|Remove-Item -Force}
}finally{Remove-Item Env:MYSQL_PWD -ErrorAction SilentlyContinue}
