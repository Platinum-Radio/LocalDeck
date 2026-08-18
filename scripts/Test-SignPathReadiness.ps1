[CmdletBinding()]
param()

Set-StrictMode -Version Latest
$ErrorActionPreference='Stop'
$root=[IO.Path]::GetFullPath((Join-Path $PSScriptRoot '..'))
$failures=New-Object 'System.Collections.Generic.List[string]'

function Add-Failure([string]$Message){$script:failures.Add($Message)}
function Assert-File([string]$RelativePath){
  if(!(Test-Path -LiteralPath (Join-Path $root $RelativePath))){Add-Failure "Verplicht bestand ontbreekt: $RelativePath"}
}
function Assert-Text([string]$RelativePath,[string]$Pattern,[string]$Description){
  $path=Join-Path $root $RelativePath
  if(!(Test-Path -LiteralPath $path)){Add-Failure "Kan $Description niet controleren; $RelativePath ontbreekt.";return}
  $content=Get-Content -LiteralPath $path -Raw
  if($content-notmatch$Pattern){Add-Failure "$Description ontbreekt in $RelativePath."}
}

$requiredFiles=@(
  'LICENSE',
  'NOTICE',
  'README.md',
  'CONTRIBUTING.md',
  'CODE_SIGNING_POLICY.md',
  'PRIVACY.md',
  'SECURITY.md',
  'SUPPORT.md',
  'THIRD-PARTY-NOTICES.md',
  'licenses\Composer-MIT.txt',
  'licenses\Electron-MIT.txt',
  'licenses\Lucide-ISC.txt',
  'licenses\Mailpit-MIT.txt',
  'licenses\mkcert-BSD-3-Clause.txt',
  'licenses\node-qrcode-MIT.txt',
  'licenses\React-MIT.txt',
  'licenses\Redis-for-Windows-BSD-3-Clause.txt',
  'licenses\WinSW-MIT.txt',
  'licenses\yaml-ISC.txt',
  'runtime-packages\runtime-sources.json',
  'LocalDeck-Website\code-signing.php',
  '.github\CODEOWNERS',
  '.github\PULL_REQUEST_TEMPLATE.md',
  '.github\workflows\source-integrity.yml',
  '.signpath\policies\localdeck\release-signing.yml'
)
$requiredFiles|ForEach-Object{Assert-File $_}

Assert-Text 'LICENSE' '^\s*Apache License\s+Version 2\.0' 'Apache-2.0-licentietekst'
Assert-Text 'README.md' 'Free code signing provided by SignPath\.io, certificate by SignPath Foundation\.' 'vereiste SignPath-attributie'
Assert-Text 'README.md' '\[Code signing policy\]\(CODE_SIGNING_POLICY\.md\)' 'link naar het code-signingbeleid'
Assert-Text 'README.md' '\[Privacy\]\(PRIVACY\.md\)' 'link naar het privacybeleid'
Assert-Text 'CODE_SIGNING_POLICY.md' 'Existing public test releases are not signed by SignPath Foundation' 'eerlijke SignPath-status'
Assert-Text 'PRIVACY.md' 'Telemetry is not implemented' 'telemetrieverklaring'
Assert-Text 'electron\runtime.ts' 'autoUpdateCheck:false' 'privacyvriendelijke standaard voor updatecontrole'
Assert-Text 'CONTRIBUTING.md' 'multi-factor authentication' 'MFA-regel voor bijdragers'
Assert-Text 'LocalDeck-Website\code-signing.php' 'Free code signing provided by SignPath\.io, certificate by SignPath Foundation\.' 'SignPath-attributie op de website'
Assert-Text '.signpath\policies\localdeck\release-signing.yml' 'require_github_hosted:\s*true' 'verplichte GitHub-hosted SignPath-runner'
Assert-Text '.signpath\policies\localdeck\release-signing.yml' 'disallow_reruns:\s*true' 'blokkade op herstarten van signing-builds'

$packageFile=Join-Path $root 'package.json'
if(Test-Path -LiteralPath $packageFile){
  $package=Get-Content -LiteralPath $packageFile -Raw|ConvertFrom-Json
  if($package.license-ne'Apache-2.0'){Add-Failure 'package.json gebruikt niet Apache-2.0.'}
  if($package.repository.url-ne'https://github.com/Platinum-Radio/LocalDeck.git'){Add-Failure 'package.json verwijst niet naar de canonieke repository.'}
  if($package.packageManager-notmatch'^pnpm@\d+\.\d+\.\d+$'){Add-Failure 'package.json zet de pnpm-versie niet exact vast.'}
}

