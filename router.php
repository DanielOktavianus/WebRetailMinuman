<?php
/**
 * PHP Built-in Server Router
 * Dipakai oleh Railway agar file PHP di-serve langsung, bukan lewat index.php
 */
$uri  = urldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));
$file = __DIR__ . $uri;

// File statis / PHP yang ada → serve langsung
if ($uri !== '/' && file_exists($file) && !is_dir($file)) {
    return false;
}

// Request ke direktori dengan index.php di dalamnya
if (is_dir($file) && file_exists(rtrim($file, '/') . '/index.php')) {
    return false;
}

// Root / → index.php
if ($uri === '/') {
    require __DIR__ . '/index.php';
    return true;
}

// File tidak ditemukan → 404
http_response_code(404);
echo '<h1>404 Not Found</h1><p>' . htmlspecialchars($uri) . '</p>';
return true;
