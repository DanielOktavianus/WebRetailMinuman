<?php
require_once __DIR__ . '/../../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: resep_tambah.php'); exit;
}

$ResepNo    = isset($_POST['ResepNo'])    && is_numeric($_POST['ResepNo'])    ? (int) $_POST['ResepNo']    : null;
$MenuNo     = isset($_POST['MenuNo'])     && is_numeric($_POST['MenuNo'])     ? (int) $_POST['MenuNo']     : null;
$keterangan = isset($_POST['keterangan']) ? trim($_POST['keterangan'])         : '';

if ($ResepNo === null || $MenuNo === null || $keterangan === '') {
    echo 'Data tidak lengkap.'; exit;
}

$stmt = mysqli_prepare($conn, "UPDATE resep SET MenuNo = ?, Keterangan = ? WHERE ResepNo = ?");
if (!$stmt) die('Prepare gagal: ' . mysqli_error($conn));

mysqli_stmt_bind_param($stmt, 'isi', $MenuNo, $keterangan, $ResepNo);
if (mysqli_stmt_execute($stmt)) {
    mysqli_stmt_close($stmt);
    header('Location: resep_tambah.php'); exit;
} else {
    die('Update gagal: ' . mysqli_stmt_error($stmt));
}
?>
