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
  return manifest;
 }finally{clearTimeout(timer)}
}
