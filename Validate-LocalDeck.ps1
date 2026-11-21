[CmdletBinding()]
param()

Set-StrictMode -Version Latest
$ErrorActionPreference='Stop'
Add-Type -AssemblyName System.IO.Compression.FileSystem
$root=[IO.Path]::GetFullPath($PSScriptRoot)
$node=Join-Path $root 'tools\node\node.exe'
if(!(Test-Path -LiteralPath $node)){$node=(Get-Command node.exe -ErrorAction Stop).Source}
$requiredPackages=@('apache.zip','php.zip','php-8.2.zip','php-8.3.zip','php-8.4.zip','php-8.6.zip','mysql.zip','phpmyadmin.zip','mailpit.zip','redis.zip','WinSW.NET4.exe','composer.phar','vc_redist.x64.exe','mkcert.exe')

function Invoke-Node([string[]]$Arguments,[string]$Label){
  Write-Host "[controle] $Label" -ForegroundColor Cyan
  & $node @Arguments
  if($LASTEXITCODE-ne0){throw "$Label is mislukt met code $LASTEXITCODE."}
}

function Assert-CompactZip([string]$Name,[string[]]$ForbiddenPatterns,[string[]]$RequiredMarkers){
  $archiveFile=Join-Path (Join-Path $root 'runtime-packages') $Name
  $archive=[IO.Compression.ZipFile]::OpenRead($archiveFile)
  try{
    $entries=@($archive.Entries|ForEach-Object{$_.FullName.Replace('\','/')})
    foreach($pattern in $ForbiddenPatterns){
      $unexpected=$entries|Where-Object{$_-match$pattern}|Select-Object -First 1
      if($unexpected){throw "Runtimepakket $Name bevat uitgesloten distributiebestand: $unexpected"}
    }
    foreach($marker in $RequiredMarkers){
      if(!($entries|Where-Object{$_.EndsWith($marker,[StringComparison]::OrdinalIgnoreCase)}|Select-Object -First 1)){
        throw "Runtimepakket $Name mist verplicht bestand: $marker"
      }
    }
  }finally{$archive.Dispose()}
}

Push-Location $root
try{
  & (Join-Path $root 'scripts\Test-SignPathReadiness.ps1')
  if($LASTEXITCODE-ne0){throw 'De open-source- en SignPath-readinesscontrole is mislukt.'}

  $packageDirectory=Join-Path $root 'runtime-packages'
  $manifestFile=Join-Path $packageDirectory 'offline-runtime.json'
  if(!(Test-Path -LiteralPath $manifestFile)){throw 'Het offline-runtime-manifest ontbreekt.'}
  $manifest=Get-Content -LiteralPath $manifestFile -Raw|ConvertFrom-Json
  if($manifest.packages.Count-ne$requiredPackages.Count){throw "Het runtime-manifest bevat $($manifest.packages.Count) in plaats van $($requiredPackages.Count) pakketten."}
  foreach($name in $requiredPackages){
    $entry=$manifest.packages|Where-Object{$_.name-eq$name}|Select-Object -First 1
    $file=Join-Path $packageDirectory $name
    if(!$entry-or!(Test-Path -LiteralPath $file)){throw "Runtimepakket ontbreekt: $name"}
    if((Get-FileHash -LiteralPath $file -Algorithm SHA256).Hash-ne$entry.sha256){throw "SHA-256-controle mislukt voor $name"}
  }
  Write-Host "[controle] $($requiredPackages.Count) offline runtimepakketten zijn intact" -ForegroundColor Green
  if(!$manifest.PSObject.Properties['optimization']-or$manifest.optimization.profile-ne'windows-x64-runtime-only'){throw 'Het runtime-manifest mist het compacte Windows-runtimeprofiel.'}
  Assert-CompactZip 'mysql.zip' @('(?i)\.pdb$','(?i)\.lib$','(?i)(^|/)include/','(?i)/lib/plugin/debug/','(?i)/bin/[^/]+-debug\.dll$','(?i)/bin/libmecab\.dll$','(?i)/lib/mecab/','(?i)/lib/plugin/libpluginmecab\.dll$') @('bin/mysqld.exe','bin/mysql.exe','bin/mysqladmin.exe','bin/mysqldump.exe')
  Assert-CompactZip 'phpmyadmin.zip' @('(?i)/locale/(?!en_GB/|nl/)[^/]+/') @('index.php','vendor/autoload.php','locale/en_GB/LC_MESSAGES/phpmyadmin.mo','locale/nl/LC_MESSAGES/phpmyadmin.mo')
  Assert-CompactZip 'redis.zip' @('(?i)\.pdb$') @('redis-server.exe','redis-cli.exe')
  Assert-CompactZip 'apache.zip' @('(?i)/(include|lib)/') @('bin/httpd.exe','modules/mod_ssl.so','modules/mod_proxy_fcgi.so')
  Write-Host '[controle] Runtimepakketten bevatten alleen uitvoerbare Windows-onderdelen' -ForegroundColor Green

  $packageConfig=Get-Content -LiteralPath (Join-Path $root 'package.json') -Raw|ConvertFrom-Json
  $productionDependencies=@($packageConfig.dependencies.PSObject.Properties.Name|Sort-Object)
  if(($productionDependencies-join',')-ne'qrcode,yaml'){throw "Onverwachte productie-afhankelijkheden worden in de app verpakt: $($productionDependencies-join', ')"}
  $electronLanguages=@($packageConfig.build.electronLanguages|Sort-Object)
  if(($electronLanguages-join',')-ne'en-GB,en-US,nl'){throw "Onverwachte Electron-talen in de Windows-build: $($electronLanguages-join', ')"}
  if($packageConfig.build.compression-ne'maximum'){throw 'De Windows-build gebruikt niet de maximale distributiecompressie.'}
  if($packageConfig.build.nsis.include-ne'build/installer.nsh'){throw 'De veilige NSIS-verwijderdialoog is niet gekoppeld.'}
  $nsisInclude=Get-Content -LiteralPath (Join-Path $root 'build\installer.nsh') -Raw
  if($nsisInclude-notmatch'MB_DEFBUTTON2'-or$nsisInclude-notmatch'websites, databases, back-ups'){throw 'De uninstaller bewaart gebruikersgegevens niet aantoonbaar als veilige standaard.'}
  $releaseBuilder=Get-Content -LiteralPath (Join-Path $root 'scripts\build-release.ps1') -Raw
  if($releaseBuilder-match'(?i)--win\s+nsis\s+portable'){throw 'De releasebuilder maakt nog een overbodige portable EXE.'}
  Write-Host '[controle] App-afhankelijkheden, talen en compressie zijn distributiecompact' -ForegroundColor Green

  foreach($script in Get-ChildItem -LiteralPath (Join-Path $root 'scripts') -Filter '*.ps1' -File){
    $tokens=$null;$parseErrors=$null
    [System.Management.Automation.Language.Parser]::ParseFile($script.FullName,[ref]$tokens,[ref]$parseErrors)|Out-Null
    if($parseErrors.Count){throw "PowerShell-syntaxfout in $($script.Name): $($parseErrors[0].Message)"}
  }
  Write-Host '[controle] Alle PowerShell-scripts hebben geldige syntax' -ForegroundColor Green

  & (Join-Path $root 'scripts\Test-SupportBundlePrivacy.ps1')
  if($LASTEXITCODE-ne0){throw 'De privacytest voor supportbundels is mislukt.'}
  & (Join-Path $root 'scripts\Test-WorkspaceExportPrivacy.ps1')
  if($LASTEXITCODE-ne0){throw 'De privacytest voor werkruimte-export is mislukt.'}

  $phpCandidates=@(
    (Join-Path $root 'LocalDeck-Startklaar\runtime\php-8.6\php.exe'),
    (Join-Path $root 'LocalDeck-Startklaar\runtime\php\php.exe'),
    (Join-Path $root 'LocalDeck-Startklaar\runtime\php-8.4\php.exe'),
    (Join-Path $root 'LocalDeck-Startklaar\runtime\php-8.3\php.exe'),
    (Join-Path $root 'LocalDeck-Startklaar\runtime\php-8.2\php.exe')
  )
  $php=$null
  foreach($candidate in $phpCandidates){
    if(!(Test-Path -LiteralPath $candidate)){continue}
    & $candidate -n -r 'exit(0);' 2>$null
    if($LASTEXITCODE-eq0){$php=$candidate;break}
  }
  $websiteRoot=Join-Path $root 'LocalDeck-Website'
  if(!$php){throw 'Geen van de meegeleverde PHP-runtimes kan de websitetests uitvoeren.'}
  Write-Host "[controle] Websitetests gebruiken $php" -ForegroundColor DarkGray
  $phpFiles=Get-ChildItem -LiteralPath $websiteRoot -Filter '*.php' -Recurse -File
  foreach($phpFile in $phpFiles){
    $lintOutput=& $php -l $phpFile.FullName 2>&1
    if($LASTEXITCODE-ne0){throw "PHP-syntaxfout in $($phpFile.FullName): $lintOutput"}
  }
  foreach($testFile in Get-ChildItem -LiteralPath (Join-Path $websiteRoot 'tests') -Filter '*.test.php' -File){
    & $php $testFile.FullName
    if($LASTEXITCODE-ne0){throw "Websitetest $($testFile.Name) is mislukt met code $LASTEXITCODE."}
  }
  Write-Host "[controle] $($phpFiles.Count) websitebestanden en alle websitetests zijn geldig" -ForegroundColor Green

  Invoke-Node @('node_modules/typescript/bin/tsc','-p','tsconfig.electron.json') 'Electron-hoofdproces en gedeelde types'
  Invoke-Node @('node_modules/typescript/bin/tsc','--noEmit') 'TypeScript-interface'
  Invoke-Node @('node_modules/vitest/vitest.mjs','run') 'Automatische tests'
  Invoke-Node @('node_modules/vite/bin/vite.js','build') 'Dashboard-build'

  $validationDirectory=Join-Path $root '.validation';New-Item -ItemType Directory -Path $validationDirectory -Force|Out-Null
  $version=(Get-Content -LiteralPath (Join-Path $root 'package.json') -Raw|ConvertFrom-Json).version
  [ordered]@{version=$version;validatedAt=(Get-Date).ToString('o');packages=$requiredPackages.Count;status='passed'}|ConvertTo-Json|Set-Content -LiteralPath (Join-Path $validationDirectory 'last-success.json') -Encoding UTF8
  Write-Host "`nLocalDeck $version is intern gevalideerd. Er zijn geen downloadbestanden gemaakt." -ForegroundColor Green
}finally{Pop-Location}
