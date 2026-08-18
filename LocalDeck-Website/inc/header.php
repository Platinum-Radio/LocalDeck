<?php
$pageTitle = $pageTitle ?? 'LocalDeck';
$pageDescription = $pageDescription ?? t('De moderne lokale PHP-ontwikkelomgeving voor Windows.', 'The modern local PHP development environment for Windows.');
$pageKey = $pageKey ?? 'home';
$canonicalPath = $canonicalPath ?? (parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/');
$canonicalParams = $canonicalParams ?? [];
$canonical = site_origin() . $canonicalPath . '?' . http_build_query(array_merge($canonicalParams, ['lang' => $language]));
$alternateNl = site_origin() . $canonicalPath . '?' . http_build_query(array_merge($canonicalParams, ['lang' => 'nl']));
$alternateEn = site_origin() . $canonicalPath . '?' . http_build_query(array_merge($canonicalParams, ['lang' => 'en']));
$latestSiteRelease = latest_published_release();
$structuredData = array_values(array_filter([
    [
        '@type' => 'Organization',
        '@id' => site_origin() . '/#organization',
        'name' => 'LocalDeck',
        'url' => site_origin(),
        'logo' => site_origin() . '/assets/logo.png',
        'sameAs' => [LOCALDECK_GITHUB_URL],
    ],
    [
        '@type' => 'WebSite',
        '@id' => site_origin() . '/#website',
        'name' => 'LocalDeck',
        'url' => site_origin(),
        'inLanguage' => ['nl', 'en'],
        'publisher' => ['@id' => site_origin() . '/#organization'],
    ],
    [
        '@type' => 'SoftwareApplication',
        '@id' => site_origin() . '/#software',
        'name' => 'LocalDeck',
        'url' => site_origin(),
        'applicationCategory' => 'DeveloperApplication',
        'operatingSystem' => 'Windows 11, Windows 10 x64',
        'softwareVersion' => (string) ($latestSiteRelease['version'] ?? LOCALDECK_SITE_VERSION),
        'description' => $pageDescription,
        'downloadUrl' => site_origin() . '/downloads.php?lang=' . $language,
        'isAccessibleForFree' => true,
        'publisher' => ['@id' => site_origin() . '/#organization'],
        'storageRequirements' => '4 GB available disk space',
        'featureList' => 'Apache, PHP 8.2-8.5, MySQL, phpMyAdmin, Mailpit, Redis, automatic local HTTPS',
        'offers' => ['@type' => 'Offer', 'price' => '0', 'priceCurrency' => 'EUR'],
    ],
    $structuredData ?? null,
]));
?>
<!doctype html>
<html lang="<?= e($language) ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#080b14">
    <meta name="description" content="<?= e($pageDescription) ?>">
    <meta name="author" content="LocalDeck">
    <meta name="robots" content="index,follow,max-image-preview:large">
    <meta property="og:type" content="website">
    <meta property="og:title" content="<?= e($pageTitle) ?>">
    <meta property="og:description" content="<?= e($pageDescription) ?>">
    <meta property="og:site_name" content="LocalDeck">
    <meta property="og:url" content="<?= e($canonical) ?>">
    <meta property="og:image" content="<?= e(site_origin()) ?>/assets/og.png">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">
    <meta property="og:locale" content="<?= $language === 'en' ? 'en_US' : 'nl_NL' ?>">
    <meta name="twitter:card" content="summary_large_image">
    <link rel="canonical" href="<?= e($canonical) ?>">
    <link rel="alternate" hreflang="nl" href="<?= e($alternateNl) ?>">
    <link rel="alternate" hreflang="en" href="<?= e($alternateEn) ?>">
    <link rel="alternate" hreflang="x-default" href="<?= e($alternateEn) ?>">
    <link rel="icon" type="image/png" href="/assets/favicon.png">
    <link rel="stylesheet" href="/assets/site.css?v=<?= e(LOCALDECK_SITE_VERSION) ?>">
    <?php if ($pageKey === 'downloads'): ?><link rel="stylesheet" href="/assets/downloads.css?v=<?= e(LOCALDECK_SITE_VERSION) ?>"><?php endif; ?>
    <link rel="stylesheet" href="/assets/enhancements.css?v=<?= e(LOCALDECK_SITE_VERSION) ?>">
    <script src="/assets/site.js?v=<?= e(LOCALDECK_SITE_VERSION) ?>" defer></script>
    <script type="application/ld+json"><?= json_encode(['@context' => 'https://schema.org', '@graph' => $structuredData], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?></script>
    <title><?= e($pageTitle) ?></title>
</head>
<body data-page="<?= e($pageKey) ?>">
<a class="skip-link" href="#content"><?= e(t('Naar de inhoud', 'Skip to content')) ?></a>
<header class="site-header">
    <div class="shell header-inner">
        <a class="brand" href="<?= e(with_language('index.php')) ?>" aria-label="LocalDeck home">
            <img src="/assets/logo.png" alt="" width="42" height="42">
            <span>Local<span>Deck</span></span>
        </a>
        <button class="menu-button" type="button" aria-expanded="false" aria-controls="main-navigation">
            <span></span><span></span><span></span><b><?= e(t('Menu', 'Menu')) ?></b>
        </button>
        <nav id="main-navigation" class="main-nav" aria-label="<?= e(t('Hoofdnavigatie', 'Main navigation')) ?>">
            <a<?= nav_active('home', $pageKey) ?> href="<?= e(with_language('index.php')) ?>"><?= e(t('Overzicht', 'Overview')) ?></a>
            <a<?= nav_active('updates', $pageKey) ?> href="<?= e(with_language('updates.php')) ?>"><?= e(t('Updates', 'Updates')) ?></a>
            <a<?= nav_active('wiki', $pageKey) ?> href="<?= e(with_language('wiki.php')) ?>">Wiki</a>
            <a<?= nav_active('support', $pageKey) ?> href="<?= e(with_language('community.php')) ?>"><?= e(t('Support', 'Support')) ?></a>
            <a<?= nav_active('downloads', $pageKey) ?> href="<?= e(with_language('downloads.php')) ?>">Downloads</a>
            <a class="github-nav" href="<?= e(LOCALDECK_GITHUB_URL) ?>" target="_blank" rel="noopener">GitHub <span aria-hidden="true">↗</span></a>
            <span class="language-switch" aria-label="<?= e(t('Taal kiezen', 'Choose language')) ?>">
                <a href="<?= e(language_switch_url('nl')) ?>"<?= $language === 'nl' ? ' aria-current="true"' : '' ?>>NL</a>
                <i></i>
                <a href="<?= e(language_switch_url('en')) ?>"<?= $language === 'en' ? ' aria-current="true"' : '' ?>>EN</a>
            </span>
        </nav>
    </div>
</header>
<main id="content">
