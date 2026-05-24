<?php
require_once __DIR__ . '/../../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ukuran_tambah.php');
    exit;
}

$UkuranNo    = isset($_POST['UkuranNo']) && is_numeric($_POST['UkuranNo']) ? (int) $_POST['UkuranNo'] : null;
$nama_ukuran = isset($_POST['nama_ukuran']) ? trim($_POST['nama_ukuran']) : '';

if ($UkuranNo === null || $nama_ukuran === '') {
    echo 'Data tidak lengkap.';
    exit;
}

$stmt = mysqli_prepare($conn, "UPDATE ukuran SET nama_ukuran = ? WHERE UkuranNo = ?");
if (!$stmt) die('Prepare gagal: ' . mysqli_error($conn));

mysqli_stmt_bind_param($stmt, 'si', $nama_ukuran, $UkuranNo);
if (mysqli_stmt_execute($stmt)) {
    mysqli_stmt_close($stmt);
    header('Location: ukuran_tambah.php');
    exit;
} else {
    die('Update gagal: ' . mysqli_stmt_error($stmt));
}
?>
