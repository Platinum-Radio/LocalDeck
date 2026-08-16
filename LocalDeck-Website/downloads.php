<?php
declare(strict_types=1);
require __DIR__ . '/inc/bootstrap.php';

$catalog = release_catalog();
$releases = sorted_releases($catalog['releases'] ?? []);
$latest = $releases[0] ?? ['version' => '1.0.0', 'published' => false, 'artifacts' => []];
$olderReleases = array_slice($releases, 1);
$artifactPresentation = [
    'setup-x64' => [t('LocalDeck installeren — EXE', 'Install LocalDeck — EXE'), t('Start het EXE-bestand en doorloop de Windows-installatiewizard.', 'Run the EXE file and follow the Windows installation wizard.'), '↓', 'EXE · INSTALLEREN'],
    'zip-x64' => [t('Zonder installatie — ZIP uitpakken', 'No installation — extract ZIP'), t('Pak het ZIP-bestand uit in een map naar keuze en start daarna LocalDeck.exe.', 'Extract the ZIP file to any folder and then start LocalDeck.exe.'), '◇', 'ZIP · UITPAKKEN'],
];

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
        <div class="version-card">
            <span><?= e(t('NIEUWSTE VERSIE', 'LATEST VERSION')) ?></span>
            <strong><?= e((string) $latest['version']) ?></strong>
            <i class="preview-badge"><?= e(($latest['published'] ?? false) ? t('STABIEL', 'STABLE') : t('INTERNE PREVIEW', 'INTERNAL PREVIEW')) ?></i>
            <small><?= e(format_release_date($latest['releasedAt'] ?? null)) ?></small>
        </div>
    </div>
</section>

<section class="section shell">
    <div class="download-summary">
        <div>
            <span class="download-stat"><b><?= e((string) download_count((string) $latest['version'])) ?></b><?= e(t('gestarte downloads van deze versie', 'downloads initiated for this version')) ?></span>
            <span class="download-stat"><b><?= e((string) (download_statistics()['total'] ?? 0)) ?></b><?= e(t('gestarte downloads in totaal', 'downloads initiated in total')) ?></span>
        </div>
        <p><?= e(t('De teller registreert alleen een downloadstart nadat het gekozen releasebestand werkelijk bestaat. Er worden geen IP-adressen of trackingcookies opgeslagen.', 'The counter records a download start only after the selected release file actually exists. No IP addresses or tracking cookies are stored.')) ?></p>
    </div>

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
                <small><?= $artifact && !empty($artifact['sha256']) ? 'SHA-256: ' . e(substr((string) $artifact['sha256'], 0, 16)) . '…' : e(t('Hash verschijnt na definitieve publicatie.', 'Hash appears after final publication.')) ?></small>
            </article>
        <?php endforeach; ?>
    </div>

    <div class="integrity-grid">
        <article><span>1</span><div><b><?= e(t('Bestand aanwezig', 'File present')) ?></b><p><?= e(t('De teller weigert ontbrekende of onbekende bestanden.', 'The counter rejects missing or unknown files.')) ?></p></div></article>
        <article><span>2</span><div><b>SHA-256</b><p><?= e(t('LocalDeck vergelijkt het bestand met de hash uit de feed.', 'LocalDeck compares the file with the hash in the feed.')) ?></p></div></article>
        <article><span>3</span><div><b>Windows x64</b><p><?= e(t('De EXE en ZIP zijn gereed voor Windows 10 en Windows 11.', 'The EXE and ZIP are ready for Windows 10 and Windows 11.')) ?></p></div></article>
    </div>
</section>

<section class="section release-history" id="older-releases">
    <div class="shell">
        <div class="section-heading archive-heading">
            <div>
                <span class="eyebrow"><i></i><?= e(t('Downloadarchief', 'Download archive')) ?></span>
                <h2><?= e(t('Oudere versies', 'Older versions')) ?></h2>
                <p><?= e(t('Kies een eerdere versie als EXE-installatie of als ZIP om uit te pakken. De nieuwste oudere versie staat steeds bovenaan.', 'Choose an earlier version as an EXE installer or as a ZIP to extract. The newest previous version is always listed first.')) ?></p>
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
                    <details class="archive-release">
                        <summary>
                            <span class="archive-chevron" aria-hidden="true">›</span>
                            <span class="archive-version"><b>LocalDeck <?= e($version) ?></b><small><?= e(format_release_date($release['releasedAt'] ?? null)) ?></small></span>
                            <span class="archive-channel"><?= e(ucfirst((string) ($release['channel'] ?? 'stable'))) ?></span>
                            <span class="archive-download-total"><b><?= e((string) download_count($version)) ?></b><small><?= e(t('downloads', 'downloads')) ?></small></span>
                            <span class="archive-status <?= $availableCount > 0 ? 'available' : 'unavailable' ?>"><i></i><?= e($availableCount > 0 ? t('Beschikbaar', 'Available') : (($release['published'] ?? false) ? t('Bestand ontbreekt', 'File missing') : t('Preview', 'Preview'))) ?></span>
                        </summary>
                        <div class="archive-release-body">
                            <?php if (!empty($release['notes'])): ?><p class="archive-notes"><?= e((string) $release['notes']) ?></p><?php endif; ?>
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
