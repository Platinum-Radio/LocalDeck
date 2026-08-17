<?php
declare(strict_types=1);
require __DIR__ . '/inc/bootstrap.php';
$startedAt = microtime(true);
$checks = website_health_checks();
$allHealthy = !in_array(false, array_column($checks, 'healthy'), true);
$latest = latest_published_release();
$checkedAt = new DateTimeImmutable();
$pageKey = 'status';
$pageTitle = t('Systeemstatus', 'System status') . ' — LocalDeck';
$pageDescription = t('Live componentcontrole van de LocalDeck-website, downloads, updatefeed en support.', 'Live component checks for the LocalDeck website, downloads, update feed, and support.');
require __DIR__ . '/inc/header.php';
?>
<section class="page-hero compact"><div class="shell"><span class="eyebrow"><i></i>LIVE SYSTEM STATUS</span><h1><?= e($allHealthy ? t('Alle websiteonderdelen zijn operationeel.', 'All website components are operational.') : t('Een websiteonderdeel vraagt aandacht.', 'A website component needs attention.')) ?></h1><p><?= e(t('Deze pagina voert bij iedere opening een nieuwe servercontrole uit. De lokale Apache-, PHP- en MySQL-status van jouw computer blijft uitsluitend zichtbaar in het LocalDeck-dashboard.', 'This page performs a new server-side check on every visit. The local Apache, PHP, and MySQL status on your computer remains visible only in the LocalDeck dashboard.')) ?></p></div></section>
<section class="section shell status-list">
    <div class="status-overview"><span class="status-pulse <?= $allHealthy ? 'ok' : 'error' ?>"><?= e($allHealthy ? '✓' : '!') ?></span><div><b><?= e($allHealthy ? t('Alles operationeel', 'Everything operational') : t('Aandacht nodig', 'Needs attention')) ?></b><small>LocalDeck <?= e((string) ($latest['version'] ?? LOCALDECK_SITE_VERSION)) ?> · <?= e(t('gecontroleerd op ', 'checked at ')) ?><?= e($checkedAt->format('d-m-Y H:i:s T')) ?> · <?= e((string) max(1, (int) round((microtime(true) - $startedAt) * 1000))) ?> ms</small></div><a href="<?= e(with_language('api/status.php')) ?>">JSON API ↗</a></div>
    <?php foreach ($checks as $check): ?><article><span class="status-check <?= $check['healthy'] ? 'ok' : 'error' ?>"><?= $check['healthy'] ? '✓' : '!' ?></span><div><b><?= e((string) $check['name']) ?></b><p><?= e((string) $check['detail']) ?></p></div><strong><?= e($check['healthy'] ? t('Operationeel', 'Operational') : t('Aandacht nodig', 'Needs attention')) ?></strong></article><?php endforeach; ?>
    <div class="incident-panel"><div><span>✓</span><div><b><?= e(t('Geen bekende incidenten', 'No known incidents')) ?></b><p><?= e(t('Er is momenteel geen gepland onderhoud of bekende storing voor website, downloads of updatefeed.', 'There is currently no planned maintenance or known incident for the website, downloads, or update feed.')) ?></p></div></div><small><?= e(t('Dit is componentcontrole, geen garantie voor externe netwerkbereikbaarheid vanuit iedere regio.', 'These are component checks, not a guarantee of external network reachability from every region.')) ?></small></div>
</section>
<?php require __DIR__ . '/inc/footer.php'; ?>
