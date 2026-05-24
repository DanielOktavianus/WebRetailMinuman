<?php
require_once __DIR__ . '/../../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: bahan_tambah.php');
    exit;
}

$BahanNo = isset($_POST['BahanNo']) && is_numeric($_POST['BahanNo']) ? (int) $_POST['BahanNo'] : null;
$nama_bahan = isset($_POST['nama_bahan']) ? trim($_POST['nama_bahan']) : '';

if ($BahanNo === null || $nama_bahan === '') {
    echo 'Data tidak lengkap.';
    exit;
}

$stmt = mysqli_prepare($conn, "UPDATE bahan SET nama_bahan = ? WHERE BahanNo = ?");
if (!$stmt) {
    die('Prepare gagal: ' . mysqli_error($conn));
}

mysqli_stmt_bind_param($stmt, 'si', $nama_bahan, $BahanNo);
if (mysqli_stmt_execute($stmt)) {
    mysqli_stmt_close($stmt);
    header('Location: bahan_tambah.php');
    exit;
} else {
    die('Update gagal: ' . mysqli_stmt_error($stmt));
}
?>
