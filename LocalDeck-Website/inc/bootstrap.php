<?php
declare(strict_types=1);

const LOCALDECK_SITE_ROOT = __DIR__ . '/..';
const LOCALDECK_SITE_VERSION = '1.0.0';
const LOCALDECK_CONTACT_RECIPIENT = 'chatgpt@platinumradio.nl';
const LOCALDECK_MAIL_SENDER = 'website@localdeck.nl';

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

function release_artifact_is_available(array $release, array $artifact): bool
{
    if (empty($release['published']) || empty($artifact['file'])) {
        return false;
    }

    $downloadsRoot = realpath(LOCALDECK_SITE_ROOT . '/downloads/releases');
    $absoluteFile = realpath(LOCALDECK_SITE_ROOT . '/' . ltrim((string) $artifact['file'], '/\\'));

    return $downloadsRoot !== false
        && $absoluteFile !== false
        && is_file($absoluteFile)
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
    $subjectText = '[LocalDeck website] ' . ($typeLabels[$type] ?? 'Bericht') . ': ' . $title;
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
