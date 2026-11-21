<?php
declare(strict_types=1);
require __DIR__ . '/inc/bootstrap.php';

$pageKey = 'updates';
$pageTitle = t('Updatesysteem', 'Update system') . ' — LocalDeck';
$pageDescription = t('Bekijk beschikbare LocalDeck-versies, wijzigingen, updatekanalen en veilige downloadinformatie.', 'View available LocalDeck versions, changes, update channels, and safe download information.');
$releases = published_releases();
$latest = $releases[0] ?? [];
$latestVersion = (string) ($latest['version'] ?? LOCALDECK_SITE_VERSION);
$latestChannel = (string) ($latest['channel'] ?? 'stable');
$latestComponents = array_values(array_filter($latest['components'] ?? [], 'is_array'));
require __DIR__ . '/inc/header.php';
?>
<section class="page-hero updates-hero">
    <div class="shell updates-hero-grid">
        <div>
            <span class="eyebrow"><i></i><?= e(t('LOCALDECK UPDATESYSTEEM', 'LOCALDECK UPDATE SYSTEM')) ?></span>
            <h1><?= e(t('Alle updates op één plek.', 'Every update in one place.')) ?></h1>
            <p><?= e(t('LocalDeck toont beschikbare versies, veranderingen, veilige downloads en herstelmogelijkheden nu rechtstreeks in het programma. Deze pagina en de updatefeeds gebruiken dezelfde releasebron.', 'LocalDeck now shows available versions, changes, safe downloads, and recovery options directly inside the application. This page and the update feeds use the same release source.')) ?></p>
            <div class="updates-actions">
                <a class="button primary" href="<?= e(with_language('downloads.php')) ?>"><?= e(t('Downloads bekijken', 'View downloads')) ?></a>
                <a class="button secondary" href="downloads/beta.json" target="_blank" rel="noopener"><?= e(t('Bètafeed bekijken', 'View beta feed')) ?> ↗</a>
            </div>
        </div>
        <aside class="update-version-panel">
            <span><?= e(t('NIEUWSTE BESCHIKBARE VERSIE', 'LATEST AVAILABLE VERSION')) ?></span>
            <strong><?= e($latestVersion) ?></strong>
            <i><?= e(strtoupper($latestChannel)) ?> · WINDOWS X64</i>
            <small><?= e(format_release_date($latest['releasedAt'] ?? null)) ?></small>
        </aside>
    </div>
</section>

<?php if ($latestComponents !== []): ?>
<section class="section shell release-component-overview">
    <div class="section-heading"><span class="eyebrow"><i></i><?= e(t('ONDERDELEN VAN DEZE RELEASE', 'COMPONENTS IN THIS RELEASE')) ?></span><h2><?= e(t('Precies zichtbaar wat 1.2.0 bijwerkt.', 'See exactly what 1.2.0 updates.')) ?></h2><p><?= e(t('De desktop-app en serverruntime worden afzonderlijk beoordeeld. Je ziet vooraf welke app-onderdelen wijzigen en of de ingebouwde PHP-omgeving opnieuw is opgebouwd.', 'The desktop app and server runtime are assessed separately. You can see which app components change and whether the bundled PHP environment was rebuilt.')) ?></p></div>
    <div class="release-component-strip">
        <?php foreach ($latestComponents as $component): ?>
            <article><span><?= e(($component['status'] ?? '') === 'added' ? '+' : (($component['status'] ?? '') === 'unchanged' ? '✓' : '↑')) ?></span><div><b><?= e((string) ($component['name'] ?? 'Onderdeel')) ?></b><small><?php if (!empty($component['currentVersion']) && $component['currentVersion'] !== ($component['targetVersion'] ?? '')): ?><?= e((string) $component['currentVersion']) ?> → <?php endif; ?><?= e((string) ($component['targetVersion'] ?? '')) ?></small></div></article>
        <?php endforeach; ?>
    </div>
    <div class="release-component-meta"><span><?= e(t('Updatestrategie', 'Update strategy')) ?><b><?= e((string) ($latest['updateStrategy'] ?? 'component-aware')) ?></b></span><span><?= e(t('Serverruntime', 'Server runtime')) ?><b><?= e(!empty($latest['runtimeChanged']) ? t('Bijgewerkt', 'Updated') : t('Ongewijzigd en gecontroleerd', 'Unchanged and verified')) ?></b></span><span><?= e(t('Pakketgrootte', 'Package size')) ?><b><?= e(!empty($latest['packageBytes']) ? number_format(((int) $latest['packageBytes']) / 1048576, 1, ',', '.') . ' MB' : '—') ?></b></span></div>
