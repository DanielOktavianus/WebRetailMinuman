<?php
require_once __DIR__ . '/../config/app.php';

// Auth Helper - Role-based Access Control (RBAC)

/**
 * Check if user is logged in
 */
function isLoggedIn() {
    return isset($_SESSION['login']) && $_SESSION['login'] === true;
}

/**
 * Get current user role
 * @return string|null - 'admin', 'karyawan', or null
 */
function getUserRole() {
    return isset($_SESSION['role']) ? $_SESSION['role'] : null;
}

/**
 * Get current username
 */
function getUsername() {
    return isset($_SESSION['username']) ? $_SESSION['username'] : null;
}

/**
 * Check if user has specific role
 * @param string $role
 * @return bool
 */
function hasRole($role) {
    return getUserRole() === $role;
}

/**
 * Check if user has any of the given roles
 * @param array $roles
 * @return bool
 */
function hasAnyRole($roles = []) {
    $userRole = getUserRole();
    return in_array($userRole, $roles, true);
}

/**
 * Require login - redirect if not logged in
 */
function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: ' . APP_BASE . '/auth/login.php');
        exit;
    }
}

/**
 * Require specific role(s)
 * @param string|array $roles
 */
function requireRole($roles) {
    requireLogin();
    $rolesArray = is_array($roles) ? $roles : [$roles];

    if (!hasAnyRole($rolesArray)) {
        header('HTTP/1.0 403 Forbidden');
        die('Access Denied: Anda tidak memiliki akses ke halaman ini.');
    }
}

/**
 * Get allowed modules for current role
 * @return array
 */
function getAllowedModules() {
    $role = getUserRole();

    $moduleAccess = [
        'admin' => [
            'dashboard', 'menu', 'varian_menu', 'resep', 'detail_resep',
            'satuan', 'stok', 'bahan', 'produsen', 'customer', 'karyawan',
            'users', 'metode_pembayaran', 'voucher', 'transaksi', 'transaksi_detail'
        ],
        'karyawan' => [
            'dashboard', 'transaksi', 'transaksi_detail'
        ]
    ];

    return isset($moduleAccess[$role]) ? $moduleAccess[$role] : [];
}

/**
 * Check if user can access specific module
 * @param string $module
 * @return bool
 */
function canAccessModule($module) {
    return in_array($module, getAllowedModules(), true);
}

/**
 * Get menu structure filtered by user role
 * @return array
 */
function getFilteredMenuStructure() {
    $allowedModules = getAllowedModules();
    $b = APP_BASE;

    $allLinks = [
        ['label' => 'Dashboard', 'href' => $b . '/dashboard/dashboard.php', 'type' => 'link', 'module' => 'dashboard'],
        ['label' => 'Menu', 'type' => 'group', 'module' => 'menu', 'items' => [
            'Menu'        => $b . '/modules/menu/menu_tambah.php',
            'Varian Menu' => $b . '/modules/varian_menu/varian_tambah.php',
        ]],
        ['label' => 'Resep', 'type' => 'group', 'module' => 'resep', 'items' => [
            'Resep'        => $b . '/modules/resep/resep_tambah.php',
            'Detail Resep' => $b . '/modules/detail_resep/detail_resep_tambah.php',
            'Satuan'       => $b . '/modules/satuan/satuan_tambah.php',
        ]],
        ['label' => 'Stok', 'type' => 'group', 'module' => 'stok', 'items' => [
            'Stok'     => $b . '/modules/stok/stok_tambah.php',
            'Bahan'    => $b . '/modules/bahan/bahan_tambah.php',
            'Produsen' => $b . '/modules/produsen/produsen_list.php',
        ]],
        ['label' => 'Transaksi', 'type' => 'group', 'module' => 'transaksi', 'items' => [
            'Transaksi'        => $b . '/modules/transaksi/transaksi_tambah.php',
            'Detail Transaksi' => $b . '/modules/transaksi/transaksi_list.php',
        ]],
        ['label' => 'Voucher',            'href' => $b . '/modules/voucher/voucher_tambah.php',                    'type' => 'link', 'module' => 'voucher'],
        ['label' => 'Metode Pembayaran',  'href' => $b . '/modules/metode_pembayaran/metode_tambah.php',          'type' => 'link', 'module' => 'metode_pembayaran'],
        ['label' => 'User', 'type' => 'group', 'module' => 'users', 'items' => [
            'Users' => $b . '/modules/users/user_tambah.php',
        ]],
        ['label' => 'Karyawan', 'href' => $b . '/modules/karyawan/karyawan_tambah.php', 'type' => 'link', 'module' => 'karyawan'],
    ];

    // Filter based on allowed modules
    return array_filter($allLinks, function($item) use ($allowedModules) {
        return isset($item['module']) && in_array($item['module'], $allowedModules);
    });
}

/**
 * Logout user
 */
function logout() {
    session_destroy();
    header('Location: ' . APP_BASE . '/auth/login.php');
    exit;
}
?>
