[CmdletBinding()]
param()

Set-StrictMode -Version Latest
$ErrorActionPreference='Stop'
$work=Join-Path ([IO.Path]::GetTempPath()) ("LocalDeck-support-test-"+[guid]::NewGuid().ToString('N'))
$runtime=Join-Path $work 'runtime';$expanded=Join-Path $work 'expanded';$archive=Join-Path $work 'support.zip'
try{
  New-Item -ItemType Directory -Path (Join-Path $runtime 'logs'),(Join-Path $runtime 'apache\conf') -Force|Out-Null
  [ordered]@{
    services=@([ordered]@{id='mysql';version='8.4';port=3306;installed=$true})
    projects=@([ordered]@{name='Geheim project';domain='klant.test';path='C:\Users\Ramon\Sites\Klant';documentRoot='C:\Users\Ramon\Sites\Klant\public';phpVersion='8.5';secure=$true;template='custom';profile=[ordered]@{isolated=$false}})
    databases=@([ordered]@{name='klant_database'})
    mailAddresses=@([ordered]@{address='ramon@example.com'})
    snapshots=@()
    webhookEvents=@([ordered]@{body='password=SuperSecret123';headers=[ordered]@{authorization='Bearer abcdef'}})
    settings=[ordered]@{smtpUsername='ramon@example.com';updateFeedUrl='https://example.com/feed?token=SuperSecret123';dataDirectory=$runtime;projectsDirectory='C:\Users\Ramon\Sites';language='nl';folderMode=$true;runtimeMode='application';defaultPhpVersion='8.5';autoBackup=$true;backupSchedule='daily';backupRetention=7;webControlEnabled=$true;webControlPort=7331;dnsMode='hosts';mailMode='capture';atomicUpdates=$true;requireSignedUpdates=$true;offlineFirst=$true;lanSharingEnabled=$false}
    atomicUpdate=[ordered]@{status='staged';currentVersion='1.1.0';lastKnownGoodVersion='1.0.0';stagedFile='C:\Users\Ramon\Downloads\update.exe'}
  }|ConvertTo-Json -Depth 12|Set-Content -LiteralPath (Join-Path $runtime 'state.json') -Encoding UTF8
  'Authorization: Bearer abcdef; password=SuperSecret123; mail=ramon@example.com; project=klant.test; path=C:\Users\Ramon\Sites\Klant; remote=192.168.1.20'|Set-Content -LiteralPath (Join-Path $runtime 'logs\localdeck.log') -Encoding UTF8
  'password=SuperSecret123'|Set-Content -LiteralPath (Join-Path $runtime 'my.ini') -Encoding UTF8
  '{}'|Set-Content -LiteralPath (Join-Path $runtime 'runtime.json') -Encoding UTF8
  & powershell.exe -NoLogo -NoProfile -NonInteractive -ExecutionPolicy Bypass -File (Join-Path $PSScriptRoot 'export-support-bundle.ps1') -RuntimeDir $runtime -OutputFile $archive|Out-Null
  if($LASTEXITCODE-ne0-or!(Test-Path -LiteralPath $archive)){throw 'De privacytest kon geen supportbundel maken.'}
  Expand-Archive -LiteralPath $archive -DestinationPath $expanded
  $content=(Get-ChildItem -LiteralPath $expanded -File -Recurse|ForEach-Object{Get-Content -LiteralPath $_.FullName -Raw})-join"`n"
  foreach($forbidden in @('SuperSecret123','ramon@example.com','C:\Users\Ramon','klant.test','192.168.1.20','Bearer abcdef')){if($content.IndexOf($forbidden,[StringComparison]::OrdinalIgnoreCase)-ge0){throw "Supportbundel lekt testwaarde: $forbidden"}}
  foreach($required in @('state-sanitized.json','support-manifest.json','system.json')){if(!(Test-Path -LiteralPath (Join-Path $expanded $required))){throw "Supportbundel mist $required"}}
  Write-Host 'support-bundle-privacy: OK'
}finally{
  if(Test-Path -LiteralPath $work){Remove-Item -LiteralPath $work -Recurse -Force}
}
