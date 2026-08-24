<?php
declare(strict_types=1);
require __DIR__ . '/inc/bootstrap.php';
$pageKey = 'code-signing';
$pageTitle = 'Code signing — LocalDeck';
$pageDescription = t('Het openbare bouw-, ondertekenings- en verificatiebeleid van LocalDeck.', 'The public LocalDeck build, signing, and verification policy.');
require __DIR__ . '/inc/header.php';
?>
<section class="page-hero compact"><div class="shell"><span class="eyebrow"><i></i>SOFTWARE SUPPLY CHAIN</span><h1><?= e(t('Controleerbaar gebouwd en ondertekend.', 'Verifiably built and signed.')) ?></h1><p><?= e(t('LocalDeck bereidt de aanvraag voor gratis open-source-ondertekening via SignPath Foundation voor.', 'LocalDeck is preparing its application for free open-source signing through SignPath Foundation.')) ?></p></div></section>
<article class="shell legal-copy">
    <section>
        <h2><?= e(t('Huidige status', 'Current status')) ?></h2>
        <p><?= e(t('De bestaande publieke testbestanden zijn nog niet door SignPath Foundation ondertekend. We noemen een bestand pas ondertekend nadat de vertrouwde GitHub-workflow, SignPath-controles en Authenticode-verificatie allemaal zijn geslaagd.', 'Existing public test files are not yet signed by SignPath Foundation. A file is described as signed only after the trusted GitHub workflow, SignPath checks, and Authenticode verification have all succeeded.')) ?></p>
        <p><strong>Free code signing provided by SignPath.io, certificate by SignPath Foundation.</strong></p>
        <p><?= e(t('Deze verplichte bronvermelding beschrijft de geplande sponsoring na goedkeuring en is geen bewering dat de huidige release al is ondertekend.', 'This required attribution describes the planned sponsorship after approval and is not a claim that the current release is already signed.')) ?></p>
    </section>
    <section>
        <h2><?= e(t('Open bron en herkomst', 'Open source and provenance')) ?></h2>
        <p><?= e(t('LocalDeck gebruikt de Apache-2.0-licentie. De broncode, buildscripts, dependency-lock en vastgezette runtimebronnen staan openbaar op GitHub. Ondertekende releases moeten vanaf een onveranderlijke tag op een door GitHub gehoste Windows-runner worden gebouwd.', 'LocalDeck uses the Apache-2.0 license. Source code, build scripts, dependency lock, and pinned runtime sources are public on GitHub. Signed releases must be built from an immutable tag on a GitHub-hosted Windows runner.')) ?></p>
        <a class="button secondary" href="<?= e(LOCALDECK_GITHUB_URL) ?>/blob/main/CODE_SIGNING_POLICY.md" target="_blank" rel="noopener"><?= e(t('Volledig ondertekeningsbeleid', 'Full signing policy')) ?> →</a>
    </section>
    <section>
        <h2><?= e(t('Een download controleren', 'Verify a download')) ?></h2>
        <p><?= e(t('Vergelijk eerst de volledige SHA-256 met de waarde op de downloadpagina. Zodra ondertekende releases beschikbaar zijn, moet Get-AuthenticodeSignature daarnaast de status Valid en de verwachte uitgever tonen.', 'First compare the complete SHA-256 with the value on the download page. Once signed releases are available, Get-AuthenticodeSignature must also show status Valid and the expected publisher.')) ?></p>
        <pre><code>Get-FileHash -Algorithm SHA256 ".\LocalDeck-Setup.exe"
Get-AuthenticodeSignature ".\LocalDeck-Setup.exe" | Format-List</code></pre>
    </section>
    <section>
        <h2><?= e(t('Beleid en meldingen', 'Policies and reporting')) ?></h2>
        <p><?= e(t('Bekijk ook het privacybeleid, beveiligingsmodel en de openbare GitHub-licentie. Meld mogelijke kwetsbaarheden altijd privé.', 'Also review the privacy policy, security model, and public GitHub license. Always report potential vulnerabilities privately.')) ?></p>
        <p><a href="<?= e(with_language('privacy.php')) ?>">Privacy</a> · <a href="<?= e(with_language('security.php')) ?>"><?= e(t('Veiligheid', 'Security')) ?></a> · <a href="<?= e(LOCALDECK_GITHUB_URL) ?>/blob/main/LICENSE" target="_blank" rel="noopener">Apache-2.0</a></p>
    </section>
</article>
<?php require __DIR__ . '/inc/footer.php'; ?>
