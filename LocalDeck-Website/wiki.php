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
$canonicalParams = ['article' => $slug];
$structuredData = [
    '@type' => 'TechArticle',
    'headline' => content_text($article['title']),
    'description' => $pageDescription,
    'dateModified' => '2026-08-17',
    'inLanguage' => $language,
    'author' => ['@type' => 'Organization', 'name' => 'LocalDeck'],
];
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
                <?php $searchLabel = strtolower((string) json_encode($item, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)); ?>
                <a href="<?= e(with_language('wiki.php?article=' . rawurlencode($articleSlug))) ?>" data-search-label="<?= e($searchLabel) ?>"<?= $articleSlug === $slug ? ' class="active" aria-current="page"' : '' ?>><span><?= e(match ($articleSlug) {'start' => '⌂', 'installatie' => '↓', 'services' => '◉', 'projecten-https' => '◇', 'databases' => '▦', 'mail' => '✉', 'updates' => '↻', 'windows-beveiliging' => '▣', 'foutcodes' => '#', default => '!'}) ?></span><div><b><?= e(content_text($item['title'])) ?></b><small><?= e(content_text($item['summary'])) ?></small></div></a>
            <?php endforeach; ?>
        </nav>
        <div class="sidebar-help"><span>?</span><b><?= e(t('Iets niet gevonden?', 'Could not find it?')) ?></b><p><?= e(t('Stuur een vraag of documentatie-idee naar het LocalDeck-team.', 'Send a question or documentation idea to the LocalDeck team.')) ?></p><a href="<?= e(with_language('community.php?type=docs&title=' . rawurlencode(t('Vraag over de LocalDeck-wiki', 'Question about the LocalDeck wiki')))) ?>"><?= e(t('Naar support', 'Go to support')) ?> →</a></div>
    </aside>
    <article class="wiki-article">
        <div class="article-breadcrumb"><a href="<?= e(with_language('wiki.php')) ?>">Wiki</a><span>/</span><b><?= e(content_text($article['title'])) ?></b></div>
        <header><div class="doc-version"><label><?= e(t('Documentatieversie', 'Documentation version')) ?><select aria-label="<?= e(t('Documentatieversie', 'Documentation version')) ?>"><option><?= e(LOCALDECK_DOCUMENTATION_VERSION) ?></option></select></label></div><h1><?= e(content_text($article['title'])) ?></h1><p><?= e(content_text($article['summary'])) ?></p><div class="article-meta"><span>◷ <?= e((string) $article['minutes']) ?> min</span><span>LocalDeck <?= e(LOCALDECK_DOCUMENTATION_VERSION) ?></span><span><?= e(t('Bijgewerkt 17-08-2026', 'Updated 2026-08-17')) ?></span></div></header>
        <?php foreach ($article['sections'] as $section): ?>
            <section>
                <h2><?= e(content_text($section['title'])) ?></h2>
                <?php foreach ($section['paragraphs'] ?? [] as $paragraph): ?><p><?= e(content_text($paragraph)) ?></p><?php endforeach; ?>
                <?php if (!empty($section['steps'])): ?><ol><?php foreach ($section['steps'] as $step): ?><li><?= e(content_text($step)) ?></li><?php endforeach; ?></ol><?php endif; ?>
                <?php foreach ($section['commands'] ?? [] as $command): ?><div class="code-copy"><code><?= e((string) $command) ?></code><button type="button" data-copy="<?= e((string) $command) ?>"><?= e(t('Kopiëren', 'Copy')) ?></button></div><?php endforeach; ?>
            </section>
            <?php if ($slug === 'start' && $section === $article['sections'][0]): ?><figure class="doc-screenshot"><img src="/assets/localdeck-dashboard.png" width="1265" height="711" loading="lazy" decoding="async" alt="<?= e(t('LocalDeck-dashboard met actieve services', 'LocalDeck dashboard with active services')) ?>"><figcaption><?= e(t('Het echte dashboard van LocalDeck 1.0.0.', 'The real LocalDeck 1.0.0 dashboard.')) ?></figcaption></figure><?php endif; ?>
            <?php if ($slug === 'projecten-https' && $section === $article['sections'][0]): ?><figure class="doc-screenshot"><img src="/assets/localdeck-projects.png" width="1265" height="711" loading="lazy" decoding="async" alt="<?= e(t('LocalDeck-projectoverzicht met HTTPS', 'LocalDeck project overview with HTTPS')) ?>"><figcaption><?= e(t('Projectdomein, PHP-versie en HTTPS in één overzicht.', 'Project domain, PHP version, and HTTPS in one view.')) ?></figcaption></figure><?php endif; ?>
        <?php endforeach; ?>
        <div class="article-callout"><span>✓</span><div><b><?= e(t('Deze documentatie hoort bij LocalDeck 1.0.0', 'This documentation belongs to LocalDeck 1.0.0')) ?></b><p><?= e(t('Zie je een fout of ontbreekt een stap? Meld het via Support → Wiki of documentatie.', 'Found an error or missing step? Report it through Support → Wiki or documentation.')) ?></p></div></div>
        <div class="doc-feedback" data-doc-feedback><div><b><?= e(t('Was deze uitleg nuttig?', 'Was this article helpful?')) ?></b><p><?= e(t('Je keuze wordt niet gevolgd of opgeslagen.', 'Your choice is not tracked or stored.')) ?></p></div><button type="button" data-doc-helpful><?= e(t('Ja, duidelijk', 'Yes, clear')) ?></button><a href="<?= e(with_language('community.php?type=docs&title=' . rawurlencode(t('Documentatie verbeteren: ', 'Improve documentation: ') . content_text($article['title'])))) ?>"><?= e(t('Nee, verbetering melden', 'No, report an improvement')) ?></a></div>
        <div class="article-next">
            <span><?= e(t('Volgende onderwerp', 'Next topic')) ?></span>
            <?php $keys = array_keys($articles); $next = $keys[(array_search($slug, $keys, true) + 1) % count($keys)]; ?>
            <a href="<?= e(with_language('wiki.php?article=' . $next)) ?>"><b><?= e(content_text($articles[$next]['title'])) ?></b><span>→</span></a>
        </div>
    </article>
</div>
<?php require __DIR__ . '/inc/footer.php'; ?>
