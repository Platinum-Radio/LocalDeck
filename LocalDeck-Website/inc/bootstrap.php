<?php
declare(strict_types=1);

const LOCALDECK_SITE_ROOT = __DIR__ . '/..';
const LOCALDECK_SITE_VERSION = '1.1.0-test.1';
const LOCALDECK_DOCUMENTATION_VERSION = '1.0.0';
const LOCALDECK_CONTACT_RECIPIENT = 'chatgpt@platinumradio.nl';
const LOCALDECK_MAIL_SENDER = 'website@localdeck.nl';
const LOCALDECK_GITHUB_URL = 'https://github.com/Platinum-Radio/LocalDeck';

$requestedLanguage = strtolower((string) ($_GET['lang'] ?? $_COOKIE['localdeck_site_language'] ?? 'nl'));
$language = $requestedLanguage === 'en' ? 'en' : 'nl';
if (isset($_GET['lang'])) {
    setcookie('localdeck_site_language', $language, [
        'expires' => time() + 31536000,
        'path' => '/',
        'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
        'httponly' => false,
        'samesite' => 'Lax',
    ]);
}

header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: strict-origin-when-cross-origin');
header('Permissions-Policy: camera=(), microphone=(), geolocation=()');
header('Cross-Origin-Opener-Policy: same-origin');
if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') {
    header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
}
header("Content-Security-Policy: default-src 'self'; img-src 'self' data:; style-src 'self'; script-src 'self'; font-src 'self'; connect-src 'self'; frame-ancestors 'none'; base-uri 'self'; form-action 'self'");

