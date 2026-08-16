import{describe,expect,it,vi}from'vitest';
import{compareVersions,fetchUpdateManifest}from'./updates.js';

describe('updateversies',()=>{
 it('herkent nieuwere, gelijke en oudere versies',()=>{expect(compareVersions('0.4.1','0.4.0')).toBe(1);expect(compareVersions('0.4.0','0.4.0')).toBe(0);expect(compareVersions('0.3.9','0.4.0')).toBe(-1)});
 it('gebruikt de aangeleverde veilige netwerklaag voor de updatefeed',async()=>{const request=vi.fn(async()=>new Response(JSON.stringify({version:'1.0.1',downloadUrl:'https://localdeck.example/download.php?version=1.0.1&artifact=setup-x64',channel:'stable',sha256:'a'.repeat(64),signed:false}),{status:200,headers:{'content-type':'application/json'}}));const manifest=await fetchUpdateManifest('https://localdeck.example/downloads/windows.json','stable',request);expect(manifest.version).toBe('1.0.1');expect(manifest.signed).toBe(false);expect(request).toHaveBeenCalledOnce();expect(request.mock.calls[0][0]).toBe('https://localdeck.example/downloads/windows.json')});
 it('weigert een updatefeed zonder geldige SHA-256',async()=>{const request=vi.fn(async()=>new Response(JSON.stringify({version:'1.0.1',downloadUrl:'https://localdeck.example/download.exe',channel:'stable'}),{status:200}));await expect(fetchUpdateManifest('https://localdeck.example/downloads/windows.json','stable',request)).rejects.toThrow('SHA-256')});
 it('weigert onbeveiligde publieke feeds vóór een netwerkrequest',async()=>{const request=vi.fn();await expect(fetchUpdateManifest('http://localdeck.example/windows.json','stable',request)).rejects.toThrow('HTTPS');expect(request).not.toHaveBeenCalled()});
});
