[CmdletBinding()]param([switch]$AllowUnsigned)
$ErrorActionPreference='Stop';$root=[IO.Path]::GetFullPath((Join-Path $PSScriptRoot '..'));Push-Location $root
try{
  if(!$AllowUnsigned-and!$env:CSC_LINK){throw 'CSC_LINK ontbreekt. Gebruik een code-signingcertificaat of -AllowUnsigned voor een interne preview.'}
  $version=(Get-Content package.json -Raw|ConvertFrom-Json).version
  pnpm check;if($LASTEXITCODE-ne0){throw 'Tests zijn mislukt.'}
  pnpm build;if($LASTEXITCODE-ne0){throw 'Build is mislukt.'}
  pnpm exec electron-builder --win nsis portable;if($LASTEXITCODE-ne0){throw 'Windows-build is mislukt.'}
  $unpacked=Join-Path $root 'release\win-unpacked';if(!(Test-Path $unpacked)){throw 'De uitgepakte Windows-build ontbreekt.'}
  $zip=Join-Path $root "release\LocalDeck-$version-Windows-x64.zip";Compress-Archive -Path (Join-Path $unpacked '*') -DestinationPath $zip -CompressionLevel Optimal -Force
  Get-ChildItem release -File|ForEach-Object{Get-FileHash $_.FullName -Algorithm SHA256}|Format-Table -AutoSize
}finally{Pop-Location}
