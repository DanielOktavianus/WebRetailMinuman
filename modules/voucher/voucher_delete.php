<?php
require_once __DIR__ . '/../../config/database.php';

$voucherNo = isset($_GET['voucherNo']) && is_numeric($_GET['voucherNo']) ? (int) $_GET['voucherNo'] : null;

if ($voucherNo === null) {
	echo 'ID tidak valid.';
	exit;
}

$stmt = mysqli_prepare($conn, "DELETE FROM voucher WHERE voucherNo = ?");
if (!$stmt) { die('Prepare gagal: ' . mysqli_error($conn)); }
mysqli_stmt_bind_param($stmt, 'i', $voucherNo);
$ok = mysqli_stmt_execute($stmt);
mysqli_stmt_close($stmt);

header('Location: voucher_tambah.php');
exit;

?>
