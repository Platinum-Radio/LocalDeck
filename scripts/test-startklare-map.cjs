const path=require('node:path');
const fs=require('node:fs');
const net=require('node:net');
const http=require('node:http');
const assert=require('node:assert/strict');
const{RuntimeManager}=require('../dist-electron/runtime.js');

const portFree=port=>new Promise(resolve=>{const server=net.createServer();server.once('error',()=>resolve(false));server.once('listening',()=>server.close(()=>resolve(true)));server.listen(port,'127.0.0.1')});
async function freePort(start,reserved=new Set()){for(let port=start;port<Math.min(65535,start+1000);port++)if(!reserved.has(port)&&await portFree(port))return port;throw new Error(`Geen vrije poort gevonden vanaf ${start}.`)}
const httpAddress=(host,port)=>`http://${host}${port===80?'':`:${port}`}`;
const requestVhost=(port,host,pathname)=>new Promise((resolve,reject)=>{const request=http.get({hostname:'127.0.0.1',port,path:pathname,headers:{host}},response=>{let body='';response.setEncoding('utf8');response.on('data',chunk=>body+=chunk);response.on('end',()=>resolve({status:response.statusCode||0,body}))});request.once('error',reject)});

async function main(){
 const root=path.resolve(process.argv[2]||'');
 if(!root||!fs.existsSync(path.join(root,'LocalDeck.exe')))throw new Error('Geef de map LocalDeck-Startklaar op.');
 const runtimeDirectory=path.join(root,'runtime'),projectsDirectory=path.join(root,'websites');
 const runtime=new RuntimeManager(runtimeDirectory,{allowSimulation:false,bundledPackagesDirectory:path.join(root,'resources','installer','packages'),projectsDirectory,webRootDirectory:projectsDirectory,folderMode:true});
 let php86Probe;
 try{
  const initial=runtime.snapshot();
  assert.equal(initial.installation.status,'installed','De runtime is niet volledig geïnstalleerd.');
  assert.equal(initial.settings.folderMode,true);
  assert.equal(initial.settings.projectsDirectory,path.resolve(projectsDirectory));
  assert(initial.projects.some(project=>project.path===path.join(projectsDirectory,'voorbeeld-website')),'De voorbeeldwebsite is niet automatisch ontdekt.');
  const manifest=JSON.parse(fs.readFileSync(path.join(runtimeDirectory,'runtime.json'),'utf8').replace(/^\uFEFF/,''));
  assert.equal(manifest.apache,'2.4.71');
  assert.equal(manifest.mysql,'8.4.13');
  assert.equal(manifest.mailpit,'1.34.0');
  assert.equal(manifest.redis,'8.10.3');
  assert.deepEqual(manifest.php,['8.2','8.3','8.4','8.5','8.6']);
  const example=initial.projects.find(project=>project.path===path.join(projectsDirectory,'voorbeeld-website'));
  runtime.setProjectPhpVersion(example.id,'8.6');
  php86Probe=path.join(example.path,'localdeck-php-version.php');
  fs.writeFileSync(php86Probe,"<?php echo PHP_VERSION;\n",'utf8');
  const testRanges={apache:18080,php:19000,mysql:13306,phpmyadmin:18081,mail:18025,redis:16379},chosen=new Set();
  for(const service of initial.services){const selected=await freePort(testRanges[service.id],chosen);runtime.updateServicePort(service.id,selected);chosen.add(selected)}
  const reserved=new Set(runtime.snapshot().services.map(service=>service.port));
  const webControlPort=await freePort(17331,reserved);reserved.add(webControlPort);
  const smtpPort=await freePort(11025,reserved);
  runtime.updateSettings({webControlPort,smtpPort});
  await runtime.toggleAll(true);
  const running=runtime.snapshot();
  assert(running.services.every(service=>service.status==='running'&&service.health==='healthy'),JSON.stringify(running.services));
  const apachePort=running.services.find(service=>service.id==='apache').port,phpMyAdminPort=running.services.find(service=>service.id==='phpmyadmin').port;
  const home=await fetch(`${httpAddress('localhost',apachePort)}/`);
  assert(home.ok,'localhost is niet bereikbaar.');
  assert((await home.text()).includes('Alles draait'),'De LocalDeck-startpagina ontbreekt op localhost.');
  const loopback=await fetch(`${httpAddress('127.0.0.1',apachePort)}/`);
  assert(loopback.ok,'127.0.0.1 is niet bereikbaar.');
  const website=await fetch(`${httpAddress('localhost',apachePort)}/voorbeeld-website/`);
  const websiteBody=await website.text();
  assert(website.ok,`De voorbeeldwebsite is niet bereikbaar (${website.status}): ${websiteBody.slice(0,300)}`);
  assert(websiteBody.includes('De mapmodus'),`De PHP-voorbeeldwebsite is niet uitgevoerd: ${websiteBody.slice(0,300)}`);
  const php86=await requestVhost(apachePort,example.domain,'/localdeck-php-version.php'),php86Body=php86.body;
  assert(php86.status>=200&&php86.status<300,`De PHP 8.6-projectpool is niet bereikbaar (${php86.status}): ${php86Body.slice(0,300)}`);
  assert(php86Body.startsWith('8.6.0'),`Het PHP 8.6-project gebruikte niet de verwachte runtime: ${php86Body.slice(0,100)}`);
  const phpMyAdmin=await fetch(`http://127.0.0.1:${phpMyAdminPort}/`);
  assert(phpMyAdmin.ok,'phpMyAdmin is niet bereikbaar.');
  const phpMyAdminBody=await phpMyAdmin.text();
  assert(!/id=["']input_username|name=["']pma_username/i.test(phpMyAdminBody),'phpMyAdmin toont nog een inlogscherm in plaats van automatisch aan te melden.');
  assert(/phpMyAdmin|Databases|Database/i.test(phpMyAdminBody),'Het phpMyAdmin-beheer werd niet geladen.');
  const databaseName=`localdeck_folder_test_${Date.now()}`;
  runtime.createDatabase(databaseName);
  assert(runtime.snapshot().databases.some(database=>database.name===databaseName),'De testdatabase kon niet worden aangemaakt.');
  runtime.deleteDatabase(runtime.snapshot().databases.find(database=>database.name===databaseName).id);
  await runtime.toggleAll(false);
  runtime.setProjectPhpVersion(example.id,'8.5');
  console.log('GESLAAGD: zes services, localhost, 127.0.0.1, PHP 8.6, phpMyAdmin-autologin en MySQL zijn gecontroleerd.');
 }finally{if(php86Probe&&fs.existsSync(php86Probe))fs.unlinkSync(php86Probe);await runtime.shutdown()}
}

main().catch(error=>{console.error(error instanceof Error?error.stack:error);process.exitCode=1});
