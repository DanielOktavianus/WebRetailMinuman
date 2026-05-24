<?php
/**
 * Application base path configuration.
 *
 * Local XAMPP  : APP_BASE env var tidak di-set → default '/SKRIPSIS8'
 * Railway/prod : Set environment variable APP_BASE='' (string kosong)
 */
if (!defined('APP_BASE')) {
    $appBase = getenv('APP_BASE');
    define('APP_BASE', $appBase !== false ? $appBase : '/SKRIPSIS8');
}
