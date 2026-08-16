[CmdletBinding()]
param(
  [Parameter(Mandatory)][string]$File,
  [string]$Publisher=''
)
$ErrorActionPreference='Stop'
$File=[IO.Path]::GetFullPath($File)
if(!(Test-Path -LiteralPath $File -PathType Leaf)){throw 'Het gedownloade updatebestand ontbreekt.'}
$signature=Get-AuthenticodeSignature -LiteralPath $File
if($signature.Status-ne[Management.Automation.SignatureStatus]::Valid){throw "De update heeft geen geldige Authenticode-handtekening ($($signature.Status)). Het bestand is niet gestart."}
if($Publisher-and$signature.SignerCertificate.Subject-notlike"*$Publisher*"){throw "De uitgever van de update komt niet overeen met '$Publisher'."}
Write-Output $signature.SignerCertificate.Subject
