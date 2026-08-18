[CmdletBinding()]
param(
  [Parameter(Mandatory=$true)][string]$PackageDirectory
)

Set-StrictMode -Version Latest
$ErrorActionPreference='Stop'
Add-Type -AssemblyName System.IO.Compression.FileSystem

$packageRoot=[IO.Path]::GetFullPath($PackageDirectory)
if(!(Test-Path -LiteralPath $packageRoot)){throw "Runtimepakketmap ontbreekt: $packageRoot"}
foreach($stale in Get-ChildItem -LiteralPath $packageRoot -File -Force|Where-Object{$_.Name-like'.*.optimize-*.tmp'}){
  if([IO.Path]::GetFullPath($stale.DirectoryName)-ne$packageRoot){throw "Onveilige tijdelijke pakketlocatie: $($stale.FullName)"}
  Remove-Item -LiteralPath $stale.FullName -Force
}

function Test-RemoveEntry([string]$Package,[string]$Name){
  $normalized=$Name.Replace('\','/')
  switch($Package){
    'mysql.zip' {
      return $normalized-match'(?i)(\.pdb$|\.lib$|(^|/)include/|/lib/plugin/debug/|/bin/[^/]+-debug\.dll$)'
    }
    'redis.zip' {
      return $normalized-match'(?i)\.pdb$'
    }
    'apache.zip' {
      return $normalized-match'(?i)/(include|lib)/'
    }
    default {return $false}
  }
}

function Get-RequiredMarkers([string]$Package){
  switch($Package){
    'mysql.zip' {return @('bin/mysqld.exe','bin/mysql.exe','bin/mysqladmin.exe','bin/mysqldump.exe')}
    'redis.zip' {return @('redis-server.exe','redis-cli.exe')}
    'apache.zip' {return @('bin/httpd.exe','modules/mod_ssl.so','modules/mod_proxy_fcgi.so')}
    default {return @()}
  }
}

function Assert-ZipMarkers([string]$Archive,[string[]]$Markers){
  $zip=[IO.Compression.ZipFile]::OpenRead($Archive)
  try{
    $names=@($zip.Entries|ForEach-Object{$_.FullName.Replace('\','/')})
    foreach($marker in $Markers){
      if(!($names|Where-Object{$_.EndsWith($marker,[StringComparison]::OrdinalIgnoreCase)}|Select-Object -First 1)){
        throw "Verplicht runtimebestand ontbreekt na opschonen van $(Split-Path $Archive -Leaf): $marker"
      }
    }
  }finally{$zip.Dispose()}
}

function Optimize-Zip([string]$Name){
  $archive=Join-Path $packageRoot $Name
  if(!(Test-Path -LiteralPath $archive)){throw "Runtimepakket ontbreekt: $Name"}
  $before=(Get-Item -LiteralPath $archive).Length
  $temporary=Join-Path $packageRoot ('.'+$Name+'.optimize-'+[guid]::NewGuid().ToString('N')+'.tmp')
  $backup=Join-Path $packageRoot ('.'+$Name+'.backup-'+[guid]::NewGuid().ToString('N')+'.tmp')
  $source=$null
  $destination=$null
  $removedCount=0
  $removedBytes=[long]0
  try{
    $source=[IO.Compression.ZipFile]::OpenRead($archive)
    $destination=[IO.Compression.ZipFile]::Open($temporary,[IO.Compression.ZipArchiveMode]::Create)
    foreach($entry in $source.Entries){
      if(Test-RemoveEntry $Name $entry.FullName){$removedCount++;$removedBytes+=$entry.CompressedLength;continue}
      $newEntry=$destination.CreateEntry($entry.FullName,[IO.Compression.CompressionLevel]::Optimal)
      if($entry.LastWriteTime.Year-ge1980){$newEntry.LastWriteTime=$entry.LastWriteTime}
      if($entry.Length-gt0){
        $input=$entry.Open()
        $output=$newEntry.Open()
        try{$input.CopyTo($output)}finally{$output.Dispose();$input.Dispose()}
      }
    }
  }finally{
    if($destination){$destination.Dispose()}
    if($source){$source.Dispose()}
  }
  if($removedCount-eq0){Remove-Item -LiteralPath $temporary -Force;Write-Host "[compact]  $Name was al opgeschoond" -ForegroundColor DarkGray;return $before}
  Assert-ZipMarkers $temporary (Get-RequiredMarkers $Name)
  try{
    [IO.File]::Replace($temporary,$archive,$backup)
    Remove-Item -LiteralPath $backup -Force
  }catch{
    if(Test-Path -LiteralPath $temporary){Remove-Item -LiteralPath $temporary -Force}
    throw
  }
  $after=(Get-Item -LiteralPath $archive).Length
  Write-Host ("[compact]  {0}: {1:N1} MB -> {2:N1} MB ({3} bestanden verwijderd)" -f $Name,($before/1MB),($after/1MB),$removedCount) -ForegroundColor Green
  return $before
}

$targets=@('mysql.zip','redis.zip','apache.zip')
$sourceSizes=@{}
foreach($target in $targets){$sourceSizes[$target]=Optimize-Zip $target}

$manifestFile=Join-Path $packageRoot 'offline-runtime.json'
if(Test-Path -LiteralPath $manifestFile){
  $manifest=Get-Content -LiteralPath $manifestFile -Raw|ConvertFrom-Json
  foreach($package in $manifest.packages){
    $file=Join-Path $packageRoot $package.name
    if(!(Test-Path -LiteralPath $file)){continue}
    $package.sha256=(Get-FileHash -LiteralPath $file -Algorithm SHA256).Hash
    $package.bytes=(Get-Item -LiteralPath $file).Length
    if($sourceSizes.ContainsKey([string]$package.name)){
      if(!$package.PSObject.Properties['sourceBytes']){$package|Add-Member -NotePropertyName sourceBytes -NotePropertyValue ([long]$sourceSizes[[string]$package.name])}
      $package|Add-Member -NotePropertyName packaging -NotePropertyValue 'windows-x64-runtime-only' -Force
    }else{
      if($package.PSObject.Properties['sourceBytes']){$package.PSObject.Properties.Remove('sourceBytes')}
      if($package.PSObject.Properties['packaging']-and$package.packaging-eq'windows-x64-runtime-only'){$package.PSObject.Properties.Remove('packaging')}
    }
  }
  $manifest|Add-Member -NotePropertyName optimization -NotePropertyValue ([ordered]@{profile='windows-x64-runtime-only';rulesVersion=1;optimizedAt=(Get-Date).ToString('o')}) -Force
  $manifest|ConvertTo-Json -Depth 8|Set-Content -LiteralPath $manifestFile -Encoding UTF8
}

Write-Host "Runtimepakketten zijn compact en blijven volledig offline bruikbaar: $packageRoot" -ForegroundColor Green
