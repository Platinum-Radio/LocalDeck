import {ArrowRight, CalendarDays, CheckCircle2, Download, ExternalLink, FileKey2, History, RefreshCw, RotateCcw, ShieldCheck, Sparkles} from 'lucide-react';
import type {StateAction} from './ControlCenter';

const versionDate = (value: string | undefined, language: AppSettings['language']) => {
  if (!value) return language === 'en' ? 'Date unavailable' : 'Datum onbekend';
  try {return new Intl.DateTimeFormat(language === 'en' ? 'en-GB' : 'nl-NL', {day: 'numeric', month: 'long', year: 'numeric'}).format(new Date(value));}
  catch {return value;}
};

export function UpdateCenter({state, action}: {state: AppState; action: StateAction}) {
  const update = state.update;
  const language = state.settings.language;
  const english = language === 'en';
  const available = update.status === 'available';
  const checking = update.status === 'checking';
  const history = update.history ?? [];
  const title = english ? update.titleEn || update.title : update.title;
  const notes = english ? update.notesEn || update.notes : update.notes;
  const statusLabel = checking ? (english ? 'Checking…' : 'Controleren…') : available ? (english ? 'Update available' : 'Update beschikbaar') : update.status === 'error' ? (english ? 'Check failed' : 'Controle mislukt') : update.status === 'current' ? (english ? 'Up to date' : 'Actueel') : (english ? 'Not checked yet' : 'Nog niet gecontroleerd');

  return <div className="updateCenter">
    <section className={`updateHero ${available ? 'available' : ''}`}>
      <div className="updateHeroIcon">{available ? <Sparkles /> : <RefreshCw className={checking ? 'spin' : ''} />}</div>
      <div className="updateHeroCopy">
        <span className="eyebrow">{english ? 'LOCALDECK UPDATE CENTER' : 'LOCALDECK UPDATECENTRUM'}</span>
        <h2>{available ? title || `LocalDeck ${update.latestVersion}` : english ? `You are using LocalDeck ${update.currentVersion}` : `Je gebruikt LocalDeck ${update.currentVersion}`}</h2>
        <p>{available ? notes || (english ? 'A new LocalDeck version is ready.' : 'Er staat een nieuwe LocalDeck-versie klaar.') : update.status === 'error' ? update.error : english ? 'View versions, changes, verification, and recovery from one place.' : 'Bekijk versies, wijzigingen, controle en herstel vanuit één plek.'}</p>
        <div className="updateHeroActions">
          <button className="primary" disabled={checking} onClick={() => void action(() => window.localdeck.checkUpdates(true))}><RefreshCw className={checking ? 'spin' : ''} />{checking ? statusLabel : english ? 'Check for updates' : 'Controleren op updates'}</button>
          {available ? <button className="ghost" onClick={() => void action(() => window.localdeck.downloadUpdate())}><Download />{english ? 'Download safely' : 'Veilig downloaden'}</button> : null}
          <button className="ghost" onClick={() => void action(() => window.localdeck.openUpdatesWebsite())}><ExternalLink />{english ? 'Updates on LocalDeck.nl' : 'Updates op LocalDeck.nl'}</button>
        </div>
      </div>
      <div className="updateVersionJump"><span>v{update.currentVersion}</span><ArrowRight /><strong>v{available ? update.latestVersion : update.currentVersion}</strong><small className={update.status}>{statusLabel}</small></div>
    </section>

    <section className="updateFacts" aria-label={english ? 'Update status' : 'Updatestatus'}>
      <article><History /><span><small>{english ? 'Channel' : 'Kanaal'}</small><b>{state.settings.updateChannel === 'beta' ? (english ? 'Beta' : 'Bèta') : (english ? 'Stable' : 'Stabiel')}</b></span></article>
      <article><CalendarDays /><span><small>{english ? 'Last checked' : 'Laatst gecontroleerd'}</small><b>{update.checkedAt ? versionDate(update.checkedAt, language) : (english ? 'Not yet' : 'Nog niet')}</b></span></article>
      <article><FileKey2 /><span><small>SHA-256</small><b>{update.sha256 ? (english ? 'Published and verified on download' : 'Gepubliceerd en gecontroleerd bij download') : (english ? 'Available with a release' : 'Beschikbaar bij een release')}</b></span></article>
      <article><ShieldCheck /><span><small>{english ? 'Recovery' : 'Herstel'}</small><b>{state.settings.atomicUpdates ? (english ? 'Automatic restore point' : 'Automatisch herstelpunt') : (english ? 'Disabled' : 'Uitgeschakeld')}</b></span></article>
    </section>

    {available ? <section className="panel updateAvailablePanel"><div><span className="eyebrow">{english ? 'READY TO DOWNLOAD' : 'KLAAR OM TE DOWNLOADEN'}</span><h3>LocalDeck {update.latestVersion}</h3><p>{notes}</p></div><button className="primary" onClick={() => void action(() => window.localdeck.downloadUpdate())}><Download />{english ? 'Create restore point & download' : 'Herstelpunt maken & downloaden'}</button></section> : null}

    <div className="updateSectionHeading"><div><span className="eyebrow">{english ? 'VERSION HISTORY' : 'VERSIEGESCHIEDENIS'}</span><h3>{english ? 'What changed in LocalDeck' : 'Wat is er gewijzigd in LocalDeck'}</h3></div><label className="updateAutoToggle"><input type="checkbox" checked={state.settings.autoUpdateCheck} onChange={event => void action(() => window.localdeck.updateSettings({autoUpdateCheck: event.target.checked}))} /><span />{english ? 'Check automatically' : 'Automatisch controleren'}</label></div>

    <section className="updateTimeline">
      {history.map(release => {
        const releaseTitle = english ? release.titleEn || release.title : release.title;
        const releaseNotes = english ? release.notesEn || release.notes : release.notes;
        const changes = english ? release.changesEn || release.changes : release.changes;
        const isCurrent = release.version === update.currentVersion;
        const isAvailable = available && release.version === update.latestVersion;
        return <article className={`updateReleaseCard ${isAvailable ? 'highlight' : ''}`} key={`${release.channel}-${release.version}`}>
          <div className="updateReleaseRail"><span /><i /></div>
          <div className="updateReleaseContent">
            <header><div><span className="updateReleaseVersion">v{release.version}</span>{isCurrent ? <em>{english ? 'INSTALLED' : 'GEÏNSTALLEERD'}</em> : null}{isAvailable ? <em className="available">{english ? 'AVAILABLE' : 'BESCHIKBAAR'}</em> : null}<em className="channel">{release.channel === 'beta' ? (english ? 'BETA' : 'BÈTA') : (english ? 'STABLE' : 'STABIEL')}</em></div><time>{versionDate(release.publishedAt, language)}</time></header>
            <h3>{releaseTitle}</h3><p>{releaseNotes}</p>
            {changes.length ? <ul>{changes.map((change, index) => <li key={index}><CheckCircle2 />{change}</li>)}</ul> : null}
          </div>
        </article>;
      })}
      {!history.length ? <section className="panel emptyState"><History /><h3>{english ? 'No version history loaded' : 'Nog geen versiegeschiedenis geladen'}</h3><p>{english ? 'Check the update feed to load published releases.' : 'Controleer de updatefeed om gepubliceerde versies te laden.'}</p></section> : null}
    </section>

    <section className="updateFooterActions">
      <button className="ghost" disabled={!update.rollbackUrl} onClick={() => void action(() => window.localdeck.rollbackUpdate())}><RotateCcw />{english ? `Download previous version${update.rollbackVersion ? ` ${update.rollbackVersion}` : ''}` : `Vorige versie${update.rollbackVersion ? ` ${update.rollbackVersion}` : ''} downloaden`}</button>
      <button className="ghost" onClick={() => void action(() => window.localdeck.openUpdatesWebsite())}><ExternalLink />{english ? 'Complete online changelog' : 'Volledig online wijzigingsoverzicht'}</button>
    </section>
  </div>;
}
