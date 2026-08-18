import {useEffect, useMemo, useState} from 'react';
import {
  Activity, AlertTriangle, Archive, Beaker, CheckCircle2, ChevronRight, Code2, Command,
  Database, FolderInput, FolderOpen, GitFork, Globe2, Info, LayoutDashboard, ListChecks,
  Mail, Network, PackageOpen, Plus, Radio, RefreshCw, Server, Settings, ShieldCheck,
  Sparkles, Stethoscope, Terminal, Webhook, Wrench, X, Zap,
} from 'lucide-react';
import type {LucideIcon} from 'lucide-react';
import {
  ControlDashboard, ControlDatabases, ControlLogs, ControlMail, ControlProjects, ControlRuntime,
  ControlServices, FirstRunPopup, UpdatePopup, type ActionFeedback, type StateAction,
} from './ControlCenter';
import {CatalogPage, DatabaseWorkbench, MailSettings, ProjectProfiles, RuntimeExtras, SecurityPage, SnapshotsPage, TasksPage} from './AdvancedCenter';
import {InspectorPage, StudioPage} from './ProjectStudio';
import {DatabaseUsersPanel, MailRetentionPanel, OperationsCenter} from './OperationsCenter';
import {ActionCenterPage, ApiStudioPage, AutomationPage, QualityLabPage} from './EngineeringCenter';
import {DebugInbox, LocalDeckFix, ProjectWorkspace, type EasePage} from './EaseCenter';
import {SettingsCenter} from './SettingsCenter';
import {installDocumentTranslations, translateText} from './i18n';

type Page = EasePage;
type CreationKind = 'new' | 'existing' | 'git';
type ProjectCreationTemplate = 'php' | 'wordpress' | 'laravel' | 'symfony' | 'drupal';
type ActionNotice = Omit<ActionFeedback, 'tone'> & {id: number; tone: 'success' | 'info' | 'warning' | 'error'};
interface NavigationItem {id: Page; label: string; icon: LucideIcon; simple: boolean}

const navigation: NavigationItem[] = [
  {id: 'dashboard', label: 'Overzicht', icon: LayoutDashboard, simple: true},
  {id: 'projects', label: 'Projecten', icon: Code2, simple: true},
  {id: 'services', label: 'Services', icon: Server, simple: true},
  {id: 'databases', label: 'Databases', icon: Database, simple: true},
  {id: 'mail', label: 'E-mail', icon: Mail, simple: true},
  {id: 'doctor', label: 'LocalDeck Fix', icon: Stethoscope, simple: true},
  {id: 'settings', label: 'Instellingen', icon: Settings, simple: true},
  {id: 'studio', label: 'Project Studio', icon: GitFork, simple: false},
  {id: 'automation', label: 'Automatisering', icon: Network, simple: false},
  {id: 'actions', label: 'Action Center', icon: Zap, simple: false},
  {id: 'quality', label: 'Quality Lab', icon: Beaker, simple: false},
  {id: 'api', label: 'API & Webhooks', icon: Webhook, simple: false},
  {id: 'snapshots', label: 'Snapshots', icon: Archive, simple: false},
  {id: 'tasks', label: 'Taken', icon: ListChecks, simple: false},
  {id: 'security', label: 'Beveiliging', icon: ShieldCheck, simple: false},
  {id: 'catalog', label: 'Catalogus', icon: PackageOpen, simple: false},
  {id: 'runtime', label: 'Installatie', icon: Wrench, simple: false},
  {id: 'inspector', label: 'Debug Inbox', icon: Activity, simple: false},
  {id: 'logs', label: 'Logboek', icon: Terminal, simple: false},
];

