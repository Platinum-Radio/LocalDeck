<?php
declare(strict_types=1);
require __DIR__ . '/inc/bootstrap.php';

$releases = published_releases();
$latest = $releases[0] ?? ['version' => '1.0.0', 'published' => false, 'artifacts' => []];
$olderReleases = array_slice($releases, 1);
$latestIsPrerelease = ($latest['channel'] ?? 'stable') !== 'stable';
$artifactPresentation = [
    'setup-x64' => [t('LocalDeck installeren — EXE', 'Install LocalDeck — EXE'), t('Start het EXE-bestand en doorloop de Windows-installatiewizard.', 'Run the EXE file and follow the Windows installation wizard.'), '↓', 'EXE · INSTALLEREN'],
    'zip-x64' => [t('Zonder installatie — ZIP uitpakken', 'No installation — extract ZIP'), t('Pak het ZIP-bestand uit in een map naar keuze en start daarna LocalDeck.exe.', 'Extract the ZIP file to any folder and then start LocalDeck.exe.'), '◇', 'ZIP · UITPAKKEN'],
];
$releaseAnchor = static function (array $release): string {
    $version = strtolower((string) ($release['version'] ?? 'onbekend'));
    return 'release-' . trim((string) preg_replace('/[^a-z0-9]+/i', '-', $version), '-');
};

$pageKey = 'downloads';
$pageTitle = 'LocalDeck Downloads — Windows';
$pageDescription = t('Veilige LocalDeck-downloads per versie met controleerbare hashes en downloadaantallen.', 'Safe LocalDeck downloads by version with verifiable hashes and download counts.');
require __DIR__ . '/inc/header.php';
?>
<section class="page-hero download-hero">
    <div class="shell hero-grid small">
        <div>
            <span class="eyebrow"><i></i>LOCALDECK FOR WINDOWS</span>
            <h1><?= e(t('Download met zekerheid.', 'Download with confidence.')) ?></h1>
            <p><?= e(t('Kies uit precies twee bestanden: de EXE om LocalDeck te installeren of de ZIP om uit te pakken en direct te starten. De nieuwste versie staat altijd bovenaan en bij ieder bestand staat de SHA-256-hash.', 'Choose from exactly two files: the EXE to install LocalDeck or the ZIP to extract and run directly. The newest version is always shown first and every file includes its SHA-256 hash.')) ?></p>
        </div>
        <aside class="version-picker" aria-label="<?= e(t('Beschikbare LocalDeck-versies', 'Available LocalDeck versions')) ?>">
            <div class="version-picker-heading"><span><?= e(t('KIES JE VERSIE', 'CHOOSE YOUR VERSION')) ?></span><small><?= e((string) count($releases)) ?> <?= e(t('versies beschikbaar', 'versions available')) ?></small></div>
            <?php foreach ($releases as $index => $release): ?>
                <?php $channel = (string) ($release['channel'] ?? 'stable'); ?>
                <a class="version-choice<?= $index === 0 ? ' current' : '' ?>" href="#<?= e($releaseAnchor($release)) ?>">
                    <span class="version-choice-copy"><small><?= e($channel === 'stable' ? t('STABIELE VERSIE', 'STABLE VERSION') : t('TESTVERSIE', 'TEST VERSION')) ?></small><strong><?= e((string) ($release['version'] ?? '—')) ?></strong></span>
                    <span class="version-choice-format">EXE + ZIP <i aria-hidden="true">↓</i></span>
                </a>
            <?php endforeach; ?>
        </aside>
    </div>
</section>

