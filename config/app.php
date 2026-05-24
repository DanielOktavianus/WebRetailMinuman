<?php
/**
 * Application base path configuration.
 * Local XAMPP  → '/SKRIPSIS8'
 * Railway/prod → '' (root)
 */
if (!defined('APP_BASE')) {
    $host = $_SERVER['HTTP_HOST'] ?? $_SERVER['SERVER_NAME'] ?? '';
    // Railway domain atau env var HOSTED=1
    if (strpos($host, 'railway.app') !== false || getenv('HOSTED') === '1') {
        define('APP_BASE', '');
    } else {
        define('APP_BASE', '/SKRIPSIS8');
    }
}