const subtitles: Record<Page, string> = {
  dashboard: 'Alles instellen en bedienen vanuit één werkplek.',
  projects: 'Open, beheer, test en herstel je website vanuit één scherm.',
  studio: 'Git, blueprints, branchomgevingen en reproduceerbare runtimes.',
  services: 'Kies app-modus of echte Windows-services.',
  automation: 'Startprofielen, afhankelijkheden, poortbindingen, queues en schedulers.',
  actions: 'Duurzame voortgang, resultaten, herstelpunten, annuleren en opnieuw uitvoeren.',
  quality: 'Rehearsals, release gates, performance, browser-, dependency- en migratiecontroles.',
  api: 'OpenAPI, lokale requests, testdata, webhookopvang en reproduceerbare replays.',
  databases: 'Databases, tabellen, veilige SQL, gebruikers, import, export en back-ups.',
  snapshots: 'Herstelpunten voor projectbestanden en gekoppelde databases.',
  tasks: 'Composer-, Node-, test- en eigen projectopdrachten.',
  mail: 'Lokale testadressen, Mailbeheer Pro en configureerbare SMTP-routes.',
  doctor: 'Eén controle- en herstelplek voor LocalDeck en al je projecten.',
  security: 'Lokale controles, automatisch herstel en updatevertrouwen.',
  catalog: 'Templates, adapters, migratie en overdraagbare profielen.',
  runtime: 'Ingebouwde offline runtime, PHP-versies, extensies en herstel.',
  inspector: 'Requests, PHP, SQL, taken, webhooks en logs in één inbox.',
  logs: 'Doorzoek gebeurtenissen en gecombineerde servicelogs.',
  settings: 'Gedrag, domeinen, updates, back-ups, privacy en webbeheer.',
};

function badgeFor(id: Page, state: AppState) {
  if (id === 'actions') return state.actionOperations.filter(item => item.status === 'running').length;
  if (id === 'logs') return state.logs.length;
  if (id === 'snapshots') return state.snapshots.length;
  if (id === 'security') return state.securityFindings.filter(item => !item.resolved).length;
  if (id === 'doctor') return state.projectHealth.filter(report => report.status !== 'ok').length;
  if (id === 'databases') return state.databases.length;
  if (id === 'inspector') return state.inspectorEvents.filter(item => item.severity !== 'info').length;
  return 0;
}

