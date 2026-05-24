<?php
/**
 * Application base path configuration.
 *
 * Local XAMPP  : tidak ada RAILWAY_ENVIRONMENT → pakai '/SKRIPSIS8'
 * Railway/prod : RAILWAY_ENVIRONMENT otomatis di-set Railway → pakai ''
 */
if (!defined('APP_BASE')) {
    if (getenv('RAILWAY_ENVIRONMENT') !== false || getenv('RAILWAY_SERVICE_NAME') !== false) {
        define('APP_BASE', '');        // Railway: app di root domain
    } else {
        define('APP_BASE', '/SKRIPSIS8'); // Local XAMPP
    }
}