<section class="section shell" id="<?= e($releaseAnchor($latest)) ?>">
    <?php $totalDownloads = (int) (download_statistics()['total'] ?? 0); ?>
    <div class="download-summary<?= $totalDownloads === 0 ? ' new-release' : '' ?>">
        <?php if ($totalDownloads > 0): ?><div><span class="download-stat"><b><?= e((string) download_count((string) $latest['version'])) ?></b><?= e(t('gestarte downloads van deze versie', 'downloads initiated for this version')) ?></span><span class="download-stat"><b><?= e((string) $totalDownloads) ?></b><?= e(t('gestarte downloads in totaal', 'downloads initiated in total')) ?></span></div><?php else: ?><div class="new-release-label"><span>✦</span><b><?= e(t('Nieuwe publieke release', 'New public release')) ?></b></div><?php endif; ?>
        <p><?= e(t('De teller registreert alleen een downloadstart nadat het gekozen releasebestand werkelijk bestaat. Er worden geen IP-adressen of trackingcookies opgeslagen.', 'The counter records a download start only after the selected release file actually exists. No IP addresses or tracking cookies are stored.')) ?></p>
    </div>

    <?php if (!empty($latest['warning'])): ?><div class="notice-banner download-help"><span>i</span><div><b><?= e(t('Testversie voor vroege gebruikers', 'Test release for early adopters')) ?></b><p><?= e((string) $latest['warning']) ?></p><a href="<?= e(with_language('updates.php')) ?>"><?= e(t('Bekijk alle wijzigingen', 'See all changes')) ?> →</a></div></div><?php endif; ?>

    <div class="section-heading">
        <span class="eyebrow"><i></i><?= e(t('Nieuwste versie', 'Latest version')) ?></span>
        <h2>LocalDeck <?= e((string) $latest['version']) ?></h2>
        <p><?= e(t('EXE = installeren. ZIP = alleen uitpakken. Beide bestanden bevatten dezelfde complete LocalDeck-omgeving voor Windows x64.', 'EXE = install. ZIP = extract only. Both files contain the same complete LocalDeck environment for Windows x64.')) ?></p>
    </div>

    <div class="download-grid">
        <?php foreach ($artifactPresentation as $id => [$title, $description, $symbol, $format]): ?>
            <?php $artifact = find_release_artifact($latest, $id); ?>
            <?php $ready = $artifact !== null && release_artifact_is_available($latest, $artifact); ?>
            <article class="download-card<?= $ready ? ' ready' : '' ?>">
                <span class="download-symbol"><?= e($symbol) ?></span>
                <div>
                    <span class="platform-label"><?= e($format) ?> · WINDOWS X64</span>
                    <h3><?= e($title) ?></h3>
                    <p><?= e($description) ?></p>
                </div>
                <dl>
                    <div><dt><?= e(t('Versie', 'Version')) ?></dt><dd><?= e((string) $latest['version']) ?></dd></div>
                    <div><dt><?= e(t('Grootte', 'Size')) ?></dt><dd><?= e((string) ($artifact['sizeLabel'] ?? '—')) ?></dd></div>
                    <div><dt><?= e(t('Downloads', 'Downloads')) ?></dt><dd><?= e((string) download_count((string) $latest['version'], $id)) ?></dd></div>
                </dl>
                <?php if ($ready): ?>
                    <a class="button primary" href="download.php?version=<?= e(rawurlencode((string) $latest['version'])) ?>&amp;artifact=<?= e(rawurlencode($id)) ?>"><?= e(t('Downloaden', 'Download')) ?></a>
                <?php else: ?>
                    <span class="button disabled" aria-disabled="true"><?= e(t('Nog niet gepubliceerd', 'Not published yet')) ?></span>
                <?php endif; ?>
                <?php if ($artifact && !empty($artifact['sha256'])): ?><div class="hash-box"><span>SHA-256</span><code><?= e((string) $artifact['sha256']) ?></code><button type="button" data-copy="<?= e((string) $artifact['sha256']) ?>"><?= e(t('Kopiëren', 'Copy')) ?></button></div><?php else: ?><small><?= e(t('Hash verschijnt na definitieve publicatie.', 'Hash appears after final publication.')) ?></small><?php endif; ?>
            </article>
        <?php endforeach; ?>
    </div>

    <div class="release-notes-panel"><div><span class="eyebrow"><i></i><?= e(t('Release notes', 'Release notes')) ?></span><h2><?= e(t('Nieuw in LocalDeck ', 'New in LocalDeck ')) ?><?= e((string) $latest['version']) ?></h2><p><?= e(release_text($latest, 'notes')) ?></p></div><a class="button secondary" href="<?= e(with_language('updates.php')) ?>"><?= e(t('Alle updates', 'All updates')) ?> →</a></div>

    <div class="requirements-panel"><div><span>▣</span><b>Windows 11 x64</b><small><?= e(t('Windows 10 x64 blijft compatibel', 'Windows 10 x64 remains compatible')) ?></small></div><div><span>◫</span><b><?= e(t('Minimaal 4 GB vrij', 'At least 4 GB free')) ?></b><small><?= e(t('Voor runtime en tijdelijke extractie', 'For runtime and temporary extraction')) ?></small></div><div><span>○</span><b><?= e(t('Geen account nodig', 'No account required')) ?></b><small><?= e(t('Geen activatie of licentiesleutel', 'No activation or license key')) ?></small></div><div><span>↻</span><b><?= e(t('Eenmalige eerste start', 'One-time first start')) ?></b><small><?= e(t('Configuratie en certificaatvertrouwen', 'Configuration and certificate trust')) ?></small></div></div>

    <div class="verify-panel"><div><span class="eyebrow"><i></i><?= e(t('Zelf controleren', 'Verify it yourself')) ?></span><h2><?= e(t('Vergelijk de SHA-256 in PowerShell.', 'Compare the SHA-256 in PowerShell.')) ?></h2><p><?= e(t('Open PowerShell in de downloadmap, voer de opdracht uit en vergelijk de volledige waarde met de hash hierboven.', 'Open PowerShell in the download folder, run the command, and compare the complete value with the hash above.')) ?></p></div><div class="verify-commands"><?php foreach ($artifactPresentation as $id => $_presentation): ?><?php $artifact = find_release_artifact($latest, $id); if (!$artifact || empty($artifact['file'])) continue; $filename = basename(str_replace('\\', '/', (string) $artifact['file'])); ?><div><code>Get-FileHash -Algorithm SHA256 ".\<?= e($filename) ?>"</code><button type="button" data-copy="Get-FileHash -Algorithm SHA256 &quot;.\<?= e($filename) ?>&quot;"><?= e(t('Kopiëren', 'Copy')) ?></button></div><?php endforeach; ?></div></div>

    <div class="notice-banner download-help"><span>?</span><div><b><?= e(t('Windows toont een beveiligingsmelding?', 'Windows shows a security notice?')) ?></b><p><?= e(t('Controleer eerst of het bestand van LocalDeck.nl komt en of de SHA-256 klopt. Bekijk daarna de stappen voor Windows-beveiliging in de wiki.', 'First verify that the file came from LocalDeck.nl and that its SHA-256 matches. Then review the Windows security steps in the wiki.')) ?></p><a href="<?= e(with_language('wiki.php?article=windows-beveiliging')) ?>"><?= e(t('Open de veilige uitleg', 'Open the security guide')) ?> →</a></div></div>

    <div class="notice-banner download-help"><span>✓</span><div><b><?= e(t('Open-source ondertekeningsbeleid', 'Open-source signing policy')) ?></b><p>Free code signing provided by SignPath.io, certificate by SignPath Foundation. <?= e(t('De aanvraag wordt nog beoordeeld; bekijk de statuspagina voor de actuele ondertekeningsstatus.', 'The application is still under review; see the status page for the current signing status.')) ?></p><a href="<?= e(with_language('code-signing.php')) ?>"><?= e(t('Bekijk status en verificatie', 'View status and verification')) ?> →</a></div></div>

    <div class="integrity-grid">
        <article><span>1</span><div><b><?= e(t('Bestand aanwezig', 'File present')) ?></b><p><?= e(t('De teller weigert ontbrekende of onbekende bestanden.', 'The counter rejects missing or unknown files.')) ?></p></div></article>
        <article><span>2</span><div><b>SHA-256</b><p><?= e(t('LocalDeck vergelijkt het bestand met de hash uit de feed.', 'LocalDeck compares the file with the hash in the feed.')) ?></p></div></article>
        <article><span>3</span><div><b>Windows x64</b><p><?= e(t('Windows 11 is het primaire platform; Windows 10 x64 blijft compatibel.', 'Windows 11 is the primary platform; Windows 10 x64 remains compatible.')) ?></p></div></article>
    </div>
