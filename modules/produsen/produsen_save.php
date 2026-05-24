<?php
require_once '../../config/database.php';
require_once '../../helpers/auth_helper.php';

// Require admin access
requireRole('admin');

// Simple server-side validation
$nama = isset($_POST['nama']) ? trim($_POST['nama']) : '';
$alamat = isset($_POST['alamat']) ? trim($_POST['alamat']) : '';
$kontak = isset($_POST['kontak']) ? trim($_POST['kontak']) : '';

if ($nama === '') {
    echo "Nama produsen harus diisi.";
    exit;
}

$stmt = mysqli_prepare($conn, "INSERT INTO produsen (Nama_Produsen, Alamat, Kontak) VALUES (?, ?, ?)");
if (!$stmt) {
    die('Prepare failed: ' . mysqli_error($conn));
}
mysqli_stmt_bind_param($stmt, 'sss', $nama, $alamat, $kontak);
$ok = mysqli_stmt_execute($stmt);
if ($ok) {
    mysqli_stmt_close($stmt);
    header('Location: produsen_list.php');
    exit;
} else {
    echo 'Gagal menyimpan: ' . mysqli_error($conn);
}
?>
