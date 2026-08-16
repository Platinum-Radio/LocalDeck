<?php
declare(strict_types=1);
require dirname(__DIR__) . '/inc/bootstrap.php';
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, max-age=0');
echo json_encode(download_statistics(), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

