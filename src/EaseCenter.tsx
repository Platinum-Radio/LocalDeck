import {useMemo, useState} from 'react';
import {
  Activity,
  Archive,
  Bug,
  CheckCircle2,
  ChevronRight,
  CircleAlert,
  Clipboard,
  Code2,
  Database,
  ExternalLink,
  FolderOpen,
  GitBranch,
  Globe2,
  HardDrive,
  HeartPulse,
  Mail,
  Network,
  Play,
  RefreshCw,
  Settings2,
  ShieldCheck,
  Sparkles,
  Square,
  Terminal,
  Wrench,
} from 'lucide-react';
import type {LucideIcon} from 'lucide-react';
import type {StateAction} from './ControlCenter';

interface EaseProps {
  state: AppState;
  action: StateAction;
}

interface ProjectWorkspaceProps extends EaseProps {
  navigate: (page: EasePage) => void;
  openProject: () => void;
}

export type EasePage = 'dashboard' | 'projects' | 'studio' | 'services' | 'automation' | 'actions' | 'quality' | 'api' | 'databases' | 'snapshots' | 'tasks' | 'mail' | 'doctor' | 'security' | 'catalog' | 'runtime' | 'inspector' | 'logs' | 'settings';

function ProjectTool({icon: Icon, title, detail, actionLabel, onClick, tone = ''}: {
  icon: LucideIcon;
  title: string;
  detail: string;
  actionLabel: string;
  onClick: () => void;
  tone?: string;
}) {
  return (
    <article className={`projectTool ${tone}`}>
      <span><Icon /></span>
      <div><h3>{title}</h3><p>{detail}</p></div>
      <button type="button" onClick={onClick}>{actionLabel}<ChevronRight /></button>
    </article>
  );
}

