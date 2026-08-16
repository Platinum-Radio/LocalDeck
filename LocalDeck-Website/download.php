<?php
declare(strict_types=1);
require __DIR__ . '/inc/bootstrap.php';

function fail_download(int $status, string $message): never
{
    http_response_code($status);
    header('Content-Type: text/plain; charset=utf-8');
    echo $message;
    exit;
}

function record_download_start(string $version, string $artifact): void
{
    $path = LOCALDECK_SITE_ROOT . '/private/download-stats.json';
    $handle = fopen($path, 'c+');
    if ($handle === false) {
        throw new RuntimeException('Downloadteller is niet schrijfbaar.');
    }
    try {
        if (!flock($handle, LOCK_EX)) {
            throw new RuntimeException('Downloadteller is tijdelijk bezet.');
        }
        rewind($handle);
        $stats = json_decode(stream_get_contents($handle) ?: '', true);
        if (!is_array($stats)) {
            $stats = ['total' => 0, 'versions' => []];
        }
        $stats['total'] = max(0, (int) ($stats['total'] ?? 0)) + 1;
        $stats['versions'][$version] ??= ['total' => 0, 'artifacts' => []];
        $stats['versions'][$version]['total'] = max(0, (int) ($stats['versions'][$version]['total'] ?? 0)) + 1;
        $stats['versions'][$version]['artifacts'][$artifact] ??= ['count' => 0];
        $stats['versions'][$version]['artifacts'][$artifact]['count'] = max(0, (int) ($stats['versions'][$version]['artifacts'][$artifact]['count'] ?? 0)) + 1;
        $stats['versions'][$version]['artifacts'][$artifact]['lastDownloadAt'] = (new DateTimeImmutable())->format(DATE_ATOM);
        $stats['updatedAt'] = (new DateTimeImmutable())->format(DATE_ATOM);
        ftruncate($handle, 0);
        rewind($handle);
        fwrite($handle, json_encode($stats, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        fflush($handle);
        flock($handle, LOCK_UN);
    } finally {
        fclose($handle);
    }
}

$version = (string) ($_GET['version'] ?? '');
$artifactId = (string) ($_GET['artifact'] ?? '');
if (!preg_match('/^\d+\.\d+\.\d+(?:-[a-z0-9.-]+)?$/i', $version) || !preg_match('/^[a-z0-9-]+$/i', $artifactId)) {
    fail_download(400, 'Ongeldige downloadaanvraag.');
}

$release = null;
foreach (release_catalog()['releases'] ?? [] as $candidate) {
    if (($candidate['version'] ?? '') === $version) {
        $release = $candidate;
        break;
    }
}
if (!$release || empty($release['published'])) {
    fail_download(404, 'Deze release is nog niet gepubliceerd.');
}

$artifact = find_release_artifact($release, $artifactId);
if (!$artifact || empty($artifact['file'])) {
    fail_download(404, 'Dit releasebestand bestaat niet.');
}

$absoluteFile = realpath(LOCALDECK_SITE_ROOT . '/' . ltrim((string) $artifact['file'], '/\\'));
if (!release_artifact_is_available($release, $artifact) || $absoluteFile === false) {
    fail_download(404, 'Dit releasebestand is nog niet beschikbaar.');
}

record_download_start($version, $artifactId);
$relativeUrl = str_replace(DIRECTORY_SEPARATOR, '/', substr($absoluteFile, strlen(LOCALDECK_SITE_ROOT) + 1));
header('Cache-Control: no-store');
header('Location: ' . implode('/', array_map('rawurlencode', explode('/', $relativeUrl))), true, 302);
exit;
