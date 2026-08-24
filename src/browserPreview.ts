import type{AppState,FrameworkAction,ServiceId}from'../electron/types';

const now=new Date().toISOString();
let previewState:AppState={
 services:[
  {id:'apache',name:'Apache',description:'Webserver en virtuele hosts',status:'running',port:80,version:'2.4.68',installed:true,health:'healthy',pid:4120,restartCount:0},
  {id:'php',name:'PHP',description:'Geïsoleerde FastCGI-pools',status:'running',port:9000,version:'8.5',installed:true,health:'healthy',pid:4188,restartCount:0},
  {id:'mysql',name:'MySQL',description:'Lokale databaseserver',status:'running',port:3306,version:'8.4.4',installed:true,health:'healthy',pid:4240,restartCount:0},
  {id:'phpmyadmin',name:'phpMyAdmin',description:'Databasebeheer in de browser',status:'running',port:8080,version:'5.2.2',installed:true,health:'healthy'},
  {id:'mail',name:'Mailpit',description:'Lokale e-mailopvang',status:'running',port:8025,version:'1.30.7',installed:true,health:'healthy',pid:4312,restartCount:0},
  {id:'redis',name:'Redis',description:'Cache en queues',status:'stopped',port:6379,version:'5.0.14.1',installed:true,health:'unknown',restartCount:0}
 ],
 projects:[{id:'preview-project',name:'Webshop Preview',path:'C:\\Projects\\webshop',documentRoot:'C:\\Projects\\webshop\\public',domain:'webshop.test',phpVersion:'8.4',secure:true,certificate:{status:'active',certificateFile:'C:\\LocalDeck\\runtime\\certs\\projects\\preview-project\\certificate.pem',keyFile:'C:\\LocalDeck\\runtime\\certs\\projects\\preview-project\\private-key.pem'},createdAt:now,template:'laravel',repositoryUrl:'https://example.invalid/webshop.git',branch:'main',profile:{webServer:'apache',phpExtensions:['curl','mbstring','openssl','pdo_mysql'],environment:'development',databaseName:'webshop',databaseEngine:'mysql',databaseVersion:'8.4',nodeVersion:'22',mailMode:'capture',debugToolbar:true,isolated:true,runtimeLocked:true,xdebugMode:'debug',schedulerEnabled:false,httpPort:8088,httpsPort:8448,env:{APP_ENV:'local'}}}],
 blueprints:[{projectId:'preview-project',schemaVersion:3,file:'C:\\Projects\\webshop\\.localdeck.yml',lockFile:'C:\\Projects\\webshop\\.localdeck.lock.json',locked:true,hash:'8d6c8a9d…',updatedAt:now,summary:'Laravel · PHP 8.4 · MySQL 8.4',warnings:[]}],
 branchEnvironments:[{id:'preview-branch',projectId:'preview-project',childProjectId:'preview-project-feature',branch:'feature/checkout',path:'C:\\Projects\\webshop-checkout',domain:'checkout.webshop.test',databaseName:'webshop_checkout',databaseCloned:true,httpPort:8091,httpsPort:8451,status:'ready',createdAt:now}],
 components:[
  {id:'apache',name:'Apache HTTP Server',category:'webserver',version:'2.4.68',installed:true,active:true,verified:true,required:true,description:'Modulaire Windows-webserver'},
  {id:'php84',name:'PHP 8.4',category:'language',version:'8.4',installed:true,active:true,verified:true,required:false,description:'Projectruntime voor Webshop Preview'},
  {id:'php85',name:'PHP 8.5',category:'language',version:'8.5',installed:true,active:true,verified:true,required:true,description:'Standaard PHP-runtime'},
  {id:'mysql',name:'MySQL',category:'database',version:'8.4.4',installed:true,active:true,verified:true,required:true,description:'Lokale databaseserver'},
  {id:'mailpit',name:'Mailpit',category:'service',version:'1.30.7',installed:true,active:true,verified:true,required:false,description:'Lokale SMTP-opvang'}
 ],
 componentPacks:[{id:'mariadb',name:'MariaDB',description:'Alternatieve database als offline pakket.',category:'database',status:'planned',requiresAdmin:false,license:'GPL-2.0'},{id:'nginx',name:'Nginx',description:'Alternatieve webserver als offline pakket.',category:'webserver',status:'planned',requiresAdmin:false,license:'BSD-2-Clause'}],
 runtimePackages:[{id:'apache',name:'Apache HTTP Server',version:'2.4.68',file:'apache.zip',sha256:'PREVIEW',bytes:13602524,installed:true,verified:true,required:true,detail:'SHA-256 gecontroleerd'},{id:'php-8.5',name:'PHP 8.5',version:'8.5.9',file:'php.zip',sha256:'PREVIEW',bytes:36150664,installed:true,verified:true,required:true,detail:'SHA-256 gecontroleerd'}],
 projectHealth:[{projectId:'preview-project',projectName:'Webshop Preview',domain:'webshop.test',score:92,status:'warning',checkedAt:now,checks:[{id:'root',label:'Projectmap',status:'ok',detail:'Projectmap gevonden.',fixable:false},{id:'domain',label:'Lokaal domein',status:'warning',detail:'.test vereist hosts-sync of lokale DNS.',fixable:true}]}],
 productionParity:[{projectId:'preview-project',projectName:'Webshop Preview',score:100,status:'ok',checkedAt:now,checks:[{id:'php',label:'PHP-versie',status:'ok',local:'8.4',expected:'8.4',detail:'Lokale en productieversie zijn gelijk.'}]}],
 preflightReports:[{projectId:'preview-project',projectName:'Webshop Preview',status:'ok',startedAt:now,finishedAt:now,steps:[{id:'php-lint',label:'PHP lint',status:'ok',detail:'Geen syntaxfouten.',durationMs:42}]}],
 testLabReports:[{projectId:'preview-project',projectName:'Webshop Preview',url:'https://webshop.test:8448',status:'ok',checkedAt:now,durationMs:186,checks:[{id:'http',label:'HTTP-respons',status:'ok',detail:'200 OK'}]}],
 secretVault:[{id:'secret-1',key:'PAYMENT_API_TOKEN',projectId:'preview-project',scope:'project',updatedAt:now,encryption:'windows-dpapi'}],
  portConflicts:[{serviceId:'apache',serviceName:'Apache',port:80,status:'localdeck',suggestedPort:81,checkedAt:now}],
  portBindings:[{id:'apache-tcp4',serviceId:'apache',label:'Apache',protocol:'TCP',family:'IPv4',address:'127.0.0.1',port:80,role:'primary',status:'localdeck',reservedBy:'Apache',checkedAt:now}],
  serviceDependencies:[{serviceId:'apache',requires:['php'],usedBy:[],reason:'Webserver gebruikt de actieve PHP-pools.'},{serviceId:'php',requires:[],usedBy:['apache','phpmyadmin'],reason:'FastCGI-runtime.'}],
  startProfiles:[{id:'full-stack',name:'Volledige stack',description:'Alle lokale services.',services:['mysql','php','apache','phpmyadmin','redis','mail'],projectIds:[],builtIn:true,active:true,updatedAt:now}],
  actionOperations:[{id:'operation-1',kind:'performance',title:'Performancebaseline · Webshop Preview',status:'completed',progress:100,message:'186 ms gemiddeld.',projectId:'preview-project',startedAt:now,updatedAt:now,finishedAt:now,canRetry:true,canCancel:false,steps:[{id:'step-1',label:'Requests meten',status:'ok',detail:'5 samples',finishedAt:now}]}],
  safeChanges:[],
  runtimeLifecycle:[{version:'8.2',status:'security',usedBy:[],installed:true,detail:'Oudste ondersteunde lijn.'},{version:'8.3',status:'security',usedBy:[],installed:true,detail:'Stabiele beveiligingslijn.'},{version:'8.4',status:'current',usedBy:['Webshop Preview'],installed:true,detail:'Actuele lijn.'},{version:'8.5',status:'current',usedBy:[],installed:true,detail:'Actuele lijn.'}],
  engineeringReports:[{id:'report-1',kind:'performance',projectId:'preview-project',projectName:'Webshop Preview',title:'Performance · Webshop Preview',status:'ok',score:100,summary:'186 ms gemiddeld, p95 214 ms.',createdAt:now,checks:[{id:'http',label:'HTTP-status',status:'ok',detail:'200'}],artifacts:[],metrics:{averageMs:186,p95Ms:214}}],
  apiWorkspaces:[{id:'api-1',name:'Webshop API',baseUrl:'http://webshop.test:8088',projectId:'preview-project',endpoints:[{id:'endpoint-1',method:'GET',path:'/api/products',summary:'Producten ophalen'}],createdAt:now,updatedAt:now}],apiRuns:[],
  webhookEndpoints:[{id:'hook-1',name:'Betalingen',path:'/hooks/preview',secret:'preview-secret',enabled:true,createdAt:now}],webhookEvents:[],
  workers:[{id:'worker-1',projectId:'preview-project',name:'Laravel queue',command:'php',args:['artisan','queue:work'],status:'stopped',logs:[],autoRestart:true,restartCount:0}],replayCaptures:[],
 devDrive:{supported:true,drive:'C:',isDevDrive:false,status:'available',detail:'De projectmap staat niet op een Dev Drive.',securityMode:'Windows Defender standaardbeleid',checkedAt:now},
 crashRecovery:{sessionId:'preview-session',uncleanShutdownDetected:false,recoveredServices:[],status:'clean',detail:'Vorige sessie is netjes afgesloten.',checkedAt:now},
  atomicUpdate:{status:'healthy',currentVersion:'1.1.0-test.1',lastKnownGoodVersion:'1.1.0-test.1',checkedAt:now,detail:'De actieve versie heeft de start- en runtimecontrole doorstaan.'},
 releaseReadiness:{score:80,status:'warning',checkedAt:now,checks:[{id:'runtime',label:'Offline runtime',status:'ok',detail:'13/13 pakketten aanwezig.'},{id:'binary-signature',label:'Programmaundertekening',status:'warning',detail:'RC-build is nog niet ondertekend.'}]},
 inspectorEvents:[
  {id:'event-1',at:now,projectId:'preview-project',kind:'request',severity:'info',title:'GET /products',detail:'200 · Apache → PHP 8.4',durationMs:38},
  {id:'event-2',at:now,projectId:'preview-project',kind:'sql',severity:'info',title:'SELECT products',detail:'12 rijen · webshop',durationMs:7},
  {id:'event-3',at:now,projectId:'preview-project',kind:'php',severity:'warning',title:'Langzame controller',detail:'CheckoutController::index',durationMs:486}
 ],
 resourceUsage:{capturedAt:now,appMemoryMb:118,serviceMemoryMb:302,totalMemoryMb:420,processes:[{id:'app',name:'LocalDeck',pid:3900,memoryMb:118},{id:'apache',name:'Apache',pid:4120,memoryMb:45},{id:'php-8.5',name:'PHP 8.5',pid:4188,memoryMb:73},{id:'php-8.4',name:'PHP 8.4',pid:4194,memoryMb:58},{id:'mysql',name:'MySQL',pid:4240,memoryMb:106},{id:'mail',name:'Mailpit',pid:4312,memoryMb:20}]},
 shares:[],
 databases:[{id:'db-webshop',name:'webshop',charset:'utf8mb4',collation:'utf8mb4_unicode_ci',tables:18,size:'6.4 MB',createdAt:now},{id:'db-tests',name:'webshop_test',charset:'utf8mb4',collation:'utf8mb4_unicode_ci',tables:18,size:'2.1 MB',createdAt:now}],
 mailAddresses:[{id:'mail-1',address:'orders@webshop.test',projectId:'preview-project',description:'Bestelbevestigingen',createdAt:now}],
 backups:[{id:'backup-1',database:'webshop',file:'webshop-2026-08-15.sql.gz',size:'1.9 MB',createdAt:now}],
 snapshots:[{id:'snapshot-1',projectId:'preview-project',projectName:'Webshop Preview',file:'webshop-before-upgrade.zip',size:'14.8 MB',createdAt:now,includesDatabase:true,includesConfiguration:true,includesMail:true,description:'Voor PHP-upgrade'}],
 tasks:[{id:'task-1',projectId:'preview-project',name:'Tests',command:'php',args:['artisan','test'],category:'test',safe:true,lastRunAt:now,lastExitCode:0}],
 securityFindings:[{id:'security-1',severity:'info',category:'Netwerk',title:'Alleen lokaal bereikbaar',detail:'Services luisteren op loopback.',fixable:false,resolved:false}],
 plugins:[{id:'wordpress',name:'WordPress',description:'Projecttemplate met WP-CLI',category:'template',version:'1.0.0',enabled:true,installed:true,builtIn:true},{id:'redis',name:'Redis',description:'Lokale cache en queues',category:'service',version:'1.0.0',enabled:true,installed:true,builtIn:true}],
 repairActions:[],offlineRuntime:{status:'ready',directory:'D:\\LocalDeck-offline',packages:9,requiredPackages:9,lastCheckedAt:now},
 phpVersions:[{version:'8.2',installed:true,active:false},{version:'8.3',installed:true,active:false},{version:'8.4',installed:true,active:false},{version:'8.5',installed:true,active:true}],
 phpExtensions:[{id:'curl',label:'cURL',enabled:true,available:true,description:'HTTP-client'},{id:'xdebug',label:'Xdebug',enabled:true,available:true,description:'Debugger en profiler'},{id:'redis',label:'Redis',enabled:false,available:true,description:'Redis-client'}],
 installation:{status:'installed',progress:100,message:'Runtime gereed',finishedAt:now},
 diagnostics:[{id:'ports',label:'Poortcontrole',status:'ok',detail:'Geen conflicten gevonden.',fixable:false},{id:'hosts',label:'Domeinen',status:'ok',detail:'Hosts en certificaten zijn gesynchroniseerd.',fixable:true},{id:'runtime',label:'Runtime',status:'ok',detail:'Alle vereiste onderdelen zijn geverifieerd.',fixable:true}],
  update:{status:'current',currentVersion:'1.1.0-test.1',latestVersion:'1.1.0-test.1',checkedAt:now,showPopup:false},
  settings:{simulationMode:false,startWithWindows:false,minimizeToTray:true,telemetry:false,privacyMode:false,language:navigator.language.toLowerCase().startsWith('nl')?'nl':'en',dataDirectory:'C:\\LocalDeck\\runtime',projectsDirectory:'C:\\LocalDeck\\websites',folderMode:true,defaultPhpVersion:'8.5',localDomainSuffix:'localhost',autoBackup:true,backupRetention:14,runtimeMode:'application',serviceAutoStart:false,autoRestart:true,restoreServicesAfterCrash:true,atomicUpdates:true,autoUpdateCheck:false,updateChannel:'stable',updateFeedUrl:'https://localdeck.nl/downloads/windows.json',firstRunComplete:true,backupSchedule:'daily',webControlEnabled:true,webControlPort:4466,dnsMode:'hosts',mailMode:'capture',mailMaxMessages:1000,mailMaxAgeDays:30,smtpHost:'',smtpPort:587,smtpUsername:'',requireSignedUpdates:false,trustedUpdatePublisher:'',trustedPackPublisher:'AA BB CC',combinedLogs:true,securityScanOnStart:true,resourceSaver:true,idleStopMinutes:30,lanSharingEnabled:false,mcpEnabled:false,offlineFirst:true},
 logs:[{id:'log-1',at:now,level:'success',source:'Runtime',message:'Apache, PHP en MySQL zijn gestart.'},{id:'log-2',at:now,level:'info',source:'Project',message:'webshop.test gebruikt PHP 8.4.'}]
};