export function ProjectWorkspace({state, action, navigate, openProject}: ProjectWorkspaceProps) {
  const [selectedId, setSelectedId] = useState(state.projects[0]?.id ?? '');
  const [branch, setBranch] = useState('test/nieuwe-versie');
  const [duration, setDuration] = useState(60);
  const selected = state.projects.find(project => project.id === selectedId) ?? state.projects[0];
  const projectId = selected?.id ?? '';
  const health = state.projectHealth.find(item => item.projectId === projectId);
  const snapshots = state.snapshots.filter(item => item.projectId === projectId);
  const mailCount = state.mailAddresses.filter(item => item.projectId === projectId).length;
  const branchCopies = state.branchEnvironments.filter(item => item.projectId === projectId);
  const share = state.shares.find(item => item.projectId === projectId && item.status === 'active');
  const databaseName = selected?.profile?.databaseName;
  const database = state.databases.find(item => item.name === databaseName);

  const open = (kind: 'site' | 'folder' | 'terminal' | 'code' | 'developer') => {
    if (!selected) return;
    void action(async () => {
      await window.localdeck.openProject(selected.id, kind);
      return window.localdeck.getState();
    }, kind === 'site' ? {
      pending: {title: 'Project voorbereiden', message: 'Services, domein en HTTPS worden gecontroleerd.', tone: 'info'},
      success: {title: 'Project geopend', message: `${selected.name} is gereed in je browser.`},
    } : undefined);
  };

  const workOnProject = () => {
    if (!selected) return;
    void action(async () => {
      await window.localdeck.runProjectDoctor(selected.id);
      await window.localdeck.openProject(selected.id, 'site');
      return window.localdeck.getState();
    }, {
      pending: {title: 'Werkplek voorbereiden', message: `LocalDeck maakt ${selected.name} startklaar.`, tone: 'info'},
      success: {title: 'Alles staat klaar', message: `${selected.name} draait met de juiste services, PHP-versie en HTTPS.`},
    });
  };

  if (!selected) {
    return (
      <section className="projectWorkspaceEmpty">
        <div><Sparkles /></div>
        <span className="eyebrow">PROJECTWERKPLEK</span>
        <h2>Begin met je eerste lokale website</h2>
        <p>LocalDeck maakt de map, het domein, HTTPS en de ontwikkelomgeving automatisch voor je klaar.</p>
        <button className="primary big" type="button" onClick={openProject}><Play />Nieuw project maken</button>
      </section>
    );
  }

  return (
    <div className="projectWorkspace">
      <section className="projectFocus">
        <div className="projectFocusTop">
          <div>
            <span className="eyebrow"><Sparkles />PROJECTWERKPLEK</span>
            <label className="projectPicker">
              <span>Actief project</span>
              <select value={selected.id} onChange={event => setSelectedId(event.target.value)}>
                {state.projects.map(project => <option key={project.id} value={project.id}>{project.name}</option>)}
              </select>
            </label>
          </div>
          <div className={`projectScore ${health?.status ?? 'warning'}`}>
            <HeartPulse />
            <span><b>{health?.score ?? '—'}</b><small>gezondheid</small></span>
          </div>
        </div>
        <h2>{selected.name}</h2>
        <p>{selected.domain} · PHP {selected.phpVersion} · {selected.secure ? 'automatische HTTPS' : 'HTTP'}</p>
        <div className="projectPrimaryActions">
          <button className="primary big" type="button" onClick={workOnProject}><Play />Werk aan project</button>
          <button className="ghost" type="button" onClick={() => open('code')}><Code2 />Code</button>
          <button className="ghost" type="button" onClick={() => open('folder')}><FolderOpen />Map</button>
          <button className="ghost" type="button" onClick={() => open('terminal')}><Terminal />Terminal</button>
        </div>
        <div className="projectFacts">
          <span><Globe2 /><b>{selected.certificate?.status === 'active' ? 'HTTPS actief' : 'HTTPS wordt voorbereid'}</b></span>
          <span><Database /><b>{databaseName || 'Database nog niet gekoppeld'}</b></span>
          <span><Archive /><b>{snapshots.length} herstelpunten</b></span>
          <span><Mail /><b>{mailCount} testadressen</b></span>
        </div>
      </section>

      <section className="projectToolGrid" aria-label="Projectgereedschap">
        <ProjectTool icon={Globe2} title="Website & runtime" detail={`${selected.domain} · PHP ${selected.phpVersion}`} actionLabel="Open website" onClick={() => open('site')} />
        <ProjectTool icon={Database} title="Database" detail={database ? `${database.name} · ${database.tables} tabellen · ${database.size}` : databaseName || 'Aanmaken en beheren'} actionLabel="phpMyAdmin" onClick={() => void action(() => window.localdeck.openTool('phpmyadmin'))} />
        <ProjectTool icon={Mail} title="Lokale e-mail" detail={`${mailCount} adressen · alle mail blijft lokaal`} actionLabel="Mailbeheer" onClick={() => navigate('mail')} />
        <ProjectTool icon={Archive} title="Veilig werken" detail={`${snapshots.length} snapshots voor dit project`} actionLabel="Herstelpunt maken" onClick={() => void action(() => window.localdeck.createSnapshot(selected.id, 'Snel herstelpunt vanuit projectwerkplek'), {success: {title: 'Herstelpunt gemaakt', message: `${selected.name} kan veilig worden teruggezet.`}})} />
        <ProjectTool icon={Bug} title="Ontwikkelen & debuggen" detail="Requests, PHP, SQL en taken in één inbox" actionLabel="Debug Inbox" onClick={() => navigate('inspector')} />
        <ProjectTool icon={Wrench} title="Controleren & herstellen" detail={health ? `${health.checks.filter(check => check.status !== 'ok').length} aandachtspunten` : 'Nog niet gecontroleerd'} actionLabel="LocalDeck Fix" onClick={() => navigate('doctor')} tone={health?.status === 'error' ? 'danger' : ''} />
      </section>

      <section className="projectQuickLab">
        <article>
          <header><GitBranch /><div><h3>Testkopie</h3><p>Eigen map, poorten en gekloonde database.</p></div></header>
          <div className="projectInlineAction">
            <input aria-label="Naam van testbranch" value={branch} onChange={event => setBranch(event.target.value)} placeholder="test/nieuwe-versie" />
            <button className="ghost" type="button" disabled={!branch.trim()} onClick={() => void action(() => window.localdeck.createBranchEnvironment(selected.id, branch.trim()), {pending: {title: 'Testkopie maken', message: 'Map, poorten en database worden geïsoleerd.', tone: 'info'}, success: {title: 'Testkopie gereed', message: `${branch} heeft een eigen lokale omgeving.`}})}><Sparkles />Maak testkopie</button>
          </div>
          {branchCopies.length ? <small>{branchCopies.length} actieve testkopieën · beheer ze in Project Studio.</small> : <small>Nog geen testkopieën voor dit project.</small>}
        </article>
        <article>
          <header><Network /><div><h3>Tijdelijk delen</h3><p>Accountloze LAN-link met token en vervaldatum.</p></div></header>
          {share ? (
            <div className="activeShare">
              <code>{share.url}</code>
              <button className="iconButton" title="Link kopiëren" onClick={() => void action(() => window.localdeck.copyShare(share.id))}><Clipboard /></button>
              <button className="iconButton" title="Delen stoppen" onClick={() => void action(() => window.localdeck.stopShare(share.id))}><Square /></button>
            </div>
          ) : (
            <div className="projectInlineAction compact">
              <select aria-label="Geldigheid tijdelijke link" value={duration} onChange={event => setDuration(Number(event.target.value))}>
                <option value={15}>15 minuten</option><option value={60}>1 uur</option><option value={120}>2 uur</option>
              </select>
              <button className="ghost" type="button" disabled={!state.settings.lanSharingEnabled} onClick={() => void action(() => window.localdeck.startShare(selected.id, duration), {success: {title: 'Deellink gereed', message: 'De tijdelijke LAN-link en QR-code zijn aangemaakt.'}})}><Network />Link maken</button>
            </div>
          )}
          <small>{state.settings.lanSharingEnabled ? 'Alleen bereikbaar binnen je lokale netwerk.' : 'Schakel LAN-delen bewust in bij Instellingen.'}</small>
        </article>
      </section>
    </div>
  );
}

