<?php
/**
 * Application base path configuration.
 * Local XAMPP  → '/WebRetailMinuman'
 * Railway/prod → '' (root)
 */
if (!defined('APP_BASE')) {
    $host = $_SERVER['HTTP_HOST'] ?? $_SERVER['SERVER_NAME'] ?? '';
    // Railway domain atau env var HOSTED=1
    if (strpos($host, 'railway.app') !== false || getenv('HOSTED') === '1') {
        define('APP_BASE', '');
    } else {
        define('APP_BASE', '/WebRetailMinuman');
    }
}

if (!defined('PUBLIC_DEMO')) {
    $publicDemoEnv = getenv('PUBLIC_DEMO');
    if ($publicDemoEnv === false && getenv('DEMO_MODE') !== false) {
        $publicDemoEnv = getenv('DEMO_MODE');
    }

    $publicDemo = ($publicDemoEnv === false) ? true : ($publicDemoEnv === '1');
    define('PUBLIC_DEMO', $publicDemo);
}
