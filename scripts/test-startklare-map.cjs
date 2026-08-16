const path=require('node:path');
const fs=require('node:fs');
const net=require('node:net');
const assert=require('node:assert/strict');
const{RuntimeManager}=require('../dist-electron/runtime.js');

const portFree=port=>new Promise(resolve=>{const server=net.createServer();server.once('error',()=>resolve(false));server.once('listening',()=>server.close(()=>resolve(true)));server.listen(port,'127.0.0.1')});
async function freePort(start,reserved=new Set()){for(let port=start;port<Math.min(65535,start+1000);port++)if(!reserved.has(port)&&await portFree(port))return port;throw new Error(`Geen vrije poort gevonden vanaf ${start}.`)}
const httpAddress=(host,port)=>`http://${host}${port===80?'':`:${port}`}`;

async function main(){
 const root=path.resolve(process.argv[2]||'');
 if(!root||!fs.existsSync(path.join(root,'LocalDeck.exe')))throw new Error('Geef de map LocalDeck-Startklaar op.');
 const runtimeDirectory=path.join(root,'runtime'),projectsDirectory=path.join(root,'websites');
 const runtime=new RuntimeManager(runtimeDirectory,{allowSimulation:false,bundledPackagesDirectory:path.join(root,'resources','installer','packages'),projectsDirectory,webRootDirectory:projectsDirectory,folderMode:true});
 try{
  const initial=runtime.snapshot();
  assert.equal(initial.installation.status,'installed','De runtime is niet volledig geïnstalleerd.');
  assert.equal(initial.settings.folderMode,true);
  assert.equal(initial.settings.projectsDirectory,path.resolve(projectsDirectory));
  assert(initial.projects.some(project=>project.path===path.join(projectsDirectory,'voorbeeld-website')),'De voorbeeldwebsite is niet automatisch ontdekt.');
  const defaults={apache:80,php:9000,mysql:3306,phpmyadmin:8080,mail:8025,redis:6379},chosen=new Set();
  for(const service of initial.services){const preferred=defaults[service.id];if(!chosen.has(preferred)&&await portFree(preferred))runtime.updateServicePort(service.id,preferred);else await runtime.autoPort(service.id);chosen.add(runtime.snapshot().services.find(item=>item.id===service.id).port)}
  const reserved=new Set(runtime.snapshot().services.map(service=>service.port));
  runtime.updateSettings({webControlPort:await freePort(runtime.snapshot().settings.webControlPort,reserved)});
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
  const phpMyAdmin=await fetch(`http://127.0.0.1:${phpMyAdminPort}/`);
  assert(phpMyAdmin.ok,'phpMyAdmin is niet bereikbaar.');
  const phpMyAdminBody=await phpMyAdmin.text();
  assert(!/id=["']input_username|name=["']pma_username/i.test(phpMyAdminBody),'phpMyAdmin toont nog een inlogscherm in plaats van automatisch aan te melden.');
  assert(/phpMyAdmin|Databases|Database/i.test(phpMyAdminBody),'Het phpMyAdmin-beheer werd niet geladen.');
  const databaseName=`localdeck_folder_test_${Date.now()}`;
  runtime.createDatabase(databaseName);
  assert(runtime.snapshot().databases.some(database=>database.name===databaseName),'De testdatabase kon niet worden aangemaakt.');
  runtime.deleteDatabase(runtime.snapshot().databases.find(database=>database.name===databaseName).id);
  console.log('GESLAAGD: zes services, localhost, 127.0.0.1, PHP-website, phpMyAdmin-autologin en MySQL zijn gecontroleerd.');
 }finally{await runtime.shutdown()}
}

main().catch(error=>{console.error(error instanceof Error?error.stack:error);process.exitCode=1});
