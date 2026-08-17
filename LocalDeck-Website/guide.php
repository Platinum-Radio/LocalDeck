<?php
declare(strict_types=1);
if (!defined('LOCALDECK_GUIDE_SLUG')) {
    http_response_code(404);
    exit('Not found');
}
require __DIR__ . '/inc/bootstrap.php';
$guides = require __DIR__ . '/inc/guides.php';
$guide = $guides[LOCALDECK_GUIDE_SLUG] ?? null;
if (!is_array($guide)) {
    http_response_code(404);
    exit('Not found');
}
$pageKey = 'guides';
$pageTitle = content_text($guide['title']) . ' — LocalDeck';
$pageDescription = content_text($guide['summary']);
$canonicalPath = '/' . $guide['file'];
$structuredData = [
    '@type' => 'TechArticle',
    'headline' => content_text($guide['title']),
    'description' => $pageDescription,
    'dateModified' => '2026-08-17',
    'inLanguage' => $language,
    'author' => ['@type' => 'Organization', 'name' => 'LocalDeck'],
];
require __DIR__ . '/inc/header.php';
?>
<section class="page-hero compact guide-hero"><div class="shell"><div class="article-breadcrumb"><a href="<?= e(with_language('guides.php')) ?>"><?= e(t('Handleidingen', 'Guides')) ?></a><span>/</span><b><?= e(content_text($guide['title'])) ?></b></div><span class="eyebrow"><i></i>LOCALDECK GUIDE</span><h1><?= e(content_text($guide['title'])) ?></h1><p><?= e(content_text($guide['intro'])) ?></p></div></section>
<article class="shell guide-article"><?php foreach ($guide['sections'] as [$title, $paragraph]): ?><section><h2><?= e(content_text($title)) ?></h2><p><?= e(content_text($paragraph)) ?></p></section><?php endforeach; ?><div class="article-callout"><span>✓</span><div><b><?= e(t('Geschreven voor LocalDeck 1.0.0', 'Written for LocalDeck 1.0.0')) ?></b><p><?= e(t('Controleer na iedere migratie je project, database en lokale mail voordat je de oude omgeving verwijdert.', 'After every migration, verify your project, database, and local mail before removing the old environment.')) ?></p></div></div><div class="guide-actions"><a class="button primary" href="<?= e(with_language('downloads.php')) ?>"><?= e(t('Download LocalDeck', 'Download LocalDeck')) ?></a><a class="button secondary" href="<?= e(with_language('community.php')) ?>"><?= e(t('Vraag stellen', 'Ask a question')) ?></a></div></article>
<?php require __DIR__ . '/inc/footer.php'; ?>