export function LocalDeckFix({state, action}: EaseProps) {
  const unhealthyProjects = state.projectHealth.filter(item => item.status !== 'ok');
  const badDiagnostics = state.diagnostics.filter(item => item.status !== 'ok');
  const conflicts = state.portConflicts.filter(item => item.status === 'occupied');
  const findings = state.securityFindings.filter(item => !item.resolved && item.severity !== 'info');
  const issueCount = unhealthyProjects.length + badDiagnostics.length + conflicts.length + findings.length;

  const runChecks = () => void action(async () => {
    await window.localdeck.runDiagnostics();
    await window.localdeck.runProjectDoctor();
    await window.localdeck.scanSecurity();
    return window.localdeck.inspectAllPorts();
  }, {
    pending: {title: 'Volledige controle gestart', message: 'Runtime, projecten, beveiliging en poorten worden bekeken.', tone: 'info'},
    success: next => {
      const remaining = next.diagnostics.filter(item => item.status !== 'ok').length + next.projectHealth.filter(item => item.status !== 'ok').length;
      return {title: 'Controle afgerond', message: remaining ? `${remaining} onderdelen vragen nog aandacht.` : 'LocalDeck en alle projecten zijn gezond.', tone: remaining ? 'warning' : 'success'};
    },
  });

  return (
    <div className="fixCenter">
      <section className={`fixHero ${issueCount ? 'attention' : 'healthy'}`}>
        <div className="fixHeroIcon">{issueCount ? <CircleAlert /> : <CheckCircle2 />}</div>
        <div>
          <span className="eyebrow">LOCALDECK FIX</span>
          <h2>{issueCount ? `${issueCount} punten kunnen beter` : 'Je lokale omgeving is gezond'}</h2>
          <p>Een begrijpelijke controle van runtime, services, poorten, domeinen, HTTPS, projecten en beveiliging.</p>
        </div>
        <div className="fixActions">
          <button className="ghost" type="button" onClick={runChecks}><RefreshCw />Alles controleren</button>
          <button className="primary" type="button" onClick={() => void action(() => window.localdeck.repairAll(), {pending: {title: 'Veilig herstel gestart', message: 'LocalDeck maakt waar nodig herstelpunten en past veilige oplossingen toe.', tone: 'info'}, success: {title: 'Herstel afgerond', message: 'De omgeving is opnieuw gecontroleerd en veilig bijgewerkt.'}})}><Sparkles />Alles veilig herstellen</button>
        </div>
      </section>

      <section className="fixSummaryGrid">
        <article className={badDiagnostics.length ? 'warning' : 'ok'}><HardDrive /><span><b>LocalDeck runtime</b><small>{badDiagnostics.length ? `${badDiagnostics.length} aandachtspunten` : 'Bestanden en configuratie in orde'}</small></span></article>
        <article className={conflicts.length ? 'warning' : 'ok'}><Network /><span><b>Services & poorten</b><small>{conflicts.length ? `${conflicts.length} externe conflicten` : 'Geen blokkades gevonden'}</small></span></article>
        <article className={unhealthyProjects.length ? 'warning' : 'ok'}><Globe2 /><span><b>Projecten & HTTPS</b><small>{unhealthyProjects.length ? `${unhealthyProjects.length} projecten vragen aandacht` : 'Alle projecten bereikbaar'}</small></span></article>
        <article className={findings.length ? 'warning' : 'ok'}><ShieldCheck /><span><b>Beveiliging</b><small>{findings.length ? `${findings.length} bevindingen` : 'Geen open waarschuwingen'}</small></span></article>
      </section>

      <section className="panel fixProjectList">
        <div className="panelHead"><div><h3>Projectgezondheid</h3><p>Alleen concrete problemen en veilige herstelacties.</p></div><HeartPulse /></div>
        {state.projectHealth.map(report => {
          const issues = report.checks.filter(check => check.status !== 'ok');
          return (
            <article key={report.projectId} className={report.status}>
              <span className="fixScore">{report.score}</span>
              <div><b>{report.projectName}</b><small>{issues.length ? issues.map(item => item.label).join(' · ') : `${report.domain} is gereed`}</small></div>
              {issues.length ? <button className="ghost" type="button" onClick={() => void action(() => window.localdeck.repairProject(report.projectId), {pending: {title: `${report.projectName} herstellen`, message: 'LocalDeck controleert iedere wijziging voordat deze wordt toegepast.', tone: 'info'}, success: {title: 'Project hersteld', message: `${report.projectName} is opnieuw ingericht en gecontroleerd.`}})}><Wrench />Veilig herstellen</button> : <span className="fixReady"><CheckCircle2 />Gereed</span>}
            </article>
          );
        })}
        {!state.projectHealth.length ? <p className="emptyInline">Klik op Alles controleren om projecten te beoordelen.</p> : null}
      </section>
    </div>
  );
}

