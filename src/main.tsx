import React from'react';import{createRoot}from'react-dom/client';import'./styles.css';import'./database.css';import'./brand.css';import'./v03.css';import'./v04.css';import'./v05.css';import'./v06.css';import'./v07.css';import'./v08.css';import'./v09.css';import'./v10.css';import'./rollback.css';import'./settings.css';import'./v11.css';import Shell from'./Shell';

async function start(){
 if(import.meta.env.DEV&&!window.localdeck){const{installBrowserPreview}=await import('./browserPreview');installBrowserPreview()}
 createRoot(document.getElementById('root')!).render(<React.StrictMode><Shell/></React.StrictMode>);
}

void start();
