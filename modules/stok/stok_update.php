<?php
require_once __DIR__ . '/../../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: stok_tambah.php');
    exit;
}

$StokNo = isset($_POST['StokNo']) && is_numeric($_POST['StokNo']) ? (int) $_POST['StokNo'] : null;
$ProdusenNo = isset($_POST['ProdusenNo']) && is_numeric($_POST['ProdusenNo']) && $_POST['ProdusenNo'] !== '' ? (int) $_POST['ProdusenNo'] : null;
$BahanNo = isset($_POST['BahanNo']) && is_numeric($_POST['BahanNo']) ? (int) $_POST['BahanNo'] : null;
$SatuanNo = isset($_POST['SatuanNo']) && is_numeric($_POST['SatuanNo']) ? (int) $_POST['SatuanNo'] : null;
$jumlah_stok = isset($_POST['jumlah_stok']) && is_numeric($_POST['jumlah_stok']) ? (int) $_POST['jumlah_stok'] : null;
$batas_minimum = isset($_POST['batas_minimum']) && is_numeric($_POST['batas_minimum']) ? (float) $_POST['batas_minimum'] : null;

if ($StokNo === null || $BahanNo === null || $SatuanNo === null || $jumlah_stok === null || $batas_minimum === null) {
    echo 'Data tidak lengkap.';
    exit;
}

$stmt = mysqli_prepare($conn, "UPDATE stok SET ProdusenNo = ?, BahanNo = ?, SatuanNo = ?, jumlah_stok = ?, batas_minimum = ? WHERE StokNo = ?");
if (!$stmt) {
    die('Prepare gagal: ' . mysqli_error($conn));
}

mysqli_stmt_bind_param($stmt, 'iiiidi', $ProdusenNo, $BahanNo, $SatuanNo, $jumlah_stok, $batas_minimum, $StokNo);
if (mysqli_stmt_execute($stmt)) {
    mysqli_stmt_close($stmt);
    header('Location: stok_tambah.php');
    exit;
} else {
    die('Update gagal: ' . mysqli_stmt_error($stmt));
}
?>