type InboxKind = 'all' | 'request' | 'php' | 'sql' | 'queue' | 'task' | 'security' | 'log' | 'webhook' | 'operation';
type InboxSeverity = 'info' | 'warning' | 'error';
interface InboxEntry {
  id: string;
  at: string;
  projectId?: string;
  kind: Exclude<InboxKind, 'all'>;
  severity: InboxSeverity;
  title: string;
  detail: string;
}

function inboxKindLabel(kind: InboxEntry['kind']) {
  const labels: Record<InboxEntry['kind'], string> = {request: 'Request', php: 'PHP', sql: 'SQL', queue: 'Queue', task: 'Taak', security: 'Beveiliging', log: 'Log', webhook: 'Webhook', operation: 'Actie'};
  return labels[kind];
}

export function DebugInbox({state, action}: EaseProps) {
  const [projectId, setProjectId] = useState('all');
  const [kind, setKind] = useState<InboxKind>('all');
  const [onlyProblems, setOnlyProblems] = useState(false);
  const entries = useMemo<InboxEntry[]>(() => {
    const inspector: InboxEntry[] = state.inspectorEvents.map(item => ({id: `inspector-${item.id}`, at: item.at, projectId: item.projectId, kind: item.kind, severity: item.severity, title: item.title, detail: item.detail}));
    const logs: InboxEntry[] = state.logs.map(item => ({id: `log-${item.id}`, at: item.at, kind: 'log', severity: item.level === 'error' ? 'error' : item.level === 'warning' ? 'warning' : 'info', title: item.source, detail: item.message}));
    const webhooks: InboxEntry[] = state.webhookEvents.map(item => ({id: `webhook-${item.id}`, at: item.receivedAt, kind: 'webhook', severity: 'info', title: `${item.method} webhook`, detail: item.body || 'Lege requestbody'}));
    const operations: InboxEntry[] = state.actionOperations.filter(item => item.status === 'failed' || item.status === 'running').map(item => ({id: `operation-${item.id}`, at: item.updatedAt, projectId: item.projectId, kind: 'operation', severity: item.status === 'failed' ? 'error' : 'info', title: item.title, detail: item.message}));
    return [...inspector, ...logs, ...webhooks, ...operations].sort((left, right) => Date.parse(right.at) - Date.parse(left.at));
  }, [state.actionOperations, state.inspectorEvents, state.logs, state.webhookEvents]);
  const filtered = entries.filter(entry => (projectId === 'all' || entry.projectId === projectId) && (kind === 'all' || entry.kind === kind) && (!onlyProblems || entry.severity !== 'info'));

  return (
    <div className="debugInbox">
      <section className="debugHero">
        <div><span className="eyebrow"><Activity />DEBUG INBOX</span><h2>Alles wat aandacht vraagt, in één tijdlijn</h2><p>Requests, PHP, SQL, queues, taken, webhooks en servicelogs zonder tussen schermen te wisselen.</p></div>
        <div className="debugCounters"><span><b>{entries.length}</b><small>gebeurtenissen</small></span><span className="warning"><b>{entries.filter(item => item.severity !== 'info').length}</b><small>aandacht</small></span></div>
      </section>
      <section className="debugToolbar">
        <select aria-label="Filter op project" value={projectId} onChange={event => setProjectId(event.target.value)}><option value="all">Alle projecten</option>{state.projects.map(project => <option key={project.id} value={project.id}>{project.name}</option>)}</select>
        <select aria-label="Filter op type" value={kind} onChange={event => setKind(event.target.value as InboxKind)}><option value="all">Alle typen</option><option value="request">Requests</option><option value="php">PHP</option><option value="sql">SQL</option><option value="queue">Queues</option><option value="task">Taken</option><option value="security">Beveiliging</option><option value="webhook">Webhooks</option><option value="operation">Acties</option><option value="log">Servicelogs</option></select>
        <label><input type="checkbox" checked={onlyProblems} onChange={event => setOnlyProblems(event.target.checked)} />Alleen problemen</label>
        <button className="ghost" type="button" onClick={() => void action(async () => {await window.localdeck.refreshInspector(); return window.localdeck.collectLogs();}, {success: {title: 'Debug Inbox vernieuwd', message: 'Nieuwe events en servicelogs zijn samengevoegd.'}})}><RefreshCw />Vernieuwen</button>
      </section>
      <section className="debugTimeline">
        {filtered.map(entry => (
          <article key={entry.id} className={entry.severity}>
            <i />
            <time>{new Date(entry.at).toLocaleTimeString(state.settings.language === 'nl' ? 'nl-NL' : 'en-US', {hour: '2-digit', minute: '2-digit', second: '2-digit'})}</time>
            <span className="debugKind">{inboxKindLabel(entry.kind)}</span>
            <div><b>{entry.title}</b><p>{entry.detail}</p></div>
            <span className={`debugSeverity ${entry.severity}`}>{entry.severity === 'error' ? 'Fout' : entry.severity === 'warning' ? 'Aandacht' : 'Info'}</span>
          </article>
        ))}
        {!filtered.length ? <div className="debugEmpty"><CheckCircle2 /><h3>Geen gebeurtenissen voor dit filter</h3><p>Vernieuw de inbox of kies een ruimer filter.</p></div> : null}
      </section>
    </div>
  );
}

export function InterfaceModeCard({state, action}: EaseProps) {
  return (
    <div className="interfaceModeChoice" role="group" aria-label="Interfacemodus">
      <button type="button" className={(state.settings.interfaceMode ?? 'simple') === 'simple' ? 'selected' : ''} onClick={() => void action(() => window.localdeck.updateSettings({interfaceMode: 'simple'}))}>
        <Sparkles /><span><b>Eenvoudig</b><small>Dagelijkse functies en duidelijke taal.</small></span>
      </button>
      <button type="button" className={state.settings.interfaceMode === 'advanced' ? 'selected' : ''} onClick={() => void action(() => window.localdeck.updateSettings({interfaceMode: 'advanced'}))}>
        <Settings2 /><span><b>Geavanceerd</b><small>Alle ontwikkel-, kwaliteits- en automatiseringstools.</small></span>
      </button>
    </div>
  );
}
