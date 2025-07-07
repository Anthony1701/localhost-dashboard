<?php

function loadTranslations($lang = 'cs') {
    $langFile = __DIR__ . "/lang/{$lang}.json";
    $fallbackFile = __DIR__ . "/lang/cs.json";
    
    // Pokud soubor jazyka neexistuje, použij češtinu jako fallback
    if (!file_exists($langFile)) {
        $langFile = $fallbackFile;
    }
    
    $translations = file_exists($langFile) 
        ? json_decode(file_get_contents($langFile), true) 
        : [];
    
    return $translations ?: [];
}

function getTranslation($translations, $key, $fallback = '') {
    $keys = explode('.', $key);
    $value = $translations;
    
    foreach ($keys as $k) {
        if (isset($value[$k])) {
            $value = $value[$k];
        } else {
            return $fallback ?: $key;
        }
    }
    
    return $value;
}

function t($translations, $key, $fallback = '') {
    return getTranslation($translations, $key, $fallback);
}

function getCurrentLanguage($config) {
    return $config['language'] ?? 'cs';
}

function getAvailableLanguages() {
    $languages = [];
    $langDir = __DIR__ . '/lang/';
    
    if (is_dir($langDir)) {
        $files = scandir($langDir);
        foreach ($files as $file) {
            if (pathinfo($file, PATHINFO_EXTENSION) === 'json') {
                $lang = pathinfo($file, PATHINFO_FILENAME);
                $languages[] = $lang;
            }
        }
    }
    
    return $languages;
}
