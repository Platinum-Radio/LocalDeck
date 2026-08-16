<?php
declare(strict_types=1);
require __DIR__ . '/inc/bootstrap.php';
$articles = require __DIR__ . '/inc/content.php';
$slug = preg_replace('/[^a-z0-9-]/', '', strtolower((string) ($_GET['article'] ?? 'start')));
$article = $articles[$slug] ?? $articles['start'];
if (!isset($articles[$slug])) {
    $slug = 'start';
}
$pageKey = 'wiki';
$pageTitle = content_text($article['title']) . ' — LocalDeck Wiki';
$pageDescription = content_text($article['summary']);
require __DIR__ . '/inc/header.php';
?>
<section class="page-hero compact">
    <div class="shell">
        <span class="eyebrow"><i></i>LOCALDECK WIKI</span>
        <h1><?= e(t('Duidelijke uitleg, direct naast het product.', 'Clear guidance, right beside the product.')) ?></h1>
        <p><?= e(t('Doorzoekbare documentatie voor installatie, dagelijks gebruik, beveiliging en probleemoplossing.', 'Searchable documentation for installation, daily use, security, and troubleshooting.')) ?></p>
        <label class="wiki-search"><span aria-hidden="true">⌕</span><input type="search" placeholder="<?= e(t('Zoek in onderwerpen…', 'Search topics…')) ?>" data-wiki-search></label>
    </div>
</section>
<div class="shell wiki-layout">
    <aside class="wiki-sidebar">
        <strong><?= e(t('Onderwerpen', 'Topics')) ?></strong>
        <nav aria-label="Wiki">
            <?php foreach ($articles as $articleSlug => $item): ?>
                <a href="<?= e(with_language('wiki.php?article=' . rawurlencode($articleSlug))) ?>" data-search-label="<?= e(strtolower(implode(' ', $item['title']) . ' ' . implode(' ', $item['summary']))) ?>"<?= $articleSlug === $slug ? ' class="active" aria-current="page"' : '' ?>><span><?= e(match ($articleSlug) {'start' => '⌂', 'installatie' => '↓', 'services' => '◉', 'projecten-https' => '◇', 'databases' => '▦', 'mail' => '✉', 'updates' => '↻', default => '!'}) ?></span><div><b><?= e(content_text($item['title'])) ?></b><small><?= e(content_text($item['summary'])) ?></small></div></a>
            <?php endforeach; ?>
        </nav>
        <div class="sidebar-help"><span>?</span><b><?= e(t('Iets niet gevonden?', 'Could not find it?')) ?></b><p><?= e(t('Dien een vraag of documentatie-idee in via de community.', 'Submit a question or documentation idea through the community.')) ?></p><a href="<?= e(with_language('community.php')) ?>"><?= e(t('Naar community', 'Go to community')) ?> →</a></div>
    </aside>
    <article class="wiki-article">
        <div class="article-breadcrumb"><a href="<?= e(with_language('wiki.php')) ?>">Wiki</a><span>/</span><b><?= e(content_text($article['title'])) ?></b></div>
        <header><h1><?= e(content_text($article['title'])) ?></h1><p><?= e(content_text($article['summary'])) ?></p><div class="article-meta"><span>◷ <?= e((string) $article['minutes']) ?> min</span><span>LocalDeck 0.10</span><span><?= e(t('Bijgewerkt 16-08-2026', 'Updated 2026-08-16')) ?></span></div></header>
        <?php foreach ($article['sections'] as $section): ?>
            <section>
                <h2><?= e(content_text($section['title'])) ?></h2>
                <?php foreach ($section['paragraphs'] ?? [] as $paragraph): ?><p><?= e(content_text($paragraph)) ?></p><?php endforeach; ?>
                <?php if (!empty($section['steps'])): ?><ol><?php foreach ($section['steps'] as $step): ?><li><?= e(content_text($step)) ?></li><?php endforeach; ?></ol><?php endif; ?>
            </section>
        <?php endforeach; ?>
        <div class="article-callout"><span>✓</span><div><b><?= e(t('Deze documentatie hoort bij LocalDeck 1.0.0', 'This documentation belongs to LocalDeck 1.0.0')) ?></b><p><?= e(t('Zie je een fout of ontbreekt een stap? Meld het via Community → Documentatie.', 'Found an error or missing step? Report it through Community → Documentation.')) ?></p></div></div>
        <div class="article-next">
            <span><?= e(t('Volgende onderwerp', 'Next topic')) ?></span>
            <?php $keys = array_keys($articles); $next = $keys[(array_search($slug, $keys, true) + 1) % count($keys)]; ?>
            <a href="<?= e(with_language('wiki.php?article=' . $next)) ?>"><b><?= e(content_text($articles[$next]['title'])) ?></b><span>→</span></a>
        </div>
    </article>
</div>
<?php require __DIR__ . '/inc/footer.php'; ?>
