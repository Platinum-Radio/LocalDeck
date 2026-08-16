<?php
declare(strict_types=1);
require __DIR__ . '/inc/bootstrap.php';
$pageKey = 'home';
$pageTitle = 'LocalDeck — ' . t('Lokale PHP-ontwikkeling voor Windows', 'Local PHP development for Windows');
$pageDescription = t('Apache, PHP, MySQL, phpMyAdmin, Mailpit en Redis in één moderne lokale Windows-omgeving.', 'Apache, PHP, MySQL, phpMyAdmin, Mailpit, and Redis in one modern local Windows environment.');
$catalog = release_catalog();
$latest = $catalog['releases'][0] ?? null;
require __DIR__ . '/inc/header.php';
?>
<section class="hero">
    <div class="hero-glow hero-glow-one"></div>
    <div class="hero-glow hero-glow-two"></div>
    <div class="shell hero-grid">
        <div class="hero-copy">
            <span class="eyebrow"><i></i><?= e(t('Gebouwd voor Windows 10 & 11', 'Built for Windows 10 & 11')) ?></span>
            <h1><?= e(t('Lokale PHP-ontwikkeling.', 'Local PHP development.')) ?><br><span><?= e(t('Opnieuw ontworpen.', 'Reimagined.')) ?></span></h1>
            <p><?= e(t('Alles wat je nodig hebt om websites lokaal te bouwen — overzichtelijk, veilig en volledig vanuit één map.', 'Everything you need to build websites locally—clear, secure, and entirely from one folder.')) ?></p>
            <div class="hero-actions">
                <a class="button primary" href="<?= e(with_language('downloads.php')) ?>"><?= e(t('Bekijk downloads', 'View downloads')) ?><svg aria-hidden="true" viewBox="0 0 24 24"><path d="M12 3v12m0 0 5-5m-5 5-5-5M5 21h14"/></svg></a>
                <a class="button secondary" href="<?= e(with_language('wiki.php')) ?>"><?= e(t('Lees de wiki', 'Read the wiki')) ?></a>
            </div>
            <div class="trust-row">
                <span><b>100%</b> <?= e(t('lokaal', 'local')) ?></span>
                <span><b>6</b> <?= e(t('services', 'services')) ?></span>
                <span><b>0</b> <?= e(t('verplichte accounts', 'required accounts')) ?></span>
            </div>
        </div>
        <div class="product-window" aria-label="<?= e(t('Voorbeeld van het LocalDeck-dashboard', 'LocalDeck dashboard preview')) ?>">
            <div class="window-bar"><span></span><span></span><span></span><b>LOCALDECK / CONTROL CENTER</b></div>
            <div class="window-body">
                <div class="mini-sidebar">
                    <img src="assets/logo.png" alt="" width="42" height="42">
                    <i class="active"></i><i></i><i></i><i></i><i></i>
                </div>
                <div class="dashboard-preview">
                    <div class="preview-top"><div><small><?= e(t('SYSTEEMSTATUS', 'SYSTEM STATUS')) ?></small><strong><?= e(t('Alles draait lokaal', 'Everything runs locally')) ?></strong></div><span class="health"><i></i><?= e(t('Gezond', 'Healthy')) ?></span></div>
                    <div class="service-list">
                        <?php foreach ([['Apache', '80', 'violet'], ['PHP 8.5', '9000', 'cyan'], ['MySQL 8.4', '3306', 'blue'], ['phpMyAdmin', '8080', 'pink'], ['Mailpit', '8025', 'amber'], ['Redis', '6379', 'red']] as [$name, $port, $color]): ?>
                            <div class="service-card"><span class="service-icon <?= e($color) ?>"></span><div><b><?= e($name) ?></b><small>127.0.0.1:<?= e($port) ?></small></div><i class="switch-on"></i></div>
                        <?php endforeach; ?>
                    </div>
                    <div class="preview-project"><span class="project-mark">L</span><div><b>localdeck.localhost</b><small>PHP 8.5 · HTTPS</small></div><span>↗</span></div>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="signal-strip">
    <div class="shell signal-grid">
        <div><span class="signal-icon">W</span><p><small><?= e(t('PLATFORM', 'PLATFORM')) ?></small><b>Windows 10 & 11</b></p></div>
        <div><span class="signal-icon">PHP</span><p><small><?= e(t('STANDAARD RUNTIME', 'DEFAULT RUNTIME')) ?></small><b>PHP 8.5</b></p></div>
        <div><span class="signal-icon">SSL</span><p><small><?= e(t('PER PROJECT', 'PER PROJECT')) ?></small><b><?= e(t('Automatisch HTTPS', 'Automatic HTTPS')) ?></b></p></div>
        <div><span class="signal-icon">↻</span><p><small><?= e(t('UPDATEKANAAL', 'UPDATE CHANNEL')) ?></small><b><?= e(t('Automatisch & controleerbaar', 'Automatic & verifiable')) ?></b></p></div>
    </div>
