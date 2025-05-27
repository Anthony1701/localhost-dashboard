<?php
$configFile = __DIR__ . '/config.json';

$config = file_exists($configFile)
    ? json_decode(file_get_contents($configFile), true)
    : ['projects' => [], 'default' => ''];

$projects = $config['projects'] ?? [];

$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['folder'])) {
    http_response_code(400);
    echo 'Missing folder';
    exit;
}

$folder = $data['folder'];
$title = trim($data['title'] ?? '');
$description = trim($data['description'] ?? '');
$tags = array_filter(array_map('trim', $data['tags'] ?? []));
$group = trim($data['group'] ?? '') ?: 'Ostatní';
$entry = trim($data['entry'] ?? '', '/');

$projectData = [
    'title' => $title ?: $folder,
];

if ($description !== '') {
    $projectData['description'] = $description;
}

if (!empty($tags)) {
    $projectData['tags'] = $tags;
}

if ($group !== '') {
    $projectData['group'] = $group;
}

if ($entry !== '') {
    $projectData['entry'] = $entry;
}

$config['projects'][$folder] = $projectData;

// Ulož zpět do souboru
file_put_contents(
    $configFile,
    json_encode($config, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
);

echo 'OK';