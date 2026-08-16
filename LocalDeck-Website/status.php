<?php
declare(strict_types=1);
require __DIR__ . '/inc/bootstrap.php';
$catalog = release_catalog();
$stats = download_statistics();
$latest = $catalog['releases'][0] ?? [];
$checks = [
    [t('Website', 'Website'), is_file(__DIR__ . '/index.php'), t('PHP-pagina beschikbaar', 'PHP page available')],
    [t('Wiki', 'Wiki'), is_file(__DIR__ . '/inc/content.php'), count(require __DIR__ . '/inc/content.php') . ' ' . t('onderwerpen', 'topics')],
    [t('Updatefeed', 'Update feed'), is_file(__DIR__ . '/downloads/windows.json'), (string) ($latest['version'] ?? '—')],
    [t('Downloadteller', 'Download counter'), is_writable(__DIR__ . '/private/download-stats.json'), (string) ($stats['total'] ?? 0) . ' ' . t('downloads', 'downloads')],
];
$pageKey = 'status';
$pageTitle = t('Systeemstatus', 'System status') . ' — LocalDeck';
$pageDescription = t('Lokale status van de LocalDeck-website, wiki en updatefeed.', 'Local status of the LocalDeck website, wiki, and update feed.');
require __DIR__ . '/inc/header.php';
?>
<section class="page-hero compact"><div class="shell"><span class="eyebrow"><i></i>SYSTEM STATUS</span><h1><?= e(t('Websiteketen gereed voor controle.', 'Website chain ready for review.')) ?></h1><p><?= e(t('Dit overzicht controleert de websiteonderdelen. De servicestatus zelf blijft zichtbaar in het LocalDeck-dashboard.', 'This overview checks website components. Service status itself remains visible in the LocalDeck dashboard.')) ?></p></div></section>
<section class="section shell status-list">
    <?php foreach ($checks as [$name, $healthy, $detail]): ?><article><span class="status-check <?= $healthy ? 'ok' : 'error' ?>"><?= $healthy ? '✓' : '!' ?></span><div><b><?= e($name) ?></b><p><?= e($detail) ?></p></div><strong><?= e($healthy ? t('Operationeel', 'Operational') : t('Aandacht nodig', 'Needs attention')) ?></strong></article><?php endforeach; ?>
    <div class="notice-banner"><span>ℹ</span><div><b><?= e(t('Lokale preview', 'Local preview')) ?></b><p><?= e(t('Er is nog geen publieke uptimecontrole. Die wordt pas gekoppeld zodra domein en hosting zijn gekozen.', 'There is no public uptime monitor yet. It will be connected after domain and hosting are chosen.')) ?></p></div></div>
</section>
<?php require __DIR__ . '/inc/footer.php'; ?>

