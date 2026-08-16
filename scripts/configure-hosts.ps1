[CmdletBinding()]
param(
  [Parameter(Mandatory=$true)][string]$RuntimeDir,
  [switch]$ForceCertificates
)

Set-StrictMode -Version Latest
$ErrorActionPreference='Stop'
$runtime=[IO.Path]::GetFullPath($RuntimeDir)
$stateFile=Join-Path $runtime 'state.json'
if(!(Test-Path -LiteralPath $stateFile)){throw 'LocalDeck state.json ontbreekt.'}
$state=Get-Content -LiteralPath $stateFile -Raw|ConvertFrom-Json
$projects=@($state.projects)
$mkcert=Join-Path $runtime 'tools\mkcert.exe'
if(!(Test-Path -LiteralPath $mkcert)){throw 'De ingebouwde mkcert-runtime ontbreekt.'}

$hosts=Join-Path $env:SystemRoot 'System32\drivers\etc\hosts'
$hostsContent=Get-Content -LiteralPath $hosts -Raw
$usesHosts=!$state.settings.dnsMode-or$state.settings.dnsMode-eq'hosts'
$testDomains=@($projects|Where-Object{$_.domain-like'*.test'}|ForEach-Object{[string]$_.domain})
$start='# LocalDeck BEGIN';$end='# LocalDeck END';$pattern='(?ms)^# LocalDeck BEGIN.*?^# LocalDeck END\s*'
$expectedLines=@($start);foreach($domain in $testDomains){$expectedLines+="127.0.0.1`t$domain";$expectedLines+="::1`t$domain"};$expectedLines+=$end
$expectedBlock=($expectedLines-join"`r`n").Trim();$currentMatch=[regex]::Match($hostsContent,$pattern);$currentBlock=if($currentMatch.Success){$currentMatch.Value.Trim()}else{''};$hostsNeedUpdate=$usesHosts-and$currentBlock-ne$expectedBlock
$needsCertificates=@($projects|Where-Object{$_.secure-ne$false}).Count-gt0

$certRoot=Join-Path $runtime 'certs\projects'
$trustMarker=Join-Path $runtime 'certs\local-ca-trusted.json'
$identity=[Security.Principal.WindowsIdentity]::GetCurrent()
$principal=New-Object Security.Principal.WindowsPrincipal($identity)
$isAdmin=$principal.IsInRole([Security.Principal.WindowsBuiltInRole]::Administrator)
function Test-LocalCaTrusted {
  try{
    $caRoot=(& $mkcert -CAROOT|Select-Object -Last 1).Trim();$rootCertificate=Join-Path $caRoot 'rootCA.pem'
    if(!(Test-Path -LiteralPath $rootCertificate)){return $false}
    $certificate=New-Object System.Security.Cryptography.X509Certificates.X509Certificate2($rootCertificate)
    $store=New-Object System.Security.Cryptography.X509Certificates.X509Store('Root','CurrentUser');$store.Open('ReadOnly')
    try{return [bool]($store.Certificates|Where-Object{$_.Thumbprint-eq$certificate.Thumbprint}|Select-Object -First 1)}finally{$store.Close()}
  }catch{return $false}
}
$caTrusted=Test-LocalCaTrusted
$needsAdmin=$hostsNeedUpdate-or($needsCertificates-and!$caTrusted)
if($needsAdmin-and!$isAdmin){
  $arguments="-NoLogo -NoProfile -NonInteractive -WindowStyle Hidden -ExecutionPolicy Bypass -File `"$PSCommandPath`" -RuntimeDir `"$runtime`""
  if($ForceCertificates){$arguments+=' -ForceCertificates'}
  $elevated=Start-Process powershell.exe -Verb RunAs -WindowStyle Hidden -ArgumentList $arguments -Wait -PassThru
  exit $elevated.ExitCode
}

