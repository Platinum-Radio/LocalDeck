import {useState} from 'react';
import {
  ArchiveRestore,
  Box,
  ExternalLink,
  Globe2,
  HardDrive,
  Languages,
  Network,
  RefreshCw,
  RotateCcw,
  Save,
  Server,
  Settings,
  ShieldCheck,
} from 'lucide-react';
import type {LucideIcon} from 'lucide-react';
import type {StateAction} from './ControlCenter';
import {InterfaceModeCard} from './EaseCenter';

type SettingsTab = 'general' | 'network' | 'updates' | 'privacy';

interface SettingsTabDefinition {
  id: SettingsTab;
  label: string;
  description: string;
  icon: LucideIcon;
}

const settingsTabs: SettingsTabDefinition[] = [
  {id: 'general', label: 'Algemeen', description: 'Taal en programmagedrag', icon: Settings},
  {id: 'network', label: 'Netwerk & domeinen', description: 'Lokale toegang en integraties', icon: Network},
  {id: 'updates', label: 'Updates & back-ups', description: 'Versies, herstel en vertrouwen', icon: RefreshCw},
  {id: 'privacy', label: 'Privacy & beveiliging', description: 'Lokale gegevens en diagnose', icon: ShieldCheck},
];

interface SettingsProps {
  state: AppState;
  action: StateAction;
}

interface ToggleProps {
  label: string;
  description: string;
  checked: boolean;
  onChange: (checked: boolean) => void;
  disabled?: boolean;
}

function CompactToggle({label, description, checked, onChange, disabled = false}: ToggleProps) {
  return (
    <label className={`compactSettingToggle${disabled ? ' disabled' : ''}`}>
      <span>
        <b>{label}</b>
        <small>{description}</small>
      </span>
      <input
        type="checkbox"
        checked={checked}
        disabled={disabled}
        aria-label={label}
        onChange={event => onChange(event.target.checked)}
      />
      <i aria-hidden="true" />
    </label>
  );
}

function SettingsCardHeader({icon: Icon, title, description}: {icon: LucideIcon; title: string; description: string}) {
  return (
    <header className="settingsCardHeader">
      <span><Icon /></span>
      <div><h3>{title}</h3><p>{description}</p></div>
    </header>
  );
}

