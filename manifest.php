<?php
require_once __DIR__ . '/config/app.php';
header('Content-Type: application/manifest+json');
$b = APP_BASE;
echo json_encode([
    'name'             => 'Monitoring Penjualan',
    'short_name'       => 'Monitoring',
    'description'      => 'Sistem monitoring penjualan dan stok usaha minuman',
    'start_url'        => $b . '/dashboard/dashboard.php',
    'scope'            => $b . '/',
    'display'          => 'standalone',
    'orientation'      => 'portrait-primary',
    'background_color' => '#667eea',
    'theme_color'      => '#667eea',
    'icons' => [
        ['src' => $b . '/assets/img/icon-192.png', 'sizes' => '192x192', 'type' => 'image/png',       'purpose' => 'any'],
        ['src' => $b . '/assets/img/icon-512.png', 'sizes' => '512x512', 'type' => 'image/png',       'purpose' => 'maskable'],
        ['src' => $b . '/assets/img/icon-192.svg', 'sizes' => '192x192', 'type' => 'image/svg+xml'],
    ],
    'categories' => ['productivity', 'business'],
    'shortcuts' => [
        [
            'name'        => 'Dashboard',
            'short_name'  => 'Dashboard',
            'description' => 'Buka Dashboard',
            'url'         => $b . '/dashboard/dashboard.php',
            'icons'       => [['src' => $b . '/assets/img/icon-192.png', 'sizes' => '192x192', 'type' => 'image/png']],
        ],
        [
            'name'        => 'Transaksi',
            'short_name'  => 'Transaksi',
            'description' => 'Buat Transaksi Baru',
            'url'         => $b . '/modules/transaksi/transaksi_tambah.php',
            'icons'       => [['src' => $b . '/assets/img/icon-192.png', 'sizes' => '192x192', 'type' => 'image/png']],
        ],
    ],
], JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
