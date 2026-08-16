[CmdletBinding()]
param(
  [Parameter(Mandatory=$true)][string]$RuntimeDir,
  [ValidateSet('Install','Uninstall','Start','Stop','StartAll','StopAll','Toggle','Refresh','Status')][string]$Action='Status',
  [ValidateSet('apache','php','php82','php83','php84','php85','mysql','phpmyadmin','mail','redis','dns','control','all')][string]$Service='all',
  [string]$AppExe=''
)
$ErrorActionPreference='Stop'
$RuntimeDir=[IO.Path]::GetFullPath($RuntimeDir)
$identity=[Security.Principal.WindowsIdentity]::GetCurrent()
$principal=New-Object Security.Principal.WindowsPrincipal($identity)
$requiresAdmin=$Action-in@('Install','Uninstall','Start','Stop','StartAll','StopAll','Toggle','Refresh')
if($requiresAdmin-and!$principal.IsInRole([Security.Principal.WindowsBuiltInRole]::Administrator)){
  $arguments="-NoLogo -NoProfile -NonInteractive -WindowStyle Hidden -ExecutionPolicy Bypass -File `"$PSCommandPath`" -RuntimeDir `"$RuntimeDir`" -Action $Action -Service $Service"
  if($AppExe){$arguments+=" -AppExe `"$AppExe`""}
  $elevated=Start-Process powershell.exe -Verb RunAs -WindowStyle Hidden -ArgumentList $arguments -Wait -PassThru
  exit $elevated.ExitCode
}
$stateFile=Join-Path $RuntimeDir 'state.json'
$wrapper=Join-Path $RuntimeDir 'service-wrapper\WinSW.NET4.exe'
$serviceDir=Join-Path $RuntimeDir 'services'
if(!(Test-Path $stateFile)){throw 'LocalDeck state.json ontbreekt.'}
if(!(Test-Path $wrapper)){throw 'De Windows-servicewrapper ontbreekt. Herstel eerst de runtime.'}
New-Item -ItemType Directory -Force $serviceDir|Out-Null
$state=Get-Content $stateFile -Raw|ConvertFrom-Json
function Escape-Xml([string]$Value){return [Security.SecurityElement]::Escape($Value)}
function Port([string]$Id,[int]$Fallback){$item=$state.services|Where-Object id-eq$Id|Select-Object -First 1;if($item){return[int]$item.port};return $Fallback}
function Smtp-Port{if($state.settings.smtpPort){return[int]$state.settings.smtpPort};return 1025}
function Mail-Max{if($state.settings.mailMaxMessages){return[int]$state.settings.mailMaxMessages};return 1000}
function Mail-MaxAge{if($state.settings.mailMaxAgeDays){return"$([int]$state.settings.mailMaxAgeDays)d"};return''}
function MySql-Password{$script=Join-Path $RuntimeDir 'automation\get-secret.ps1';$secrets=Join-Path $RuntimeDir 'secrets.json';if(!(Test-Path $script)-or!(Test-Path $secrets)){throw 'Het beveiligde MySQL-geheim ontbreekt.'};$password=[string](& $script -SecretsFile $secrets);if(!$password.Trim()){throw 'Het MySQL-wachtwoord kon niet veilig worden gelezen.'};return $password.Trim()}
function Php-Dir{$version=$state.settings.defaultPhpVersion;$candidate=Join-Path $RuntimeDir "php-$version";if(Test-Path $candidate){return $candidate};return(Join-Path $RuntimeDir 'php')}
function Php-PoolPort([string]$Version){$base=Port php 9000;if($Version-eq$state.settings.defaultPhpVersion){return $base};$supported=@('8.2','8.3','8.4','8.5');$index=[Array]::IndexOf($supported,$Version);if($index-lt0){$index=0};return($base+10+$index)}
function Service-Definition([string]$Id){
  if($Id-match'^php(\d)(\d)$'){$version="$($Matches[1]).$($Matches[2])";$directory=Join-Path $RuntimeDir "php-$version";return@{Name="PHP $version FastCGI";Exe=Join-Path $directory 'php-cgi.exe';Args="-b 127.0.0.1:$(Php-PoolPort $version)";Env='<env name="PHP_FCGI_CHILDREN" value="2" />'}}
  $php=Php-Dir
  switch($Id){
    'apache' {@{Name='Apache';Exe=Join-Path $RuntimeDir 'apache\bin\httpd.exe';Args="-f `"$(Join-Path $RuntimeDir 'apache\conf\httpd-localdeck.conf')`" -DFOREGROUND"}}
    'php' {@{Name='PHP FastCGI';Exe=Join-Path $php 'php-cgi.exe';Args="-b 127.0.0.1:$(Port php 9000)";Env='<env name="PHP_FCGI_CHILDREN" value="2" />'}}
    'mysql' {@{Name='MySQL';Exe=Join-Path $RuntimeDir 'mysql\bin\mysqld.exe';Args="--defaults-file=`"$(Join-Path $RuntimeDir 'my.ini')`" --port=$(Port mysql 3306) --console"}}
    'phpmyadmin' {$password=MySql-Password;@{Name='phpMyAdmin';Exe=Join-Path $php 'php.exe';Args="-S 127.0.0.1:$(Port phpmyadmin 8080) -t `"$(Join-Path $RuntimeDir 'phpmyadmin')`"";Env="<env name=`"LOCALDECK_MYSQL_PASSWORD`" value=`"$(Escape-Xml $password)`" />"}}
    'mail' {$maxAge=Mail-MaxAge;@{Name='Mailpit';Exe=Join-Path $RuntimeDir 'mailpit\mailpit.exe';Args="--listen 127.0.0.1:$(Port mail 8025) --smtp 127.0.0.1:$(Smtp-Port) --database `"$(Join-Path $RuntimeDir 'mailpit.db')`" --disable-version-check --max $(Mail-Max)$(if($maxAge){" --max-age $maxAge"})"}}
    'redis' {@{Name='Redis';Exe=Join-Path $RuntimeDir 'redis\redis-server.exe';Args="--bind 127.0.0.1 --port $(Port redis 6379)"}}
    'dns' {if(!$AppExe){throw 'Het LocalDeck-programmapad ontbreekt voor lokale DNS.'};@{Name='Local DNS';Exe=$AppExe;Args="--localdeck-dns-service --localdeck-runtime-dir=`"$RuntimeDir`""}}
    'control' {if(!$AppExe){throw 'Het LocalDeck-programmapad ontbreekt voor webbeheer.'};@{Name='Web Control';Exe=$AppExe;Args="--localdeck-control-service --localdeck-runtime-dir=`"$RuntimeDir`""}}
  }
}
function Wrapper-Path([string]$Id){return Join-Path $serviceDir "LocalDeck-$Id.exe"}
function Xml-Path([string]$Id){return Join-Path $serviceDir "LocalDeck-$Id.xml"}
function Write-ServiceConfig([string]$Id){
  $definition=Service-Definition $Id
  if(!(Test-Path $definition.Exe)){throw "$($definition.Name) is niet geïnstalleerd."}
  $serviceId="LocalDeck$($Id.Substring(0,1).ToUpper()+$Id.Substring(1))"
  $xml=@"
<service>
  <id>$serviceId</id>
  <name>LocalDeck $($definition.Name)</name>
  <description>LocalDeck Windows development service: $($definition.Name)</description>
  <executable>$(Escape-Xml $definition.Exe)</executable>
  <arguments>$(Escape-Xml $definition.Args)</arguments>
  $($definition.Env)
  <workingdirectory>$(Escape-Xml (Split-Path $definition.Exe))</workingdirectory>
  <startmode>Automatic</startmode>
  <delayedAutoStart>true</delayedAutoStart>
  <hidewindow>true</hidewindow>
  <stoptimeout>20 sec</stoptimeout>
  <onfailure action="restart" delay="5 sec" />
  <onfailure action="restart" delay="15 sec" />
  <onfailure action="none" />
  <resetfailure>1 hour</resetfailure>
  <logpath>$(Escape-Xml (Join-Path $RuntimeDir 'logs'))</logpath>
  <log mode="roll-by-size"><sizeThreshold>10240</sizeThreshold><keepFiles>5</keepFiles></log>
</service>
"@
  New-Item -ItemType Directory -Force (Join-Path $RuntimeDir 'logs')|Out-Null
  Copy-Item $wrapper (Wrapper-Path $Id) -Force
  Set-Content (Xml-Path $Id) $xml -Encoding UTF8
  if($Id-eq'phpmyadmin'){& icacls.exe (Xml-Path $Id) /inheritance:r /grant:r "$env:USERNAME`:F" 'SYSTEM:F'|Out-Null}
}
function Invoke-Wrapper([string]$Id,[string]$Command){
  $exe=Wrapper-Path $Id
  if(!(Test-Path $exe)){Write-ServiceConfig $Id}
  & $exe $Command
  if($LASTEXITCODE-ne0-and$Command-notin@('stop','uninstall')){throw "Serviceopdracht $Command voor $Id is mislukt."}
}
$phpPools=@('php');$defaultPhp=[string]$state.settings.defaultPhpVersion;foreach($version in @($state.projects|ForEach-Object{$_.phpVersion}|Where-Object{$_-and$_-ne$defaultPhp}|Select-Object -Unique)){if($version-match'^(\d)\.(\d)$'-and(Test-Path (Join-Path $RuntimeDir "php-$version\php-cgi.exe"))){$phpPools+="php$($Matches[1])$($Matches[2])"}}
$ids=@('mysql')+$phpPools+@('apache','phpmyadmin','redis','mail');if($AppExe){if($state.settings.dnsMode-eq'local-dns'){$ids+='dns'};$ids+='control'}
if($Service-eq'php'){$ids=$phpPools}elseif($Service-ne'all'){$ids=@($Service)}
switch($Action){
  'Install' {foreach($id in $ids){Write-ServiceConfig $id;Invoke-Wrapper $id 'install'};foreach($id in $ids){Invoke-Wrapper $id 'start'}}
  'Uninstall' {foreach($id in @($ids)[($ids.Count-1)..0]){Invoke-Wrapper $id 'stop';Invoke-Wrapper $id 'uninstall'}}
  'Start' {foreach($id in $ids){Invoke-Wrapper $id 'start'}}
  'Stop' {foreach($id in @($ids)[($ids.Count-1)..0]){Invoke-Wrapper $id 'stop'}}
  'StartAll' {foreach($id in $ids){Invoke-Wrapper $id 'start'}}
  'StopAll' {foreach($id in @($ids)[($ids.Count-1)..0]){Invoke-Wrapper $id 'stop'}}
  'Toggle' {foreach($id in $ids){$windowsName="LocalDeck$($id.Substring(0,1).ToUpper()+$id.Substring(1))";$status=(Get-Service -Name $windowsName -ErrorAction SilentlyContinue).Status;if($status-eq'Running'){Invoke-Wrapper $id 'stop'}else{Invoke-Wrapper $id 'start'}}}
  'Refresh' {foreach($id in $ids){Write-ServiceConfig $id;$windowsName="LocalDeck$($id.Substring(0,1).ToUpper()+$id.Substring(1))";if(Get-Service -Name $windowsName -ErrorAction SilentlyContinue){Invoke-Wrapper $id 'refresh'}else{Invoke-Wrapper $id 'install'}}}
  'Status' {@(foreach($id in $ids){$windowsName="LocalDeck$($id.Substring(0,1).ToUpper()+$id.Substring(1))";$item=Get-Service -Name $windowsName -ErrorAction SilentlyContinue;[pscustomobject]@{id=$id;installed=[bool]$item;status=if($item){$item.Status.ToString()}else{'NotInstalled'}}}) | ConvertTo-Json}
}
