<?php
declare(strict_types=1);
require __DIR__ . '/inc/bootstrap.php';

$pageKey = 'updates';
$pageTitle = t('Laatste updates', 'Latest updates') . ' — LocalDeck';
$pageDescription = t('Bekijk wat er nieuw en verbeterd is in de nieuwste LocalDeck-versies voor Windows.', 'See what is new and improved in the latest LocalDeck versions for Windows.');
require __DIR__ . '/inc/header.php';
?>
<section class="page-hero updates-hero">
    <div class="shell updates-hero-grid">
        <div>
            <span class="eyebrow"><i></i><?= e(t('PRODUCTUPDATE', 'PRODUCT UPDATE')) ?></span>
            <h1><?= e(t('Laatste updates', 'Latest updates')) ?></h1>
            <p><?= e(t('Een helder overzicht van nieuwe functies, verbeteringen en belangrijke technische wijzigingen in LocalDeck.', 'A clear overview of new features, improvements, and important technical changes in LocalDeck.')) ?></p>
            <div class="updates-actions">
                <a class="button primary" href="<?= e(with_language('downloads.php')) ?>"><?= e(t('Testversie downloaden', 'Download test release')) ?></a>
                <a class="button secondary" href="<?= e(LOCALDECK_GITHUB_URL) ?>/blob/main/UPDATES.md" target="_blank" rel="noopener"><?= e(t('Updates op GitHub', 'Updates on GitHub')) ?> ↗</a>
            </div>
        </div>
        <aside class="update-version-panel">
            <span><?= e(t('NIEUWSTE TESTVERSIE', 'LATEST TEST RELEASE')) ?></span>
            <strong>1.1.0-test.1</strong>
            <i><?= e(t('PRERELEASE · WINDOWS X64', 'PRERELEASE · WINDOWS X64')) ?></i>
            <small>18 <?= e(t('augustus', 'August')) ?> 2026</small>
        </aside>
    </div>
</section>

<section class="section shell update-release featured">
    <header class="update-release-heading">
        <div><span class="eyebrow"><i></i>1.1.0-test.1</span><h2><?= e(t('Klaar voor de volgende testronde.', 'Ready for the next testing round.')) ?></h2></div>
        <span class="update-channel"><?= e(t('Testversie', 'Prerelease')) ?></span>
    </header>
    <p class="update-lead"><?= e(t('Deze versie behoudt de volledig offline LocalDeck-runtime en maakt het programma eenvoudiger, beter schaalbaar en veel completer voor dagelijks Windows-ontwikkelwerk.', 'This version keeps LocalDeck’s fully offline runtime while making the application easier, more responsive, and much more complete for everyday Windows development.')) ?></p>

    <div class="updates-summary-grid">
        <article><span>01</span><h3><?= e(t('Rustiger en schaalbaar', 'Simpler and responsive')) ?></h3><p><?= e(t('Een eenvoudige en geavanceerde weergave, vernieuwde instellingen en een indeling die netjes werkt vanaf 1280 × 720.', 'Simple and advanced views, reorganized settings, and a layout that works cleanly from 1280 × 720 upward.')) ?></p></article>
        <article><span>02</span><h3><?= e(t('Volledige projectcyclus', 'Complete project lifecycle')) ?></h3><p><?= e(t('Maak projecten met PHP, database, lokaal domein en automatisch HTTPS. Verwijderen ruimt na een duidelijke bevestiging ook alle gekoppelde onderdelen op.', 'Create projects with PHP, a database, a local domain, and automatic HTTPS. Removal also cleans every linked component after clear confirmation.')) ?></p></article>
        <article><span>03</span><h3><?= e(t('Betrouwbare servicecontrole', 'Reliable service control')) ?></h3><p><?= e(t('Poortinspectie, Port Autopilot, LocalDeck Fix, preflightcontroles en bruikbare meldingen helpen problemen veilig oplossen.', 'Port inspection, Port Autopilot, LocalDeck Fix, preflight checks, and actionable notifications help resolve problems safely.')) ?></p></article>
        <article><span>04</span><h3><?= e(t('Meer ontwikkelgereedschap', 'More developer tooling')) ?></h3><p><?= e(t('PHP 8.2–8.5 per project, Database Lab, API-tests, webhooks, workers, Xdebug, branches, capsules en snapshots.', 'PHP 8.2–8.5 per project, Database Lab, API tests, webhooks, workers, Xdebug, branches, capsules, and snapshots.')) ?></p></article>
        <article><span>05</span><h3><?= e(t('Kleiner zonder in te leveren', 'Smaller without compromise')) ?></h3><p><?= e(t('Ongebruikte buildtools en talen zijn verwijderd en runtime-archieven zijn veilig geoptimaliseerd. Alle benodigde programma’s blijven ingebouwd.', 'Unused build tools and languages are removed and runtime archives are safely optimized. Every required program remains bundled.')) ?></p></article>
        <article><span>06</span><h3><?= e(t('Nederlands en Engels', 'Dutch and English')) ?></h3><p><?= e(t('De desktopapp, lokale startpagina, Webbeheer, Mailbeheer en publieke website zijn in beide talen beschikbaar.', 'The desktop app, local start page, Web Management, Mail Management, and public website are available in both languages.')) ?></p></article>
    </div>

    <div class="update-checks">
        <div><strong>59</strong><span><?= e(t('automatische tests geslaagd', 'automated tests passed')) ?></span></div>
        <div><strong>6/6</strong><span><?= e(t('echte services gecontroleerd', 'real services verified')) ?></span></div>
        <div><strong>2</strong><span><?= e(t('Windows-downloadvormen', 'Windows download formats')) ?></span></div>
        <div><strong>100%</strong><span><?= e(t('offline runtime ingebouwd', 'offline runtime bundled')) ?></span></div>
    </div>

    <div class="notice-banner update-notice"><span>i</span><div><b><?= e(t('Dit is een testversie', 'This is a test release')) ?></b><p><?= e(t('Gebruik 1.1.0-test.1 om de nieuwste verbeteringen te controleren en meld bevindingen via Support. LocalDeck 1.0.0 blijft de stabiele versie.', 'Use 1.1.0-test.1 to test the newest improvements and report findings through Support. LocalDeck 1.0.0 remains the stable release.')) ?></p></div></div>
</section>

<section class="section update-history">
    <div class="shell">
        <div class="section-heading"><span class="eyebrow"><i></i><?= e(t('Eerdere update', 'Previous update')) ?></span><h2>LocalDeck 1.0.0</h2><p>16 <?= e(t('augustus', 'August')) ?> 2026</p></div>
        <article class="update-history-card"><div><span>1.0.0</span><h3><?= e(t('De eerste publieke Windows-release', 'The first public Windows release')) ?></h3><p><?= e(t('De complete basis met Apache, PHP 8.2–8.5, MySQL, phpMyAdmin, Mailpit, Redis, Composer, automatisch lokaal HTTPS en een volledig ingebouwde offline runtime.', 'The complete foundation with Apache, PHP 8.2–8.5, MySQL, phpMyAdmin, Mailpit, Redis, Composer, automatic local HTTPS, and a fully bundled offline runtime.')) ?></p></div><a class="button secondary" href="<?= e(with_language('downloads.php')) ?>#older-releases"><?= e(t('Bekijk downloads', 'View downloads')) ?></a></article>
    </div>
</section>
<?php require __DIR__ . '/inc/footer.php'; ?>
