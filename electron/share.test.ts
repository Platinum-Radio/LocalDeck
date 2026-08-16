import{afterEach,describe,expect,it}from'vitest';
import{createServer,type Server}from'node:http';
import{startLanShare}from'./share.js';
import type{Project}from'./types.js';

const servers:Server[]=[];
afterEach(()=>servers.splice(0).forEach(server=>server.close()));
const listen=(server:Server)=>new Promise<number>(resolve=>server.listen(0,'127.0.0.1',()=>{const address=server.address();resolve(typeof address==='object'&&address?address.port:0)}));

describe('tijdelijk LAN-delen',()=>{
 it('beveiligt het pad met een token en herschrijft lokale redirects en cookies',async()=>{let receivedHost='';const target=createServer((request,response)=>{receivedHost=String(request.headers.host);if(request.url==='/redirect'){response.writeHead(302,{location:'http://demo.test/dashboard','set-cookie':'demo=1; Path=/; HttpOnly'});response.end();return}response.end(`doel:${request.url}`)});servers.push(target);const targetPort=await listen(target),project:Project={id:'project-1',name:'Demo',path:'C:\\Project',domain:'demo.test',phpVersion:'8.4',secure:false,createdAt:new Date().toISOString()},{server,session}=await startLanShare(project,targetPort,30);servers.push(server);const shared=new URL(session.url);shared.hostname='127.0.0.1';const invalid=await fetch(`${shared.origin}/verkeerd`);expect(invalid.status).toBe(404);const valid=await fetch(new URL('hello?x=1',shared));expect(await valid.text()).toBe('doel:/hello?x=1');expect(receivedHost).toBe('demo.test');const redirect=await fetch(new URL('redirect',shared),{redirect:'manual'});expect(redirect.status).toBe(302);expect(redirect.headers.get('location')).toBe(`${shared.origin}${shared.pathname}dashboard`);expect(redirect.headers.get('set-cookie')).toContain(`Path=${shared.pathname}`)});
});