New-Item -ItemType Directory -Path $certRoot -Force|Out-Null
if($needsCertificates-and!$caTrusted){
  & $mkcert -install|Out-Host
  if($LASTEXITCODE-ne0){throw 'De lokale HTTPS-certificaatautoriteit kon niet in Windows worden vertrouwd.'}
  $caTrusted=Test-LocalCaTrusted
  if(!$caTrusted){throw 'Windows bevestigt de lokale HTTPS-certificaatautoriteit niet als vertrouwd.'}
}
if($caTrusted-and!(Test-Path -LiteralPath $trustMarker)){
  [ordered]@{trustedAt=(Get-Date).ToString('o');machine=$env:COMPUTERNAME;user=$identity.Name}|ConvertTo-Json|Set-Content -LiteralPath $trustMarker -Encoding UTF8
}

$activeIds=New-Object 'System.Collections.Generic.HashSet[string]' ([StringComparer]::OrdinalIgnoreCase)
foreach($project in $projects){
  if($project.secure-eq$false){continue}
  $id=[string]$project.id;$domain=([string]$project.domain).Trim().ToLowerInvariant()
  if($id-notmatch'^[a-zA-Z0-9-]{1,80}$'){throw "Ongeldig project-ID voor HTTPS: $id"}
  if($domain-notmatch'^(?=.{1,253}$)(?:[a-z0-9](?:[a-z0-9-]{0,61}[a-z0-9])?\.)+(?:localhost|test)$'){throw "Ongeldig lokaal domein voor HTTPS: $domain"}
  [void]$activeIds.Add($id)
  $directory=Join-Path $certRoot $id;$certificateFile=Join-Path $directory 'certificate.pem';$keyFile=Join-Path $directory 'private-key.pem';$metadataFile=Join-Path $directory 'certificate.json'
  New-Item -ItemType Directory -Path $directory -Force|Out-Null
  $renew=$ForceCertificates-or!(Test-Path -LiteralPath $certificateFile)-or!(Test-Path -LiteralPath $keyFile)-or!(Test-Path -LiteralPath $metadataFile)
  if(!$renew){
    try{$metadata=Get-Content -LiteralPath $metadataFile -Raw|ConvertFrom-Json;$renew=([string]$metadata.domain-ne$domain)-or([datetime]$metadata.renewAfter-lt(Get-Date))}catch{$renew=$true}
  }
  if($renew){
    & $mkcert -cert-file $certificateFile -key-file $keyFile $domain|Out-Host
    if($LASTEXITCODE-ne0){throw "Het lokale SSL-certificaat voor $domain kon niet worden gemaakt."}
    [ordered]@{projectId=$id;domain=$domain;generatedAt=(Get-Date).ToString('o');renewAfter=(Get-Date).AddMonths(18).ToString('o')}|ConvertTo-Json|Set-Content -LiteralPath $metadataFile -Encoding UTF8
  }
  & icacls.exe $directory /inheritance:r /grant:r "$($identity.Name):(OI)(CI)F" 'SYSTEM:(OI)(CI)F'|Out-Null
}

foreach($directory in Get-ChildItem -LiteralPath $certRoot -Directory -ErrorAction SilentlyContinue){if(!$activeIds.Contains($directory.Name)){Remove-Item -LiteralPath $directory.FullName -Recurse -Force}}

if($usesHosts-and($hostsNeedUpdate-or$isAdmin)){
  $clean=[regex]::Replace($hostsContent,$pattern,'')
  $backup="$hosts.localdeck.bak";if(!(Test-Path -LiteralPath $backup)){Copy-Item -LiteralPath $hosts -Destination $backup}
  Set-Content -LiteralPath $hosts -Value ($clean.TrimEnd()+"`r`n`r`n"+($expectedLines-join"`r`n")+"`r`n") -Encoding ASCII
  ipconfig /flushdns|Out-Null
}

Write-Host "LocalDeck HTTPS: $($activeIds.Count) projectspecifieke certificaten gereed."