</section>
<?php endif; ?>

<section class="section shell update-system-overview">
    <div class="section-heading"><span class="eyebrow"><i></i><?= e(t('HOE HET WERKT', 'HOW IT WORKS')) ?></span><h2><?= e(t('Controleren, begrijpen en veilig bijwerken.', 'Check, understand, and update safely.')) ?></h2><p><?= e(t('Het Updatecentrum in LocalDeck blijft accountloos en gebruikt alleen de gekozen HTTPS-updatefeed.', 'The LocalDeck Update Center remains account-free and only uses the selected HTTPS update feed.')) ?></p></div>
    <div class="updates-summary-grid update-system-grid">
        <article><span>01</span><h3><?= e(t('Zichtbaar in de app', 'Visible in the app')) ?></h3><p><?= e(t('Een vaste optie Updates toont de huidige versie, beschikbare versie, kanaal, controletijd en volledige versiegeschiedenis.', 'A dedicated Updates option shows the current release, available release, channel, check time, and complete version history.')) ?></p></article>
        <article><span>02</span><h3><?= e(t('Duidelijke wijzigingen', 'Clear changes')) ?></h3><p><?= e(t('Iedere release bevat Nederlandse en Engelse release notes en een overzicht van de belangrijkste verbeteringen.', 'Every release contains Dutch and English release notes plus a summary of the most important improvements.')) ?></p></article>
        <article><span>03</span><h3><?= e(t('Veilig gecontroleerd', 'Safely verified')) ?></h3><p><?= e(t('Downloads worden via HTTPS opgehaald en vóór gebruik gecontroleerd met SHA-256 en, zodra beschikbaar, Authenticode.', 'Downloads use HTTPS and are verified with SHA-256 and, when available, Authenticode before use.')) ?></p></article>
        <article><span>04</span><h3><?= e(t('Herstel vóór update', 'Recovery before update')) ?></h3><p><?= e(t('Bij atomische updates maakt LocalDeck eerst een volledig werkruimteherstelpunt en actuele databaseback-ups.', 'For atomic updates, LocalDeck first creates a complete workspace restore point and current database backups.')) ?></p></article>
    </div>
    <div class="update-feed-links">
        <div><b><?= e(t('Stabiel kanaal', 'Stable channel')) ?></b><code>https://localdeck.nl/downloads/windows.json</code></div>
        <div><b><?= e(t('Bètakanaal', 'Beta channel')) ?></b><code>https://localdeck.nl/downloads/beta.json</code></div>
    </div>
</section>

<section class="section update-history">
    <div class="shell">
        <div class="section-heading"><span class="eyebrow"><i></i><?= e(t('VERSIEGESCHIEDENIS', 'VERSION HISTORY')) ?></span><h2><?= e(t('Gepubliceerde LocalDeck-updates', 'Published LocalDeck updates')) ?></h2><p><?= e(t('De nieuwste versie staat altijd bovenaan. Oudere downloads blijven via het downloadarchief beschikbaar.', 'The newest version is always listed first. Older downloads remain available through the download archive.')) ?></p></div>
        <div class="website-update-timeline">
            <?php foreach ($releases as $index => $release): ?>
                <?php $changes = release_changes($release); ?>
                <article class="update-history-card<?= $index === 0 ? ' featured-release' : '' ?>" id="version-<?= e((string) ($release['version'] ?? '')) ?>">
                    <div class="website-update-version"><span><?= e((string) ($release['version'] ?? '')) ?></span><small><?= e(strtoupper((string) ($release['channel'] ?? 'stable'))) ?></small></div>
                    <div class="website-update-content">
                        <div class="website-update-heading"><div><h3><?= e(release_text($release, 'title')) ?></h3><time><?= e(format_release_date($release['releasedAt'] ?? null)) ?></time></div><a class="button secondary" href="<?= e(with_language('downloads.php')) ?>#older-releases"><?= e(t('Downloads', 'Downloads')) ?></a></div>
                        <p><?= e(release_text($release, 'notes')) ?></p>
                        <?php if ($changes !== []): ?><ul><?php foreach ($changes as $change): ?><li><span>✓</span><?= e($change) ?></li><?php endforeach; ?></ul><?php endif; ?>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php require __DIR__ . '/inc/footer.php'; ?>
