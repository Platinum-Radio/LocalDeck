import{describe,expect,it}from'vitest';
import{readFileSync}from'node:fs';
import path from'node:path';

describe('offline Windows-installer',()=>{
 const script=readFileSync(path.join(process.cwd(),'scripts','install-runtime.ps1'),'utf8');
 const secretScript=readFileSync(path.join(process.cwd(),'scripts','get-secret.ps1'),'utf8');
 const hostsScript=readFileSync(path.join(process.cwd(),'scripts','configure-hosts.ps1'),'utf8');

 it('installeert alleen uit de meegeleverde pakketmap',()=>{
  expect(script).toContain("[string]$PackageDir = (Join-Path $PSScriptRoot 'packages')");
  expect(script).toContain('Assert-PackageSet');
  expect(script).not.toMatch(/Invoke-WebRequest|Invoke-RestMethod|Start-BitsTransfer|winget(?:\.exe)?/i);
 });

 it('gebruikt volledig gekwalificeerde DPAPI-types',()=>{
  expect(script).toContain('[System.Security.Cryptography.ProtectedData]::Protect');
  expect(script).toContain('[System.Security.Cryptography.ProtectedData]::Unprotect');
  expect(script).toContain('[System.Security.Cryptography.DataProtectionScope]::CurrentUser');
  expect(script).not.toMatch(/(?<!System\.)Security\.Cryptography\.ProtectedData/);
  expect(secretScript).toContain('Add-Type -AssemblyName System.Security');
  expect(secretScript).toContain('[System.Security.Cryptography.ProtectedData]::Unprotect');
  expect(secretScript).not.toMatch(/(?<!System\.)Security\.Cryptography\.ProtectedData/);
 });

 it('controleert alle dertien runtimeonderdelen en hun SHA-256',()=>{
  for(const name of['apache.zip','php.zip','php-8.2.zip','php-8.3.zip','php-8.4.zip','mysql.zip','phpmyadmin.zip','mailpit.zip','redis.zip','WinSW.NET4.exe','composer.phar','vc_redist.x64.exe','mkcert.exe'])expect(script).toContain(`'${name}'`);
  expect(script).toContain('Get-FileHash -LiteralPath $file -Algorithm SHA256');
  expect(script).toContain('Get-Command tar.exe');
  expect(script).not.toContain('Expand-Archive -LiteralPath $zip');
  expect(script).toContain('& robocopy.exe $Source $Destination');
  expect(script).toContain('function Remove-TreeSafe');
 });

 it('maakt zonder download een eigen certificaat voor ieder lokaal project',()=>{
  expect(hostsScript).toContain("'certs\\projects'");
  expect(hostsScript).toContain("'certificate.pem'");
  expect(hostsScript).toContain("'private-key.pem'");
  expect(hostsScript).toContain('& $mkcert -install');
  expect(hostsScript).toContain('& $mkcert -cert-file $certificateFile -key-file $keyFile $domain');
  expect(hostsScript).not.toMatch(/Invoke-WebRequest|Invoke-RestMethod|Start-BitsTransfer|winget(?:\.exe)?/i);
 });
});
