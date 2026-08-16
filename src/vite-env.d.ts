/// <reference types="vite/client" />

import type{
 AppSettings as LocalDeckSettings,
 AppState as LocalDeckState,
 DatabaseQueryResult as LocalDeckQueryResult,
 DatabaseSchemaDiff as LocalDeckSchemaDiff,
 DatabaseTable as LocalDeckTable,
 DatabaseUser as LocalDeckDatabaseUser,
 FrameworkAction as LocalDeckFrameworkAction,
 MailMode as LocalDeckMailMode,
 Project as LocalDeckProject,
 ProjectProfile as LocalDeckProjectProfile,
 ProjectTask as LocalDeckProjectTask,
 RuntimeMode as LocalDeckRuntimeMode,
 ServiceId as LocalDeckServiceId,
 ServiceState as LocalDeckServiceState,
 UpdateState as LocalDeckUpdateState
}from'../electron/types';

declare global{
 type AppState=LocalDeckState;
 type AppSettings=LocalDeckSettings;
 type Project=LocalDeckProject;
 type ProjectProfile=LocalDeckProjectProfile;
 type ProjectTask=LocalDeckProjectTask;
 type RuntimeMode=LocalDeckRuntimeMode;
 type ServiceId=LocalDeckServiceId;
 type ServiceState=LocalDeckServiceState;
 type MailMode=LocalDeckMailMode;
 type DatabaseTable=LocalDeckTable;
 type DatabaseQueryResult=LocalDeckQueryResult;
 type DatabaseSchemaDiff=LocalDeckSchemaDiff;
 type DatabaseUser=LocalDeckDatabaseUser;
 type FrameworkAction=LocalDeckFrameworkAction;
 type UpdateState=LocalDeckUpdateState;

 interface Window{localdeck:{
  getState():Promise<AppState>;toggleService(id:ServiceId):Promise<AppState>;toggleAll(start:boolean):Promise<AppState>;updateServicePort(id:ServiceId,port:number):Promise<AppState>;autoPort(id:ServiceId):Promise<AppState>;inspectPort(id:ServiceId):Promise<import('../electron/types').PortConflictInfo>;portAutopilot(id:ServiceId):Promise<AppState>;inspectAllPorts():Promise<AppState>;saveStartProfile(profile:{id?:string;name:string;description?:string;services:ServiceId[];projectIds?:string[]}):Promise<AppState>;deleteStartProfile(id:string):Promise<AppState>;applyStartProfile(id:string):Promise<AppState>;cancelOperation(id:string):Promise<AppState>;retryOperation(id:string):Promise<AppState>;dismissOperation(id:string):Promise<AppState>;clearCompletedOperations():Promise<AppState>;rollbackSafeChange(id:string):Promise<AppState>;
  runDiagnostics():Promise<AppState>;repairRuntime():Promise<AppState>;repairAll():Promise<AppState>;installRuntime():Promise<AppState>;setRuntimeMode(mode:RuntimeMode):Promise<AppState>;syncDomains():Promise<AppState>;exportDiagnostics():Promise<AppState>;exportSupportBundle():Promise<AppState>;
  addProject(project:Omit<Project,'id'|'createdAt'>):Promise<AppState>;scaffoldProject(project:{name:string;parentDir:string;domain:string;phpVersion:string;template:'php'|'wordpress'|'laravel'|'symfony'|'drupal'}):Promise<AppState>;cloneProject(repositoryUrl:string,parentDirectory:string,name?:string):Promise<AppState>;bootstrapProject(id:string):Promise<AppState>;createBranchEnvironment(id:string,branch:string):Promise<AppState>;removeBranchEnvironment(id:string):Promise<AppState>;refreshBlueprints():Promise<AppState>;runProjectDoctor(id?:string):Promise<AppState>;repairProject(id:string):Promise<AppState>;runProductionParity(id:string):Promise<AppState>;runPreflight(id:string):Promise<AppState>;runTestLab(id:string):Promise<AppState>;writeIdeIntegration(id:string):Promise<AppState>;setProjectXdebug(id:string,mode:ProjectProfile['xdebugMode']):Promise<AppState>;runDependencyGuard(id:string):Promise<AppState>;generateSbom(id:string):Promise<AppState>;runDriftDetector(id:string):Promise<AppState>;runMigrationLab(id:string):Promise<AppState>;runRehearsal(id:string):Promise<AppState>;runReleaseGate(id:string):Promise<AppState>;runPerformanceBaseline(id:string):Promise<AppState>;runBrowserMatrix(id:string):Promise<AppState>;runMailQuality():Promise<AppState>;
  importXampp():Promise<AppState>;importLegacy(kind:'xampp'|'wamp'|'laragon'):Promise<AppState>;importProjectManifest():Promise<AppState>;writeProjectManifest(id:string):Promise<AppState>;updateProjectProfile(id:string,patch:Partial<ProjectProfile>):Promise<AppState>;setProjectPhpVersion(id:string,version:string):Promise<AppState>;exportProject(id:string):Promise<AppState>;removeProject(id:string,confirmation:string):Promise<AppState>;openProject(id:string,kind:'site'|'folder'|'terminal'|'code'|'developer'):Promise<void>;runComposer(id:string,command:'install'|'update'|'audit'):Promise<AppState>;
  createSnapshot(projectId:string,description?:string):Promise<AppState>;restoreSnapshot(id:string):Promise<AppState>;deleteSnapshot(id:string):Promise<AppState>;refreshSnapshots():Promise<AppState>;addTask(task:Omit<ProjectTask,'id'|'lastRunAt'|'lastExitCode'>):Promise<AppState>;deleteTask(id:string):Promise<AppState>;runTask(projectId:string,taskId:string):Promise<AppState>;saveWorker(worker:{id?:string;projectId:string;name:string;command:string;args:string[];autoRestart?:boolean}):Promise<AppState>;startWorker(id:string):Promise<AppState>;stopWorker(id:string):Promise<AppState>;deleteWorker(id:string):Promise<AppState>;
  updateSettings(patch:Partial<AppSettings>):Promise<AppState>;clearLogs():Promise<AppState>;collectLogs():Promise<AppState>;chooseDirectory():Promise<string|null>;openWorkspace():Promise<string>;openWebsite():Promise<AppState>;exportWorkspace():Promise<AppState>;importWorkspace():Promise<AppState>;
  createDatabase(name:string,charset:string,collation:string):Promise<AppState>;copyDatabasePassword():Promise<AppState>;deleteDatabase(id:string):Promise<AppState>;refreshDatabases():Promise<AppState>;getDatabaseTables(id:string):Promise<DatabaseTable[]>;queryDatabase(id:string,query:string):Promise<DatabaseQueryResult>;importDatabase(id:string):Promise<AppState>;importDatabaseSanitized(id:string):Promise<AppState>;exportDatabase(id:string):Promise<AppState>;backupDatabase(id:string):Promise<AppState>;restoreBackup(id:string):Promise<AppState>;refreshBackups():Promise<AppState>;compareDatabaseSchemas(leftId:string,rightId:string):Promise<DatabaseSchemaDiff>;getDatabaseUsers():Promise<DatabaseUser[]>;createDatabaseUser(databaseId:string,username:string,password:string):Promise<AppState>;deleteDatabaseUser(username:string,host:string):Promise<AppState>;generateTestData(id:string,table:string,count:number):Promise<AppState>;anonymizeDatabase(id:string):Promise<AppState>;
  addMailAddress(address:string,description:string,projectId?:string):Promise<AppState>;deleteMailAddress(id:string):Promise<AppState>;testMail():Promise<AppState>;openTool(id:ServiceId|'webcontrol'):Promise<AppState>;togglePhpExtension(id:string):Promise<AppState>;installDeveloperTool(id:'node'|'git'|'vscode'):Promise<AppState>;
  scanSecurity():Promise<AppState>;fixSecurity(id:string):Promise<AppState>;togglePlugin(id:string):Promise<AppState>;refreshOfflineRuntime():Promise<AppState>;verifyRuntimePackages():Promise<AppState>;assessReleaseReadiness():Promise<AppState>;importOfflineRuntime():Promise<AppState>;exportOfflineRuntime():Promise<AppState>;prepareOfflineRuntime():Promise<AppState>;refreshComponents():Promise<AppState>;importComponentPack():Promise<AppState>;inspectComponentPack():Promise<AppState>;refreshInspector():Promise<AppState>;refreshResources():Promise<AppState>;getFrameworkActions(projectId:string):Promise<FrameworkAction[]>;runFrameworkAction(projectId:string,actionId:string):Promise<AppState>;
  importApiWorkspace():Promise<AppState>;saveApiWorkspace(workspace:{id?:string;name:string;baseUrl:string;projectId?:string}):Promise<AppState>;deleteApiWorkspace(id:string):Promise<AppState>;runApiRequest(workspaceId:string,endpointId:string,body:string):Promise<AppState>;generateApiFixture(workspaceId:string,endpointId:string):Promise<string>;createWebhookEndpoint(name:string):Promise<AppState>;deleteWebhookEndpoint(id:string):Promise<AppState>;replayWebhook(eventId:string,targetUrl:string):Promise<AppState>;saveReplayCapture(capture:Omit<import('../electron/types').ReplayCapture,'id'|'createdAt'>):Promise<AppState>;runReplayCapture(id:string):Promise<AppState>;deleteReplayCapture(id:string):Promise<AppState>;
  startShare(projectId:string,durationMinutes:number):Promise<AppState>;stopShare(id:string):Promise<AppState>;copyShare(id:string):Promise<AppState>;copyMcpConfig():Promise<AppState>;copyCliCommand():Promise<AppState>;
  saveSecret(key:string,value:string,projectId?:string):Promise<AppState>;deleteSecret(id:string):Promise<AppState>;detectDevDrive():Promise<AppState>;
  checkUpdates(manual?:boolean):Promise<AppState>;downloadUpdate():Promise<AppState>;rollbackUpdate():Promise<AppState>;dismissUpdate():Promise<AppState>;onStateChanged(callback:(state:AppState)=>void):()=>void;
 }}
}

export{};
