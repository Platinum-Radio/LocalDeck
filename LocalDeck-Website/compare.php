<?php
declare(strict_types=1);
require __DIR__ . '/inc/bootstrap.php';
$pageKey = 'compare';
$pageTitle = t('LocalDeck vergelijken met XAMPP, Laragon, Herd en DDEV', 'Compare LocalDeck with XAMPP, Laragon, Herd, and DDEV');
$pageDescription = t('Een controleerbare vergelijking van lokale ontwikkelomgevingen voor Windows.', 'A verifiable comparison of local development environments for Windows.');
$structuredData = [
    '@type' => 'WebPage',
    'name' => $pageTitle,
    'description' => $pageDescription,
    'dateModified' => '2026-08-17',
];
$rows = [
    [t('Primaire aanpak', 'Primary approach'), 'Windows native', 'Apache bundle', 'Windows native', 'Windows/macOS native', 'Docker containers'],
    [t('Account of activatie vereist', 'Account or activation required'), t('Nee', 'No'), t('Nee', 'No'), t('Nee voor lokaal gebruik', 'No for local use'), t('Nee voor Basic', 'No for Basic'), t('Nee', 'No')],
    [t('Complete offline runtime inbegrepen', 'Complete offline runtime included'), t('Ja', 'Yes'), t('Ja', 'Yes'), t('Afhankelijk van editie', 'Depends on edition'), t('Gedeeltelijk', 'Partly'), t('Nee, Docker vereist', 'No, Docker required')],
    [t('PHP-versie per project', 'PHP version per project'), t('Ja, gelijktijdig', 'Yes, simultaneously'), t('Handmatig', 'Manual'), t('Meerdere versies', 'Multiple versions'), t('Ja', 'Yes'), t('Ja', 'Yes')],
    [t('Automatisch lokaal HTTPS', 'Automatic local HTTPS'), t('Ja', 'Yes'), t('Handmatig', 'Manual'), t('Ja', 'Yes'), t('Ja', 'Yes'), t('Ja', 'Yes')],
    [t('App- én Windows-servicemodus', 'App and Windows service modes'), t('Ja', 'Yes'), t('Windows-services mogelijk', 'Windows services possible'), t('Eigen procesbeheer', 'Own process management'), t('Eigen procesbeheer', 'Own process management'), t('Via Docker', 'Through Docker')],
    [t('Snapshots en veilig herstel', 'Snapshots and safe repair'), t('Ingebouwd', 'Built in'), t('Niet centraal', 'Not centralized'), t('Beperkt', 'Limited'), t('Afhankelijk van editie', 'Depends on edition'), t('Database-snapshots', 'Database snapshots')],
    [t('Lokale mailopvang', 'Local mail capture'), t('Mailpit + Mailbeheer', 'Mailpit + Mail Management'), t('Niet standaard', 'Not standard'), 'Mailpit', t('Beschikbaar', 'Available'), t('Via add-on', 'Through add-on')],
    [t('Grafisch databasebeheer inbegrepen', 'Graphical database management included'), 'phpMyAdmin', 'phpMyAdmin', t('Optioneel', 'Optional'), t('Extern of Pro', 'External or Pro'), t('Extern', 'External')],
];
require __DIR__ . '/inc/header.php';
?>
<section class="page-hero compact"><div class="shell"><span class="eyebrow"><i></i><?= e(t('EERLIJK VERGELIJKEN', 'COMPARE CLEARLY')) ?></span><h1><?= e(t('Kies de omgeving die bij je werk past.', 'Choose the environment that fits your work.')) ?></h1><p><?= e(t('LocalDeck is Windows-first en offline-first. Andere oplossingen kunnen beter passen wanneer je Docker, macOS, Linux of een puur Laravel-gerichte workflow nodig hebt.', 'LocalDeck is Windows-first and offline-first. Other solutions may fit better when you need Docker, macOS, Linux, or a Laravel-only workflow.')) ?></p></div></section>
<section class="section shell">
    <div class="comparison-intro"><span><?= e(t('Laatst gecontroleerd: 17-08-2026', 'Last reviewed: 2026-08-17')) ?></span><p><?= e(t('Functies kunnen per editie en versie veranderen. Controleer voor een definitieve keuze altijd de gekoppelde officiële productpagina’s.', 'Features can change by edition and version. Always check the linked official product pages before making a final choice.')) ?></p></div>
    <div class="comparison-scroll" tabindex="0" aria-label="<?= e(t('Vergelijkingstabel', 'Comparison table')) ?>"><table class="comparison-table"><thead><tr><th><?= e(t('Onderdeel', 'Capability')) ?></th><th class="featured">LocalDeck</th><th>XAMPP</th><th>Laragon</th><th>Herd</th><th>DDEV</th></tr></thead><tbody><?php foreach ($rows as $row): ?><tr><?php foreach ($row as $index => $cell): ?><<?= $index === 0 ? 'th scope="row"' : 'td' ?><?= $index === 1 ? ' class="featured"' : '' ?>><?= e((string) $cell) ?></<?= $index === 0 ? 'th' : 'td' ?>><?php endforeach; ?></tr><?php endforeach; ?></tbody></table></div>
    <div class="choice-grid">
        <article><span>LD</span><h2>LocalDeck</h2><p><?= e(t('Voor Windows-gebruikers die een complete lokale stack, projectisolatie, herstel en offline runtimes in één dashboard willen.', 'For Windows users who want a complete local stack, project isolation, repair, and offline runtimes in one dashboard.')) ?></p></article>
        <article><span>X</span><h2>XAMPP</h2><p><?= e(t('Voor een bekende, eenvoudige Apache/PHP-demoserver zonder uitgebreide projectlaag.', 'For a familiar, straightforward Apache/PHP demo server without an extensive project layer.')) ?></p></article>
        <article><span>L</span><h2>Laragon</h2><p><?= e(t('Voor een lichte Windows-omgeving met uitbreidbare runtimes en snel projectbeheer.', 'For a lightweight Windows environment with extensible runtimes and fast project management.')) ?></p></article>
        <article><span>H</span><h2>Herd</h2><p><?= e(t('Voor een sterk gestroomlijnde Laravel- en PHP-workflow.', 'For a highly streamlined Laravel and PHP workflow.')) ?></p></article>
        <article><span>D</span><h2>DDEV</h2><p><?= e(t('Voor teams die reproduceerbare Docker-omgevingen op meerdere besturingssystemen nodig hebben.', 'For teams that need reproducible Docker environments across operating systems.')) ?></p></article>
    </div>
    <div class="source-panel"><b><?= e(t('Officiële bronnen', 'Official sources')) ?></b><a href="https://www.apachefriends.org/" target="_blank" rel="noopener">XAMPP ↗</a><a href="https://laragon.org/docs" target="_blank" rel="noopener">Laragon ↗</a><a href="https://herd.laravel.com/windows" target="_blank" rel="noopener">Laravel Herd ↗</a><a href="https://ddev.com/" target="_blank" rel="noopener">DDEV ↗</a></div>
</section>
<section class="cta-section"><div class="shell cta-card"><div><span class="eyebrow"><i></i><?= e(t('ZELF CONTROLEREN', 'VERIFY IT YOURSELF')) ?></span><h2><?= e(t('Probeer LocalDeck zonder account.', 'Try LocalDeck without an account.')) ?></h2><p><?= e(t('Kies de EXE-installatie of pak de ZIP uit in een map naar keuze.', 'Choose the EXE installer or extract the ZIP into a folder of your choice.')) ?></p></div><a class="button primary" href="<?= e(with_language('downloads.php')) ?>"><?= e(t('Naar downloads', 'Go to downloads')) ?> →</a></div></section>
<?php require __DIR__ . '/inc/footer.php'; ?>
