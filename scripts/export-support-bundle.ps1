[CmdletBinding()]
param(
  [Parameter(Mandatory=$true)][string]$RuntimeDir,
  [Parameter(Mandatory=$true)][string]$OutputFile
)

Set-StrictMode -Version Latest
$ErrorActionPreference='Stop'
$runtime=[IO.Path]::GetFullPath($RuntimeDir);$output=[IO.Path]::GetFullPath($OutputFile)
$temp=Join-Path ([IO.Path]::GetTempPath()) ("LocalDeck-support-"+[guid]::NewGuid().ToString('N'));New-Item -ItemType Directory -Path $temp -Force|Out-Null
try{
  [ordered]@{generatedAt=(Get-Date).ToString('o');windows=[Environment]::OSVersion.VersionString;architecture=$env:PROCESSOR_ARCHITECTURE;powershell=$PSVersionTable.PSVersion.ToString();note='Wachtwoorden, certificaatsleutels, database-inhoud en websitebestanden zijn uitgesloten.'}|ConvertTo-Json|Set-Content -LiteralPath (Join-Path $temp 'system.json') -Encoding UTF8
  $stateFile=Join-Path $runtime 'state.json';if(Test-Path -LiteralPath $stateFile){$state=Get-Content -LiteralPath $stateFile -Raw|ConvertFrom-Json;if($state.settings){$state.settings.smtpUsername='[verwijderd]';$state.settings.updateFeedUrl=$(if($state.settings.updateFeedUrl){'[ingesteld]'}else{''});$state.settings.dataDirectory='[lokaal pad]';$state.settings.projectsDirectory='[lokaal pad]'};foreach($project in @($state.projects)){$project.path='[projectpad]';$project.documentRoot='[projectpad]'};$state|ConvertTo-Json -Depth 20|Set-Content -LiteralPath (Join-Path $temp 'state-sanitized.json') -Encoding UTF8}
  $logs=Join-Path $runtime 'logs';if(Test-Path -LiteralPath $logs){New-Item -ItemType Directory -Path (Join-Path $temp 'logs')|Out-Null;Get-ChildItem -LiteralPath $logs -File|ForEach-Object{Get-Content -LiteralPath $_.FullName -Tail 500|ForEach-Object{$_-replace'[A-Z]:\\[^\s\"]+','[pad]'}|Set-Content -LiteralPath (Join-Path $temp "logs\$($_.Name)") -Encoding UTF8}}
  foreach($config in @('apache\conf\httpd-localdeck.conf','apache\conf\localdeck-vhosts.conf','my.ini','runtime.json')){$source=Join-Path $runtime $config;if(Test-Path -LiteralPath $source){$safe=($config-replace'[\\/]','-');(Get-Content -LiteralPath $source -Raw)-replace'[A-Z]:\\[^\s\"]+','[pad]'|Set-Content -LiteralPath (Join-Path $temp $safe) -Encoding UTF8}}
  if(Test-Path -LiteralPath $output){Remove-Item -LiteralPath $output -Force};Compress-Archive -Path (Join-Path $temp '*') -DestinationPath $output -CompressionLevel Optimal
}finally{if(Test-Path -LiteralPath $temp){Remove-Item -LiteralPath $temp -Recurse -Force}}
Write-Host $output

