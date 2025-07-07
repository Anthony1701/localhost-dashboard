<?php
$configFile = __DIR__ . '/config.json';

$config = file_exists($configFile)
    ? json_decode(file_get_contents($configFile), true)
    : ['projects' => [], 'default' => '', 'language' => 'cs'];

$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['language'])) {
    http_response_code(400);
    echo 'Missing language';
    exit;
}

$language = $data['language'];

// Ověř, že jazyk existuje
$availableLanguages = [];
$langDir = __DIR__ . '/lang/';
if (is_dir($langDir)) {
    $files = scandir($langDir);
    foreach ($files as $file) {
        if (pathinfo($file, PATHINFO_EXTENSION) === 'json') {
            $availableLanguages[] = pathinfo($file, PATHINFO_FILENAME);
        }
    }
}

if (!in_array($language, $availableLanguages)) {
    http_response_code(400);
    echo 'Invalid language';
    exit;
}

$config['language'] = $language;

// Ulož zpět do souboru
file_put_contents(
    $configFile,
    json_encode($config, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
);

echo 'OK';
