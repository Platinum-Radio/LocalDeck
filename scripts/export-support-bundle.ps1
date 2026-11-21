[CmdletBinding()]
param(
  [Parameter(Mandatory=$true)][string]$RuntimeDir,
  [Parameter(Mandatory=$true)][string]$OutputFile
)

Set-StrictMode -Version Latest
$ErrorActionPreference='Stop'
$runtime=[IO.Path]::GetFullPath($RuntimeDir)
$output=[IO.Path]::GetFullPath($OutputFile)
$temp=Join-Path ([IO.Path]::GetTempPath()) ("LocalDeck-support-"+[guid]::NewGuid().ToString('N'))
New-Item -ItemType Directory -Path $temp -Force|Out-Null

function Get-SafeProperty($Object,[string]$Name,$Default=$null){
  if($null-ne$Object-and$Object.PSObject.Properties[$Name]){return $Object.$Name}
  return $Default
}

function Protect-SupportText([string]$Value){
  if([string]::IsNullOrEmpty($Value)){return $Value}
  $result=$Value
  foreach($privatePath in @([Environment]::GetFolderPath('UserProfile'),$runtime)){
    if($privatePath){$result=[regex]::Replace($result,[regex]::Escape($privatePath),'[lokaal-pad]',[Text.RegularExpressions.RegexOptions]::IgnoreCase)}
  }
  $result=[regex]::Replace($result,'(?i)Bearer\s+[A-Za-z0-9._~+/-]+=*','Bearer [verwijderd]')
  $result=[regex]::Replace($result,'(?i)([?&](?:password|passwd|pwd|token|secret|api[_-]?key|authorization)=)[^&\s"'']+','$1[verwijderd]')
  $result=[regex]::Replace($result,'(?i)\b(password|passwd|pwd|token|secret|api[_-]?key|authorization)\b\s*[:=]\s*[^\s,;"'']+','$1=[verwijderd]')
  $result=[regex]::Replace($result,'(?i)\b[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,}\b','[e-mail]')
  $result=[regex]::Replace($result,'\b(?:\d{1,3}\.){3}\d{1,3}\b','[ip-adres]')
  $result=[regex]::Replace($result,'(?i)\b[a-z0-9-]+(?:\.[a-z0-9-]+)*\.(?:test|localhost)\b','[lokaal-domein]')
  $result=[regex]::Replace($result,'(?i)\b[A-Z]:\\[^\r\n"<>|]+','[lokaal-pad]')
  return $result
}