export default function Shell() {
  const requestedPage = new URLSearchParams(window.location.search).get('page') as Page | null;
  const initialPage = navigation.some(item => item.id === requestedPage) ? requestedPage! : 'dashboard';
  const [state, setState] = useState<AppState | null>(null);
  const [page, setPage] = useState<Page>(initialPage);
  const [projectModal, setProjectModal] = useState(false);
  const [palette, setPalette] = useState(false);
  const [error, setError] = useState('');
  const [notice, setNotice] = useState<ActionNotice | null>(null);

  useEffect(() => {void window.localdeck.getState().then(setState); return window.localdeck.onStateChanged(setState);}, []);
  useEffect(() => {if (!state) return; return installDocumentTranslations(state.settings.language);}, [state?.settings.language]);
  useEffect(() => {if (!notice) return; const timer = window.setTimeout(() => setNotice(current => current?.id === notice.id ? null : current), 5600); return () => window.clearTimeout(timer);}, [notice]);
  useEffect(() => {
    const key = (event: KeyboardEvent) => {
      if ((event.ctrlKey || event.metaKey) && event.key.toLowerCase() === 'k') {event.preventDefault(); setPalette(value => !value);}
      else if (event.key === 'Escape') setPalette(false);
    };
    window.addEventListener('keydown', key); return () => window.removeEventListener('keydown', key);
  }, []);

  const showNotice = (feedback: ActionFeedback, tone = feedback.tone ?? 'success') => setNotice({id: Date.now(), title: feedback.title, message: feedback.message, tone});
  const action: StateAction = async (operation, options) => {
    if (options?.pending) showNotice(options.pending, 'info');
    try {
      setError(''); const next = await operation(); setState(next);
      if (options?.success) showNotice(typeof options.success === 'function' ? options.success(next) : options.success);
      return true;
    } catch (reason) {
      const raw = reason instanceof Error ? reason.message : String(reason);
      const message = raw.replace(/^Error invoking remote method '[^']+':\s*(?:Error:\s*)?/i, '');
      setError(message); setNotice({id: Date.now(), title: 'Actie niet uitgevoerd', message, tone: 'error'}); return false;
    }
  };
  if (!state) return <div className="loader"><RefreshCw className="spin" />LocalDeck starten…</div>;

  const interfaceMode = state.settings.interfaceMode ?? 'simple';
  const visibleNavigation = navigation.filter(item => interfaceMode === 'advanced' || item.simple);
  const running = state.services.filter(service => service.status === 'running').length;
  const current = navigation.find(item => item.id === page) ?? navigation[0];
  const NoticeIcon = notice?.tone === 'success' ? CheckCircle2 : notice?.tone === 'info' ? Info : AlertTriangle;
  const switchMode = (mode: 'simple' | 'advanced') => {
    if (mode === 'simple' && !current.simple) setPage('dashboard');
    void action(() => window.localdeck.updateSettings({interfaceMode: mode}), {success: {title: mode === 'simple' ? 'Eenvoudige modus actief' : 'Geavanceerde modus actief', message: mode === 'simple' ? 'Alle dagelijkse functies blijven direct bereikbaar.' : 'Alle ontwikkel- en kwaliteitstools zijn nu zichtbaar.', tone: 'info'}});
  };

  return (
    <div className={`app interface-${interfaceMode} ${state.settings.privacyMode ? 'privacyMode' : ''}`}>
      <aside>
        <div className="brand"><div className="brandmark"><img src="./icon-512.png" alt="" /></div><div><b>LocalDeck</b><small>Windows development</small></div></div>
        <nav aria-label="Hoofdnavigatie">
          {visibleNavigation.map(item => {
            const Icon = item.icon; const badge = badgeFor(item.id, state); const firstAdvanced = interfaceMode === 'advanced' && item.id === 'studio';
            return <div className="navigationEntry" key={item.id}>{firstAdvanced ? <small className="navigationGroup">GEAVANCEERD</small> : null}<button title={item.label} className={page === item.id ? 'active' : ''} onClick={() => setPage(item.id)}><Icon /><span>{item.label}</span>{badge ? <i>{badge}</i> : null}</button></div>;
          })}
        </nav>
        <div className="sidebarMode" role="group" aria-label="Interfacemodus"><button title="Eenvoudige modus" className={interfaceMode === 'simple' ? 'active' : ''} onClick={() => switchMode('simple')}><Sparkles /><span>Eenvoudig</span></button><button title="Geavanceerde modus" className={interfaceMode === 'advanced' ? 'active' : ''} onClick={() => switchMode('advanced')}><Settings /><span>Geavanceerd</span></button></div>
        <div className="runtime"><span><Radio />Uitvoermodus</span><strong className={state.settings.runtimeMode === 'windows-service' ? 'green' : 'amber'}>{state.settings.runtimeMode === 'windows-service' ? 'WINDOWS-SERVICE' : 'APP-MODUS'}</strong><small>{running}/{state.services.length} services actief{state.settings.folderMode ? ' · mapmodus' : ''}</small></div>
        <button className="sidebarWebsite" type="button" onClick={() => void action(() => window.localdeck.openWebsite())}><Globe2 /><span><b>LocalDeck.nl</b><small>Website & documentatie</small></span><ChevronRight /></button>
        <div className="version">LocalDeck v1.1.0-test.1 · Windows</div>
      </aside>
      <main>
        <header><div><h1>{current.label}</h1><p>{subtitles[page]}</p></div><div className="headerActions"><div className={`health ${running ? 'online' : ''}`}><span />{running ? `${running} actief` : 'Alles gestopt'}</div><button className="ghost commandButton" onClick={() => setPalette(true)}><Command />Ctrl K</button><button className="ghost privacyButton" title="Presentatie-/privacymodus" onClick={() => void action(() => window.localdeck.updateSettings({privacyMode: !state.settings.privacyMode}))}><ShieldCheck /><span>{state.settings.privacyMode ? 'Privacy aan' : 'Privacy'}</span></button><button className="ghost workspaceButton" onClick={() => void window.localdeck.openWorkspace()}><FolderOpen /><span>Websites-map</span></button><button className="ghost webControlButton" onClick={() => void action(() => window.localdeck.openTool('webcontrol'))}>Webbeheer</button><button className="primary" onClick={() => setProjectModal(true)}><Plus /><span>Nieuw project</span></button></div></header>
        {error ? <div className="error"><ShieldCheck />{error}<button onClick={() => setError('')}><X /></button></div> : null}
        {page === 'dashboard' ? <ControlDashboard state={state} action={action} openProject={() => setProjectModal(true)} /> : null}
        {page === 'projects' ? <><ProjectWorkspace state={state} action={action} navigate={setPage} openProject={() => setProjectModal(true)} />{interfaceMode === 'advanced' ? <details className="advancedDrawer"><summary><Settings />Geavanceerd projectbeheer<ChevronRight /></summary><ControlProjects state={state} action={action} openProject={() => setProjectModal(true)} /><ProjectProfiles state={state} action={action} /></details> : null}</> : null}
        {page === 'studio' ? <StudioPage state={state} action={action} /> : null}
        {page === 'services' ? <ControlServices state={state} action={action} /> : null}
        {page === 'automation' ? <AutomationPage state={state} action={action} /> : null}
        {page === 'actions' ? <ActionCenterPage state={state} action={action} /> : null}
        {page === 'quality' ? <QualityLabPage state={state} action={action} /> : null}
        {page === 'api' ? <ApiStudioPage state={state} action={action} /> : null}
        {page === 'databases' ? <><ControlDatabases state={state} action={action} /><DatabaseWorkbench state={state} /><DatabaseUsersPanel state={state} action={action} /></> : null}
        {page === 'snapshots' ? <SnapshotsPage state={state} action={action} /> : null}
        {page === 'tasks' ? <TasksPage state={state} action={action} /> : null}
        {page === 'mail' ? <><ControlMail state={state} action={action} /><MailSettings state={state} action={action} /><MailRetentionPanel state={state} action={action} /></> : null}
        {page === 'doctor' ? <><LocalDeckFix state={state} action={action} />{interfaceMode === 'advanced' ? <details className="advancedDrawer"><summary><Settings />Technische diagnose en overdracht<ChevronRight /></summary><OperationsCenter state={state} action={action} /></details> : null}</> : null}
        {page === 'security' ? <SecurityPage state={state} action={action} /> : null}
        {page === 'catalog' ? <CatalogPage state={state} action={action} /> : null}
        {page === 'runtime' ? <><ControlRuntime state={state} action={action} /><RuntimeExtras state={state} action={action} /></> : null}
        {page === 'inspector' ? <><DebugInbox state={state} action={action} />{interfaceMode === 'advanced' ? <details className="advancedDrawer"><summary><Activity />Technische inspector en resources<ChevronRight /></summary><InspectorPage state={state} action={action} /></details> : null}</> : null}
        {page === 'logs' ? <ControlLogs state={state} action={action} /> : null}
        {page === 'settings' ? <SettingsCenter state={state} action={action} /> : null}
      </main>
      {projectModal ? <ProjectPopup state={state} action={action} close={() => setProjectModal(false)} /> : null}
      {palette ? <CommandPalette state={state} navigate={next => {setPage(next); setPalette(false);}} action={action} openProject={() => {setProjectModal(true); setPalette(false);}} close={() => setPalette(false)} /> : null}
      <UpdatePopup update={state.update} action={action} /><FirstRunPopup state={state} action={action} />
      {notice ? <div className={`actionToast ${notice.tone}`} role={notice.tone === 'error' ? 'alert' : 'status'} aria-live="polite"><div className="actionToastIcon"><NoticeIcon /></div><div><b>{notice.title}</b><p>{notice.message}</p></div><button aria-label="Melding sluiten" onClick={() => setNotice(null)}><X /></button><span className="actionToastTimer" /></div> : null}
    </div>
  );
}

