<?php
require_once __DIR__ . '/../../config/database.php';

$metode_pembayaranNo = isset($_GET['metode_pembayaranNo']) && is_numeric($_GET['metode_pembayaranNo']) ? (int) $_GET['metode_pembayaranNo'] : null;

if ($metode_pembayaranNo === null) {
	echo 'ID tidak valid.';
	exit;
}

$stmt = mysqli_prepare($conn, "DELETE FROM metode_pembayaran WHERE metode_pembayaranNo = ?");
if (!$stmt) { die('Prepare gagal: ' . mysqli_error($conn)); }
mysqli_stmt_bind_param($stmt, 'i', $metode_pembayaranNo);
$ok = mysqli_stmt_execute($stmt);
mysqli_stmt_close($stmt);

header('Location: metode_tambah.php?deleted=1');
exit;

?>
