<?php
declare(strict_types=1);

require __DIR__ . '/inc/bootstrap.php';

header('Content-Type: application/xml; charset=UTF-8');
header('Cache-Control: public, max-age=3600');

$articles = require __DIR__ . '/inc/content.php';
$guides = require __DIR__ . '/inc/guides.php';
$lastModified = '2026-08-17';
$locations = [];

foreach (['nl', 'en'] as $siteLanguage) {
    foreach (['index.php', 'downloads.php', 'wiki.php', 'guides.php', 'compare.php', 'community.php', 'security.php', 'privacy.php', 'status.php'] as $page) {
        $locations[] = site_origin() . '/' . $page . '?lang=' . $siteLanguage;
    }
    foreach (array_keys($articles) as $slug) {
        $locations[] = site_origin() . '/wiki.php?article=' . rawurlencode((string) $slug) . '&lang=' . $siteLanguage;
    }
    foreach ($guides as $guide) {
        $locations[] = site_origin() . '/' . ltrim((string) $guide['file'], '/') . '?lang=' . $siteLanguage;
    }
}

echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
<?php foreach (array_unique($locations) as $location): ?>
    <url>
        <loc><?= htmlspecialchars($location, ENT_XML1 | ENT_QUOTES, 'UTF-8') ?></loc>
        <lastmod><?= $lastModified ?></lastmod>
    </url>
<?php endforeach; ?>
</urlset>