</section>

<section class="section shell">
    <div class="section-heading centered">
        <span class="eyebrow"><i></i><?= e(t('Eén werkplek', 'One workspace')) ?></span>
        <h2><?= e(t('Minder losse tools. Meer bouwen.', 'Fewer disconnected tools. More building.')) ?></h2>
        <p><?= e(t('LocalDeck brengt de volledige lokale stack samen zonder dat je eerst accounts, licenties of cloudkoppelingen nodig hebt.', 'LocalDeck brings the complete local stack together without requiring accounts, licenses, or cloud connections first.')) ?></p>
    </div>
    <div class="feature-grid">
        <article class="feature-card feature-large">
            <span class="feature-number">01</span><div class="feature-symbol violet">⌁</div>
            <h3><?= e(t('Services onder controle', 'Services under control')) ?></h3>
            <p><?= e(t('Start Apache, PHP, MySQL, phpMyAdmin, Mailpit en Redis samen of afzonderlijk. Poortcontrole en herstel zijn ingebouwd.', 'Start Apache, PHP, MySQL, phpMyAdmin, Mailpit, and Redis together or individually. Port checks and repair are built in.')) ?></p>
            <div class="chip-row"><span>Apache</span><span>PHP 8.5</span><span>MySQL 8.4</span><span>Redis</span></div>
        </article>
        <article class="feature-card"><span class="feature-number">02</span><div class="feature-symbol cyan">◇</div><h3><?= e(t('Project-HTTPS', 'Project HTTPS')) ?></h3><p><?= e(t('Elk project krijgt automatisch een eigen lokaal certificaat en herkenbaar .localhost-domein.', 'Every project automatically receives its own local certificate and recognizable .localhost domain.')) ?></p></article>
        <article class="feature-card"><span class="feature-number">03</span><div class="feature-symbol blue">▦</div><h3><?= e(t('Databasebeheer', 'Database management')) ?></h3><p><?= e(t('Maak databases en gebruikers, voer veilige SQL uit en open phpMyAdmin direct vanuit het dashboard.', 'Create databases and users, run safe SQL, and open phpMyAdmin directly from the dashboard.')) ?></p></article>
        <article class="feature-card"><span class="feature-number">04</span><div class="feature-symbol pink">✉</div><h3><?= e(t('Mail zonder risico', 'Mail without risk')) ?></h3><p><?= e(t('Vang testmail lokaal op en beheer adressen, retentie en routes vanuit Mailbeheer.', 'Capture test email locally and manage addresses, retention, and routes from Mail Management.')) ?></p></article>
        <article class="feature-card"><span class="feature-number">05</span><div class="feature-symbol amber">↯</div><h3><?= e(t('App- of servicemodus', 'App or service mode')) ?></h3><p><?= e(t('Laat alles alleen draaien met LocalDeck, of kies echte Windows-services die zelfstandig actief blijven.', 'Run everything only with LocalDeck, or choose real Windows services that remain active independently.')) ?></p></article>
    </div>
</section>

