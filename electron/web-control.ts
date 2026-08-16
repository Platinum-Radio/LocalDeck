import{createServer,type IncomingMessage,type ServerResponse}from'node:http';
import{randomBytes}from'node:crypto';
import{existsSync,readFileSync,writeFileSync}from'node:fs';
import path from'node:path';
import type{RuntimeManager}from'./runtime.js';
import type{AppState,ServiceId}from'./types.js';

export interface WebControlActions{
 toggleService(id:ServiceId):Promise<unknown>;
 toggleAll(start:boolean):Promise<unknown>;
 updatePort(id:ServiceId,port:number):Promise<unknown>;
 refreshService(id:ServiceId):Promise<unknown>;
}

function body(request:IncomingMessage){return new Promise<any>((resolve,reject)=>{let value='';request.on('data',chunk=>{value+=chunk;if(value.length>64*1024)reject(new Error('Verzoek is te groot.'))});request.on('end',()=>{try{resolve(value?JSON.parse(value):{})}catch{reject(new Error('Ongeldige JSON.'))}})})}
function rawBody(request:IncomingMessage){return new Promise<string>((resolve,reject)=>{let value='';request.on('data',chunk=>{value+=String(chunk);if(value.length>256*1024)reject(new Error('Webhook is groter dan 256 KB.'))});request.on('end',()=>resolve(value));request.on('error',reject)})}
function json(response:ServerResponse,status:number,value:unknown){response.writeHead(status,{'content-type':'application/json; charset=utf-8','cache-control':'no-store'});response.end(JSON.stringify(value))}
const pageHeaders={'content-type':'text/html; charset=utf-8','cache-control':'no-store','content-security-policy':"default-src 'self'; style-src 'unsafe-inline'; script-src 'self' 'unsafe-inline'; connect-src 'self'; img-src 'self' data:; base-uri 'none'; frame-ancestors 'none'; form-action 'self'",'x-content-type-options':'nosniff','referrer-policy':'no-referrer'};
const cleanId=(value:string,label:string)=>{if(!/^[A-Za-z0-9._:-]{1,200}$/.test(value))throw new Error(`Ongeldige ${label}.`);return value};
const cleanTag=(value:string)=>{const tag=value.trim();if(!tag||tag.length>50||/[\u0000-\u001f]/.test(tag))throw new Error('Gebruik een tag van 1–50 geldige tekens.');return tag};
async function mailpitRequest(runtime:RuntimeManager,resource:string,init:RequestInit={}){
 const mail=runtime.snapshot().services.find(service=>service.id==='mail');if(!mail?.installed&&!runtime.snapshot().settings.simulationMode)throw new Error('Mailpit is niet geïnstalleerd.');
 try{return await fetch(`http://127.0.0.1:${mail?.port??8025}${resource}`,{...init,headers:{accept:'application/json, text/plain, */*',...(init.body?{'content-type':'application/json'}:{}),...init.headers},signal:AbortSignal.timeout(6000)})}
 catch{throw new Error('Mailpit is niet bereikbaar. Start de Mailpit-service en probeer opnieuw.')}
}
async function relayMailpit(runtime:RuntimeManager,response:ServerResponse,resource:string,init:RequestInit={},downloadName?:string){const upstream=await mailpitRequest(runtime,resource,init),contentType=upstream.headers.get('content-type')||'application/octet-stream',headers:Record<string,string>={'content-type':contentType,'cache-control':'no-store','x-content-type-options':'nosniff'};if(downloadName)headers['content-disposition']=`attachment; filename="${downloadName.replace(/[^A-Za-z0-9._-]/g,'_')}"`;response.writeHead(upstream.status,headers);response.end(Buffer.from(await upstream.arrayBuffer()))}

export function getWebControlToken(dataDirectory:string){const file=path.join(dataDirectory,'web-control-token.txt');if(existsSync(file))return readFileSync(file,'utf8').trim();const token=randomBytes(32).toString('hex');writeFileSync(file,token,{encoding:'utf8',mode:0o600});return token}