function CommandPalette({state, navigate, action, openProject, close}: {state: AppState; navigate: (page: Page) => void; action: StateAction; openProject: () => void; close: () => void}) {
  const [query, setQuery] = useState(''); const needle = query.trim().toLowerCase(); const language = state.settings.language;
  const pages = navigation.filter(item => !needle || translateText(item.label, language).toLowerCase().includes(needle));
  const commands = [
    {id: 'all-start', label: 'Alle services starten', icon: Radio, run: () => action(() => window.localdeck.toggleAll(true))},
    {id: 'all-stop', label: 'Alle services stoppen', icon: X, run: () => action(() => window.localdeck.toggleAll(false))},
    {id: 'ports', label: 'Port Autopilot 2 uitvoeren', icon: Network, run: () => action(() => window.localdeck.inspectAllPorts())},
    {id: 'new-project', label: 'Nieuw project aanmaken', icon: Plus, run: async () => {openProject(); return true;}},
    {id: 'fix', label: 'LocalDeck Fix openen', icon: Stethoscope, run: async () => {navigate('doctor'); return true;}},
    {id: 'web', label: 'Webbeheer openen', icon: Globe2, run: () => action(() => window.localdeck.openTool('webcontrol'))},
    {id: 'website', label: 'LocalDeck.nl openen', icon: Globe2, run: () => action(() => window.localdeck.openWebsite())},
  ].filter(item => !needle || translateText(item.label, language).toLowerCase().includes(needle));
  return <div className="backdrop commandBackdrop" onMouseDown={event => event.target === event.currentTarget && close()}><section className="commandPalette" role="dialog" aria-modal="true" aria-label="Opdrachten zoeken"><div className="commandSearch"><Command /><input autoFocus value={query} onChange={event => setQuery(event.target.value)} placeholder="Zoek pagina of actie…" /><kbd>ESC</kbd></div><div className="commandResults"><small>PAGINA'S</small>{pages.map(item => <button key={item.id} onClick={() => navigate(item.id)}><item.icon /><span>{translateText(item.label, language)}</span><ChevronRight /></button>)}<small>SNELLE ACTIES</small>{commands.map(item => <button key={item.id} onClick={() => {void item.run(); close();}}><item.icon /><span>{translateText(item.label, language)}</span><ChevronRight /></button>)}{!pages.length && !commands.length ? <p>Geen opdrachten gevonden.</p> : null}</div><footer><span>{state.projects.length} projecten · {state.services.filter(item => item.status === 'running').length} services actief</span><span><kbd>Ctrl</kbd> <kbd>K</kbd></span></footer></section></div>;
}