const listeners=new Set<(state:AppState)=>void>();
const publish=()=>{previewState={...previewState};listeners.forEach(listener=>listener(previewState));return previewState};
const frameworkActions:FrameworkAction[]=[
 {id:'laravel-migrate',projectId:'preview-project',name:'Database migreren',description:'Voert openstaande Laravel-migraties uit.',command:'php',args:['artisan','migrate'],destructive:false,available:true},
 {id:'laravel-cache',projectId:'preview-project',name:'Caches wissen',description:'Wist configuratie-, route- en viewcaches.',command:'php',args:['artisan','optimize:clear'],destructive:false,available:true}
];

export function installBrowserPreview(){
 if(window.localdeck)return;
 const api=new Proxy({}, {get(_target,property){
  if(property==='getState')return async()=>previewState;
  if(property==='onStateChanged')return(listener:(state:AppState)=>void)=>{listeners.add(listener);return()=>listeners.delete(listener)};
  if(property==='chooseDirectory')return async()=>'C:\\Projects';
  if(property==='openWebsite')return async()=>{window.open('https://localdeck.nl/','_blank','noopener,noreferrer');return previewState};
  if(property==='getFrameworkActions')return async()=>frameworkActions;
  if(property==='getDatabaseTables')return async()=>[{name:'products',rows:124,engine:'InnoDB',size:'96 KB',collation:'utf8mb4_unicode_ci'},{name:'users',rows:18,engine:'InnoDB',size:'64 KB',collation:'utf8mb4_unicode_ci'}];
  if(property==='queryDatabase')return async()=>({columns:['id','name','status'],rows:[{id:'1',name:'Demo product',status:'active'}],durationMs:4,limited:true});
  if(property==='compareDatabaseSchemas')return async()=>({left:'webshop',right:'webshop_test',onlyLeft:['audit_log'],onlyRight:[],changed:[{table:'products',left:'18 kolommen',right:'17 kolommen'}]});
  if(property==='getDatabaseUsers')return async()=>[{user:'root',host:'localhost',plugin:'caching_sha2_password',locked:false},{user:'webshop_app',host:'127.0.0.1',plugin:'caching_sha2_password',locked:false}];
  if(property==='openProject')return async()=>undefined;
  if(property==='toggleService')return async(id:ServiceId)=>{previewState={...previewState,services:previewState.services.map(service=>service.id===id?{...service,status:service.status==='running'?'stopped':'running',health:service.status==='running'?'unknown':'healthy'}:service)};return publish()};
  if(property==='toggleAll')return async(start:boolean)=>{previewState={...previewState,services:previewState.services.map(service=>({...service,status:start?'running':'stopped',health:start?'healthy':'unknown'}))};return publish()};
  if(property==='setRuntimeMode')return async(mode:AppState['settings']['runtimeMode'])=>{previewState={...previewState,settings:{...previewState.settings,runtimeMode:mode}};return publish()};
  if(property==='updateSettings')return async(patch:Partial<AppState['settings']>)=>{previewState={...previewState,settings:{...previewState.settings,...patch}};return publish()};
  return async()=>publish();
 }});
 window.localdeck=api as Window['localdeck'];
}
