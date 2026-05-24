<?php
require_once __DIR__ . '/../../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
	header('Location: metode_tambah.php');
	exit;
}

$metode_pembayaranNo = isset($_POST['metode_pembayaranNo']) && is_numeric($_POST['metode_pembayaranNo']) ? (int) $_POST['metode_pembayaranNo'] : null;
$nama_metode = isset($_POST['nama_metode']) ? trim($_POST['nama_metode']) : '';

if ($metode_pembayaranNo === null || $nama_metode === '') {
	echo 'Data tidak lengkap.';
	exit;
}

$stmt = mysqli_prepare($conn, "UPDATE metode_pembayaran SET nama_metode = ? WHERE metode_pembayaranNo = ?");
if (!$stmt) { die('Prepare gagal: ' . mysqli_error($conn)); }
mysqli_stmt_bind_param($stmt, 'si', $nama_metode, $metode_pembayaranNo);
$ok = mysqli_stmt_execute($stmt);
mysqli_stmt_close($stmt);

header('Location: metode_tambah.php');
exit;

?>
