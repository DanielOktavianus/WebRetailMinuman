<?php
require_once __DIR__ . '/../../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
	header('Location: voucher_tambah.php');
	exit;
}

$voucherNo    = isset($_POST['voucherNo'])    && is_numeric($_POST['voucherNo'])    ? (int)   $_POST['voucherNo']    : null;
$nama_voucher = isset($_POST['nama_voucher']) ? trim($_POST['nama_voucher'])                                          : '';
$nilai_diskon = isset($_POST['nilai_diskon']) && is_numeric($_POST['nilai_diskon']) ? (float) $_POST['nilai_diskon'] : null;

if ($voucherNo === null || $nama_voucher === '' || $nilai_diskon === null || $nilai_diskon < 0) {
	echo 'Data tidak lengkap atau tidak valid.';
	exit;
}

$stmt = mysqli_prepare($conn, "UPDATE voucher SET nama_voucher = ?, nilai_diskon = ? WHERE voucherNo = ?");
if (!$stmt) { die('Prepare gagal: ' . mysqli_error($conn)); }
mysqli_stmt_bind_param($stmt, 'sdi', $nama_voucher, $nilai_diskon, $voucherNo);
$ok = mysqli_stmt_execute($stmt);
mysqli_stmt_close($stmt);

header('Location: voucher_tambah.php');
exit;

?>
