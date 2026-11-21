(() => {
  'use strict';

  const storageKey = 'localdeck-ultimate-demo-state-v2';
  const $ = (selector, root = document) => root.querySelector(selector);
  const $$ = (selector, root = document) => [...root.querySelectorAll(selector)];
  const icon = name => `<svg aria-hidden="true"><use href="#i-${name}"></use></svg>`;
  const escapeHtml = value => String(value).replace(/[&<>'"]/g, char => ({'&':'&amp;','<':'&lt;','>':'&gt;',"'":'&#39;','"':'&quot;'}[char]));

  const defaults = {
    selectedProject: 'webshop',
    projectFilter: 'all',
    timelineFilter: 'all',
    theme: 'dark',
    focusProfile: 'balanced',
    operatingMode: 'app',
    projects: [
      { id: 'webshop', name: 'Webshop', domain: 'webshop.localhost', type: 'Laravel', php: '8.5', database: 'webshop', running: true, favorite: true, last: 'Nu actief', response: '24 ms', requests: 184, mail: 2 },
      { id: 'localdeck-site', name: 'LocalDeck Website', domain: 'localdeck.localhost', type: 'PHP', php: '8.5', database: 'localdeck_site', running: false, favorite: true, last: 'Gisteren, 21:18', response: '31 ms', requests: 92, mail: 0 },
      { id: 'voorbeeldblog', name: 'Voorbeeldblog', domain: 'voorbeeldblog.localhost', type: 'WordPress', php: '8.2', database: 'voorbeeldblog', running: false, favorite: false, last: 'Maandag, 14:42', response: '45 ms', requests: 38, mail: 5 }
    ],
    services: [
      { id: 'apache', name: 'Apache', version: '2.4', port: 443, running: true, required: 'Webshop', icon: 'globe' },
      { id: 'php', name: 'PHP 8.5', version: 'FastCGI pool', port: 9000, running: true, required: 'Webshop', icon: 'code' },
      { id: 'mysql', name: 'MySQL', version: '8.4 LTS', port: 3306, running: true, required: 'Webshop', icon: 'database' },
      { id: 'redis', name: 'Redis', version: '7.4', port: 6379, running: true, required: 'Webshop', icon: 'layers' },
      { id: 'mail', name: 'Mail', version: 'Mailpit engine', port: 1025, running: true, required: 'Webshop', icon: 'mail' },
      { id: 'postgres', name: 'PostgreSQL', version: '18 · componentpack', port: 5432, running: false, required: 'Geen actief project', icon: 'database' }
    ],
    operations: [
      { id: 1, title: 'Werkplekcontrole voltooid', detail: '12 controles geslaagd · geen blokkades', status: 'done', time: '2 min geleden', progress: 100 },
      { id: 2, title: 'Automatisch herstelpunt', detail: 'Webshop · vóór dependency-update', status: 'done', time: '18 min geleden', progress: 100 }
    ],
    snapshots: [
      { id: 1, name: 'Vóór dependency-update', project: 'Webshop', date: 'Vandaag, 14:22', size: '418 MB', detail: 'Bestanden · database · configuratie · mail' },
      { id: 2, name: 'Werkende checkout', project: 'Webshop', date: 'Gisteren, 20:41', size: '402 MB', detail: 'Bestanden · database · configuratie' },
      { id: 3, name: 'PHP 8.5-migratie', project: 'LocalDeck Website', date: '16 augustus, 11:06', size: '286 MB', detail: 'Bestanden · database · configuratie' },
      { id: 4, name: 'WordPress schoon', project: 'Voorbeeldblog', date: '14 augustus, 09:17', size: '731 MB', detail: 'Bestanden · database · mail' }
    ]
  };

  const baseTimeline = [
    { id: 1, time: '15:42:18', kind: 'http', severity: 'ok', title: 'POST /checkout', detail: '200 OK · checkout afronden', duration: '742 ms', method: 'POST', status: '200 OK', project: 'Webshop', code: 'POST https://webshop.localhost/checkout', waterfall: [['Apache', 18], ['PHP', 40], ['SQL', 92], ['Mail', 16]] },
    { id: 2, time: '15:42:18', kind: 'sql', severity: 'warning', title: 'SELECT orders WHERE customer_email', detail: '18.420 rijen onderzocht · index aanbevolen', duration: '684 ms', method: 'SELECT', status: 'Traag', project: 'Webshop', code: 'SELECT * FROM orders\nWHERE customer_email = ?\nORDER BY created_at DESC LIMIT 1;', waterfall: [['Wachten', 4], ['Query', 96]] },
    { id: 3, time: '15:42:17', kind: 'queue', severity: 'ok', title: 'SendOrderConfirmation', detail: 'Queuejob succesvol verwerkt', duration: '41 ms', method: 'JOB', status: 'Geslaagd', project: 'Webshop', code: 'App\\Jobs\\SendOrderConfirmation', waterfall: [['Boot', 24], ['Handler', 76]] },
    { id: 4, time: '15:42:17', kind: 'mail', severity: 'ok', title: 'Bestelling #10482 bevestigd', detail: 'naar klant@example.test · lokaal onderschept', duration: '8 ms', method: 'SMTP', status: 'Opgevangen', project: 'Webshop', code: 'From: shop@webshop.localhost\nTo: klant@example.test\nSubject: Bestelling #10482 bevestigd', waterfall: [['SMTP', 100]] },
    { id: 5, time: '15:41:52', kind: 'php', severity: 'error', title: 'Undefined array key "coupon"', detail: 'CartService.php:142 · niet-fataal', duration: '2 ms', method: 'PHP', status: 'Warning', project: 'Webshop', code: 'CartService.php:142\n$discount = $payload[\'coupon\'];', waterfall: [['PHP', 100]] },
    { id: 6, time: '15:41:36', kind: 'http', severity: 'ok', title: 'GET /api/cart', detail: '200 OK · 4 producten', duration: '19 ms', method: 'GET', status: '200 OK', project: 'Webshop', code: 'GET https://webshop.localhost/api/cart', waterfall: [['Apache', 16], ['PHP', 58], ['SQL', 26]] }
  ];

  let state = loadState();
  let timeline = [...baseTimeline];
  let selectedTrace = timeline[0]?.id;
  let pendingSafeAction = null;
  let activeCockpitTab = 'overview';
  let commandIndex = 0;
  let template = 'PHP';
  let selectedTool = 'database';

  const toolCatalog = {
    database: {label:'Database Studio', eyebrow:'DATA · WEBSHOP', icon:'database', color:'violet', summary:'Je database in context, zonder losse beheerpagina.', detail:'Bekijk tabellen, pas rijen aan, vergelijk schema’s en probeer SQL eerst in een geïsoleerde snapshot.', command:'database:inspect webshop --safe', facts:['MySQL 8.4 LTS', '12 tabellen', 'Laatste query 8 ms']},
    mail: {label:'Mail Center', eyebrow:'MAIL · 2 NIEUW', icon:'mail', color:'cyan', summary:'Inboxen, adressen en SMTP-beheer op één plek.', detail:'Maak lokale adressen, bekijk HTML en headers, controleer spamregels en vang alle uitgaande projectmail veilig af.', command:'mail:open webshop --admin', facts:['2 nieuwe berichten', 'SMTP :1025', 'Uitgaand geblokkeerd']},
    terminal: {label:'Command Hub', eyebrow:'AUTOMATION · GEEN DOSBOX', icon:'terminal', color:'green', summary:'Projecttaken zonder opflitsende consolevensters.', detail:'Start Composer, Artisan, WP-CLI, schedulers en eigen taken met uitvoer, historie en annuleerbare processen in LocalDeck zelf.', command:'task:run webshop artisan:test', facts:['8 slimme taken', '0 actieve processen', 'Historie bewaard']},
    api: {label:'API Lab', eyebrow:'HTTP · REPLAY', icon:'pulse', color:'amber', summary:'Test API’s en reproduceer problemen lokaal.', detail:'Importeer OpenAPI, bewaar requests, genereer testdata en speel een complete request opnieuw af met gekoppelde SQL en logs.', command:'api:replay checkout-10482', facts:['3 collecties', '24 ms gemiddeld', 'Mocks beschikbaar']},
    logs: {label:'Live Logs', eyebrow:'OBSERVE · LIVE', icon:'eye', color:'violet', summary:'Alle logs als één doorzoekbare gebeurtenisstroom.', detail:'Apache, PHP, database en applicatielogs worden automatisch gecorreleerd met requests uit de Developer Timeline.', command:'logs:follow webshop --all', facts:['5 bronnen', 'Live volgen', '0 kritieke fouten']},
    share: {label:'Secure Share', eyebrow:'PREVIEW · TIJDELIJK', icon:'globe', color:'cyan', summary:'Deel bewust een tijdelijke lokale preview.', detail:'Maak een afgeschermde link met vervaltijd, duidelijke netwerkgrenzen en één knop om toegang direct weer in te trekken.', command:'share:create webshop --expires 30m', facts:['Nu uitgeschakeld', 'Max. 30 minuten', 'Handmatig intrekken']}
  };

  function loadState() {
    try {
      const stored = JSON.parse(localStorage.getItem(storageKey));
      if (stored && Array.isArray(stored.projects) && Array.isArray(stored.services)) return {...structuredClone(defaults), ...stored};
    } catch (_) {}
    return structuredClone(defaults);
  }

  function persist() {
    localStorage.setItem(storageKey, JSON.stringify(state));
  }

  function projectById(id) {
    return state.projects.find(project => project.id === id) || state.projects[0];
  }

  function initials(project) {
    if (project.type === 'Laravel') return 'L';
    if (project.type === 'WordPress') return 'W';
    return project.name.split(/\s+/).slice(0, 2).map(part => part[0]).join('').toUpperCase();
  }

  function renderProjectCard(project) {
    return `<article class="project-card ${project.running ? 'active-project' : ''}">
      <div class="project-card-head">
        <span class="project-avatar">${escapeHtml(initials(project))}</span>
        <div><strong>${escapeHtml(project.name)}</strong><small>${escapeHtml(project.domain)}</small></div>
        <span class="project-state ${project.running ? 'running' : ''}"><i></i>${project.running ? 'ACTIEF' : 'GESTOPT'}</span>
      </div>
      <div class="project-meta"><span>PHP ${escapeHtml(project.php)}</span><span>${escapeHtml(project.type)}</span><span class="secure">${icon('lock')} HTTPS</span></div>
      <div class="project-card-actions">
        <button class="open-project" data-project="${escapeHtml(project.id)}" type="button">Open cockpit</button>
        <button class="icon-button start-project ${project.running ? 'running' : ''}" data-toggle-project="${escapeHtml(project.id)}" type="button" aria-label="${project.running ? 'Project stoppen' : 'Project starten'}">${icon(project.running ? 'stop' : 'play')}</button>
        <button class="icon-button ${project.favorite ? 'favorite' : ''}" data-favorite="${escapeHtml(project.id)}" type="button" aria-label="Favoriet">☆</button>
      </div>
    </article>`;
  }

  function renderProjects() {
    $('#dashboardProjects').innerHTML = state.projects.slice(0, 3).map(renderProjectCard).join('');
    const query = ($('#projectSearch')?.value || '').trim().toLowerCase();
    const filtered = state.projects.filter(project => {
      const matchesText = `${project.name} ${project.domain} ${project.type}`.toLowerCase().includes(query);
      const matchesFilter = state.projectFilter === 'all' ||
        (state.projectFilter === 'running' && project.running) ||
        (state.projectFilter === 'stopped' && !project.running) ||
        (state.projectFilter === 'favorite' && project.favorite);
      return matchesText && matchesFilter;
    });
    $('#projectsList').innerHTML = filtered.length ? filtered.map(project => `<article class="project-row">
      <span class="project-avatar">${escapeHtml(initials(project))}</span>
      <div class="project-main"><strong>${escapeHtml(project.name)}</strong><small>${icon('lock')} https://${escapeHtml(project.domain)}</small></div>
      <div class="row-data"><small>Omgeving</small><strong>${escapeHtml(project.type)} · PHP ${escapeHtml(project.php)}</strong></div>
      <div class="row-data"><small>Laatst gebruikt</small><strong>${escapeHtml(project.last)}</strong></div>
      <div class="project-row-actions">
        <button class="icon-button ${project.favorite ? 'favorite' : ''}" data-favorite="${escapeHtml(project.id)}" type="button" aria-label="Favoriet">☆</button>
        <button class="secondary compact open-row" data-project="${escapeHtml(project.id)}" type="button">Open</button>
        <button class="primary compact" data-toggle-project="${escapeHtml(project.id)}" type="button">${icon(project.running ? 'stop' : 'play')}${project.running ? 'Stop' : 'Start'}</button>
      </div>
    </article>`).join('') : `<div class="empty-state"><div><h3>Geen projecten gevonden</h3><p>Pas het filter of de zoekopdracht aan.</p></div></div>`;
    $('#metricProjects').textContent = String(state.projects.length);
    $('#projectCountNav').textContent = String(state.projects.length);
    $('#metricServices').textContent = String(state.services.filter(service => service.running).length);
  }

  function renderRuntime() {
    $('#runtimeList').innerHTML = state.services.map(service => `<article class="service-row">
      <span class="service-logo">${icon(service.icon)}</span>
      <div><strong>${escapeHtml(service.name)}</strong><small>${escapeHtml(service.version)} · ${service.running ? 'gezond en bereikbaar' : 'niet actief'}</small></div>
      <div class="service-required">Nodig voor <b>${escapeHtml(service.required)}</b></div>
      <span class="port-value">:${service.port}</span>
      <div class="service-actions"><button class="icon-button" data-service-settings="${service.id}" aria-label="Service instellen">${icon('settings')}</button><button class="${service.running ? 'secondary' : 'primary'} compact" data-toggle-service="${service.id}">${icon(service.running ? 'stop' : 'play')}${service.running ? 'Stop' : 'Start'}</button></div>
    </article>`).join('');
  }

  function renderSnapshots() {
    $('#snapshotList').innerHTML = state.snapshots.map(snapshot => `<article class="snapshot-row">
      <span>${icon('history')}</span>
      <div><strong>${escapeHtml(snapshot.name)}</strong><small>${escapeHtml(snapshot.project)} · ${escapeHtml(snapshot.date)}<br>${escapeHtml(snapshot.detail)}</small></div>
      <div class="snapshot-size">${escapeHtml(snapshot.size)}</div>
      <button class="secondary compact" data-restore="${snapshot.id}" type="button">${icon('refresh')}Herstellen</button>
    </article>`).join('');
  }

  function renderTimeline() {
    const filtered = state.timelineFilter === 'all' ? timeline : timeline.filter(item => item.kind === state.timelineFilter);
    $('#timelineList').innerHTML = filtered.length ? filtered.map(item => `<article class="trace-row ${item.severity === 'ok' ? '' : item.severity} ${selectedTrace === item.id ? 'selected' : ''}" data-trace="${item.id}">
      <i></i><time>${item.time}</time><span class="trace-kind">${item.kind}</span><div><strong>${escapeHtml(item.title)}</strong><small>${escapeHtml(item.detail)}</small></div><span class="trace-duration">${item.duration}</span>
    </article>`).join('') : `<div class="empty-state"><div>${icon('pulse')}<h3>Tijdlijn is leeg</h3><p>Nieuwe lokale gebeurtenissen verschijnen hier.</p></div></div>`;
    const selected = timeline.find(item => item.id === selectedTrace) || filtered[0];
    renderTraceDetail(selected);
  }

  function renderTraceDetail(item) {
    if (!item) {
      $('#traceDetail').innerHTML = `<div class="empty-state"><div><h3>Selecteer een gebeurtenis</h3><p>Bekijk lokale technische details en samenhang.</p></div></div>`;
      return;
    }
    const max = Math.max(...item.waterfall.map(row => row[1]), 1);
    $('#traceDetail').innerHTML = `<span>TRACEDETAIL</span><h3>${escapeHtml(item.title)}</h3><p>${escapeHtml(item.detail)}</p>
      <div class="trace-meta"><div><small>Project</small><strong>${escapeHtml(item.project)}</strong></div><div><small>Type</small><strong>${escapeHtml(item.method)}</strong></div><div><small>Status</small><strong>${escapeHtml(item.status)}</strong></div><div><small>Duur</small><strong>${escapeHtml(item.duration)}</strong></div></div>
      <div class="trace-waterfall">${item.waterfall.map(row => `<div><span>${escapeHtml(row[0])}</span><i style="width:${Math.max(8, row[1] / max * 100)}%"></i><b>${row[1]}%</b></div>`).join('')}</div>
      <pre class="trace-code">${escapeHtml(item.code)}</pre>
      ${item.severity !== 'ok' ? `<button class="secondary" data-analyze="trace" type="button">${icon('spark')}Lokaal analyseren</button>` : ''}`;
  }

  function renderOperations() {
    $('#operationList').innerHTML = state.operations.map(operation => `<article class="operation ${operation.status === 'running' ? 'running' : ''}">
      <span>${icon(operation.status === 'running' ? 'refresh' : 'check')}</span>
      <div><strong>${escapeHtml(operation.title)}</strong><small>${escapeHtml(operation.detail)}</small>${operation.status === 'running' ? `<div class="progress"><i style="width:${operation.progress}%"></i></div>` : ''}</div>
      <time>${escapeHtml(operation.time)}</time>
    </article>`).join('');
    $('#activityBadge').textContent = String(state.operations.filter(item => item.status === 'running').length || 2);
  }

  function renderCockpit(project = projectById(state.selectedProject)) {
    if (!project) return;
    const tabs = ['overview', 'site', 'database', 'mail', 'debug', 'tasks', 'backups', 'environment'];
    const labels = {overview:'Overzicht', site:'Website', database:'Database', mail:'Mail', debug:'Debug', tasks:'Taken', backups:'Back-ups', environment:'Omgeving'};
    $('#cockpitContent').innerHTML = `<div class="cockpit-head">
      <span class="project-avatar">${escapeHtml(initials(project))}</span>
      <div><h2>${escapeHtml(project.name)}</h2><p>${icon('lock')} https://${escapeHtml(project.domain)} · ${escapeHtml(project.type)} · PHP ${escapeHtml(project.php)}</p></div>
      <div class="cockpit-actions"><button class="secondary compact" data-demo-open-site>${icon('external')}Open website</button><button class="${project.running ? 'secondary' : 'primary'} compact" data-toggle-project="${project.id}">${icon(project.running ? 'stop' : 'play')}${project.running ? 'Stop project' : 'Start project'}</button></div>
    </div><div class="cockpit-tabs">${tabs.map(tab => `<button class="${activeCockpitTab === tab ? 'active' : ''}" data-cockpit-tab="${tab}">${labels[tab]}</button>`).join('')}</div>
    <div id="cockpitTabBody">${renderCockpitTab(project, activeCockpitTab)}</div>`;
  }

  function renderCockpitTab(project, tab) {
    if (tab === 'overview') return `<div class="cockpit-body">
      <div class="cockpit-column">
        <article class="cockpit-panel"><h3>Vandaag in ${escapeHtml(project.name)}</h3><p>Live samenvatting van de lokale ontwikkelomgeving.</p><div class="cockpit-kpis"><div><strong>${project.requests}</strong><small>Requests</small></div><div><strong>${project.response}</strong><small>Gemiddeld</small></div><div><strong>${project.mail}</strong><small>Nieuwe mail</small></div><div><strong>0</strong><small>Mislukte jobs</small></div></div></article>
        <article class="cockpit-panel"><h3>Recente activiteit</h3><p>Gebeurtenissen uit de Developer Timeline.</p><div class="recent-list">${baseTimeline.slice(0, 4).map(event => `<div><i></i><span><strong>${escapeHtml(event.title)}</strong><small>${escapeHtml(event.detail)}</small></span><time>${event.time}</time></div>`).join('')}</div></article>
      </div>
      <div class="cockpit-column">
        <article class="cockpit-panel"><h3>Projectstack</h3><p>LocalDeck start alleen deze onderdelen.</p><div class="project-stack"><div>${icon('globe')}<strong>Apache</strong><span>gezond</span></div><div>${icon('code')}<strong>PHP ${escapeHtml(project.php)}</strong><span>gezond</span></div><div>${icon('database')}<strong>MySQL · ${escapeHtml(project.database)}</strong><span>gezond</span></div><div>${icon('mail')}<strong>Lokale mail</strong><span>${project.mail} nieuw</span></div></div></article>
        <article class="cockpit-panel"><h3>Snelle acties</h3><p>Veilige, projectspecifieke handelingen.</p><div class="quick-actions"><button class="secondary" data-demo-open-site>${icon('external')}Website</button><button class="secondary" data-cockpit-tab="database">${icon('database')}Database</button><button class="secondary" data-cockpit-tab="mail">${icon('mail')}Mail</button><button class="secondary" data-cockpit-tab="debug">${icon('pulse')}Debug</button></div></article>
      </div>
    </div>`;
    if (tab === 'environment') return `<div class="cockpit-body"><article class="cockpit-panel"><h3>Reproduceerbare omgeving</h3><p>Wijzigingen worden vooraf gecontroleerd en kunnen automatisch worden teruggedraaid.</p><div class="config-list"><label>PHP-versie<select id="cockpitPhp"><option ${project.php === '8.5' ? 'selected' : ''}>8.5</option><option ${project.php === '8.4' ? 'selected' : ''}>8.4</option><option ${project.php === '8.3' ? 'selected' : ''}>8.3</option><option ${project.php === '8.2' ? 'selected' : ''}>8.2</option></select></label><label>Webserver<select><option>Apache</option><option>Nginx (componentpack)</option><option>Caddy (componentpack)</option></select></label><label>HTTPS-domein<input value="${escapeHtml(project.domain)}"></label></div></article><article class="cockpit-panel"><h3>Project Capsule</h3><p>Deel de omgeving via Git zonder lokale geheimen op te slaan.</p><pre class="trace-code">project: ${escapeHtml(project.id)}\nphp: ${escapeHtml(project.php)}\nwebserver: apache\ndatabase: mysql\nhttps: true</pre><button class="secondary" data-demo-copy>${icon('check')}Capsule kopiëren</button></article></div>`;
    const descriptions = {
      site: ['Website', 'Open de lokale site, bekijk vhosts, HTTPS en recente requests.', 'globe'],
      database: ['Database Studio', `Beheer ${project.database}, vergelijk schema’s en voer veilige SQL uit.`, 'database'],
      mail: ['Mail Studio', `${project.mail} nieuwe testberichten voor dit project. Externe afbeeldingen blijven geblokkeerd.`, 'mail'],
      debug: ['Debug Studio', 'Requests, dumps, PHP-fouten, query’s en jobs in één gecorreleerde tijdlijn.', 'pulse'],
      tasks: ['Task Runner', 'Composer, frameworkopdrachten, queueworkers en scheduler zonder los consolevenster.', 'terminal'],
      backups: ['Time Machine', 'Bestanden, database, configuratie en mail als één herstelbaar moment.', 'history']
    };
    const [title, description, iconName] = descriptions[tab];
    return `<div class="cockpit-placeholder"><div><span>${icon(iconName)}</span><h3>${title}</h3><p>${escapeHtml(description)}</p><button class="primary" data-demo-action="${tab}" type="button">${icon('spark')}Interactieve demoactie</button></div></div>`;
  }

  function renderAll() {
    document.body.classList.toggle('light', state.theme === 'light');
    renderProjects();
    renderRuntime();
    renderSnapshots();
    renderTimeline();
    renderOperations();
    renderUltimateState();
    if ($('[data-page="cockpit"]').classList.contains('active')) renderCockpit();
  }

  function renderUltimateState() {
    const profiles = {
      focus: ['Focusmodus', 'Alleen webserver, PHP en de projectdatabase.'],
      balanced: ['Slim gebalanceerd', 'Start alleen onderdelen die je actieve projecten nodig hebben.'],
      full: ['Volledige stack', 'Alle lokale componenten staan direct voor je klaar.']
    };
    const [title, detail] = profiles[state.focusProfile] || profiles.balanced;
    if ($('#profileTitle')) $('#profileTitle').textContent = title;
    if ($('#profileDetail')) $('#profileDetail').textContent = detail;
    $$('#profileSwitcher [data-profile]').forEach(button => button.classList.toggle('active', button.dataset.profile === state.focusProfile));
    const activeServices = state.services.filter(service => service.running).length;
    if ($('#sidebarEngineDetail')) $('#sidebarEngineDetail').textContent = `${activeServices} services · ${state.operatingMode === 'service' ? 'Windows-services' : 'app-modus'}`;
    if ($('#modePill strong')) $('#modePill strong').textContent = state.operatingMode === 'service' ? 'Windows-services' : 'App-modus';
  }

  const viewMeta = {
    dashboard: ['LOCALDECK / COMMAND DECK', 'Goedemiddag, Ramon'],
    projects: ['WERKPLEK / PROJECTEN', 'Projecten'],
    timeline: ['WERKPLEK / DEVELOPER TIMELINE', 'Developer Timeline'],
    studio: ['LOCALDECK / DEV STUDIO', 'Dev Studio'],
    runtime: ['OMGEVING / RUNTIME', 'Runtime'],
    security: ['LOCALDECK / LOCAL GUARD', 'Local Guard'],
    backups: ['OMGEVING / HERSTELPUNTEN', 'Herstelpunten'],
    settings: ['LOCALDECK / INSTELLINGEN', 'Instellingen'],
    cockpit: ['PROJECT / COCKPIT', 'Project Cockpit']
  };

  function showView(view, options = {}) {
    $$('.view').forEach(page => page.classList.toggle('active', page.dataset.page === view));
    $$('.nav-item').forEach(button => button.classList.toggle('active', button.dataset.view === view));
    const [eyebrow, title] = viewMeta[view] || viewMeta.dashboard;
    $('#viewEyebrow').textContent = eyebrow;
    $('#viewTitle').textContent = options.title || title;
    if (view === 'cockpit') renderCockpit();
    if (view === 'timeline') renderTimeline();
    if (view === 'studio') renderStudioPreview(selectedTool);
    document.querySelector('main').scrollTo({top: 0, behavior: 'smooth'});
  }

  function openCockpit(id) {
    state.selectedProject = id;
    activeCockpitTab = 'overview';
    persist();
    const project = projectById(id);
    showView('cockpit', {title: project.name});
  }

  function addOperation(title, detail, onComplete) {
    const operation = {id: Date.now(), title, detail, status: 'running', time: 'nu', progress: 8};
    state.operations.unshift(operation);
    renderOperations();
    persist();
    openDrawer();
    const steps = [24, 48, 73, 91, 100];
    let index = 0;
    const timer = setInterval(() => {
      operation.progress = steps[index++];
      if (operation.progress >= 100) {
        clearInterval(timer);
        operation.status = 'done';
        operation.time = 'zojuist';
        if (onComplete) onComplete();
        toast('Actie voltooid', title, 'check');
      }
      renderAll();
      persist();
    }, 360);
  }

  function toggleProject(id) {
    const project = projectById(id);
    if (!project) return;
    const start = !project.running;
    addOperation(`${project.name} ${start ? 'starten' : 'stoppen'}`, start ? `Runtime controleren · PHP ${project.php} · database · HTTPS` : 'Ongebruikte services gecontroleerd en veilig stoppen', () => {
      project.running = start;
      project.last = start ? 'Nu actief' : 'Zojuist gestopt';
      if (id === 'webshop') state.services.slice(0, 5).forEach(service => service.running = start);
    });
  }

  function toggleService(id) {
    const service = state.services.find(item => item.id === id);
    if (!service) return;
    const start = !service.running;
    addOperation(`${service.name} ${start ? 'starten' : 'stoppen'}`, `Poort ${service.port} controleren · configuratie valideren · healthcheck`, () => service.running = start);
  }

  function openDrawer() {
    $('#activityDrawer').classList.add('open');
    $('#activityDrawer').setAttribute('aria-hidden', 'false');
    $('#drawerScrim').classList.add('open');
  }

  function closePanels() {
    $('#activityDrawer').classList.remove('open');
    $('#assistantPanel').classList.remove('open');
    $('#activityDrawer').setAttribute('aria-hidden', 'true');
    $('#assistantPanel').setAttribute('aria-hidden', 'true');
    $('#drawerScrim').classList.remove('open');
  }

  function openAssistant(prompt = 'query') {
    closePanels();
    $('#assistantPanel').classList.add('open');
    $('#assistantPanel').setAttribute('aria-hidden', 'false');
    $('#drawerScrim').classList.add('open');
    const userText = prompt === 'php' ? 'Wat moeten we doen met PHP 8.2?' : prompt === 'trace' ? 'Kun je deze fout analyseren?' : 'Waarom reageerde de checkout zo langzaam?';
    $('.chat.user p').textContent = userText;
    const typing = $('.typing');
    const answer = $('.answer');
    typing.style.display = 'flex';
    answer.style.opacity = '.25';
    setTimeout(() => { typing.style.display = 'none'; answer.style.opacity = '1'; }, 650);
  }

  function toast(title, detail, iconName = 'check', actionLabel = '') {
    const item = document.createElement('article');
    item.className = 'toast';
    item.innerHTML = `<span>${icon(iconName)}</span><div><strong>${escapeHtml(title)}</strong><small>${escapeHtml(detail)}</small></div>${actionLabel ? `<button>${escapeHtml(actionLabel)}</button>` : ''}`;
    $('#toastStack').append(item);
    setTimeout(() => item.remove(), 4800);
  }

  function openSafeChange(config) {
    $('#safeTitle').textContent = config.title;
    $('#safeDescription').textContent = config.description;
    $('#safeFrom').textContent = config.from;
    $('#safeTo').textContent = config.to;
    pendingSafeAction = config.apply;
    $('#safeDialog').showModal();
  }

  function showCommand() {
    commandIndex = 0;
    $('#commandInput').value = '';
    renderCommands('');
    $('#commandDialog').showModal();
    setTimeout(() => $('#commandInput').focus(), 0);
  }

  function commandItems() {
    const pages = [
      {label:'Vandaag', detail:'Open het startscherm', icon:'home', action:() => showView('dashboard'), group:'Pagina'},
      {label:'Projecten', detail:'Alle lokale projecten', icon:'grid', action:() => showView('projects'), group:'Pagina'},
      {label:'Developer Timeline', detail:'Requests, SQL, mail en debug', icon:'pulse', action:() => showView('timeline'), group:'Pagina'},
      {label:'Runtime', detail:'Services, poorten en engine', icon:'layers', action:() => showView('runtime'), group:'Pagina'},
      {label:'Dev Studio', detail:'Database, mail, opdrachten, API en logs', icon:'spark', action:() => showView('studio'), group:'Pagina'},
      {label:'Local Guard', detail:'Certificaten, poorten, geheimen en integriteit', icon:'shield', action:() => showView('security'), group:'Pagina'},
      {label:'Nieuw project', detail:'Start de projectwizard', icon:'plus', action:() => $('#projectDialog').showModal(), group:'Actie'},
      {label:'Poorten controleren', detail:'Controleer alle lokale bindings', icon:'radar', action:() => inspectPorts(), group:'Actie'},
      {label:'LocalDeck Assistent', detail:'Analyseer geselecteerde diagnosegegevens lokaal', icon:'spark', action:() => openAssistant(), group:'Actie'},
      {label:'Slim startprofiel toepassen', detail:'Start alleen wat je projecten nodig hebben', icon:'play', action:() => applyFocusProfile(), group:'Actie'},
      {label:'Alles veilig stoppen', detail:'Stop projecten en ongebruikte services', icon:'stop', action:() => stopEverything(), group:'Actie'}
    ];
    return [...pages, ...state.projects.map(project => ({label:project.name, detail:project.domain, icon:'grid', action:() => openCockpit(project.id), group:'Project'}))];
  }

  function renderCommands(query) {
    const items = commandItems().filter(item => `${item.label} ${item.detail} ${item.group}`.toLowerCase().includes(query.toLowerCase()));
    commandIndex = Math.min(commandIndex, Math.max(items.length - 1, 0));
    $('#commandResults').innerHTML = items.length ? items.map((item, index) => `<button class="command-item ${index === commandIndex ? 'selected' : ''}" data-command="${index}" type="button"><span>${icon(item.icon)}</span><div><strong>${escapeHtml(item.label)}</strong><small>${escapeHtml(item.detail)}</small></div><em>${escapeHtml(item.group)}</em></button>`).join('') : `<div class="empty-state"><p>Geen resultaat</p></div>`;
    $('#commandResults').dataset.items = JSON.stringify(items.map(item => item.label));
    return items;
  }

  function inspectPorts() {
    addOperation('Alle poorten controleren', 'IPv4 · IPv6 · TCP · projectpoorten · proceseigenaren', () => toast('Geen blokkades gevonden', 'Alle LocalDeck-poorten zijn beschikbaar of correct in gebruik.', 'radar'));
  }

  function createSnapshot() {
    const project = projectById(state.selectedProject) || state.projects[0];
    addOperation('Herstelpunt maken', `${project.name} · gewijzigde bestanden dedupliceren · database controleren`, () => {
      state.snapshots.unshift({id: Date.now(), name: 'Handmatig herstelpunt', project: project.name, date: 'Zojuist', size: '126 MB', detail: 'Bestanden · database · configuratie · mail'});
    });
  }

  function selectFocusProfile(profile) {
    if (!['focus', 'balanced', 'full'].includes(profile)) return;
    state.focusProfile = profile;
    persist();
    renderUltimateState();
  }

  function applyFocusProfile() {
    const profile = state.focusProfile || 'balanced';
    const labels = {focus:'Focusmodus', balanced:'Slim gebalanceerd', full:'Volledige stack'};
    addOperation(`${labels[profile]} toepassen`, 'Projectbehoeften analyseren · poorten controleren · processen combineren', () => {
      state.services.forEach((service, index) => {
        service.running = profile === 'full' || (profile === 'balanced' && index < 5) || (profile === 'focus' && index < 3);
      });
    });
  }

  function stopEverything() {
    const active = state.projects.some(project => project.running) || state.services.some(service => service.running);
    if (!active) return toast('Alles is al gestopt', 'Er draaien geen projecten of services.', 'check');
    addOperation('Alles veilig stoppen', 'Projecttaken afronden · databases flushen · services op afhankelijkheid stoppen', () => {
      state.projects.forEach(project => project.running = false);
      state.services.forEach(service => service.running = false);
    });
  }

  function renderStudioPreview(toolId) {
    const tool = toolCatalog[toolId] || toolCatalog.database;
    const preview = $('#studioPreview');
    if (!preview) return;
    preview.innerHTML = `<div class="preview-terminal"><span></span><span></span><span></span><code>localdeck&gt; ${escapeHtml(tool.command)}<br><b>✓</b> Projectcontext geladen &nbsp; <b>✓</b> Veilige modus actief<br>${tool.facts.map(fact => `• ${escapeHtml(fact)}`).join(' &nbsp; ')}</code></div><div><span>${escapeHtml(tool.eyebrow)}</span><h3>${escapeHtml(tool.summary)}</h3><p>${escapeHtml(tool.detail)}</p><button class="primary" data-tool-launch="${escapeHtml(toolId)}" type="button">${icon(tool.icon)}${escapeHtml(tool.label)} starten</button></div>`;
  }

  function openTool(toolId) {
    selectedTool = toolCatalog[toolId] ? toolId : 'database';
    showView('studio');
    renderStudioPreview(selectedTool);
    toast(`${toolCatalog[selectedTool].label} voorbereid`, 'Projectcontext Webshop is automatisch gekoppeld.', toolCatalog[selectedTool].icon);
  }

  function runGuardScan() {
    const button = $('#runGuardScan');
    button.disabled = true;
    button.innerHTML = `${icon('refresh')}Controleren…`;
    addOperation('Local Guard-controle', 'Bindings · certificaten · componenthashes · secrets · herstelgereedheid', () => {
      $('#guardScore').textContent = '100';
      button.disabled = false;
      button.innerHTML = `${icon('check')}Alles gecontroleerd`;
      toast('Local Guard: 100/100', 'Geen blokkades of onveilige netwerkbindings gevonden.', 'shield');
    });
  }

  document.addEventListener('click', event => {
    const target = event.target.closest('button, [data-project], [data-trace]');
    if (!target) return;
    if (target.matches('.nav-item')) showView(target.dataset.view);
    if (target.dataset.go) showView(target.dataset.go);
    if (target.dataset.project) openCockpit(target.dataset.project);
    if (target.dataset.toggleProject) toggleProject(target.dataset.toggleProject);
    if (target.dataset.favorite) {
      const project = projectById(target.dataset.favorite);
      project.favorite = !project.favorite;
      persist(); renderProjects();
      toast(project.favorite ? 'Toegevoegd aan favorieten' : 'Verwijderd uit favorieten', project.name, 'grid');
    }
    if (target.dataset.toggleService) toggleService(target.dataset.toggleService);
    if (target.dataset.serviceSettings) toast('Contextinstellingen geopend', 'In de echte versie verschijnt hier de projectspecifieke configuratie.', 'settings');
    if (target.dataset.trace) { selectedTrace = Number(target.dataset.trace); renderTimeline(); }
    if (target.dataset.analyze) openAssistant(target.dataset.analyze);
    if (target.dataset.restore) openSafeChange({title:'Herstelpunt terugzetten', description:'LocalDeck controleert eerst de huidige toestand en bewaart deze als apart herstelpunt.', from:'Huidige omgeving', to:'Geselecteerd moment', apply:() => addOperation('Herstelpunt terugzetten', 'Bestanden en database veilig herstellen · eindcontrole uitvoeren')});
    if (target.dataset.cockpitTab) {
      activeCockpitTab = target.dataset.cockpitTab;
      renderCockpit();
    }
    if (target.dataset.demoOpenSite !== undefined) toast('Website openen', 'In deze conceptdemo wordt geen echte lokale website gestart.', 'external');
    if (target.dataset.demoCopy !== undefined) toast('Project Capsule gekopieerd', 'Demo-inhoud staat nu klaar voor een teamgenoot.', 'check');
    if (target.dataset.demoAction) toast(`${target.dataset.demoAction} geopend`, 'Deze conceptactie laat zien waar de volledige functie in LocalDeck Next komt.', 'spark');
    if (target.dataset.profile) selectFocusProfile(target.dataset.profile);
    if (target.dataset.tool) openTool(target.dataset.tool);
    if (target.dataset.toolLaunch) toast(`${toolCatalog[target.dataset.toolLaunch].label} gestart`, 'In de echte app opent hier de volledig geïntegreerde studio.', toolCatalog[target.dataset.toolLaunch].icon);
    if (target.dataset.guardReport !== undefined) toast('Controleverslag gereed', 'Het lokale verslag bevat alle controles, versies en bindings.', 'shield', 'Bekijken');
    if (target.matches('[data-close-drawer]')) closePanels();
    if (target.matches('.new-project-trigger')) $('#projectDialog').showModal();
  });

  $$('.nav-item').forEach(button => button.addEventListener('dblclick', () => showView(button.dataset.view)));
  $('#heroNewProject').addEventListener('click', () => $('#projectDialog').showModal());
  $('#quickNewProject').addEventListener('click', () => $('#projectDialog').showModal());
  $('#openActivity').addEventListener('click', openDrawer);
  $('#drawerScrim').addEventListener('click', closePanels);
  $('#openAssistant').addEventListener('click', () => openAssistant());
  $('#closeAssistant').addEventListener('click', closePanels);
  $('#previewAiFix').addEventListener('click', () => {
    closePanels();
    openSafeChange({title:'Query-optimalisatie proberen', description:'LocalDeck maakt een geïsoleerde databasesnapshot, voegt een testindex toe en vergelijkt de responstijd.', from:'684 ms', to:'Verwacht < 40 ms', apply:() => addOperation('Query-optimalisatie testen', 'Databasesnapshot · index maken · checkout opnieuw meten')});
  });
  $('#themeToggle').addEventListener('click', () => { state.theme = state.theme === 'dark' ? 'light' : 'dark'; persist(); renderAll(); });
  $('#inspectPorts').addEventListener('click', inspectPorts);
  $('#createSnapshot').addEventListener('click', createSnapshot);
  $('#importProject').addEventListener('click', () => toast('Mapkiezer zou nu openen', 'Deze statische demo heeft bewust geen toegang tot je bestanden.', 'folder'));
  $('#startFavorites')?.addEventListener('click', () => {
    const stopped = state.projects.filter(project => project.favorite && !project.running);
    if (!stopped.length) return toast('Favorieten zijn al actief', 'Er hoeft niets te worden gestart.', 'check');
    addOperation('Favoriete projecten starten', `${stopped.map(project => project.name).join(', ')} · gedeelde services combineren`, () => stopped.forEach(project => project.running = true));
  });
  $('#applyProfile').addEventListener('click', applyFocusProfile);
  $('#runGuardScan').addEventListener('click', runGuardScan);
  $('#modePill').addEventListener('click', () => {
    const next = state.operatingMode === 'app' ? 'service' : 'app';
    openSafeChange({title:'Uitvoermodus wijzigen', description:'LocalDeck controleert beheerdersrechten, bewaart de actieve projectstatus en wisselt daarna gecontroleerd van uitvoermodus.', from:state.operatingMode === 'app' ? 'App-modus' : 'Windows-services', to:next === 'app' ? 'App-modus' : 'Windows-services', apply:() => addOperation('Uitvoermodus wijzigen', 'Status bewaren · services registreren · healthcheck uitvoeren', () => state.operatingMode = next)});
  });
  $('#resetDemo').addEventListener('click', () => { localStorage.removeItem(storageKey); location.reload(); });

  $('#projectFilters').addEventListener('click', event => {
    const button = event.target.closest('button[data-filter]');
    if (!button) return;
    state.projectFilter = button.dataset.filter;
    $$('#projectFilters button').forEach(item => item.classList.toggle('active', item === button));
    persist(); renderProjects();
  });
  $('#projectSearch').addEventListener('input', renderProjects);
  $('#timelineFilters').addEventListener('click', event => {
    const button = event.target.closest('button[data-kind]');
    if (!button) return;
    state.timelineFilter = button.dataset.kind;
    $$('#timelineFilters button').forEach(item => item.classList.toggle('active', item === button));
    persist(); renderTimeline();
  });
  $('#clearTimeline').addEventListener('click', () => { timeline = []; selectedTrace = null; renderTimeline(); toast('Tijdlijn gewist', 'Nieuwe gebeurtenissen worden lokaal opnieuw verzameld.', 'trash'); });

  $$('.template').forEach(button => button.addEventListener('click', () => {
    $$('.template').forEach(item => item.classList.toggle('active', item === button));
    template = button.dataset.template;
  }));
  $('#newProjectName').addEventListener('input', event => {
    $('#newProjectDomain').value = event.target.value.toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g, '').replace(/[^a-z0-9]+/g, '-').replace(/^-|-$/g, '');
  });
  $('#projectForm').addEventListener('submit', event => {
    const submitter = event.submitter;
    if (!submitter || submitter.value === 'cancel') return;
    event.preventDefault();
    const name = $('#newProjectName').value.trim();
    const domainPart = $('#newProjectDomain').value.trim();
    if (!name || !domainPart) return;
    const idBase = domainPart;
    let id = idBase;
    let suffix = 2;
    while (state.projects.some(project => project.id === id)) id = `${idBase}-${suffix++}`;
    const project = {id, name, domain:`${domainPart}.localhost`, type:template, php:$('#newProjectPhp').value, database:domainPart.replace(/-/g, '_'), running:false, favorite:false, last:'Zojuist gemaakt', response:'—', requests:0, mail:0};
    state.projects.unshift(project);
    state.selectedProject = id;
    $('#projectDialog').close();
    persist(); renderAll();
    addOperation(`${name} voorbereiden`, `Projectmap · PHP ${project.php} · database · HTTPS · Capsule`, () => { project.running = true; openCockpit(id); });
  });

  $('#safeDialog').addEventListener('close', () => {
    if ($('#safeDialog').returnValue === 'default' && pendingSafeAction) pendingSafeAction();
    pendingSafeAction = null;
  });

  $('#openCommand').addEventListener('click', showCommand);
  $('#commandInput').addEventListener('input', event => { commandIndex = 0; renderCommands(event.target.value); });
  $('#commandInput').addEventListener('keydown', event => {
    const items = renderCommands(event.currentTarget.value);
    if (event.key === 'ArrowDown') { event.preventDefault(); commandIndex = Math.min(commandIndex + 1, items.length - 1); renderCommands(event.currentTarget.value); }
    if (event.key === 'ArrowUp') { event.preventDefault(); commandIndex = Math.max(commandIndex - 1, 0); renderCommands(event.currentTarget.value); }
    if (event.key === 'Enter' && items[commandIndex]) { event.preventDefault(); $('#commandDialog').close(); items[commandIndex].action(); }
  });
  $('#commandResults').addEventListener('click', event => {
    const button = event.target.closest('[data-command]');
    if (!button) return;
    const items = renderCommands($('#commandInput').value);
    const selected = items[Number(button.dataset.command)];
    if (selected) { $('#commandDialog').close(); selected.action(); }
  });
  document.addEventListener('keydown', event => {
    if ((event.ctrlKey || event.metaKey) && event.key.toLowerCase() === 'k') { event.preventDefault(); showCommand(); }
    if (event.key === 'Escape') closePanels();
  });

  document.addEventListener('change', event => {
    if (event.target.id === 'cockpitPhp') {
      const project = projectById(state.selectedProject);
      const next = event.target.value;
      event.target.value = project.php;
      openSafeChange({title:'PHP-versie wijzigen', description:'LocalDeck controleert extensies en Composer-eisen, maakt een herstelpunt en test de website na de wijziging.', from:`PHP ${project.php}`, to:`PHP ${next}`, apply:() => addOperation(`PHP ${next} activeren`, `${project.name} · extensies controleren · FastCGI-pool wisselen · browsertest`, () => project.php = next)});
    }
  });

  $('#assistantSetting').addEventListener('change', event => toast(event.target.checked ? 'Lokale assistent ingeschakeld' : 'Lokale assistent uitgeschakeld', event.target.checked ? 'De echte versie gebruikt uitsluitend een lokaal beschikbaar model.' : 'Alle regelgebaseerde diagnose blijft beschikbaar.', 'spark'));

  setInterval(() => {
    if (!$('#liveToggle').checked || timeline.length === 0) return;
    const now = new Date().toLocaleTimeString('nl-NL', {hour12:false});
    const event = {id:Date.now(), time:now, kind:'http', severity:'ok', title:'GET /api/health', detail:'200 OK · automatische lokale healthcheck', duration:'7 ms', method:'GET', status:'200 OK', project:'Webshop', code:'GET https://webshop.localhost/api/health', waterfall:[['Apache',22],['PHP',78]]};
    timeline.unshift(event);
    if (timeline.length > 12) timeline.pop();
    if ($('[data-page="timeline"]').classList.contains('active')) renderTimeline();
  }, 9000);

  renderAll();
})();
