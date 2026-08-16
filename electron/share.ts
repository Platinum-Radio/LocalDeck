import{createServer,request as proxyRequest,type Server}from'node:http';
import{networkInterfaces}from'node:os';
import{randomBytes,randomUUID}from'node:crypto';
import type{Project,ShareSession}from'./types.js';

function lanAddress(){for(const addresses of Object.values(networkInterfaces()))for(const address of addresses??[])if(address.family==='IPv4'&&!address.internal)return address.address;return'127.0.0.1'}

export function startLanShare(project:Project,targetPort:number,durationMinutes:number){
 const token=randomBytes(18).toString('base64url'),id=randomUUID(),createdAt=new Date(),expiresAt=new Date(createdAt.getTime()+durationMinutes*60_000);
 const server=createServer((incoming,response)=>{
  const url=new URL(incoming.url||'/',`http://${incoming.headers.host||'localhost'}`),prefix=`/s/${token}`;
  if(!url.pathname.startsWith(prefix)){response.writeHead(404,{'content-type':'text/plain; charset=utf-8','cache-control':'no-store'});response.end('Deze LocalDeck-deellink is ongeldig.');return}
  if(Date.now()>=expiresAt.getTime()){response.writeHead(410,{'content-type':'text/plain; charset=utf-8','cache-control':'no-store'});response.end('Deze LocalDeck-deellink is verlopen.');return}
  const targetPath=`${url.pathname.slice(prefix.length)||'/'}${url.search}`;
  const headers={...incoming.headers,host:project.domain,'x-forwarded-host':incoming.headers.host||'', 'x-forwarded-proto':'http'};delete headers['accept-encoding'];
  const outgoing=proxyRequest({hostname:'127.0.0.1',port:targetPort,path:targetPath,method:incoming.method,headers},upstream=>{const responseHeaders:Record<string,string|string[]|number|undefined>={...upstream.headers,'cache-control':'no-store'};if(typeof responseHeaders.location==='string')try{const location=new URL(responseHeaders.location,`http://${project.domain}`);if(location.hostname===project.domain||['localhost','127.0.0.1'].includes(location.hostname))responseHeaders.location=`http://${incoming.headers.host}${prefix}${location.pathname}${location.search}${location.hash}`}catch{}if(Array.isArray(responseHeaders['set-cookie']))responseHeaders['set-cookie']=responseHeaders['set-cookie'].map(cookie=>/;\s*Path=/i.test(cookie)?cookie.replace(/;\s*Path=[^;]*/i,`; Path=${prefix}/`):`${cookie}; Path=${prefix}/`);response.writeHead(upstream.statusCode||502,responseHeaders);upstream.pipe(response)});
  outgoing.setTimeout(15_000,()=>outgoing.destroy(new Error('De lokale website reageert niet.')));outgoing.on('error',error=>{if(!response.headersSent)response.writeHead(502,{'content-type':'text/plain; charset=utf-8'});response.end(`LocalDeck kon het project niet bereiken: ${error.message}`)});incoming.pipe(outgoing);
 });
 return new Promise<{server:Server;session:ShareSession}>((resolve,reject)=>{server.once('error',reject);server.listen(0,'0.0.0.0',()=>{server.off('error',reject);const address=server.address(),port=typeof address==='object'&&address?address.port:0,session:ShareSession={id,projectId:project.id,url:`http://${lanAddress()}:${port}/s/${token}/`,token,expiresAt:expiresAt.toISOString(),status:'active',createdAt:createdAt.toISOString()};resolve({server,session})})});
}
