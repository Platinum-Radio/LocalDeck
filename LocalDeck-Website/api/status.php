<?php
declare(strict_types=1);
require __DIR__ . '/../inc/bootstrap.php';

$startedAt = microtime(true);
$checks = website_health_checks();
$healthy = !in_array(false, array_column($checks, 'healthy'), true);
$latest = latest_published_release();

header('Content-Type: application/json; charset=UTF-8');
header('Cache-Control: no-store, max-age=0');
echo json_encode([
    'product' => 'LocalDeck',
    'websiteVersion' => LOCALDECK_SITE_VERSION,
    'releaseVersion' => (string) ($latest['version'] ?? LOCALDECK_SITE_VERSION),
    'status' => $healthy ? 'operational' : 'degraded',
    'checkedAt' => (new DateTimeImmutable())->format(DATE_ATOM),
    'durationMs' => max(1, (int) round((microtime(true) - $startedAt) * 1000)),
    'checks' => $checks,
], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
