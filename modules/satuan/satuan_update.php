<?php
require_once __DIR__ . '/../../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: satuan_tambah.php');
    exit;
}

$SatuanNo = isset($_POST['SatuanNo']) && is_numeric($_POST['SatuanNo']) ? (int) $_POST['SatuanNo'] : null;
$nama_satuan = isset($_POST['nama_satuan']) ? trim($_POST['nama_satuan']) : '';

if ($SatuanNo === null || $nama_satuan === '') {
    echo 'Data tidak lengkap.';
    exit;
}

$stmt = mysqli_prepare($conn, "UPDATE satuan SET nama_satuan = ? WHERE SatuanNo = ?");
if (!$stmt) {
    die('Prepare gagal: ' . mysqli_error($conn));
}

mysqli_stmt_bind_param($stmt, 'si', $nama_satuan, $SatuanNo);
if (mysqli_stmt_execute($stmt)) {
    mysqli_stmt_close($stmt);
    header('Location: satuan_tambah.php');
    exit;
} else {
    die('Update gagal: ' . mysqli_stmt_error($stmt));
}
?>
