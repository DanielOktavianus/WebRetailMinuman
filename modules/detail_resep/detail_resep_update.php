<?php
require_once __DIR__ . '/../../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: detail_resep_tambah.php'); exit;
}

$DetailResepNo = isset($_POST['DetailResepNo']) && is_numeric($_POST['DetailResepNo']) ? (int) $_POST['DetailResepNo'] : null;
$ResepNo       = isset($_POST['ResepNo'])       && is_numeric($_POST['ResepNo'])       ? (int) $_POST['ResepNo']       : null;
$UkuranNo      = isset($_POST['UkuranNo'])      && is_numeric($_POST['UkuranNo'])      ? (int) $_POST['UkuranNo']      : null;
$StokNo        = isset($_POST['StokNo'])        && is_numeric($_POST['StokNo'])        ? (int) $_POST['StokNo']        : null;
$SatuanNo      = isset($_POST['SatuanNo'])      && is_numeric($_POST['SatuanNo'])      ? (int) $_POST['SatuanNo']      : null;
$jumlah        = isset($_POST['jumlah'])        && is_numeric($_POST['jumlah'])        ? (float) $_POST['jumlah']      : null;

if ($DetailResepNo === null || $ResepNo === null || $UkuranNo === null || $StokNo === null || $SatuanNo === null || $jumlah === null) {
    die('Data tidak lengkap. <a href="detail_resep_tambah.php">Kembali</a>');
}

$stmt = mysqli_prepare($conn,
    "UPDATE detail_resep SET ResepNo = ?, UkuranNo = ?, StokNo = ?, SatuanNo = ?, jumlah = ? WHERE DetailResepNo = ?");
if (!$stmt) die('Prepare gagal: ' . mysqli_error($conn));

mysqli_stmt_bind_param($stmt, 'iiiidi', $ResepNo, $UkuranNo, $StokNo, $SatuanNo, $jumlah, $DetailResepNo);
if (mysqli_stmt_execute($stmt)) {
    mysqli_stmt_close($stmt);
    header('Location: detail_resep_tambah.php?ResepNo=' . $ResepNo); exit;
} else {
    die('Update gagal: ' . mysqli_stmt_error($stmt));
}
?>
