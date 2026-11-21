import type{UpdateManifest}from'./types.js';

export type UpdateFetcher=(input:string,init?:RequestInit)=>Promise<Response>;

export function compareVersions(left:string,right:string){
 const a=left.replace(/^v/,'').split(/[.-]/).map(Number),b=right.replace(/^v/,'').split(/[.-]/).map(Number);
 for(let i=0;i<Math.max(a.length,b.length);i++){const difference=(a[i]||0)-(b[i]||0);if(difference)return difference>0?1:-1}
 return 0;
}

export async function fetchUpdateManifest(feedUrl:string,channel:'stable'|'beta',request:UpdateFetcher=fetch){
 if(!feedUrl)throw new Error('Er is nog geen updatefeed ingesteld.');
 const url=new URL(feedUrl);
 if(url.protocol!=='https:'&&!['127.0.0.1','localhost'].includes(url.hostname))throw new Error('De updatefeed moet HTTPS gebruiken.');
 const controller=new AbortController(),timer=setTimeout(()=>controller.abort(),8000);
 try{
  const response=await request(url.toString(),{headers:{accept:'application/json','user-agent':'LocalDeck-Updater'},signal:controller.signal});
  if(!response.ok)throw new Error(`Updateserver antwoordde met ${response.status}.`);
  const manifest=await response.json()as UpdateManifest;
  if(!/^\d+\.\d+\.\d+/.test(manifest.version)||!manifest.downloadUrl||!/^[a-f0-9]{64}$/i.test(manifest.sha256||''))throw new Error('Het updatebestand mist een geldige versie, downloadlink of SHA-256.');
  if(manifest.channel&&manifest.channel!==channel&&channel==='stable')throw new Error('Er is alleen een bèta-update beschikbaar.');
  const download=new URL(manifest.downloadUrl);
  if(download.protocol!=='https:'&&!['127.0.0.1','localhost'].includes(download.hostname))throw new Error('De downloadlink moet HTTPS gebruiken.');
  if(manifest.rollbackUrl){const rollback=new URL(manifest.rollbackUrl);if(rollback.protocol!=='https:'&&!['127.0.0.1','localhost'].includes(rollback.hostname))throw new Error('De rollbacklink moet HTTPS gebruiken.');if(!manifest.rollbackVersion||!manifest.rollbackSha256)throw new Error('De rollbackversie of SHA-256 ontbreekt.')}
  if(manifest.releaseNotesUrl){const notes=new URL(manifest.releaseNotesUrl);if(notes.protocol!=='https:'&&!['127.0.0.1','localhost'].includes(notes.hostname))throw new Error('De release-noteslink moet HTTPS gebruiken.')}
  if(manifest.history!==undefined){if(!Array.isArray(manifest.history)||manifest.history.length>25)throw new Error('De updategeschiedenis is ongeldig of te lang.');manifest.history=manifest.history.map(item=>{if(!item||!/^[0-9]+\.[0-9]+\.[0-9]+(?:-[0-9A-Za-z.-]+)?$/.test(item.version)||!['stable','beta'].includes(item.channel))throw new Error('De updategeschiedenis bevat een ongeldige versie.');return{version:item.version,title:String(item.title||`LocalDeck ${item.version}`).slice(0,160),titleEn:item.titleEn?String(item.titleEn).slice(0,160):undefined,notes:String(item.notes||'').slice(0,4000),notesEn:item.notesEn?String(item.notesEn).slice(0,4000):undefined,changes:Array.isArray(item.changes)?item.changes.map(change=>String(change).slice(0,500)).slice(0,20):[],changesEn:Array.isArray(item.changesEn)?item.changesEn.map(change=>String(change).slice(0,500)).slice(0,20):undefined,publishedAt:item.publishedAt,channel:item.channel}})}
  return manifest;
 }finally{clearTimeout(timer)}
}