function ProjectPopup({state, action, close}: {state: AppState; action: StateAction; close: () => void}) {
  const [kind, setKind] = useState<CreationKind>('new'); const [name, setName] = useState('');
  const [folder, setFolder] = useState(state.settings.projectsDirectory); const [root, setRoot] = useState('');
  const [domain, setDomain] = useState(''); const [php, setPhp] = useState(state.settings.defaultPhpVersion);
  const [template, setTemplate] = useState<ProjectCreationTemplate>('php'); const [repository, setRepository] = useState('');
  const [advanced, setAdvanced] = useState(false);
  const slug = useMemo(() => name.toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g, '').replace(/[^a-z0-9]+/g, '-').replace(/^-|-$/g, ''), [name]);
  useEffect(() => {if (name) setDomain(`${slug}.${state.settings.localDomainSuffix}`);}, [slug, state.settings.localDomainSuffix]);
  const chooseFolder = async () => {const selected = await window.localdeck.chooseDirectory(); if (!selected) return; setFolder(selected); if (kind === 'existing') {setRoot(selected); if (!name) setName(selected.split(/[\\/]/).filter(Boolean).at(-1) ?? 'Mijn website');}};
  const canSave = kind === 'git' ? Boolean(repository.trim() && folder) : Boolean(name && folder && domain);
  const save = async () => {
    if (!canSave) return;
    const saved = await action(async () => {
      let next: AppState;
      if (kind === 'git') next = await window.localdeck.cloneProject(repository.trim(), folder, name || undefined);
      else if (kind === 'existing') next = await window.localdeck.addProject({name, path: folder, documentRoot: root || folder, domain, phpVersion: php, secure: true, template: 'custom'});
      else next = await window.localdeck.scaffoldProject({name, parentDir: folder, domain, phpVersion: php, template});
      if (next.services.find(service => service.id === 'mysql')?.status !== 'running') next = await window.localdeck.toggleService('mysql');
      const project = [...next.projects].reverse().find(item => kind === 'git' ? item.repositoryUrl === repository.trim() || item.name === name : item.name === name && item.domain === domain);
      return project ? window.localdeck.bootstrapProject(project.id) : next;
    }, {pending: {title: 'Project wordt ingericht', message: 'Map, runtime, domein, HTTPS, databaseprofiel en IDE-instellingen worden voorbereid.', tone: 'info'}, success: {title: 'Project is klaar', message: 'De volledige lokale ontwikkelomgeving is aangemaakt en gecontroleerd.'}});
    if (saved) close();
  };
  return <div className="backdrop" onMouseDown={event => event.target === event.currentTarget && close()}><section className="modal projectPopup smartProjectPopup" role="dialog" aria-modal="true" aria-label="Project toevoegen"><div className="modalHead"><div><span className="eyebrow"><Sparkles />SLIMME PROJECTWIZARD</span><h2>Wat wil je maken?</h2><p>LocalDeck vult de technische instellingen automatisch in.</p></div><button className="iconButton" aria-label="Sluiten" onClick={close}><X /></button></div><div className="creationKinds"><button type="button" className={kind === 'new' ? 'active' : ''} onClick={() => setKind('new')}><Plus /><span><b>Nieuwe website</b><small>PHP of populair framework</small></span></button><button type="button" className={kind === 'existing' ? 'active' : ''} onClick={() => setKind('existing')}><FolderInput /><span><b>Bestaande map</b><small>Laat LocalDeck hem herkennen</small></span></button><button type="button" className={kind === 'git' ? 'active' : ''} onClick={() => setKind('git')}><GitFork /><span><b>Vanuit Git</b><small>Klonen en automatisch inrichten</small></span></button></div>{kind === 'new' ? <label>Type website<select value={template} onChange={event => setTemplate(event.target.value as ProjectCreationTemplate)}><option value="php">PHP-website (volledig offline)</option><option value="wordpress">WordPress</option><option value="laravel">Laravel</option><option value="symfony">Symfony</option><option value="drupal">Drupal</option></select></label> : null}{kind === 'git' ? <label>Git-adres<input autoFocus value={repository} onChange={event => setRepository(event.target.value)} placeholder="https://github.com/team/project.git" /></label> : <label>Projectnaam<input autoFocus value={name} onChange={event => setName(event.target.value)} placeholder="Mijn project" /></label>}{kind === 'git' ? <label>Projectnaam <small>(optioneel)</small><input value={name} onChange={event => setName(event.target.value)} placeholder="Wordt uit het Git-adres gehaald" /></label> : null}<label>{kind === 'existing' ? 'Bestaande projectmap' : 'Websites-map'}<div className="inputAction"><input value={folder} onChange={event => setFolder(event.target.value)} placeholder={state.settings.projectsDirectory} /><button type="button" onClick={() => void chooseFolder()}><FolderOpen />Kiezen</button></div></label><button className="advancedToggle" type="button" aria-expanded={advanced} onClick={() => setAdvanced(value => !value)}><Settings />Geavanceerde instellingen<ChevronRight /></button>{advanced && kind !== 'git' ? <div className="advancedProjectFields">{kind === 'existing' ? <label>Document root<input value={root} onChange={event => setRoot(event.target.value)} placeholder={folder || 'Projectmap'} /></label> : null}<div className="formRow"><label>Lokaal domein<input value={domain} onChange={event => setDomain(event.target.value)} placeholder={`project.${state.settings.localDomainSuffix}`} /><small>.localhost werkt zonder beheerderstoestemming.</small></label><label>PHP-versie<select value={php} onChange={event => setPhp(event.target.value)}>{state.phpVersions.map(version => <option key={version.version}>{version.version}</option>)}</select></label></div></div> : null}<div className="wizardPromise"><ShieldCheck /><div><b>Automatisch startklaar</b><p>LocalDeck regelt de juiste PHP-versie, lokale URL, HTTPS, databaseprofiel, foutregistratie en IDE-integratie. Je hoeft niets te activeren of in te loggen.</p></div></div><div className="modalFoot"><button className="ghost" onClick={close}>Annuleren</button><button className="primary" disabled={!canSave} onClick={() => void save()}><Sparkles />Project maken en inrichten</button></div></section></div>;
}
