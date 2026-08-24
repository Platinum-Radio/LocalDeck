<?php
declare(strict_types=1);

$_SERVER['REMOTE_ADDR'] = '127.0.0.1';
$_SERVER['HTTP_HOST'] = '127.0.0.1:8793';
require __DIR__ . '/../inc/bootstrap.php';

function assert_site(bool $condition, string $message): void
{
    if (!$condition) {
        throw new RuntimeException($message);
    }
}

$published = published_releases();
assert_site($published !== [], 'Er is geen publieke release beschikbaar.');
assert_site(count(array_filter($published, static fn (array $release): bool => empty($release['published']))) === 0, 'Een niet-gepubliceerde release lekt in de publieke catalogus.');

$latest = latest_published_release();
assert_site(($latest['version'] ?? '') === LOCALDECK_SITE_VERSION, 'De website- en releaseversie lopen niet gelijk.');
foreach ($latest['artifacts'] ?? [] as $artifact) {
    assert_site(is_array($artifact), 'Ongeldige artifactmetadata.');
    assert_site(release_artifact_is_available($latest, $artifact), 'Een publieke download ontbreekt: ' . (string) ($artifact['id'] ?? 'onbekend'));
    assert_site(preg_match('/^[a-f0-9]{64}$/i', (string) ($artifact['sha256'] ?? '')) === 1, 'Een publieke download heeft geen geldige SHA-256.');
}

$articles = require __DIR__ . '/../inc/content.php';
$guides = require __DIR__ . '/../inc/guides.php';
assert_site(count($articles) >= 10, 'De wiki bevat minder dan tien onderwerpen.');
assert_site(count($guides) >= 6, 'De gidsensectie is niet compleet.');
$updatesPage = file_get_contents(__DIR__ . '/../updates.php');
assert_site($updatesPage !== false && str_contains($updatesPage, '1.1.0-test.1'), 'De laatste testrelease ontbreekt op de updatepagina.');
assert_site(str_contains((string) $updatesPage, "t('Laatste updates'"), 'De updatepagina is niet tweetalig ingericht.');
$signingPage = file_get_contents(__DIR__ . '/../code-signing.php');
assert_site($signingPage !== false && str_contains($signingPage, 'Free code signing provided by SignPath.io, certificate by SignPath Foundation.'), 'De vereiste SignPath-bronvermelding ontbreekt.');
assert_site(str_contains((string) $signingPage, 'not yet signed by SignPath Foundation'), 'De SignPath-pagina vermeldt de huidige status niet eerlijk.');
$footer = file_get_contents(__DIR__ . '/../inc/footer.php');
assert_site($footer !== false && str_contains($footer, 'Code signing policy'), 'De homepagefooter linkt niet herkenbaar naar het code-signingbeleid.');

$checks = website_health_checks();
$checkIds = array_column($checks, 'id');
foreach (['website', 'wiki', 'updates', 'downloads', 'counter', 'support', 'https'] as $requiredCheck) {
    assert_site(in_array($requiredCheck, $checkIds, true), 'Statuscontrole ontbreekt: ' . $requiredCheck);
}
assert_site(!in_array(false, array_column($checks, 'healthy'), true), 'Een lokale websitecomponent is niet gezond.');

echo "website-content: OK\n";
