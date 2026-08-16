<?php
declare(strict_types=1);
require __DIR__ . '/inc/bootstrap.php';
$pageKey = 'privacy';
$pageTitle = 'Privacy — LocalDeck';
$pageDescription = t('Hoe de LocalDeck-website omgaat met downloads, community-inzendingen en privacy.', 'How the LocalDeck website handles downloads, community submissions, and privacy.');
require __DIR__ . '/inc/header.php';
?>
<section class="page-hero compact"><div class="shell"><span class="eyebrow"><i></i>PRIVACY BY DEFAULT</span><h1><?= e(t('Zo weinig mogelijk gegevens.', 'As little data as possible.')) ?></h1><p><?= e(t('De websitepreview gebruikt geen advertenties, analytics of trackingcookies.', 'The website preview uses no ads, analytics, or tracking cookies.')) ?></p></div></section>
<article class="shell legal-copy">
    <section><h2><?= e(t('Downloadteller', 'Download counter')) ?></h2><p><?= e(t('Wanneer een bestaand releasebestand via de officiële knop wordt opgevraagd, verhogen we alleen het totaal voor die versie en uitvoering. Er wordt geen IP-adres, browserprofiel of unieke gebruikerscode opgeslagen. Een getelde download betekent dat de overdracht is gestart, niet dat installatie is afgerond.', 'When an existing release file is requested through the official button, only the total for that version and edition is incremented. No IP address, browser profile, or unique user identifier is stored. A counted download means transfer started, not that installation completed.')) ?></p></section>
    <section><h2><?= e(t('Community-inzendingen', 'Community submissions')) ?></h2><p><?= e(t('Titel, uitleg, gekozen categorie en optioneel naam en e-mailadres worden lokaal in de moderatiewachtrij bewaard. Deel nooit wachtwoorden, tokens, licentiesleutels of andere geheime informatie. Bij de openbare community wordt dit beleid uitgebreid met bewaartermijnen en accountbeheer.', 'Title, description, selected category, and optional name and email address are stored locally in the moderation queue. Never share passwords, tokens, license keys, or other secret information. For the public community this policy will be extended with retention periods and account management.')) ?></p></section>
    <section><h2><?= e(t('Taalvoorkeur', 'Language preference')) ?></h2><p><?= e(t('De enige functionele cookie onthoudt of je Nederlands of Engels hebt gekozen. Deze cookie bevat geen identificerende informatie.', 'The only functional cookie remembers whether you selected Dutch or English. It contains no identifying information.')) ?></p></section>
</article>
<?php require __DIR__ . '/inc/footer.php'; ?>

