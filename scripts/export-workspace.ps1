[CmdletBinding()]
param(
  [Parameter(Mandatory=$true)][string]$RuntimeDir,
  [Parameter(Mandatory=$true)][string]$ProjectsDir,
  [Parameter(Mandatory=$true)][string]$OutputFile
)

Set-StrictMode -Version Latest
$ErrorActionPreference='Stop'
$runtime=[IO.Path]::GetFullPath($RuntimeDir);$projects=[IO.Path]::GetFullPath($ProjectsDir);$output=[IO.Path]::GetFullPath($OutputFile)
if(!(Test-Path -LiteralPath $runtime)-or!(Test-Path -LiteralPath $projects)){throw 'De LocalDeck-werkruimte is niet compleet.'}
$temp=Join-Path ([IO.Path]::GetTempPath()) ("LocalDeck-workspace-"+[guid]::NewGuid().ToString('N'))
$payload=Join-Path $temp 'LocalDeck-Workspace';New-Item -ItemType Directory -Path $payload -Force|Out-Null
try{
  Copy-Item -LiteralPath $projects -Destination (Join-Path $payload 'websites') -Recurse -Force
  foreach($name in @('backups','snapshots')){$source=Join-Path $runtime $name;if(Test-Path -LiteralPath $source){Copy-Item -LiteralPath $source -Destination (Join-Path $payload $name) -Recurse -Force}}
  $stateFile=Join-Path $runtime 'state.json';if(Test-Path -LiteralPath $stateFile){
    $state=Get-Content -LiteralPath $stateFile -Raw|ConvertFrom-Json
    if($state.settings){$state.settings.smtpUsername='';$state.settings.updateFeedUrl='';$state.settings.dataDirectory='[wordt bij import opnieuw ingesteld]';$state.settings.projectsDirectory='[wordt bij import opnieuw ingesteld]'}
    if($state.PSObject.Properties['webhookEndpoints']){foreach($endpoint in @($state.webhookEndpoints)){if($endpoint.PSObject.Properties['secret']){$endpoint.secret='[wordt na import vernieuwd]'}}}
    foreach($transient in @('webhookEvents','apiRuns','replayCaptures')){if($state.PSObject.Properties[$transient]){$state.$transient=@()}}
    if($state.PSObject.Properties['atomicUpdate']-and$state.atomicUpdate){foreach($pathName in @('stagedFile','recoveryFile')){if($state.atomicUpdate.PSObject.Properties[$pathName]){$state.atomicUpdate.$pathName=''}}}
    foreach($project in @($state.projects)){
      if($project.PSObject.Properties['repositoryUrl']-and$project.repositoryUrl){try{$repository=[UriBuilder]$project.repositoryUrl;$repository.UserName='';$repository.Password='';$project.repositoryUrl=$repository.Uri.AbsoluteUri}catch{$project.repositoryUrl=''}}
      if($project.PSObject.Properties['profile']-and$project.profile-and$project.profile.PSObject.Properties['env']-and$project.profile.env){foreach($property in @($project.profile.env.PSObject.Properties)){if($property.Name-match'(?i)(password|passwd|pwd|token|secret|api[_-]?key|authorization|private)'){$project.profile.env.PSObject.Properties.Remove($property.Name)}}}
    }
    $state|ConvertTo-Json -Depth 20|Set-Content -LiteralPath (Join-Path $payload 'state.portable.json') -Encoding UTF8
  }
  $mail=Join-Path $runtime 'mailpit.db';if(Test-Path -LiteralPath $mail){Copy-Item -LiteralPath $mail -Destination (Join-Path $payload 'mailpit.db')}
  $files=Get-ChildItem -LiteralPath $payload -Recurse -File;[ordered]@{schemaVersion=1;product='LocalDeck';exportedAt=(Get-Date).ToString('o');containsSecrets=$false;files=@($files|ForEach-Object{[ordered]@{path=$_.FullName.Substring($payload.Length+1);bytes=$_.Length;sha256=(Get-FileHash -LiteralPath $_.FullName -Algorithm SHA256).Hash}})}|ConvertTo-Json -Depth 8|Set-Content -LiteralPath (Join-Path $payload 'workspace-manifest.json') -Encoding UTF8
  $parent=Split-Path $output;New-Item -ItemType Directory -Path $parent -Force|Out-Null
  if(Test-Path -LiteralPath $output){Remove-Item -LiteralPath $output -Force}
  Compress-Archive -LiteralPath $payload -DestinationPath $output -CompressionLevel Optimal
}finally{if(Test-Path -LiteralPath $temp){Remove-Item -LiteralPath $temp -Recurse -Force}}
Write-Host $output
