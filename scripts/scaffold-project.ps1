[CmdletBinding()]
param(
  [Parameter(Mandatory=$true)][string]$RuntimeDir,
  [Parameter(Mandatory=$true)][ValidateSet('php','wordpress','laravel','symfony','drupal')][string]$Template,
  [Parameter(Mandatory=$true)][string]$ParentDir,
  [Parameter(Mandatory=$true)][string]$Slug
)
$ErrorActionPreference='Stop';$ProgressPreference='SilentlyContinue'
$parent=[IO.Path]::GetFullPath($ParentDir);if(!(Test-Path $parent)){throw 'De bovenliggende projectmap bestaat niet.'}
$target=[IO.Path]::GetFullPath((Join-Path $parent $Slug));if(!$target.StartsWith($parent.TrimEnd('\')+'\',[StringComparison]::OrdinalIgnoreCase)){throw 'Ongeldig projectpad.'};if(Test-Path $target){if((Get-ChildItem $target -Force|Select-Object -First 1)){throw 'De doelmap bestaat al en is niet leeg.'}}
$php=Join-Path $RuntimeDir 'php\php.exe';$composer=Join-Path $RuntimeDir 'composer.phar'
if($Template-eq'php'){
  New-Item -ItemType Directory -Force $target|Out-Null
  $index=@'
<?php
$siteName = basename(__DIR__);
$phpVersion = PHP_VERSION;
$now = date('d-m-Y H:i:s');
?><!doctype html>
<html lang="nl"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title><?=htmlspecialchars($siteName)?> · LocalDeck</title><style>*{box-sizing:border-box}body{margin:0;min-height:100vh;display:grid;place-items:center;background:#090b10;color:#eef0f7;font:16px system-ui,sans-serif;padding:24px}main{width:min(760px,100%);padding:44px;border:1px solid #33394a;border-radius:24px;background:linear-gradient(145deg,#171b26,#10131b);box-shadow:0 28px 80px #0008}.tag{color:#8f83ff;font-weight:800;letter-spacing:.12em}h1{font-size:clamp(38px,8vw,70px);line-height:1;margin:16px 0}h1 em{color:#69dbca;font-style:normal}p{color:#aab1c0;line-height:1.7}.grid{display:grid;grid-template-columns:repeat(2,1fr);gap:12px;margin-top:28px}.card{background:#0b0e14;border:1px solid #282e3d;border-radius:14px;padding:18px}.card b{display:block;color:#fff;margin-bottom:5px}code{color:#69dbca}@media(max-width:600px){main{padding:28px}.grid{grid-template-columns:1fr}}</style></head><body><main><div class="tag">LOCALDECK PHP-WEBSITE</div><h1>Je website<br><em>werkt.</em></h1><p>Bewerk <code>index.php</code> in deze map. LocalDeck houdt je website, runtime en databases samen in één verplaatsbare Windows-map.</p><div class="grid"><div class="card"><b>PHP-versie</b><?=htmlspecialchars($phpVersion)?></div><div class="card"><b>Serverdatum</b><?=htmlspecialchars($now)?></div><div class="card"><b>Projectmap</b><?=htmlspecialchars($siteName)?></div><div class="card"><b>Volgende stap</b>Vervang deze startpagina</div></div></main></body></html>
'@
  Set-Content -LiteralPath (Join-Path $target 'index.php') -Value $index -Encoding UTF8
  Set-Content -LiteralPath (Join-Path $target '.gitignore') -Value ".env`r`n/vendor/`r`n/node_modules/`r`n" -Encoding UTF8
}
if($Template-in@('laravel','symfony','drupal')){if(!(Test-Path $php)-or!(Test-Path $composer)){throw 'PHP en Composer moeten eerst zijn geïnstalleerd.'};$package=switch($Template){'laravel'{'laravel/laravel'}'symfony'{'symfony/skeleton'}'drupal'{'drupal/recommended-project'}};& $php $composer create-project $package $target --no-interaction;if($LASTEXITCODE-ne0){throw "Het $Template-project kon niet worden aangemaakt."}}
if($Template-eq'wordpress'){$staging=Join-Path $env:TEMP ("localdeck-wordpress-"+[guid]::NewGuid().ToString('N'));$zip="$staging.zip";try{Invoke-WebRequest -UseBasicParsing 'https://wordpress.org/latest.zip' -OutFile $zip;Expand-Archive -LiteralPath $zip -DestinationPath $staging -Force;New-Item -ItemType Directory -Force $target|Out-Null;Copy-Item (Join-Path $staging 'wordpress\*') $target -Recurse -Force;$hash=(Get-FileHash $zip -Algorithm SHA256).Hash;Write-Host "WordPress SHA256: $hash"}finally{if(Test-Path $zip){Remove-Item -LiteralPath $zip -Force};if(Test-Path $staging){Remove-Item -LiteralPath $staging -Recurse -Force}}}
@{template=$Template;createdAt=(Get-Date).ToString('o');managedBy='LocalDeck'}|ConvertTo-Json|Set-Content (Join-Path $target '.localdeck.json') -Encoding UTF8
Write-Host "Project gereed: $target"
