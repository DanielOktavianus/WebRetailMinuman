<?php
require_once __DIR__ . '/../../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: varian_tambah.php');
    exit;
}

$VarianMenuNo = isset($_POST['VarianMenuNo']) && is_numeric($_POST['VarianMenuNo']) ? (int)   $_POST['VarianMenuNo'] : null;
$MenuNo       = isset($_POST['MenuNo'])       && is_numeric($_POST['MenuNo'])       ? (int)   $_POST['MenuNo']       : null;
$ukuranNo     = isset($_POST['ukuranNo'])     && is_numeric($_POST['ukuranNo'])     ? (int)   $_POST['ukuranNo']     : null;
$harga        = isset($_POST['Harga'])        && is_numeric($_POST['Harga'])        ? (float) $_POST['Harga']        : null;

if ($VarianMenuNo === null || $MenuNo === null || $ukuranNo === null || $harga === null) {
    echo 'Data tidak lengkap.';
    exit;
}

$stmt = mysqli_prepare($conn, "UPDATE varian_menu SET MenuNo = ?, ukuranNo = ?, Harga = ? WHERE VarianMenuNo = ?");
if (!$stmt) die('Prepare gagal: ' . mysqli_error($conn));

mysqli_stmt_bind_param($stmt, 'iidi', $MenuNo, $ukuranNo, $harga, $VarianMenuNo);
if (mysqli_stmt_execute($stmt)) {
    mysqli_stmt_close($stmt);
    header('Location: varian_tambah.php');
    exit;
} else {
    die('Update gagal: ' . mysqli_stmt_error($stmt));
}
?>
