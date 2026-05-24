<?php
require_once __DIR__ . '/app.php';

// Start session safely (can be called multiple times without error)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Lokal XAMPP pakai nilai default; Railway pakai env vars yang di-set di dashboard
$host     = getenv('MYSQLHOST')     ?: 'localhost';
$user     = getenv('MYSQLUSER')     ?: 'root';
$password = getenv('MYSQLPASSWORD') ?: '';
$database = getenv('MYSQLDATABASE') ?: 'skripsi_daniel';
$port     = (int)(getenv('MYSQLPORT') ?: 3306);

$conn = mysqli_connect($host, $user, $password, $database, $port);

if (!$conn) {
    die("Koneksi ke database gagal: " . mysqli_connect_error());
}
?>