<section class="section architecture-section">
    <div class="shell split-section">
        <div>
            <span class="eyebrow"><i></i><?= e(t('Alles blijft bij elkaar', 'Everything stays together')) ?></span>
            <h2><?= e(t('Eén map. Elke website. Volledige controle.', 'One folder. Every website. Complete control.')) ?></h2>
            <p><?= e(t('Projecten leven in de map websites. Runtimes, certificaten, back-ups en instellingen blijven georganiseerd binnen LocalDeck, zodat je werkruimte begrijpelijk en verplaatsbaar blijft.', 'Projects live in the websites folder. Runtimes, certificates, backups, and settings remain organized inside LocalDeck, keeping your workspace understandable and portable.')) ?></p>
            <ul class="check-list">
                <li><?= e(t('Geen verplichte cloud of account', 'No required cloud or account')) ?></li>
                <li><?= e(t('Projectprofielen voor teams en overdracht', 'Project profiles for teams and handoff')) ?></li>
                <li><?= e(t('Snapshots vóór ingrijpende wijzigingen', 'Snapshots before major changes')) ?></li>
                <li><?= e(t('Veilige verwijdering met expliciete controle', 'Safe removal with explicit confirmation')) ?></li>
            </ul>
        </div>
        <div class="folder-map">
            <div class="folder-root"><span>▾</span><b>LocalDeck-Startklaar</b></div>
            <div class="folder-row"><span>├─</span><i>▣</i><b>LocalDeck.exe</b><small><?= e(t('dashboard', 'dashboard')) ?></small></div>
            <div class="folder-row"><span>├─</span><i>▤</i><b>runtime</b><small><?= e(t('services & gegevens', 'services & data')) ?></small></div>
            <div class="folder-row selected"><span>├─</span><i>▰</i><b>websites</b><small><?= e(t('jouw projecten', 'your projects')) ?></small></div>
            <div class="folder-row indent"><span>├─</span><i>◆</i><b>LocalDeck</b><small>localdeck.localhost</small></div>
            <div class="folder-row indent"><span>└─</span><i>◇</i><b><?= e(t('mijn-webshop', 'my-shop')) ?></b><small><?= e(t('eigen project', 'custom project')) ?></small></div>
            <div class="folder-row"><span>└─</span><i>▱</i><b>resources</b><small><?= e(t('offline gereedschap', 'offline tools')) ?></small></div>
        </div>
    </div>
</section>

<section class="section shell">
    <div class="release-panel">
        <div>
            <span class="eyebrow"><i></i><?= e(t('Huidige ontwikkelversie', 'Current development version')) ?></span>
            <h2>LocalDeck <?= e((string) ($latest['version'] ?? '1.0.0')) ?></h2>
            <p><?= e(t('De website en updateketen staan klaar voor lokale controle. Een openbaar downloadbestand wordt pas geplaatst nadat de release volledig is gevalideerd.', 'The website and update chain are ready for local review. A public download is added only after the release has been fully validated.')) ?></p>
        </div>
        <div class="release-meta">
            <span><?= e(t('Status', 'Status')) ?><b><?= e(($latest['published'] ?? false) ? t('Gepubliceerd', 'Published') : t('Interne preview', 'Internal preview')) ?></b></span>
            <span><?= e(t('Gestarte downloads', 'Downloads initiated')) ?><b><?= e((string) download_count((string) ($latest['version'] ?? '1.0.0'))) ?></b></span>
            <a class="button secondary" href="<?= e(with_language('downloads.php')) ?>"><?= e(t('Releasecentrum openen', 'Open release center')) ?></a>
        </div>
    </div>
</section>

<section class="cta-section">
    <div class="shell cta-card">
        <div><span class="eyebrow"><i></i><?= e(t('Documentatie die meegroeit', 'Documentation that grows with the product')) ?></span><h2><?= e(t('Van eerste start tot foutdiagnose.', 'From first launch to troubleshooting.')) ?></h2><p><?= e(t('De LocalDeck-wiki legt installaties, services, projecten, databases, mail, updates en herstel stap voor stap uit.', 'The LocalDeck wiki explains installation, services, projects, databases, mail, updates, and repair step by step.')) ?></p></div>
        <a class="button primary" href="<?= e(with_language('wiki.php')) ?>"><?= e(t('Open de wiki', 'Open the wiki')) ?> <span>→</span></a>
    </div>
</section>
<?php require __DIR__ . '/inc/footer.php'; ?>
