<?php
declare(strict_types=1);

$_SERVER['REMOTE_ADDR'] = '127.0.0.1';
$_SERVER['HTTP_HOST'] = '127.0.0.1:8793';

ob_start();
require __DIR__ . '/../sitemap.php';
$xml = (string) ob_get_clean();

if (!str_starts_with($xml, '<?xml version="1.0" encoding="UTF-8"?>')) {
    throw new RuntimeException('De sitemap heeft geen geldige XML-declaratie.');
}
if (substr_count($xml, '<url>') < 40) {
    throw new RuntimeException('De sitemap bevat te weinig tweetalige pagina’s.');
}
foreach (['updates.php?lang=nl', 'downloads.php?lang=nl', 'compare.php?lang=en', 'wiki.php?article=', 'xampp-alternative.php?lang=en'] as $needle) {
    if (!str_contains(html_entity_decode($xml, ENT_QUOTES | ENT_XML1, 'UTF-8'), $needle)) {
        throw new RuntimeException('Sitemaproute ontbreekt: ' . $needle);
    }
}

echo "sitemap: OK\n";
