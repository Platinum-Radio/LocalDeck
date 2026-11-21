[CmdletBinding()]
param()

Set-StrictMode -Version Latest
$ErrorActionPreference='Stop'
$work=Join-Path ([IO.Path]::GetTempPath()) ("LocalDeck-workspace-test-"+[guid]::NewGuid().ToString('N'))
$runtime=Join-Path $work 'runtime';$projects=Join-Path $work 'websites';$archive=Join-Path $work 'workspace.zip';$expanded=Join-Path $work 'expanded'
try{
  New-Item -ItemType Directory -Path $runtime,$projects -Force|Out-Null
  '<h1>test</h1>'|Set-Content -LiteralPath (Join-Path $projects 'index.html') -Encoding UTF8
  [ordered]@{
    projects=@([ordered]@{name='Test';repositoryUrl='https://user:RepoPassword@example.com/private.git';profile=[ordered]@{env=[ordered]@{APP_ENV='local';API_TOKEN='WorkspaceSecret'}}})
    webhookEndpoints=@([ordered]@{name='Test';secret='WebhookSecret'})
    webhookEvents=@([ordered]@{body='RequestSecret'})
    apiRuns=@([ordered]@{requestBody='ApiRequestSecret'})
    replayCaptures=@([ordered]@{body='ReplaySecret'})
    atomicUpdate=[ordered]@{status='staged';stagedFile='C:\Private\update.exe';recoveryFile='C:\Private\backup.zip'}
    settings=[ordered]@{smtpUsername='ramon@example.com';updateFeedUrl='https://example.com/feed?token=FeedSecret';dataDirectory=$runtime;projectsDirectory=$projects}
  }|ConvertTo-Json -Depth 12|Set-Content -LiteralPath (Join-Path $runtime 'state.json') -Encoding UTF8
  & powershell.exe -NoLogo -NoProfile -NonInteractive -ExecutionPolicy Bypass -File (Join-Path $PSScriptRoot 'export-workspace.ps1') -RuntimeDir $runtime -ProjectsDir $projects -OutputFile $archive|Out-Null
  if($LASTEXITCODE-ne0-or!(Test-Path -LiteralPath $archive)){throw 'De werkruimteprivacytest kon geen ZIP maken.'}
  Expand-Archive -LiteralPath $archive -DestinationPath $expanded
  $payload=Join-Path $expanded 'LocalDeck-Workspace';$state=Get-Content -LiteralPath (Join-Path $payload 'state.portable.json') -Raw
  foreach($forbidden in @('RepoPassword','WorkspaceSecret','WebhookSecret','RequestSecret','ApiRequestSecret','ReplaySecret','FeedSecret','ramon@example.com','C:\Private')){if($state.IndexOf($forbidden,[StringComparison]::OrdinalIgnoreCase)-ge0){throw "Werkruimte-export lekt testwaarde: $forbidden"}}
  $portable=$state|ConvertFrom-Json
  if(@($portable.webhookEvents).Count-or@($portable.apiRuns).Count-or@($portable.replayCaptures).Count){throw 'Werkruimte-export bevat nog vluchtige requestgegevens.'}
  if($portable.webhookEndpoints[0].secret-ne'[wordt na import vernieuwd]'){throw 'Webhookgeheim is niet vervangen.'}
  if(!(Test-Path -LiteralPath (Join-Path $payload 'workspace-manifest.json'))){throw 'Werkruimtemanifest ontbreekt.'}
  Write-Host 'workspace-export-privacy: OK'
}finally{
  if(Test-Path -LiteralPath $work){Remove-Item -LiteralPath $work -Recurse -Force}
}
