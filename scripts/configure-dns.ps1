[CmdletBinding()]param([Parameter(Mandatory)][string]$RuntimeDir,[ValidateSet('Enable','Disable')][string]$Action='Enable')
$ErrorActionPreference='Stop';$RuntimeDir=[IO.Path]::GetFullPath($RuntimeDir)
$identity=[Security.Principal.WindowsIdentity]::GetCurrent();$principal=New-Object Security.Principal.WindowsPrincipal($identity)
if(!$principal.IsInRole([Security.Principal.WindowsBuiltInRole]::Administrator)){$arguments="-NoLogo -NoProfile -NonInteractive -WindowStyle Hidden -ExecutionPolicy Bypass -File `"$PSCommandPath`" -RuntimeDir `"$RuntimeDir`" -Action $Action";$process=Start-Process powershell.exe -Verb RunAs -WindowStyle Hidden -ArgumentList $arguments -Wait -PassThru;exit $process.ExitCode}
$rules=Get-DnsClientNrptRule -ErrorAction SilentlyContinue|Where-Object{$_.Comment-eq'LocalDeck .test DNS'};foreach($rule in $rules){Remove-DnsClientNrptRule -Name $rule.Name -Force -ErrorAction SilentlyContinue}
if($Action-eq'Enable'){Add-DnsClientNrptRule -Namespace '.test' -NameServers '127.0.0.1' -Comment 'LocalDeck .test DNS' -DisplayName 'LocalDeck .test domains'}
ipconfig.exe /flushdns|Out-Null