</section>

<section class="section release-history" id="older-releases">
    <div class="shell">
        <div class="section-heading archive-heading">
            <div>
                <span class="eyebrow"><i></i><?= e(t('Downloadarchief', 'Download archive')) ?></span>
                <h2><?= e(t('Andere beschikbare versies', 'Other available versions')) ?></h2>
                <p><?= e(t('Alle gepubliceerde versies blijven zichtbaar. Kies hieronder direct de EXE-installatie of de ZIP om uit te pakken.', 'Every published version remains visible. Choose the EXE installer or the ZIP to extract directly below.')) ?></p>
            </div>
            <span class="archive-count"><?= e((string) count($olderReleases)) ?> <?= e(t('oudere versies', 'older versions')) ?></span>
        </div>

        <?php if ($olderReleases === []): ?>
            <div class="archive-empty">
                <span aria-hidden="true">↺</span>
                <div>
                    <b><?= e(t('Het versiearchief is klaar voor gebruik.', 'The version archive is ready.')) ?></b>
                    <p><?= e(t('Na publicatie van de volgende versie blijft deze versie hier automatisch als veilige download beschikbaar.', 'After the next version is published, this version automatically remains available here as a safe download.')) ?></p>
                </div>
            </div>
        <?php else: ?>
            <div class="release-archive">
                <?php foreach ($olderReleases as $release): ?>
                    <?php
                    $version = (string) ($release['version'] ?? '0.0.0');
                    $artifacts = array_values(array_filter($release['artifacts'] ?? [], 'is_array'));
                    $availableCount = count(array_filter($artifacts, static fn(array $artifact): bool => release_artifact_is_available($release, $artifact)));
                    ?>
                    <details class="archive-release" id="<?= e($releaseAnchor($release)) ?>" open>
                        <summary>
                            <span class="archive-chevron" aria-hidden="true">›</span>
                            <span class="archive-version"><b>LocalDeck <?= e($version) ?></b><small><?= e(format_release_date($release['releasedAt'] ?? null)) ?></small></span>
                            <span class="archive-channel"><?= e(ucfirst((string) ($release['channel'] ?? 'stable'))) ?></span>
                            <span class="archive-download-total"><b><?= e((string) download_count($version)) ?></b><small><?= e(t('downloads', 'downloads')) ?></small></span>
                            <span class="archive-status <?= $availableCount > 0 ? 'available' : 'unavailable' ?>"><i></i><?= e($availableCount > 0 ? t('Beschikbaar', 'Available') : t('Bestand ontbreekt', 'File missing')) ?></span>
                        </summary>
                        <div class="archive-release-body">
                            <?php if (release_text($release, 'notes') !== ''): ?><p class="archive-notes"><?= e(release_text($release, 'notes')) ?></p><?php endif; ?>
                            <?php if ($artifacts === []): ?>
                                <p class="archive-no-artifacts"><?= e(t('Voor deze versie zijn geen downloadbestanden geregistreerd.', 'No download files are registered for this version.')) ?></p>
                            <?php else: ?>
                                <div class="archive-artifacts">
                                    <?php foreach ($artifacts as $artifact): ?>
                                        <?php
                                        $artifactId = (string) ($artifact['id'] ?? '');
                                        $ready = $artifactId !== '' && release_artifact_is_available($release, $artifact);
                                        $label = (string) ($artifact['label'] ?? ($artifactPresentation[$artifactId][0] ?? $artifactId));
                                        ?>
                                        <article class="archive-artifact">
                                            <div>
                                                <b><?= e($label) ?></b>
                                                <span><?= e((string) ($artifact['sizeLabel'] ?? '—')) ?> · <?= e((string) download_count($version, $artifactId)) ?> <?= e(t('downloads', 'downloads')) ?></span>
                                                <code><?= !empty($artifact['sha256']) ? 'SHA-256 ' . e((string) $artifact['sha256']) : e(t('Hash nog niet beschikbaar', 'Hash not yet available')) ?></code>
                                            </div>
                                            <?php if ($ready): ?>
                                                <a class="button secondary" href="download.php?version=<?= e(rawurlencode($version)) ?>&amp;artifact=<?= e(rawurlencode($artifactId)) ?>"><?= e(t('Downloaden', 'Download')) ?></a>
                                            <?php else: ?>
                                                <span class="button disabled" aria-disabled="true"><?= e(t('Niet beschikbaar', 'Unavailable')) ?></span>
                                            <?php endif; ?>
                                        </article>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </details>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>

<section class="section shell feed-panel">
    <div>
        <span class="eyebrow"><i></i><?= e(t('Voor LocalDeck zelf', 'For LocalDeck itself')) ?></span>
        <h2><?= e(t('Dezelfde map voedt ook de updatepopup.', 'The same folder also powers the update popup.')) ?></h2>
        <p><?= e(t('De desktopapp leest dit machineleesbare JSON-bestand en controleert iedere download verplicht met SHA-256.', 'The desktop app reads this machine-readable JSON file and verifies every download with SHA-256.')) ?></p>
    </div>
    <code><?= e(site_origin()) ?>/downloads/windows.json</code>
    <a class="button secondary" href="downloads/windows.json">JSON <?= e(t('bekijken', 'view')) ?></a>
</section>
<?php require __DIR__ . '/inc/footer.php'; ?>