function e(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function t(string $nl, string $en): string
{
    global $language;
    return $language === 'en' ? $en : $nl;
}

function content_text(array $translations): string
{
    global $language;
    return (string) ($language === 'en' ? ($translations[1] ?? '') : ($translations[0] ?? ''));
}

function with_language(string $path): string
{
    global $language;
    $separator = str_contains($path, '?') ? '&' : '?';
    return $path . $separator . 'lang=' . rawurlencode($language);
}

function language_switch_url(string $targetLanguage): string
{
    $query = $_GET;
    $query['lang'] = $targetLanguage;
    $path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
    return $path . '?' . http_build_query($query);
}

function site_origin(): string
{
    $configured = trim((string) getenv('LOCALDECK_SITE_URL'));
    if ($configured !== '') {
        return rtrim($configured, '/');
    }
    $https = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
    $host = preg_replace('/[^a-zA-Z0-9.\-:\[\]]/', '', (string) ($_SERVER['HTTP_HOST'] ?? 'localdeck.localhost'));
    return ($https ? 'https://' : 'http://') . ($host ?: 'localdeck.localhost');
}

function read_json_file(string $path, array $fallback = []): array
{
    if (!is_file($path)) {
        return $fallback;
    }
    $decoded = json_decode((string) file_get_contents($path), true);
    return is_array($decoded) ? $decoded : $fallback;
}

function release_catalog(): array
{
    return read_json_file(LOCALDECK_SITE_ROOT . '/downloads/releases.json', ['releases' => []]);
}

function sorted_releases(array $releases): array
{
    $indexedReleases = [];
    foreach (array_values($releases) as $index => $release) {
        if (is_array($release)) {
            $indexedReleases[] = ['index' => $index, 'release' => $release];
        }
    }

    usort($indexedReleases, static function (array $left, array $right): int {
        $comparison = version_compare(
            (string) ($right['release']['version'] ?? '0.0.0'),
            (string) ($left['release']['version'] ?? '0.0.0')
        );

        return $comparison !== 0 ? $comparison : $left['index'] <=> $right['index'];
    });

    return array_column($indexedReleases, 'release');
}

function find_release_artifact(array $release, string $artifactId): ?array
{
    foreach ($release['artifacts'] ?? [] as $artifact) {
        if (is_array($artifact) && ($artifact['id'] ?? '') === $artifactId) {
            return $artifact;
        }
    }

    return null;
}

function published_releases(): array
{
    return array_values(array_filter(
        sorted_releases(release_catalog()['releases'] ?? []),
        static fn (array $release): bool => !empty($release['published'])
    ));
}

function latest_published_release(): array
{
    return published_releases()[0] ?? ['version' => LOCALDECK_SITE_VERSION, 'published' => false, 'artifacts' => []];
}

function latest_published_release_for_channel(string $channel): array
{
    foreach (published_releases() as $release) {
        if (($release['channel'] ?? 'stable') === $channel) {
            return $release;
        }
    }

    return [];
}

function release_artifact_is_available(array $release, array $artifact): bool
{
    if (empty($release['published']) || empty($artifact['file'])) {
        return false;
    }

    $downloadsRoot = realpath(LOCALDECK_SITE_ROOT . '/downloads/releases');
    $absoluteFile = realpath(LOCALDECK_SITE_ROOT . '/' . ltrim((string) $artifact['file'], '/\\'));

    return $downloadsRoot !== false
        && $absoluteFile !== false
        && file_exists($absoluteFile)
        && in_array(strtolower(pathinfo($absoluteFile, PATHINFO_EXTENSION)), ['exe', 'zip'], true)
        && str_starts_with(strtolower($absoluteFile), strtolower($downloadsRoot . DIRECTORY_SEPARATOR));
}

function download_statistics(): array
{
    return read_json_file(LOCALDECK_SITE_ROOT . '/private/download-stats.json', ['versions' => [], 'total' => 0]);
}

function download_count(string $version, ?string $artifact = null): int
{
    $statistics = download_statistics();
    $versionData = $statistics['versions'][$version] ?? [];
    if ($artifact !== null) {
        return max(0, (int) ($versionData['artifacts'][$artifact]['count'] ?? 0));
    }
    return max(0, (int) ($versionData['total'] ?? 0));
}

function format_release_date(?string $date): string
{
    if (!$date) {
        return t('Nog niet gepubliceerd', 'Not published yet');
    }
    try {
        $value = new DateTimeImmutable($date);
        return $value->format('d-m-Y');
    } catch (Throwable) {
        return $date;
    }
}

function nav_active(string $page, string $current): string
{
    return $page === $current ? ' aria-current="page" class="active"' : '';
}

function request_is_local(): bool
{
    return in_array($_SERVER['REMOTE_ADDR'] ?? '', ['127.0.0.1', '::1'], true);
}

function website_health_checks(): array
{
    $articles = is_file(LOCALDECK_SITE_ROOT . '/inc/content.php')
        ? require LOCALDECK_SITE_ROOT . '/inc/content.php'
        : [];
    $latest = latest_published_release();
    $stableRelease = latest_published_release_for_channel('stable');
    $betaRelease = latest_published_release_for_channel('beta');
    $stableFeed = read_json_file(LOCALDECK_SITE_ROOT . '/downloads/windows.json');
    $betaFeed = read_json_file(LOCALDECK_SITE_ROOT . '/downloads/beta.json');
    $artifacts = array_values(array_filter($latest['artifacts'] ?? [], 'is_array'));
    $availableArtifacts = array_filter(
        $artifacts,
        static fn (array $artifact): bool => release_artifact_is_available($latest, $artifact)
            && preg_match('/^[a-f0-9]{64}$/i', (string) ($artifact['sha256'] ?? '')) === 1
    );
    $statisticsPath = LOCALDECK_SITE_ROOT . '/private/download-stats.json';
    $statisticsWritable = is_file($statisticsPath)
        ? is_writable($statisticsPath)
        : is_writable(dirname($statisticsPath));
    $isHttps = parse_url(site_origin(), PHP_URL_SCHEME) === 'https';

    return [
        ['id' => 'website', 'name' => t('Website', 'Website'), 'healthy' => is_file(LOCALDECK_SITE_ROOT . '/index.php'), 'detail' => t('PHP-pagina en gedeelde layout beschikbaar', 'PHP page and shared layout available')],
        ['id' => 'wiki', 'name' => t('Documentatie', 'Documentation'), 'healthy' => count($articles) >= 8, 'detail' => count($articles) . ' ' . t('doorzoekbare onderwerpen', 'searchable topics')],
        ['id' => 'updates', 'name' => t('Updatefeeds', 'Update feeds'), 'healthy' => ($stableFeed['version'] ?? null) === ($stableRelease['version'] ?? null) && ($betaFeed['version'] ?? null) === ($betaRelease['version'] ?? null), 'detail' => t('Stabiel ', 'Stable ') . (string) ($stableFeed['version'] ?? '—') . ' · Beta ' . (string) ($betaFeed['version'] ?? '—')],
        ['id' => 'downloads', 'name' => t('Downloads', 'Downloads'), 'healthy' => $artifacts !== [] && count($availableArtifacts) === count($artifacts), 'detail' => count($availableArtifacts) . '/' . count($artifacts) . ' ' . t('bestanden met geldige metadata', 'files with valid metadata')],
        ['id' => 'counter', 'name' => t('Downloadteller', 'Download counter'), 'healthy' => $statisticsWritable, 'detail' => (string) (download_statistics()['total'] ?? 0) . ' ' . t('privacyvriendelijke registraties', 'privacy-friendly records')],
        ['id' => 'support', 'name' => t('Supportformulier', 'Support form'), 'healthy' => function_exists('mail') && filter_var(LOCALDECK_CONTACT_RECIPIENT, FILTER_VALIDATE_EMAIL) !== false, 'detail' => t('Vaste beheerinbox en lokale noodkopie geconfigureerd', 'Fixed administration inbox and local fallback copy configured')],
        ['id' => 'https', 'name' => 'HTTPS', 'healthy' => $isHttps || request_is_local(), 'detail' => $isHttps ? t('Publieke verbinding versleuteld', 'Public connection encrypted') : t('Lokale ontwikkelcontrole', 'Local development check')],
    ];
}

function mail_header_value(string $value): string
{
    return trim((string) preg_replace('/[\r\n]+/', ' ', $value));
}

function community_submission_email(array $submission): array
{
    $typeLabels = [
        'question' => 'Vraag of hulp',
        'bug' => 'Foutmelding',
        'idea' => 'Idee of verbetering',
        'docs' => 'Wiki of documentatie',
    ];
    $type = (string) ($submission['type'] ?? 'question');
    $title = mail_header_value((string) ($submission['title'] ?? 'Zonder titel'));
    $email = trim((string) ($submission['email'] ?? ''));
    $ticket = mail_header_value((string) ($submission['id'] ?? 'zonder-ticket'));
    $subjectText = '[LocalDeck website][' . $ticket . '] ' . ($typeLabels[$type] ?? 'Bericht') . ': ' . $title;
    $subject = function_exists('mb_encode_mimeheader')
        ? mb_encode_mimeheader($subjectText, 'UTF-8', 'B', "\r\n")
        : $subjectText;

    $body = implode("\r\n", [
        'Er is een nieuw bericht binnengekomen via LocalDeck.nl.',
        '',
        'Categorie: ' . ($typeLabels[$type] ?? $type),
        'Titel: ' . (string) ($submission['title'] ?? ''),
        'Naam: ' . (string) ($submission['name'] ?? 'Anoniem'),
        'E-mail: ' . ($email !== '' ? $email : 'Niet opgegeven'),
        'Taal: ' . strtoupper((string) ($submission['language'] ?? 'nl')),
        'Ontvangen: ' . (string) ($submission['createdAt'] ?? ''),
        'Bericht-ID: ' . (string) ($submission['id'] ?? ''),
        'Website: ' . site_origin(),
        '',
        'Bericht:',
        str_replace(["\r\n", "\r"], "\n", (string) ($submission['message'] ?? '')),
    ]);

    $headers = [
        'MIME-Version: 1.0',
        'Content-Type: text/plain; charset=UTF-8',
        'Content-Transfer-Encoding: 8bit',
        'From: LocalDeck Website <' . LOCALDECK_MAIL_SENDER . '>',
        'X-Mailer: LocalDeck Website/' . LOCALDECK_SITE_VERSION,
    ];
    if ($email !== '' && filter_var($email, FILTER_VALIDATE_EMAIL) && !preg_match('/[\r\n]/', $email)) {
        $headers[] = 'Reply-To: ' . mail_header_value($email);
    }

    return [
        'to' => LOCALDECK_CONTACT_RECIPIENT,
        'subject' => $subject,
        'body' => $body,
        'headers' => implode("\r\n", $headers),
    ];
}

function send_community_submission_email(array $submission, ?callable $transport = null): bool
{
    $email = community_submission_email($submission);
    $transport ??= static fn (string $to, string $subject, string $body, string $headers): bool =>
        mail($to, $subject, $body, $headers);

    try {
        return $transport($email['to'], $email['subject'], $email['body'], $email['headers']) === true;
    } catch (Throwable $error) {
        error_log('LocalDeck community-mail kon niet worden aangeboden: ' . $error->getMessage());
        return false;
    }
}