export function startWebControl(runtime:RuntimeManager,staticDirectory:string,actions:WebControlActions){
 const token=getWebControlToken(runtime.snapshot().settings.dataDirectory),index=path.join(staticDirectory,'index.html'),mail=path.join(staticDirectory,'mail.html'),i18n=path.join(staticDirectory,'i18n.js');
 const server=createServer(async(request,response)=>{try{
  const url=new URL(request.url||'/',`http://${request.headers.host||'127.0.0.1'}`),cookie=request.headers.cookie||'';
   if(url.pathname.startsWith('/hooks/')){if(request.method==='OPTIONS'){response.writeHead(204,{'access-control-allow-origin':'*','access-control-allow-methods':'GET,POST,PUT,PATCH,DELETE,OPTIONS','access-control-allow-headers':'content-type,x-localdeck-signature'});response.end();return}runtime.reloadPersistedState();const headers=Object.fromEntries(Object.entries(request.headers).flatMap(([key,value])=>value===undefined?[]:[[key,Array.isArray(value)?value.join(', '):value]])),event=runtime.recordWebhook(url.pathname,request.method||'POST',headers,await rawBody(request));if(!event){json(response,404,{error:'Webhook-endpoint bestaat niet of is uitgeschakeld.'});return}response.writeHead(202,{'content-type':'application/json; charset=utf-8','cache-control':'no-store','access-control-allow-origin':'*'});response.end(JSON.stringify({accepted:true,id:event.id,receivedAt:event.receivedAt}));return}
   const authenticated=url.searchParams.get('token')===token||cookie.split(';').some(item=>item.trim()===`localdeck_token=${token}`);
  if(url.searchParams.get('token')===token&&(request.method==='GET'||request.method==='HEAD')){response.writeHead(302,{location:url.pathname==='/'?'/':url.pathname, 'set-cookie':`localdeck_token=${token}; HttpOnly; SameSite=Strict; Path=/`});response.end();return}
  if(!authenticated){response.writeHead(401,{'content-type':'text/html; charset=utf-8'});response.end('<h1>LocalDeck</h1><p>Open webbeheer vanuit de LocalDeck-app.</p>');return}
  const origin=request.headers.origin;if(request.method!=='GET'&&request.method!=='HEAD'&&origin&&origin!==url.origin){json(response,403,{error:'Dit verzoek komt niet uit LocalDeck Webbeheer.'});return}
  runtime.reloadPersistedState();
  if(request.method==='GET'&&url.pathname==='/i18n.js'){response.writeHead(200,{'content-type':'application/javascript; charset=utf-8','cache-control':'no-store','x-content-type-options':'nosniff'});response.end(existsSync(i18n)?readFileSync(i18n,'utf8'):'');return}
  if(request.method==='GET'&&url.pathname==='/'){response.writeHead(200,pageHeaders);response.end(existsSync(index)?readFileSync(index,'utf8'):'<h1>LocalDeck Webbeheer</h1>');return}
  if(request.method==='GET'&&url.pathname==='/mail'){response.writeHead(200,pageHeaders);response.end(existsSync(mail)?readFileSync(mail,'utf8'):'<h1>LocalDeck Mailbeheer</h1>');return}
   if(request.method==='GET'&&url.pathname==='/api/state'){if(!runtime.snapshot().settings.simulationMode)await runtime.refreshServiceStates();runtime.refreshResources();const state=runtime.snapshot();json(response,200,{services:state.services,projects:state.projects,blueprints:state.blueprints,branchEnvironments:state.branchEnvironments,components:state.components,componentPacks:state.componentPacks,databases:state.databases,mailAddresses:state.mailAddresses,productionParity:state.productionParity,preflightReports:state.preflightReports,testLabReports:state.testLabReports,secretVault:state.secretVault,portConflicts:state.portConflicts,portBindings:state.portBindings,startProfiles:state.startProfiles,actionOperations:state.actionOperations.slice(0,30),engineeringReports:state.engineeringReports.slice(0,30),apiWorkspaces:state.apiWorkspaces,webhookEndpoints:state.webhookEndpoints,webhookEvents:state.webhookEvents.slice(0,50),workers:state.workers,devDrive:state.devDrive,crashRecovery:state.crashRecovery,atomicUpdate:state.atomicUpdate,resources:state.resourceUsage,inspectorEvents:state.inspectorEvents.slice(0,30),settings:{language:state.settings.language,resourceSaver:state.settings.resourceSaver,idleStopMinutes:state.settings.idleStopMinutes,offlineFirst:state.settings.offlineFirst,lanSharingEnabled:state.settings.lanSharingEnabled,mcpEnabled:state.settings.mcpEnabled,restoreServicesAfterCrash:state.settings.restoreServicesAfterCrash,atomicUpdates:state.settings.atomicUpdates,privacyMode:state.settings.privacyMode,smtpHost:state.settings.smtpHost,smtpPort:state.settings.smtpPort},runtimeMode:state.settings.runtimeMode,version:state.update.currentVersion});return}
  if(request.method==='GET'&&url.pathname==='/api/mailpit/info'){await relayMailpit(runtime,response,'/api/v1/info');return}
  if(request.method==='GET'&&url.pathname==='/api/mailpit/tags'){await relayMailpit(runtime,response,'/api/v1/tags');return}
  if(request.method==='PUT'&&url.pathname==='/api/mailpit/tags'){const value=await body(request),ids=Array.isArray(value.IDs)?value.IDs.map((id:unknown)=>cleanId(String(id),'bericht-ID')).slice(0,500):[],tags=Array.isArray(value.Tags)?value.Tags.map((tag:unknown)=>cleanTag(String(tag))).slice(0,20):[];if(!ids.length)throw new Error('Selecteer minimaal één bericht.');await relayMailpit(runtime,response,'/api/v1/tags',{method:'PUT',body:JSON.stringify({IDs:ids,Tags:tags})});return}
  const tagMatch=url.pathname.match(/^\/api\/mailpit\/tags\/([^/]+)$/);
  if(request.method==='PUT'&&tagMatch){const current=cleanTag(decodeURIComponent(tagMatch[1])),value=await body(request),name=cleanTag(String(value.Name||''));await relayMailpit(runtime,response,`/api/v1/tags/${encodeURIComponent(current)}`,{method:'PUT',body:JSON.stringify({Name:name})});return}
  if(request.method==='DELETE'&&tagMatch){const tag=cleanTag(decodeURIComponent(tagMatch[1]));await relayMailpit(runtime,response,`/api/v1/tags/${encodeURIComponent(tag)}`,{method:'DELETE'});return}
  if(request.method==='GET'&&url.pathname==='/api/mailpit/messages'){const query=(url.searchParams.get('query')||'').trim(),start=Math.max(0,Number.parseInt(url.searchParams.get('start')||'0',10)||0),limit=Math.min(500,Math.max(1,Number.parseInt(url.searchParams.get('limit')||'100',10)||100)),endpoint=query?`/api/v1/search?query=${encodeURIComponent(query)}&start=${start}&limit=${limit}`:`/api/v1/messages?start=${start}&limit=${limit}`;await relayMailpit(runtime,response,endpoint);return}
  if(request.method==='POST'&&url.pathname==='/api/mailpit/send'){const value=await body(request);await relayMailpit(runtime,response,'/api/v1/send',{method:'POST',body:JSON.stringify(value)});return}
  if(request.method==='PUT'&&url.pathname==='/api/mailpit/messages/read'){const value=await body(request),ids=Array.isArray(value.IDs)?value.IDs.map((id:unknown)=>cleanId(String(id),'bericht-ID')).slice(0,500):[];if(!ids.length)throw new Error('Selecteer minimaal één bericht.');await relayMailpit(runtime,response,'/api/v1/messages',{method:'PUT',body:JSON.stringify({IDs:ids,Read:Boolean(value.Read)})});return}
  if(request.method==='DELETE'&&url.pathname==='/api/mailpit/messages'){const value=await body(request),ids=Array.isArray(value.IDs)?value.IDs.map((id:unknown)=>cleanId(String(id),'bericht-ID')).slice(0,500):[];if(!ids.length)throw new Error('Selecteer minimaal één bericht.');await relayMailpit(runtime,response,'/api/v1/messages',{method:'DELETE',body:JSON.stringify({IDs:ids})});return}
  if(request.method==='DELETE'&&url.pathname==='/api/mailpit/messages/all'){const query=(url.searchParams.get('query')||'').trim(),endpoint=query?`/api/v1/search?query=${encodeURIComponent(query)}`:'/api/v1/messages';await relayMailpit(runtime,response,endpoint,{method:'DELETE',...(query?{}:{body:JSON.stringify({})})});return}
  const messageMatch=url.pathname.match(/^\/api\/mailpit\/message\/([^/]+)$/);
  if(request.method==='GET'&&messageMatch){await relayMailpit(runtime,response,`/api/v1/message/${encodeURIComponent(cleanId(messageMatch[1],'bericht-ID'))}`);return}
  const messageResourceMatch=url.pathname.match(/^\/api\/mailpit\/message\/([^/]+)\/(headers|raw|html-check|link-check|sa-check)$/);
  if(request.method==='GET'&&messageResourceMatch){const id=cleanId(messageResourceMatch[1],'bericht-ID'),kind=messageResourceMatch[2];await relayMailpit(runtime,response,`/api/v1/message/${encodeURIComponent(id)}/${kind}`,{},kind==='raw'?`bericht-${id}.eml`:undefined);return}
  const messagePartMatch=url.pathname.match(/^\/api\/mailpit\/message\/([^/]+)\/part\/([^/]+)$/);
  if(request.method==='GET'&&messagePartMatch){const id=cleanId(messagePartMatch[1],'bericht-ID'),part=cleanId(messagePartMatch[2],'bijlage-ID');await relayMailpit(runtime,response,`/api/v1/message/${encodeURIComponent(id)}/part/${encodeURIComponent(part)}`,{},url.searchParams.get('download')==='1'?url.searchParams.get('name')||'bijlage':undefined);return}
  const serviceMatch=url.pathname.match(/^\/api\/services\/(apache|php|mysql|phpmyadmin|mail|redis)\/toggle$/);
  if(request.method==='POST'&&serviceMatch){await actions.toggleService(serviceMatch[1]as ServiceId);json(response,200,{ok:true});return}
  const portMatch=url.pathname.match(/^\/api\/services\/(apache|php|mysql|phpmyadmin|mail|redis)\/port$/);
  if(request.method==='POST'&&portMatch){const value=await body(request),port=Number(value.port);await actions.updatePort(portMatch[1]as ServiceId,port);json(response,200,{ok:true});return}
  const portInspectMatch=url.pathname.match(/^\/api\/services\/(apache|php|mysql|phpmyadmin|mail|redis)\/port\/inspect$/);
  if(request.method==='GET'&&portInspectMatch){json(response,200,await runtime.inspectPort(portInspectMatch[1]as ServiceId));return}
  const portAutopilotMatch=url.pathname.match(/^\/api\/services\/(apache|php|mysql|phpmyadmin|mail|redis)\/port\/autopilot$/);
  if(request.method==='POST'&&portAutopilotMatch){const id=portAutopilotMatch[1]as ServiceId,info=await runtime.inspectPort(id);if(info.status==='occupied')await actions.updatePort(id,info.suggestedPort);json(response,200,{ok:true,previous:info.port,port:info.status==='occupied'?info.suggestedPort:info.port,status:info.status,ownerName:info.ownerName,ownerPid:info.ownerPid});return}
  const refreshMatch=url.pathname.match(/^\/api\/services\/(apache|php|mysql|phpmyadmin|mail|redis)\/refresh$/);
  if(request.method==='POST'&&refreshMatch){await actions.refreshService(refreshMatch[1]as ServiceId);json(response,200,{ok:true});return}
  const allMatch=url.pathname.match(/^\/api\/services\/all\/(start|stop)$/);
  if(request.method==='POST'&&allMatch){await actions.toggleAll(allMatch[1]==='start');json(response,200,{ok:true});return}
  if(request.method==='POST'&&url.pathname==='/api/mail'){const value=await body(request);runtime.addMailAddress(String(value.address||''),String(value.description||''),value.projectId?String(value.projectId):undefined);json(response,200,{ok:true});return}
  const mailMatch=url.pathname.match(/^\/api\/mail\/([a-f0-9-]+)$/i);
  if(request.method==='DELETE'&&mailMatch){runtime.deleteMailAddress(mailMatch[1]);json(response,200,{ok:true});return}
  if(request.method==='POST'&&url.pathname==='/api/databases'){const value=await body(request);runtime.createDatabase(String(value.name||''),String(value.charset||'utf8mb4'),String(value.collation||'utf8mb4_unicode_ci'));json(response,200,{ok:true});return}
  const databaseMatch=url.pathname.match(/^\/api\/databases\/([a-f0-9-]+)$/i);
  if(request.method==='DELETE'&&databaseMatch){runtime.deleteDatabase(databaseMatch[1]);json(response,200,{ok:true});return}
  const bootstrapMatch=url.pathname.match(/^\/api\/projects\/([a-f0-9-]+)\/bootstrap$/i);
  if(request.method==='POST'&&bootstrapMatch){await runtime.bootstrapProject(bootstrapMatch[1]);json(response,200,{ok:true});return}
  const parityMatch=url.pathname.match(/^\/api\/projects\/([a-f0-9-]+)\/parity$/i);
  if(request.method==='POST'&&parityMatch){runtime.runProductionParity(parityMatch[1]);json(response,200,runtime.snapshot().productionParity.find(item=>item.projectId===parityMatch[1]));return}
  const preflightMatch=url.pathname.match(/^\/api\/projects\/([a-f0-9-]+)\/preflight$/i);
  if(request.method==='POST'&&preflightMatch){await runtime.runPreflight(preflightMatch[1]);json(response,200,runtime.snapshot().preflightReports.find(item=>item.projectId===preflightMatch[1]));return}
  const testLabMatch=url.pathname.match(/^\/api\/projects\/([a-f0-9-]+)\/testlab$/i);
  if(request.method==='POST'&&testLabMatch){for(const id of['php','apache']as ServiceId[]){const service=runtime.snapshot().services.find(item=>item.id===id);if(service?.status!=='running')await actions.toggleService(id)}json(response,200,await runtime.runTestLab(testLabMatch[1]));return}
  const ideMatch=url.pathname.match(/^\/api\/projects\/([a-f0-9-]+)\/ide$/i);
  if(request.method==='POST'&&ideMatch){runtime.writeIdeIntegration(ideMatch[1]);json(response,200,{ok:true});return}
  if(request.method==='POST'&&url.pathname==='/api/secrets'){const value=await body(request);runtime.saveSecret(String(value.key||''),String(value.value||''),value.projectId?String(value.projectId):undefined);json(response,200,{ok:true});return}
  const secretMatch=url.pathname.match(/^\/api\/secrets\/([a-f0-9-]+)$/i);
  if(request.method==='DELETE'&&secretMatch){runtime.deleteSecret(secretMatch[1]);json(response,200,{ok:true});return}
  if(request.method==='GET'&&url.pathname==='/api/devdrive'){runtime.detectDevDrive();json(response,200,runtime.snapshot().devDrive);return}
  if(request.method==='GET'&&url.pathname==='/api/inspector'){runtime.collectServiceLogs();runtime.collectInspector();runtime.refreshResources();const state=runtime.snapshot();json(response,200,{events:state.inspectorEvents.slice(0,100),resources:state.resourceUsage});return}
  if(request.method==='POST'&&url.pathname==='/api/settings'){const value=await body(request),patch={...(['nl','en'].includes(value.language)?{language:value.language as AppState['settings']['language']}:{}),...(typeof value.resourceSaver==='boolean'?{resourceSaver:value.resourceSaver}:{}),...(Number.isInteger(value.idleStopMinutes)?{idleStopMinutes:Number(value.idleStopMinutes)}:{}),...(typeof value.offlineFirst==='boolean'?{offlineFirst:value.offlineFirst}:{}),...(typeof value.lanSharingEnabled==='boolean'?{lanSharingEnabled:value.lanSharingEnabled}:{}),...(typeof value.mcpEnabled==='boolean'?{mcpEnabled:value.mcpEnabled}:{}),...(typeof value.restoreServicesAfterCrash==='boolean'?{restoreServicesAfterCrash:value.restoreServicesAfterCrash}:{}),...(typeof value.atomicUpdates==='boolean'?{atomicUpdates:value.atomicUpdates}:{})};runtime.updateSettings(patch);json(response,200,{ok:true});return}
  json(response,404,{error:'Niet gevonden'});
 }catch(error){json(response,500,{error:error instanceof Error?error.message:String(error)})}});
 return new Promise<{server:ReturnType<typeof createServer>;url:string;token:string}>((resolve,reject)=>{server.once('error',reject);server.listen(runtime.snapshot().settings.webControlPort,'127.0.0.1',()=>{server.off('error',reject);resolve({server,url:`http://127.0.0.1:${runtime.snapshot().settings.webControlPort}`,token})})});
}