$sourceLockFile=Join-Path $root 'runtime-packages\runtime-sources.json'
$runtimeManifestFile=Join-Path $root 'runtime-packages\offline-runtime.json'
if((Test-Path -LiteralPath $sourceLockFile)-and(Test-Path -LiteralPath $runtimeManifestFile)){
  $sourceLock=Get-Content -LiteralPath $sourceLockFile -Raw|ConvertFrom-Json
  $runtimeManifest=Get-Content -LiteralPath $runtimeManifestFile -Raw|ConvertFrom-Json
  $sources=@($sourceLock.packages)
  $runtimePackages=@($runtimeManifest.packages)
  if($sources.Count-ne13){Add-Failure "De bron-lock bevat $($sources.Count) in plaats van 13 pakketten."}
  if($runtimePackages.Count-ne13){Add-Failure "Het runtime-manifest bevat $($runtimePackages.Count) in plaats van 13 pakketten."}
  if((@($sources.id|Sort-Object -Unique)).Count-ne$sources.Count){Add-Failure 'De bron-lock bevat dubbele pakket-id''s.'}
  if((@($sources.name|Sort-Object -Unique)).Count-ne$sources.Count){Add-Failure 'De bron-lock bevat dubbele bestandsnamen.'}
  foreach($source in $sources){
    if(([string]$source.url)-notmatch'^https://'){Add-Failure "Runtimebron $($source.id) gebruikt geen HTTPS."}
    if(([string]$source.url)-match'(?i)(/latest/|latest-stable|-latest\.)'){Add-Failure "Runtimebron $($source.id) is niet op een exacte versie vastgezet."}
    if(([string]$source.sha256)-notmatch'^[A-Fa-f0-9]{64}$'){Add-Failure "Runtimebron $($source.id) heeft geen geldige SHA-256."}
    if(([long]$source.bytes)-lt1){Add-Failure "Runtimebron $($source.id) heeft geen geldige bestandsgrootte."}
    if([string]::IsNullOrWhiteSpace([string]$source.license)){Add-Failure "Runtimebron $($source.id) heeft geen licentie-id."}
    if(([string]$source.licenseUrl)-notmatch'^https://'){Add-Failure "Runtimebron $($source.id) heeft geen HTTPS-licentielink."}
    $isSystemLibrary=($source.PSObject.Properties['systemLibrary']-and[bool]$source.systemLibrary)
    if(!$isSystemLibrary-and([string]$source.sourceCodeUrl)-notmatch'^https://'){Add-Failure "Runtimebron $($source.id) heeft geen broncodelink."}
    $prepared=$runtimePackages|Where-Object{$_.id-eq$source.id}|Select-Object -First 1
    if(!$prepared){Add-Failure "Runtimebron $($source.id) ontbreekt in offline-runtime.json.";continue}
    foreach($field in 'name','version'){
      if(([string]$prepared.$field)-ne([string]$source.$field)){Add-Failure "Veld $field wijkt af voor runtimebron $($source.id)."}
    }
    if(([string]$prepared.source)-ne([string]$source.url)){Add-Failure "De downloadbron wijkt af voor runtimebron $($source.id)."}
    if(!$source.PSObject.Properties['optimizationProfile']){
      if((([string]$prepared.sha256)-ne([string]$source.sha256))-or(([long]$prepared.bytes)-ne([long]$source.bytes))){Add-Failure "Het ongewijzigde runtimepakket $($source.id) wijkt af van de bron-lock."}
    }elseif(([long]$prepared.sourceBytes)-ne([long]$source.bytes)){
      Add-Failure "De oorspronkelijke pakketgrootte wijkt af voor $($source.id)."
    }
  }
}

$workflowFile=Join-Path $root '.github\workflows\source-integrity.yml'
if(Test-Path -LiteralPath $workflowFile){
  $workflow=Get-Content -LiteralPath $workflowFile -Raw
  if($workflow-match'(?m)^\s*pull_request_target\s*:'){Add-Failure 'De CI-workflow mag pull_request_target niet gebruiken.'}
  if($workflow-notmatch'(?m)^\s*contents:\s*read\s*$'){Add-Failure 'De CI-workflow heeft geen minimale contents: read-rechten.'}
  if($workflow-notmatch'actions/checkout@[a-f0-9]{40}'){Add-Failure 'actions/checkout is niet op een commit vastgezet.'}
  if($workflow-notmatch'actions/setup-node@[a-f0-9]{40}'){Add-Failure 'actions/setup-node is niet op een commit vastgezet.'}
}

$tracked=@(& git -C $root ls-files 2>$null)
if($LASTEXITCODE-eq0){
  $secretPatterns=@('(?i)(^|/)\.env($|\.)','(?i)\.(pfx|p12|jks|key|pem)$','(?i)(^|/)(secrets?|credentials?)(/|\.|$)')
  foreach($file in $tracked){
    if($secretPatterns|Where-Object{$file-match$_}|Select-Object -First 1){Add-Failure "Mogelijk geheim bestand staat in Git: $file"}
  }
}

if($failures.Count){
  Write-Host 'SignPath-readiness: FOUT' -ForegroundColor Red
  $failures|ForEach-Object{Write-Host " - $_" -ForegroundColor Red}
  exit 1
}

Write-Host 'SignPath-readiness: OK — beleid, licentie, bron-lock, CI en geheimencontrole zijn aanwezig.' -ForegroundColor Green
