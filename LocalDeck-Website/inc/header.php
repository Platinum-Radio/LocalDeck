<?php
$pageTitle = $pageTitle ?? 'LocalDeck';
$pageDescription = $pageDescription ?? t('De moderne lokale PHP-ontwikkelomgeving voor Windows.', 'The modern local PHP development environment for Windows.');
$pageKey = $pageKey ?? 'home';
$canonical = site_origin() . (parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/');
?>
<!doctype html>
<html lang="<?= e($language) ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#080b14">
    <meta name="description" content="<?= e($pageDescription) ?>">
    <meta property="og:type" content="website">
    <meta property="og:title" content="<?= e($pageTitle) ?>">
    <meta property="og:description" content="<?= e($pageDescription) ?>">
    <meta property="og:url" content="<?= e($canonical) ?>">
    <meta property="og:image" content="<?= e(site_origin()) ?>/assets/og.png">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">
    <meta name="twitter:card" content="summary_large_image">
    <link rel="canonical" href="<?= e($canonical) ?>">
    <link rel="icon" type="image/png" href="assets/favicon.png">
    <link rel="stylesheet" href="assets/site.css?v=<?= e(LOCALDECK_SITE_VERSION) ?>">
    <?php if ($pageKey === 'downloads'): ?><link rel="stylesheet" href="assets/downloads.css?v=<?= e(LOCALDECK_SITE_VERSION) ?>"><?php endif; ?>
    <script src="assets/site.js?v=<?= e(LOCALDECK_SITE_VERSION) ?>" defer></script>
    <title><?= e($pageTitle) ?></title>
</head>
<body data-page="<?= e($pageKey) ?>">
<a class="skip-link" href="#content"><?= e(t('Naar de inhoud', 'Skip to content')) ?></a>
<header class="site-header">
    <div class="shell header-inner">
        <a class="brand" href="<?= e(with_language('index.php')) ?>" aria-label="LocalDeck home">
            <img src="assets/logo.png" alt="" width="42" height="42">
            <span>Local<span>Deck</span></span>
        </a>
        <button class="menu-button" type="button" aria-expanded="false" aria-controls="main-navigation">
            <span></span><span></span><span></span><b><?= e(t('Menu', 'Menu')) ?></b>
        </button>
        <nav id="main-navigation" class="main-nav" aria-label="<?= e(t('Hoofdnavigatie', 'Main navigation')) ?>">
            <a<?= nav_active('home', $pageKey) ?> href="<?= e(with_language('index.php')) ?>"><?= e(t('Overzicht', 'Overview')) ?></a>
            <a<?= nav_active('wiki', $pageKey) ?> href="<?= e(with_language('wiki.php')) ?>">Wiki</a>
            <a<?= nav_active('community', $pageKey) ?> href="<?= e(with_language('community.php')) ?>"><?= e(t('Community', 'Community')) ?></a>
            <a<?= nav_active('downloads', $pageKey) ?> href="<?= e(with_language('downloads.php')) ?>">Downloads</a>
            <span class="language-switch" aria-label="<?= e(t('Taal kiezen', 'Choose language')) ?>">
                <a href="<?= e(language_switch_url('nl')) ?>"<?= $language === 'nl' ? ' aria-current="true"' : '' ?>>NL</a>
                <i></i>
                <a href="<?= e(language_switch_url('en')) ?>"<?= $language === 'en' ? ' aria-current="true"' : '' ?>>EN</a>
            </span>
        </nav>
    </div>
</header>
<main id="content">
