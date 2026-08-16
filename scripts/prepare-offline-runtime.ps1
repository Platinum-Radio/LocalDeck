[CmdletBinding()]
param([Parameter(Mandatory)][string]$OutputDir)
$ErrorActionPreference='Stop';$ProgressPreference='SilentlyContinue';$OutputDir=[IO.Path]::GetFullPath($OutputDir);New-Item -ItemType Directory -Path $OutputDir -Force|Out-Null
function Save-Package([string]$Name,[string]$Url){
  $target=Join-Path $OutputDir $Name;$partial="$target.part";if(Test-Path $partial){Remove-Item -LiteralPath $partial -Force}
  if(!(Test-Path $target)){Write-Host "[download] $Name" -ForegroundColor Cyan;Invoke-WebRequest -UseBasicParsing -Uri $Url -OutFile $partial;if((Get-Item $partial).Length-lt100KB){throw "$Name is onvolledig gedownload."};Move-Item -LiteralPath $partial -Destination $target -Force}
  [pscustomobject]@{name=$Name;sha256=(Get-FileHash $target -Algorithm SHA256).Hash;bytes=(Get-Item $target).Length;source=$Url}
}
function Save-GitHubAsset([string]$Name,[string]$Repository,[string]$Pattern){$release=Invoke-RestMethod -UseBasicParsing "https://api.github.com/repos/$Repository/releases/latest" -Headers @{'User-Agent'='LocalDeck-Offline-Pack'};$asset=$release.assets|Where-Object{$_.name-like$Pattern}|Select-Object -First 1;if(!$asset){throw "Geen pakket gevonden voor $Repository ($Pattern)."};Save-Package $Name $asset.browser_download_url}
$packages=@()
$winget=Get-Command winget.exe -ErrorAction SilentlyContinue;if(!$winget){throw 'Windows Package Manager ontbreekt; Apache kan niet worden voorbereid.'}
$apacheTemp=Join-Path $OutputDir '_apache';New-Item -ItemType Directory -Path $apacheTemp -Force|Out-Null
& $winget.Source download --id ApacheLounge.httpd --version 2.4.68 --architecture x64 --download-directory $apacheTemp --accept-package-agreements --accept-source-agreements --disable-interactivity | Out-Host
if($LASTEXITCODE-ne0){throw 'Apache-download is mislukt.'};$apache=Get-ChildItem $apacheTemp -Recurse -File -Filter '*.zip'|Select-Object -First 1;if(!$apache){throw 'Apache ZIP ontbreekt.'};Move-Item -LiteralPath $apache.FullName -Destination (Join-Path $OutputDir 'apache.zip') -Force
$packages+=[pscustomobject]@{name='apache.zip';sha256=(Get-FileHash (Join-Path $OutputDir 'apache.zip') -Algorithm SHA256).Hash;bytes=(Get-Item (Join-Path $OutputDir 'apache.zip')).Length;source='winget:ApacheLounge.httpd@2.4.68'}
$resolvedApache=[IO.Path]::GetFullPath($apacheTemp);if($resolvedApache.StartsWith([IO.Path]::GetFullPath($OutputDir),[StringComparison]::OrdinalIgnoreCase)){Remove-Item -LiteralPath $resolvedApache -Recurse -Force}
$packages+=Save-Package 'php.zip' 'https://windows.php.net/downloads/releases/latest/php-8.5-Win32-vs17-x64-latest.zip'
$packages+=Save-Package 'php-8.2.zip' 'https://windows.php.net/downloads/releases/latest/php-8.2-Win32-vs16-x64-latest.zip'
$packages+=Save-Package 'php-8.3.zip' 'https://windows.php.net/downloads/releases/latest/php-8.3-Win32-vs16-x64-latest.zip'
$packages+=Save-Package 'php-8.4.zip' 'https://windows.php.net/downloads/releases/latest/php-8.4-Win32-vs17-x64-latest.zip'
$packages+=Save-Package 'mysql.zip' 'https://cdn.mysql.com/Downloads/MySQL-8.4/mysql-8.4.10-winx64.zip'
$packages+=Save-Package 'phpmyadmin.zip' 'https://www.phpmyadmin.net/downloads/phpMyAdmin-latest-all-languages.zip'
$packages+=Save-GitHubAsset 'mailpit.zip' 'axllent/mailpit' '*windows-amd64.zip'
$packages+=Save-GitHubAsset 'redis.zip' 'tporadowski/redis' '*x64*.zip'
$packages+=Save-Package 'WinSW.NET4.exe' 'https://github.com/winsw/winsw/releases/download/v2.12.0/WinSW.NET4.exe'
$packages+=Save-Package 'composer.phar' 'https://getcomposer.org/download/latest-stable/composer.phar'
$packages+=Save-Package 'vc_redist.x64.exe' 'https://aka.ms/vc14/vc_redist.x64.exe'
$packages+=Save-GitHubAsset 'mkcert.exe' 'FiloSottile/mkcert' '*windows-amd64.exe'
[ordered]@{schemaVersion=1;createdAt=(Get-Date).ToString('o');packages=$packages}|ConvertTo-Json -Depth 5|Set-Content (Join-Path $OutputDir 'offline-runtime.json') -Encoding UTF8
Write-Host "Offline runtime gereed: $OutputDir" -ForegroundColor Green
