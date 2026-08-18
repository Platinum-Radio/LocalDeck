<?php $year = (new DateTimeImmutable())->format('Y'); ?>
</main>
<footer class="site-footer">
    <div class="shell footer-grid">
        <div>
            <a class="brand footer-brand" href="<?= e(with_language('index.php')) ?>"><img src="/assets/logo.png" alt="" width="38" height="38"><span>Local<span>Deck</span></span></a>
            <p><?= e(t('Een moderne, zelfstandige lokale ontwikkelomgeving voor Windows.', 'A modern, self-contained local development environment for Windows.')) ?></p>
        </div>
        <div>
            <strong><?= e(t('Ontdekken', 'Explore')) ?></strong>
            <a href="<?= e(with_language('updates.php')) ?>"><?= e(t('Laatste updates', 'Latest updates')) ?></a>
            <a href="<?= e(with_language('wiki.php')) ?>">Wiki</a>
            <a href="<?= e(with_language('guides.php')) ?>"><?= e(t('Handleidingen', 'Guides')) ?></a>
            <a href="<?= e(with_language('compare.php')) ?>"><?= e(t('Vergelijken', 'Compare')) ?></a>
            <a href="<?= e(with_language('community.php')) ?>"><?= e(t('Support & feedback', 'Support & feedback')) ?></a>
            <a href="<?= e(with_language('downloads.php')) ?>">Downloads</a>
        </div>
        <div>
            <strong><?= e(t('Transparantie', 'Transparency')) ?></strong>
            <a href="<?= e(with_language('privacy.php')) ?>">Privacy</a>
            <a href="<?= e(with_language('security.php')) ?>"><?= e(t('Veiligheid', 'Security')) ?></a>
            <a href="<?= e(with_language('code-signing.php')) ?>"><?= e(t('Beleid codeondertekening', 'Code signing policy')) ?></a>
            <a href="<?= e(with_language('status.php')) ?>"><?= e(t('Systeemstatus', 'System status')) ?></a>
            <a href="downloads/windows.json"><?= e(t('Stabiele updatefeed', 'Stable update feed')) ?> JSON</a>
            <a href="downloads/beta.json">Beta updatefeed JSON</a>
            <a href="<?= e(LOCALDECK_GITHUB_URL) ?>" target="_blank" rel="noopener">GitHub ↗</a>
        </div>
        <div class="footer-note">
            <span class="status-dot"></span>
            <p><?= e(t('De officiële website en updatebron van LocalDeck.', 'The official LocalDeck website and update source.')) ?></p>
            <small>Site <?= e(LOCALDECK_SITE_VERSION) ?> · © <?= e($year) ?> LocalDeck</small>
        </div>
    </div>
</footer>
</body>
</html>