export function SettingsCenter({state, action}: SettingsProps) {
  const [activeTab, setActiveTab] = useState<SettingsTab>('general');
  const [feed, setFeed] = useState(state.settings.updateFeedUrl);
  const [publisher, setPublisher] = useState(state.settings.trustedUpdatePublisher);
  const [packPublisher, setPackPublisher] = useState(state.settings.trustedPackPublisher);
  const activeDefinition = settingsTabs.find(tab => tab.id === activeTab) ?? settingsTabs[0];
  const openWebsite = () => action(
    () => window.localdeck.openWebsite(),
    {success: {title: 'Website geopend', message: 'LocalDeck.nl is geopend in je standaardbrowser.', tone: 'info'}},
  );

  return (
    <div className="settingsCenter">
      <section className="settingsWelcome">
        <div>
          <span className="eyebrow"><Settings />LOCALDECK INSTELLINGEN</span>
          <h2>Instellingen zonder eindeloos scrollen</h2>
          <p>Kies links een onderwerp. Alleen de instellingen die daarbij horen blijven in beeld.</p>
        </div>
        <button className="websiteCard" type="button" onClick={() => void openWebsite()}>
          <span><Globe2 /></span>
          <div><b>LocalDeck.nl</b><small>Website, documentatie en downloads</small></div>
          <ExternalLink />
        </button>
      </section>

      <div className="settingsWorkspace">
        <nav className="settingsNavigation" aria-label="Instellingencategorieën" role="tablist">
          {settingsTabs.map(tab => {
            const Icon = tab.icon;
            const selected = tab.id === activeTab;
            return (
              <button
                key={tab.id}
                id={`settings-tab-${tab.id}`}
                type="button"
                role="tab"
                aria-selected={selected}
                aria-controls={`settings-panel-${tab.id}`}
                className={selected ? 'active' : ''}
                onClick={() => setActiveTab(tab.id)}
              >
                <Icon />
                <span><b>{tab.label}</b><small>{tab.description}</small></span>
              </button>
            );
          })}
          <div className="settingsNavigationHelp">
            <Globe2 />
            <span><b>Hulp nodig?</b><small>Handleidingen en downloads staan op LocalDeck.nl.</small></span>
            <button type="button" onClick={() => void openWebsite()}>Website openen <ExternalLink /></button>
          </div>
        </nav>

        <section
          className="settingsTabPanel"
          id={`settings-panel-${activeTab}`}
          role="tabpanel"
          aria-labelledby={`settings-tab-${activeTab}`}
        >
          <header className="settingsTabHeader">
            <span><activeDefinition.icon /></span>
            <div><h2>{activeDefinition.label}</h2><p>{activeDefinition.description}</p></div>
          </header>

          {activeTab === 'general' ? (
            <div className="settingsCardGrid">
              <article className="settingsCard settingsCardWide">
                <SettingsCardHeader icon={Settings} title="Interfacemodus" description="Kies hoeveel gereedschap je dagelijks in beeld wilt" />
                <InterfaceModeCard state={state} action={action} />
              </article>

              <article className="settingsCard">
                <SettingsCardHeader icon={Languages} title="Taal / Language" description="Direct toepassen in alle LocalDeck-onderdelen" />
                <label className="settingsField">
                  <span><b>Interfacetaal</b><small>Ook voor Webbeheer, Mailbeheer en nieuwe startpagina's.</small></span>
                  <select aria-label="Interfacetaal" value={state.settings.language} onChange={event => void action(() => window.localdeck.updateSettings({language: event.target.value as AppSettings['language']}))}>
                    <option value="nl">Nederlands</option>
                    <option value="en">English</option>
                  </select>
                </label>
              </article>

              <article className="settingsCard">
                <SettingsCardHeader icon={Server} title="Uitvoermodus" description="Bepaal wanneer de lokale services actief blijven" />
                <div className="compactModeChoice">
                  <button type="button" className={state.settings.runtimeMode === 'application' ? 'selected' : ''} onClick={() => void action(() => window.localdeck.setRuntimeMode('application'))}>
                    <Box /><span><b>App-modus</b><small>Services stoppen met LocalDeck.</small></span>
                  </button>
                  <button type="button" className={state.settings.runtimeMode === 'windows-service' ? 'selected' : ''} onClick={() => void action(() => window.localdeck.setRuntimeMode('windows-service'))}>
                    <Server /><span><b>Windows-services</b><small>Services blijven zelfstandig draaien.</small></span>
                  </button>
                </div>
              </article>

              <article className="settingsCard settingsCardWide compactToggleGrid">
                <div className={`trayBehaviorNotice ${state.settings.minimizeToTray ? 'active' : 'inactive'}`}>
                  <ShieldCheck />
                  <span>
                    <b>{state.settings.minimizeToTray ? 'Rode kruis: LocalDeck blijft actief' : 'Rode kruis: LocalDeck wordt afgesloten'}</b>
                    <small>{state.settings.minimizeToTray ? 'Na een klik op × vind je LocalDeck terug bij de verborgen pictogrammen van Windows. Dubbelklik daar op het LocalDeck-pictogram om het venster weer te openen.' : 'Schakel Minimaliseren naar systeemvak in als services en LocalDeck na een klik op × actief moeten blijven.'}</small>
                  </span>
                </div>
                <CompactToggle label="Start LocalDeck met Windows" description="Opent het dashboard na aanmelden." checked={state.settings.startWithWindows} onChange={checked => void action(() => window.localdeck.updateSettings({startWithWindows: checked}))} />
                <CompactToggle label="Minimaliseren naar systeemvak" description={state.settings.minimizeToTray ? 'Het rode kruis verbergt LocalDeck bij de Windows-pictogrammen.' : 'Het rode kruis sluit LocalDeck volledig af.'} checked={state.settings.minimizeToTray} onChange={checked => void action(() => window.localdeck.updateSettings({minimizeToTray: checked}))} />
                <CompactToggle label="Services automatisch herstellen" description="Maximaal drie herstelpogingen in app-modus." checked={state.settings.autoRestart} onChange={checked => void action(() => window.localdeck.updateSettings({autoRestart: checked}))} />
                <CompactToggle label="Services herstellen na crash" description="Herstart alleen eerder gewenste app-services." checked={state.settings.restoreServicesAfterCrash} onChange={checked => void action(() => window.localdeck.updateSettings({restoreServicesAfterCrash: checked}))} />
                <CompactToggle label="Eco-modus" description="Stopt ongebruikte app-services automatisch." checked={state.settings.resourceSaver} onChange={checked => void action(() => window.localdeck.updateSettings({resourceSaver: checked}))} />
                <label className="settingsField compactField">
                  <span><b>Stop na inactiviteit</b><small>Alleen van toepassing in app-modus.</small></span>
                  <select disabled={!state.settings.resourceSaver} value={state.settings.idleStopMinutes} onChange={event => void action(() => window.localdeck.updateSettings({idleStopMinutes: Number(event.target.value)}))}>
                    <option value={0}>Nooit automatisch</option><option value={15}>15 minuten</option><option value={30}>30 minuten</option><option value={60}>1 uur</option><option value={120}>2 uur</option>
                  </select>
                </label>
              </article>
            </div>
          ) : null}

          {activeTab === 'network' ? (
            <div className="settingsCardGrid">
              <article className="settingsCard">
                <SettingsCardHeader icon={Globe2} title="Lokale domeinen" description="Veilige standaard voor nieuwe projecten" />
                <label className="settingsField stacked">
                  <span><b>Standaard domein</b><small>Bestaande projecten worden niet hernoemd.</small></span>
                  <select value={state.settings.localDomainSuffix} onChange={event => void action(() => window.localdeck.updateSettings({localDomainSuffix: event.target.value as AppSettings['localDomainSuffix']}))}>
                    <option value="localhost">.localhost — zonder UAC</option>
                    <option value="test">.test — hosts of lokale DNS</option>
                  </select>
                </label>
                <label className="settingsField stacked">
                  <span><b>DNS-modus</b><small>Lokale DNS vraagt eenmalig beheerderstoestemming.</small></span>
                  <select value={state.settings.dnsMode} onChange={event => void action(() => window.localdeck.updateSettings({dnsMode: event.target.value as AppSettings['dnsMode']}))}>
                    <option value="hosts">Windows hosts-bestand</option>
                    <option value="local-dns">LocalDeck DNS-service voor *.test</option>
                  </select>
                </label>
              </article>

              <article className="settingsCard compactToggleGrid">
                <SettingsCardHeader icon={Network} title="Lokale toegang" description="Beheer interfaces en lokale integraties" />
                <CompactToggle label="Webbeheer" description={`Alleen lokaal op 127.0.0.1:${state.settings.webControlPort}`} checked={state.settings.webControlEnabled} onChange={checked => void action(() => window.localdeck.updateSettings({webControlEnabled: checked}))} />
                <CompactToggle label="Tijdelijk delen via LAN" description="Maakt alleen lokaal een tijdelijke tokenlink." checked={state.settings.lanSharingEnabled} onChange={checked => void action(() => window.localdeck.updateSettings({lanSharingEnabled: checked}))} />
                <CompactToggle label="Lokale MCP-integratie" description="Geeft editors uitsluitend ingebouwde LocalDeck-tools." checked={state.settings.mcpEnabled} onChange={checked => void action(() => window.localdeck.updateSettings({mcpEnabled: checked}))} />
              </article>

              <article className="settingsCard settingsCardWide websiteSettingsCard">
                <div><span><Globe2 /></span><div><b>Officiële LocalDeck-website</b><small>Nieuws, documentatie, community en alle beschikbare versies.</small></div></div>
                <button className="ghost" type="button" onClick={() => void openWebsite()}><ExternalLink />Naar LocalDeck.nl</button>
              </article>
            </div>
          ) : null}

          {activeTab === 'updates' ? (
            <div className="settingsCardGrid">
              <article className="settingsCard">
                <SettingsCardHeader icon={RefreshCw} title="Updates" description="Releasekanaal en automatische controle" />
                <CompactToggle label="Automatisch updates controleren" description="Toont een melding zodra een nieuwe versie bestaat." checked={state.settings.autoUpdateCheck} onChange={checked => void action(() => window.localdeck.updateSettings({autoUpdateCheck: checked}))} />
                <label className="settingsField"><span><b>Updatekanaal</b><small>Stabiel of vroege bètaversies.</small></span><select value={state.settings.updateChannel} onChange={event => void action(() => window.localdeck.updateSettings({updateChannel: event.target.value as AppSettings['updateChannel']}))}><option value="stable">Stabiel</option><option value="beta">Bèta</option></select></label>
                <label className="settingsField stacked"><span><b>Updatefeed-URL</b><small>Machineleesbare Windows-updatefeed.</small></span><input value={feed} spellCheck={false} onChange={event => setFeed(event.target.value)} onBlur={() => void action(() => window.localdeck.updateSettings({updateFeedUrl: feed}))} /></label>
                <button className="ghost settingsActionButton" type="button" onClick={() => void action(() => window.localdeck.checkUpdates(true))}><RefreshCw />Nu controleren</button>
              </article>

              <article className="settingsCard">
                <SettingsCardHeader icon={ArchiveRestore} title="Back-ups" description="Schema en bewaartermijn per database" />
                <CompactToggle label="Automatische back-ups" description="Maakt back-ups volgens het gekozen schema." checked={state.settings.autoBackup} onChange={checked => void action(() => window.localdeck.updateSettings({autoBackup: checked}))} />
                <label className="settingsField"><span><b>Back-upschema</b><small>Moment waarop LocalDeck back-ups maakt.</small></span><select disabled={!state.settings.autoBackup} value={state.settings.backupSchedule} onChange={event => void action(() => window.localdeck.updateSettings({backupSchedule: event.target.value as AppSettings['backupSchedule']}))}><option value="off">Uit</option><option value="daily">Dagelijks</option><option value="weekly">Wekelijks</option></select></label>
                <label className="settingsField"><span><b>Bewaren per database</b><small>Oudere exemplaren worden automatisch opgeruimd.</small></span><select value={state.settings.backupRetention} onChange={event => void action(() => window.localdeck.updateSettings({backupRetention: Number(event.target.value)}))}>{[3, 7, 14, 30].map(number => <option key={number}>{number}</option>)}</select></label>
              </article>

              <article className="settingsCard settingsCardWide">
                <SettingsCardHeader icon={ShieldCheck} title="Updatevertrouwen" description="Hashes, Windows-handtekeningen en veilig terugdraaien" />
                <div className="compactToggleGrid">
                  <CompactToggle label="Atomische updates" description="Bewaart de laatst gezonde versie." checked={state.settings.atomicUpdates} onChange={checked => void action(() => window.localdeck.updateSettings({atomicUpdates: checked}))} />
                  <CompactToggle label="Alleen ondertekende updates" description="Controleert Authenticode na SHA-256." checked={state.settings.requireSignedUpdates} onChange={checked => void action(() => window.localdeck.updateSettings({requireSignedUpdates: checked}))} />
                </div>
                <div className="settingsPublisherGrid">
                  <label className="settingsField stacked"><span><b>Vertrouwde update-uitgever</b><small>Exacte certificaatnaam.</small></span><input value={publisher} onChange={event => setPublisher(event.target.value)} onBlur={() => void action(() => window.localdeck.updateSettings({trustedUpdatePublisher: publisher}))} /></label>
                  <label className="settingsField stacked"><span><b>Vertrouwde componentuitgever</b><small>Volledige SHA-1-thumbprint aanbevolen.</small></span><input value={packPublisher} spellCheck={false} onChange={event => setPackPublisher(event.target.value)} onBlur={() => void action(() => window.localdeck.updateSettings({trustedPackPublisher: packPublisher}))} /></label>
                </div>
                <div className="settingsButtonRow">
                  <button className="ghost" type="button" onClick={() => void action(() => window.localdeck.checkUpdates(true))}><RefreshCw />Feed vernieuwen</button>
                  <button className="ghost" type="button" disabled={!state.update.rollbackUrl} onClick={() => void action(() => window.localdeck.rollbackUpdate())}><RotateCcw />Rollbackinstaller downloaden</button>
                </div>
              </article>
            </div>
          ) : null}

          {activeTab === 'privacy' ? (
            <div className="settingsCardGrid">
              <article className="settingsCard settingsCardWide">
                <SettingsCardHeader icon={HardDrive} title="Lokale gegevens" description="Projecten en runtimegegevens blijven op deze computer" />
                <div className="settingsPath"><HardDrive /><span><b>Gegevensmap</b><small>{state.settings.dataDirectory}</small></span></div>
              </article>

              <article className="settingsCard compactToggleGrid">
                <SettingsCardHeader icon={ShieldCheck} title="Privacy" description="Zelf bepalen wat LocalDeck registreert" />
                <CompactToggle label="Anonieme diagnostiek" description="Standaard uit; deze versie verstuurt niets." checked={state.settings.telemetry} onChange={checked => void action(() => window.localdeck.updateSettings({telemetry: checked}))} />
                <CompactToggle label="Gecombineerde servicelogs" description="Voegt Apache, PHP en MySQL lokaal samen." checked={state.settings.combinedLogs} onChange={checked => void action(() => window.localdeck.updateSettings({combinedLogs: checked}))} />
              </article>

              <article className="settingsCard compactToggleGrid">
                <SettingsCardHeader icon={ShieldCheck} title="Beveiliging" description="Lokale controles en betrouwbare runtime" />
                <CompactToggle label="Beveiligingsscan bij starten" description="Voert uitsluitend lokale controles uit." checked={state.settings.securityScanOnStart} onChange={checked => void action(() => window.localdeck.updateSettings({securityScanOnStart: checked}))} />
                <CompactToggle label="Gebundelde runtime verplicht" description="Installaties gebruiken alleen gecontroleerde offline pakketten." checked disabled onChange={() => undefined} />
              </article>

              <article className="settingsCard settingsCardWide websiteSettingsCard">
                <div><span><Save /></span><div><b>Diagnoserapport</b><small>Exporteer een veilig lokaal rapport zonder geheime waarden.</small></div></div>
                <button className="ghost" type="button" onClick={() => void action(() => window.localdeck.exportDiagnostics())}><Save />Veilig diagnoserapport exporteren</button>
              </article>
            </div>
          ) : null}
        </section>
      </div>
    </div>
  );
}
