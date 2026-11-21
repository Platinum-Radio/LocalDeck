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
if (version_compare((string) ($latest['version'] ?? '0.0.0'), '1.2.0', '>=')) {
    assert_site(($latest['updateStrategy'] ?? '') === 'component-aware', 'De nieuwste release mist de componentbewuste updatestrategie.');
    assert_site(isset($latest['runtimeChanged']) && is_bool($latest['runtimeChanged']), 'De nieuwste release mist een expliciete runtime-impact.');
    assert_site(!empty($latest['components']) && is_array($latest['components']), 'De nieuwste release mist componentinformatie.');
}
foreach ($latest['artifacts'] ?? [] as $artifact) {
    assert_site(is_array($artifact), 'Ongeldige artifactmetadata.');
    assert_site(release_artifact_is_available($latest, $artifact), 'Een publieke download ontbreekt: ' . (string) ($artifact['id'] ?? 'onbekend'));
    assert_site(preg_match('/^[a-f0-9]{64}$/i', (string) ($artifact['sha256'] ?? '')) === 1, 'Een publieke download heeft geen geldige SHA-256.');
    $publicUrl = release_artifact_public_url($latest, $artifact);
    assert_site($publicUrl !== null && str_starts_with($publicUrl, '/downloads/releases/'), 'Een downloadredirect verwijst niet naar de publieke releasemap.');
}

$articles = require __DIR__ . '/../inc/content.php';
$guides = require __DIR__ . '/../inc/guides.php';
assert_site(count($articles) >= 10, 'De wiki bevat minder dan tien onderwerpen.');
assert_site(count($guides) >= 6, 'De gidsensectie is niet compleet.');
$updatesPage = file_get_contents(__DIR__ . '/../updates.php');
assert_site($updatesPage !== false && str_contains($updatesPage, 'published_releases()'), 'De updatepagina gebruikt niet de centrale releasebron.');
assert_site(str_contains((string) $updatesPage, 'release_changes($release)'), 'De versiegeschiedenis toont geen gestructureerde wijzigingen.');
assert_site(str_contains((string) $updatesPage, '$latestComponents'), 'De updatepagina toont geen componentinformatie.');
assert_site(str_contains((string) $updatesPage, "t('Updatesysteem'"), 'De updatepagina is niet tweetalig ingericht.');
$betaFeed = read_json_file(__DIR__ . '/../downloads/beta.json');
$stableFeed = read_json_file(__DIR__ . '/../downloads/windows.json');
foreach ([$betaFeed, $stableFeed] as $feed) {
    assert_site(($feed['schemaVersion'] ?? 0) === 2, 'Een updatefeed gebruikt niet schema 2.');
    assert_site(!empty($feed['releaseNotesUrl']) && str_starts_with((string) $feed['releaseNotesUrl'], 'https://'), 'Een updatefeed mist de veilige release-noteslink.');
    assert_site(!empty($feed['history']) && is_array($feed['history']), 'Een updatefeed mist versiegeschiedenis.');
    assert_site(!empty($feed['history'][0]['changes']) && !empty($feed['history'][0]['changesEn']), 'De updategeschiedenis is niet tweetalig of mist wijzigingen.');
}
$downloadsPage = file_get_contents(__DIR__ . '/../downloads.php');
assert_site($downloadsPage !== false && str_contains($downloadsPage, 'class="version-picker"'), 'De downloadpagina toont geen zichtbare versiekeuze.');
assert_site(str_contains((string) $downloadsPage, 'id="<?= e($releaseAnchor($release)) ?>" open'), 'Gepubliceerde oudere versies zijn niet standaard zichtbaar.');
assert_site(str_contains((string) $downloadsPage, 'EXE + ZIP'), 'De versiekeuze vermeldt de twee downloadvormen niet.');
$header = file_get_contents(__DIR__ . '/../inc/header.php');
assert_site($header !== false && str_contains($header, "filemtime(\$path)"), 'Gewijzigde website-assets krijgen geen automatische cacheverversing.');
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
