[CmdletBinding()]
param(
  [string]$InstallDir = (Join-Path $env:APPDATA 'LocalDeck\runtime'),
  [switch]$Force,
  [switch]$PrerequisitesOnly,
  [switch]$SkipCertificateTrust,
  [string]$PackageDir = (Join-Path $PSScriptRoot 'packages')
)
$ErrorActionPreference='Stop';$ProgressPreference='SilentlyContinue';[Console]::OutputEncoding=[Text.UTF8Encoding]::new($false);$OutputEncoding=[Text.UTF8Encoding]::new($false);Add-Type -AssemblyName System.IO.Compression.FileSystem;Add-Type -AssemblyName System.Security;$InstallDir=[IO.Path]::GetFullPath($InstallDir);$PackageDir=[IO.Path]::GetFullPath($PackageDir)
if([Environment]::Is64BitOperatingSystem-ne$true){throw 'LocalDeck ondersteunt alleen 64-bit Windows 10/11.'}
$temp=Join-Path $InstallDir 'staging';$logDir=Join-Path $InstallDir 'logs';New-Item -ItemType Directory -Force $InstallDir,$temp,$logDir|Out-Null
$installLog=Join-Path $logDir ("runtime-install-{0}.log" -f (Get-Date -Format 'yyyyMMdd-HHmmss'));Start-Transcript -LiteralPath $installLog -Force|Out-Null;Write-Host "[log]      $installLog" -ForegroundColor DarkGray
$requiredPackages=@('apache.zip','php.zip','php-8.2.zip','php-8.3.zip','php-8.4.zip','mysql.zip','phpmyadmin.zip','mailpit.zip','redis.zip','WinSW.NET4.exe','composer.phar','vc_redist.x64.exe','mkcert.exe')
function Assert-PackageSet {
  if(!(Test-Path -LiteralPath $PackageDir)){throw "De ingebouwde runtimepakketten ontbreken: $PackageDir"}
  $manifestFile=Join-Path $PackageDir 'offline-runtime.json';if(!(Test-Path -LiteralPath $manifestFile)){throw 'Het ingebouwde runtime-manifest ontbreekt.'}
  $packageManifest=Get-Content -LiteralPath $manifestFile -Raw|ConvertFrom-Json
  foreach($name in $requiredPackages){
    $file=Join-Path $PackageDir $name;if(!(Test-Path -LiteralPath $file)){throw "Ingebouwd runtimepakket ontbreekt: $name"}
    $entry=$packageManifest.packages|Where-Object{$_.name-eq$name}|Select-Object -First 1;if(!$entry){throw "Manifestvermelding ontbreekt voor $name"}
    $actual=(Get-FileHash -LiteralPath $file -Algorithm SHA256).Hash;if($actual-ne$entry.sha256){throw "Integriteitscontrole mislukt voor $name"}
  }
  Write-Host "[bundle]   $($requiredPackages.Count) ingebouwde runtimepakketten geverifieerd" -ForegroundColor Green
}
function Get-VCRuntime {
  foreach($key in 'HKLM:\SOFTWARE\Microsoft\VisualStudio\14.0\VC\Runtimes\x64','HKLM:\SOFTWARE\WOW6432Node\Microsoft\VisualStudio\14.0\VC\Runtimes\x64'){
    if(Test-Path -LiteralPath $key){$runtime=Get-ItemProperty -LiteralPath $key -ErrorAction SilentlyContinue;if($runtime.Installed-eq1-and[int]$runtime.Major-ge14){return $runtime}}
  }
  return $null
}
function Assert-MicrosoftSignature([string]$File){
  $signature=Get-AuthenticodeSignature -LiteralPath $File
  if($signature.Status-ne'Valid'-or!$signature.SignerCertificate-or$signature.SignerCertificate.Subject-notmatch 'Microsoft Corporation'){throw 'De digitale handtekening van de ingebouwde Microsoft Visual C++ Runtime is ongeldig.'}
}
function Install-VCRuntime {
  $installed=Get-VCRuntime;if($installed){Write-Host "[dependency] Microsoft Visual C++ Runtime $($installed.Version) is al geïnstalleerd" -ForegroundColor Green;return}
  $installer=Join-Path $PackageDir 'vc_redist.x64.exe';Assert-MicrosoftSignature $installer;Write-Host '[offline]  Ingebouwde Microsoft Visual C++ Runtime' -ForegroundColor Cyan;$vcLog=Join-Path $logDir 'vc-redist-install.log';$vc=Start-Process -FilePath $installer -ArgumentList '/install','/quiet','/norestart',('/log "{0}"'-f$vcLog) -WindowStyle Hidden -Wait -PassThru;if($vc.ExitCode-notin0,1638,3010-and!(Get-VCRuntime)){throw "Microsoft Visual C++ Runtime stopte met code $($vc.ExitCode). Details: $vcLog"}
  $ready=Get-VCRuntime;if(!$ready){throw 'Microsoft Visual C++ Runtime kon niet worden bevestigd via het Windows-register.'};Write-Host "[dependency] Microsoft Visual C++ Runtime $($ready.Version) is gereed" -ForegroundColor Green
}
function Remove-TreeSafe([string]$Path){
  $fullPath=[IO.Path]::GetFullPath($Path);$allowedPrefix=$InstallDir.TrimEnd('\')+'\'
  if(!$fullPath.StartsWith($allowedPrefix,[StringComparison]::OrdinalIgnoreCase)){throw "Onveilige opruimlocatie geweigerd: $fullPath"}
  if(!(Test-Path -LiteralPath $fullPath)){return}
  $empty=Join-Path $InstallDir ('.cleanup-empty-'+[guid]::NewGuid().ToString('N'));New-Item -ItemType Directory -Path $empty|Out-Null
  try{& robocopy.exe $empty $fullPath /MIR /XJ /R:2 /W:1 /NFL /NDL /NJH /NJS /NP|Out-Null;$cleanupCode=$LASTEXITCODE;if($cleanupCode-gt7){throw "De tijdelijke runtimebestanden konden niet worden opgeruimd (robocopy-code $cleanupCode)."};Remove-Item -LiteralPath $fullPath -Force}finally{if(Test-Path -LiteralPath $empty){Remove-Item -LiteralPath $empty -Force};$global:LASTEXITCODE=0}
}
function Get-Package([string]$Name){
  $zip=Join-Path $PackageDir "$Name.zip";$valid=$false
  try{$archive=[IO.Compression.ZipFile]::OpenRead($zip);$valid=$archive.Entries.Count-gt0;$archive.Dispose()}catch{$valid=$false}
  if(!$valid){throw "Het ingebouwde pakket $Name is geen geldig ZIP-archief."}
  $hash=(Get-FileHash $zip -Algorithm SHA256).Hash;Write-Host "[sha256]  $Name $hash" -ForegroundColor DarkGray
  $dest=Join-Path $temp $Name;if(Test-Path $dest){Remove-TreeSafe $dest};New-Item -ItemType Directory $dest|Out-Null
  $tar=(Get-Command tar.exe -ErrorAction SilentlyContinue).Source
  if(!$tar){throw 'Windows tar.exe ontbreekt; LocalDeck ondersteunt Windows 10 en 11.'}
  & $tar -xf $zip -C $dest
  if($LASTEXITCODE-ne0){throw "Het ingebouwde pakket $Name kon niet veilig worden uitgepakt."}
  return $dest
}
function Copy-Tree([string]$Source,[string]$Destination){
  New-Item -ItemType Directory -Path $Destination -Force|Out-Null
  & robocopy.exe $Source $Destination /E /COPY:DAT /DCOPY:DAT /R:2 /W:1 /XJ /NFL /NDL /NJH /NJS /NP|Out-Null
  $copyCode=$LASTEXITCODE
  if($copyCode-gt7){throw "De runtimebestanden konden niet worden gekopieerd (robocopy-code $copyCode)."}
  $global:LASTEXITCODE=0
}
function Find-Root([string]$Path,[string]$Marker){$hit=Get-ChildItem $Path -Recurse -Filter $Marker|Where-Object{!$_.PSIsContainer}|Select-Object -First 1;if(!$hit){throw "$Marker ontbreekt in het ingebouwde pakket."};return $hit.Directory.Parent.FullName}
function Get-FreeLoopbackPort {
  $listener=[System.Net.Sockets.TcpListener]::new([System.Net.IPAddress]::Loopback,0)
  try{$listener.Start();return ([System.Net.IPEndPoint]$listener.LocalEndpoint).Port}finally{$listener.Stop()}
}
Write-Host 'LocalDeck Windows Runtime installeren' -ForegroundColor Magenta
Assert-PackageSet;Install-VCRuntime;if($PrerequisitesOnly){Write-Host 'Ingebouwde prerequisites zijn gereed.' -ForegroundColor Green;Stop-Transcript|Out-Null;exit 0}
$apacheStage=Get-Package 'apache';$apacheRoot=Find-Root $apacheStage 'httpd.exe';Copy-Tree $apacheRoot (Join-Path $InstallDir 'apache')
$phpStage=Get-Package 'php';$phpExe=Get-ChildItem $phpStage -Recurse -Filter 'php.exe'|Where-Object{!$_.PSIsContainer}|Select-Object -First 1;$phpRoot=$phpExe.Directory.FullName;Copy-Tree $phpRoot (Join-Path $InstallDir 'php')
$phpMatrix=@('8.2','8.3','8.4');Copy-Tree (Join-Path $InstallDir 'php') (Join-Path $InstallDir 'php-8.5');foreach($v in $phpMatrix){$stage=Get-Package "php-$v";$exe=Get-ChildItem $stage -Recurse -Filter php.exe|Where-Object{!$_.PSIsContainer}|Select-Object -First 1;Copy-Tree $exe.Directory.FullName (Join-Path $InstallDir "php-$v")}
$mysqlStage=Get-Package 'mysql';$mysqlRoot=Find-Root $mysqlStage 'mysqld.exe';Copy-Tree $mysqlRoot (Join-Path $InstallDir 'mysql')
$pmaStage=Get-Package 'phpmyadmin';$pmaIndex=Get-ChildItem $pmaStage -Recurse -Filter 'index.php'|Where-Object{!$_.PSIsContainer-and$_.Directory.Name-like'phpMyAdmin*'}|Select-Object -First 1;if(!$pmaIndex){throw 'phpMyAdmin index.php ontbreekt.'};Copy-Tree $pmaIndex.Directory.FullName (Join-Path $InstallDir 'phpmyadmin')
$mailStage=Get-Package 'mailpit';$mailExe=Get-ChildItem $mailStage -Recurse -Filter 'mailpit.exe'|Select-Object -First 1;New-Item -ItemType Directory -Force (Join-Path $InstallDir 'mailpit')|Out-Null;Copy-Item $mailExe.FullName (Join-Path $InstallDir 'mailpit\mailpit.exe') -Force
$redisStage=Get-Package 'redis';$redisExe=Get-ChildItem $redisStage -Recurse -Filter 'redis-server.exe'|Select-Object -First 1;Copy-Tree $redisExe.Directory.FullName (Join-Path $InstallDir 'redis')
$wrapperDir=Join-Path $InstallDir 'service-wrapper';New-Item -ItemType Directory -Force $wrapperDir|Out-Null;$wrapperFile=Join-Path $wrapperDir 'WinSW.NET4.exe';Copy-Item -LiteralPath (Join-Path $PackageDir 'WinSW.NET4.exe') -Destination $wrapperFile -Force;$wrapperHash=(Get-FileHash $wrapperFile -Algorithm SHA256).Hash;Write-Host "[sha256]  winsw $wrapperHash" -ForegroundColor DarkGray
$phpDir=Join-Path $InstallDir 'php';Copy-Item (Join-Path $phpDir 'php.ini-production') (Join-Path $phpDir 'php.ini') -Force
$ini=Get-Content (Join-Path $phpDir 'php.ini') -Raw;$ini=$ini-replace ';extension_dir = "ext"','extension_dir = "ext"';foreach($ext in 'mysqli','pdo_mysql','mbstring','openssl','curl','zip'){ $ini=$ini-replace ";extension=$ext", "extension=$ext" };Set-Content (Join-Path $phpDir 'php.ini') $ini -Encoding UTF8
foreach($dir in Get-ChildItem $InstallDir -Directory -Filter 'php-*'){if(Test-Path (Join-Path $dir.FullName 'php.ini-production')){Copy-Item (Join-Path $dir.FullName 'php.ini-production') (Join-Path $dir.FullName 'php.ini') -Force;$versionIni=Get-Content (Join-Path $dir.FullName 'php.ini') -Raw;$versionIni=$versionIni-replace ';extension_dir = "ext"','extension_dir = "ext"';foreach($ext in 'mysqli','pdo_mysql','mbstring','openssl','curl','zip'){ $versionIni=$versionIni-replace ";extension=$ext", "extension=$ext" };Set-Content (Join-Path $dir.FullName 'php.ini') $versionIni -Encoding UTF8}}
$apacheDir=Join-Path $InstallDir 'apache';$apacheSlash=$apacheDir.Replace('\','/');$phpSlash=$phpDir.Replace('\','/');$htdocs=Join-Path $InstallDir 'www';New-Item -ItemType Directory -Force $htdocs|Out-Null
$httpd=@"
ServerRoot "$apacheSlash"
Listen 80
Listen 443
LoadModule dir_module modules/mod_dir.so
LoadModule actions_module modules/mod_actions.so
LoadModule cgi_module modules/mod_cgi.so
LoadModule alias_module modules/mod_alias.so
LoadModule mime_module modules/mod_mime.so
LoadModule log_config_module modules/mod_log_config.so
LoadModule authz_core_module modules/mod_authz_core.so
LoadModule authz_host_module modules/mod_authz_host.so
LoadModule ssl_module modules/mod_ssl.so
LoadModule socache_shmcb_module modules/mod_socache_shmcb.so
LoadModule proxy_module modules/mod_proxy.so
LoadModule proxy_fcgi_module modules/mod_proxy_fcgi.so
ServerName localhost
LogFormat "%h %l %u %t \"%r\" %>s %b" combined
DocumentRoot "$($htdocs.Replace('\','/'))"
<Directory "$($htdocs.Replace('\','/'))">
  Options Indexes FollowSymLinks
  AllowOverride All
  Require all granted
</Directory>
DirectoryIndex index.php index.html
ErrorLog "$($InstallDir.Replace('\','/'))/apache-error.log"
SSLSessionCache "shmcb:$($InstallDir.Replace('\','/'))/sslcache(512000)"
IncludeOptional conf/localdeck-vhosts.conf
"@;Set-Content (Join-Path $apacheDir 'conf\httpd-localdeck.conf') $httpd -Encoding ASCII
Set-Content (Join-Path $apacheDir 'conf\localdeck-vhosts.conf') '' -Encoding ASCII
$mysqlDir=Join-Path $InstallDir 'mysql';$data=Join-Path $InstallDir 'mysql-data';New-Item -ItemType Directory -Force $data|Out-Null
$myIni=@"
[mysqld]
basedir=$($mysqlDir.Replace('\','/'))
datadir=$($data.Replace('\','/'))
port=3306
bind-address=127.0.0.1
character-set-server=utf8mb4
collation-server=utf8mb4_unicode_ci
[client]
port=3306
user=root
"@;Set-Content (Join-Path $InstallDir 'my.ini') $myIni -Encoding ASCII
$secretsFile=Join-Path $InstallDir 'secrets.json';$newDatabase=!(Test-Path (Join-Path $data 'mysql'));if(Test-Path $secretsFile){$savedSecret=Get-Content $secretsFile -Raw|ConvertFrom-Json;if($savedSecret.mysqlRootProtected){$protected=[Convert]::FromBase64String($savedSecret.mysqlRootProtected);$dbPassword=[Text.Encoding]::UTF8.GetString([System.Security.Cryptography.ProtectedData]::Unprotect($protected,$null,[System.Security.Cryptography.DataProtectionScope]::CurrentUser))}else{$dbPassword=$savedSecret.mysqlRoot}}else{$dbPassword=-join((48..57)+(65..90)+(97..122)|Get-Random -Count 32|ForEach-Object{[char]$_})}
if($newDatabase){
  Write-Host '[config]   MySQL datamap initialiseren'; & (Join-Path $mysqlDir 'bin\mysqld.exe') --defaults-file="$(Join-Path $InstallDir 'my.ini')" --initialize-insecure;if($LASTEXITCODE-ne0){throw 'MySQL initialisatie mislukt.'}
  $bootstrapPort=Get-FreeLoopbackPort;$server=Start-Process (Join-Path $mysqlDir 'bin\mysqld.exe') -ArgumentList "--defaults-file=`"$(Join-Path $InstallDir 'my.ini')`"","--port=$bootstrapPort","--console" -WindowStyle Hidden -PassThru;$ready=$null
  for($n=0;$n-lt80;$n++){Start-Sleep -Milliseconds 250;$ready=Test-NetConnection 127.0.0.1 -Port $bootstrapPort -WarningAction SilentlyContinue;if($ready.TcpTestSucceeded){break}}
  if(!$ready-or!$ready.TcpTestSucceeded){if(!$server.HasExited){Stop-Process -Id $server.Id -Force};throw 'De tijdelijke MySQL-instantie werd niet op tijd bereikbaar.'}
  & (Join-Path $mysqlDir 'bin\mysql.exe') -u root -h 127.0.0.1 -P $bootstrapPort -e "ALTER USER 'root'@'localhost' IDENTIFIED BY '$dbPassword'; CREATE USER IF NOT EXISTS 'root'@'127.0.0.1' IDENTIFIED BY '$dbPassword'; GRANT ALL PRIVILEGES ON *.* TO 'root'@'127.0.0.1' WITH GRANT OPTION; FLUSH PRIVILEGES;";if($LASTEXITCODE-ne0){if(!$server.HasExited){Stop-Process -Id $server.Id -Force};throw 'MySQL-wachtwoord instellen mislukt.'}
  $previousMysqlPassword=$env:MYSQL_PWD;try{$env:MYSQL_PWD=$dbPassword;& (Join-Path $mysqlDir 'bin\mysqladmin.exe') -u root -h 127.0.0.1 -P $bootstrapPort shutdown;if($LASTEXITCODE-ne0){if(!$server.HasExited){Stop-Process -Id $server.Id -Force};throw 'De tijdelijke MySQL-instantie kon niet gecontroleerd worden afgesloten.'}}finally{$env:MYSQL_PWD=$previousMysqlPassword}
}
$protectedPassword=[System.Security.Cryptography.ProtectedData]::Protect([Text.Encoding]::UTF8.GetBytes($dbPassword),$null,[System.Security.Cryptography.DataProtectionScope]::CurrentUser);@{mysqlRootProtected=[Convert]::ToBase64String($protectedPassword)}|ConvertTo-Json|Set-Content $secretsFile -Encoding UTF8;& icacls.exe $secretsFile /inheritance:r /grant:r "$env:USERNAME`:F"|Out-Null
$secret=-join((48..57)+(65..90)+(97..122)|Get-Random -Count 32|ForEach-Object{[char]$_});$pmaConfig="<?php`n`$cfg['blowfish_secret']='$secret';`n`$localDeckState=json_decode((string) @file_get_contents(dirname(__DIR__).'/state.json'),true);`n`$localDeckMySqlPort='3306';`nforeach((`$localDeckState['services']??[]) as `$localDeckService){if((`$localDeckService['id']??'')==='mysql'){`$localDeckMySqlPort=(string)(`$localDeckService['port']??3306);break;}}`n`$i=1;`n`$cfg['Servers'][`$i]['auth_type']='config';`n`$cfg['Servers'][`$i]['user']='root';`n`$cfg['Servers'][`$i]['password']=(string)(getenv('LOCALDECK_MYSQL_PASSWORD')?:'');`n`$cfg['Servers'][`$i]['host']='127.0.0.1';`n`$cfg['Servers'][`$i]['port']=`$localDeckMySqlPort;`n`$cfg['Servers'][`$i]['connect_type']='tcp';`n`$cfg['Servers'][`$i]['AllowNoPassword']=false;";Set-Content (Join-Path $InstallDir 'phpmyadmin\config.inc.php') $pmaConfig -Encoding UTF8
$startpage=Join-Path $PSScriptRoot 'startpage';if(Test-Path $startpage){Copy-Item (Join-Path $startpage '*') $htdocs -Recurse -Force}else{Set-Content (Join-Path $htdocs 'index.html') '<!doctype html><title>LocalDeck</title><h1>LocalDeck draait</h1>' -Encoding UTF8};@{apache=80;php=9000;mysql=3306;phpmyadmin=8080}|ConvertTo-Json|Set-Content (Join-Path $htdocs 'localdeck-runtime.json') -Encoding UTF8;$automation=Join-Path $InstallDir 'automation';New-Item -ItemType Directory -Force $automation|Out-Null;Copy-Item (Join-Path $PSScriptRoot 'get-secret.ps1') $automation -Force
$toolsDir=Join-Path $InstallDir 'tools';New-Item -ItemType Directory -Force $toolsDir|Out-Null;$mkcert=Join-Path $toolsDir 'mkcert.exe';Copy-Item -LiteralPath (Join-Path $PackageDir 'mkcert.exe') -Destination $mkcert -Force;Write-Host '[offline]  Ingebouwde mkcert voor lokaal HTTPS' -ForegroundColor Cyan;$certs=Join-Path $InstallDir 'certs';New-Item -ItemType Directory -Force $certs|Out-Null;if(!$SkipCertificateTrust){& $mkcert -install|Out-Host;if($LASTEXITCODE-ne0){throw 'Het lokale HTTPS-rootcertificaat kon niet worden vertrouwd.'}};& $mkcert -cert-file (Join-Path $certs 'localdeck.pem') -key-file (Join-Path $certs 'localdeck-key.pem') '*.test' localhost 127.0.0.1 ::1|Out-Host;if($LASTEXITCODE-ne0){throw 'Het lokale HTTPS-certificaat kon niet worden gemaakt.'}
if(!$SkipCertificateTrust){[ordered]@{trustedAt=(Get-Date).ToString('o');machine=$env:COMPUTERNAME;user=[Security.Principal.WindowsIdentity]::GetCurrent().Name}|ConvertTo-Json|Set-Content -LiteralPath (Join-Path $certs 'local-ca-trusted.json') -Encoding UTF8}
Write-Host '[offline]  Ingebouwde Composer' -ForegroundColor Cyan;$composer=Join-Path $InstallDir 'composer.phar';Copy-Item -LiteralPath (Join-Path $PackageDir 'composer.phar') -Destination $composer -Force;$composerHash=(Get-FileHash $composer -Algorithm SHA256).Hash;Write-Host "[sha256]  composer $composerHash" -ForegroundColor DarkGray;Set-Content (Join-Path $InstallDir 'composer.bat') "@echo off`r`n`"%~dp0php\php.exe`" `"%~dp0composer.phar`" %*" -Encoding ASCII
$manifest=[ordered]@{installedAt=(Get-Date).ToString('o');platform='win32-x64';apache='2.4.68';php=@('8.2','8.3','8.4','8.5');mysql='8.4.10';phpmyadmin='5.2.3';mailpit='1.30.7';redis='5.0.14.1';winsw='2.12.0';winswSha256=$wrapperHash;composerSha256=$composerHash;paths=@{apache='apache\bin\httpd.exe';php='php\php-cgi.exe';phpCli='php\php.exe';mysql='mysql\bin\mysqld.exe';mysqlCli='mysql\bin\mysql.exe';phpmyadmin='phpmyadmin';mailpit='mailpit\mailpit.exe';redis='redis\redis-server.exe';winsw='service-wrapper\WinSW.NET4.exe'}};$manifest|ConvertTo-Json -Depth 4|Set-Content (Join-Path $InstallDir 'runtime.json') -Encoding UTF8
if(Test-Path -LiteralPath $temp){Remove-TreeSafe $temp}
Write-Host "`nGereed. Volledig offline geïnstalleerd in: $InstallDir" -ForegroundColor Green
Write-Host 'Herstart LocalDeck; simulatiemodus wordt automatisch uitgeschakeld. Er zijn geen onderdelen gedownload.'
Stop-Transcript|Out-Null
