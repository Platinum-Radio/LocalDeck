<?php
declare(strict_types=1);
require __DIR__ . '/inc/bootstrap.php';
$guides = require __DIR__ . '/inc/guides.php';
$pageKey = 'guides';
$pageTitle = t('Handleidingen voor lokale PHP-ontwikkeling — LocalDeck', 'Local PHP development guides — LocalDeck');
$pageDescription = t('Praktische uitleg voor PHP, HTTPS, WordPress, projecten en migratie vanaf XAMPP.', 'Practical guidance for PHP, HTTPS, WordPress, projects, and migration from XAMPP.');
require __DIR__ . '/inc/header.php';
?>
<section class="page-hero compact"><div class="shell"><span class="eyebrow"><i></i><?= e(t('PRAKTISCHE HANDLEIDINGEN', 'PRACTICAL GUIDES')) ?></span><h1><?= e(t('Van bestaande stack naar moderne lokale workflow.', 'From an existing stack to a modern local workflow.')) ?></h1><p><?= e(t('Korte, concrete uitleg voor veelgebruikte Windows- en PHP-scenario’s.', 'Short, concrete guidance for common Windows and PHP scenarios.')) ?></p></div></section>
<section class="section shell"><div class="guide-grid"><?php foreach ($guides as $guide): ?><a href="<?= e(with_language((string) $guide['file'])) ?>"><span>→</span><h2><?= e(content_text($guide['title'])) ?></h2><p><?= e(content_text($guide['summary'])) ?></p><b><?= e(t('Lees handleiding', 'Read guide')) ?></b></a><?php endforeach; ?></div></section>
<?php require __DIR__ . '/inc/footer.php'; ?>
