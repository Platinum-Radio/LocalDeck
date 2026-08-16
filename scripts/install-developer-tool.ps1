[CmdletBinding()]
param([Parameter(Mandatory=$true)][ValidateSet('node','git','vscode')][string]$Tool)
$ErrorActionPreference='Stop'
$winget=Get-Command winget.exe -ErrorAction SilentlyContinue;if(!$winget){throw 'Windows Package Manager (winget) ontbreekt.'}
$package=switch($Tool){'node'{'OpenJS.NodeJS.LTS'}'git'{'Git.Git'}'vscode'{'Microsoft.VisualStudioCode'}}
& $winget.Source install --id $package --silent --accept-package-agreements --accept-source-agreements --disable-interactivity
if($LASTEXITCODE-ne0){throw "$Tool kon niet worden geïnstalleerd."}