try{
  [ordered]@{
    schemaVersion=2
    generatedAt=(Get-Date).ToString('o')
    windows=[Environment]::OSVersion.VersionString
    architecture=$env:PROCESSOR_ARCHITECTURE
    powershell=$PSVersionTable.PSVersion.ToString()
    privacy='Wachtwoorden, tokens, e-mailadressen, privépaden, IP-adressen, certificaatsleutels, database-inhoud en websitebestanden zijn uitgesloten of vervangen.'
  }|ConvertTo-Json|Set-Content -LiteralPath (Join-Path $temp 'system.json') -Encoding UTF8

  $stateFile=Join-Path $runtime 'state.json'
  if(Test-Path -LiteralPath $stateFile){
    $state=Get-Content -LiteralPath $stateFile -Raw|ConvertFrom-Json
    $settings=Get-SafeProperty $state 'settings'
    $projects=@(Get-SafeProperty $state 'projects' @())
    $services=@(Get-SafeProperty $state 'services' @())
    $summary=[ordered]@{
      schemaVersion=2
      generatedAt=(Get-Date).ToString('o')
      installation=Get-SafeProperty $state 'installation'
      counts=[ordered]@{
        projects=$projects.Count
        databases=@(Get-SafeProperty $state 'databases' @()).Count
        mailAddresses=@(Get-SafeProperty $state 'mailAddresses' @()).Count
        snapshots=@(Get-SafeProperty $state 'snapshots' @()).Count
      }
      settings=[ordered]@{
        language=Get-SafeProperty $settings 'language'
        folderMode=Get-SafeProperty $settings 'folderMode'
        runtimeMode=Get-SafeProperty $settings 'runtimeMode'
        defaultPhpVersion=Get-SafeProperty $settings 'defaultPhpVersion'
        autoBackup=Get-SafeProperty $settings 'autoBackup'
        backupSchedule=Get-SafeProperty $settings 'backupSchedule'
        backupRetention=Get-SafeProperty $settings 'backupRetention'
        webControlEnabled=Get-SafeProperty $settings 'webControlEnabled'
        webControlPort=Get-SafeProperty $settings 'webControlPort'
        dnsMode=Get-SafeProperty $settings 'dnsMode'
        mailMode=Get-SafeProperty $settings 'mailMode'
        atomicUpdates=Get-SafeProperty $settings 'atomicUpdates'
        requireSignedUpdates=Get-SafeProperty $settings 'requireSignedUpdates'
        offlineFirst=Get-SafeProperty $settings 'offlineFirst'
        lanSharingEnabled=Get-SafeProperty $settings 'lanSharingEnabled'
      }
      services=@($services|ForEach-Object{[ordered]@{id=Get-SafeProperty $_ 'id';version=Get-SafeProperty $_ 'version';port=Get-SafeProperty $_ 'port';installed=Get-SafeProperty $_ 'installed'}})
      projects=@($projects|ForEach-Object{[ordered]@{template=Get-SafeProperty $_ 'template';phpVersion=Get-SafeProperty $_ 'phpVersion';secure=Get-SafeProperty $_ 'secure';isolated=Get-SafeProperty (Get-SafeProperty $_ 'profile') 'isolated'}})
      crashRecovery=Get-SafeProperty $state 'crashRecovery'
      atomicUpdate=[ordered]@{status=Get-SafeProperty (Get-SafeProperty $state 'atomicUpdate') 'status';currentVersion=Get-SafeProperty (Get-SafeProperty $state 'atomicUpdate') 'currentVersion';lastKnownGoodVersion=Get-SafeProperty (Get-SafeProperty $state 'atomicUpdate') 'lastKnownGoodVersion'}
    }
    (Protect-SupportText ($summary|ConvertTo-Json -Depth 12))|Set-Content -LiteralPath (Join-Path $temp 'state-sanitized.json') -Encoding UTF8
  }

  $logs=Join-Path $runtime 'logs'
  if(Test-Path -LiteralPath $logs){
    $safeLogs=Join-Path $temp 'logs';New-Item -ItemType Directory -Path $safeLogs -Force|Out-Null
    Get-ChildItem -LiteralPath $logs -File|ForEach-Object{
      $safeName=($_.Name-replace'[^A-Za-z0-9._-]','_')
      @(Get-Content -LiteralPath $_.FullName -Tail 500|ForEach-Object{Protect-SupportText ([string]$_)})|Set-Content -LiteralPath (Join-Path $safeLogs $safeName) -Encoding UTF8
    }
  }

  foreach($config in @('apache\conf\httpd-localdeck.conf','apache\conf\localdeck-vhosts.conf','my.ini','runtime.json')){
    $source=Join-Path $runtime $config
    if(Test-Path -LiteralPath $source){
      $safe=($config-replace'[\\/]','-')
      Protect-SupportText (Get-Content -LiteralPath $source -Raw)|Set-Content -LiteralPath (Join-Path $temp $safe) -Encoding UTF8
    }
  }

  $files=Get-ChildItem -LiteralPath $temp -File -Recurse
  [ordered]@{schemaVersion=2;generatedAt=(Get-Date).ToString('o');redacted=$true;files=@($files|ForEach-Object{[ordered]@{path=$_.FullName.Substring($temp.Length+1);bytes=$_.Length;sha256=(Get-FileHash -LiteralPath $_.FullName -Algorithm SHA256).Hash}})}|ConvertTo-Json -Depth 6|Set-Content -LiteralPath (Join-Path $temp 'support-manifest.json') -Encoding UTF8
  $parent=Split-Path -Parent $output;if($parent){New-Item -ItemType Directory -Path $parent -Force|Out-Null}
  if(Test-Path -LiteralPath $output){Remove-Item -LiteralPath $output -Force}
  Compress-Archive -Path (Join-Path $temp '*') -DestinationPath $output -CompressionLevel Optimal
}finally{
  if(Test-Path -LiteralPath $temp){Remove-Item -LiteralPath $temp -Recurse -Force}
}
Write-Host $output
