<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../helpers/auth_helper.php';

requireRole('admin');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
	header('Location: karyawan_tambah.php');
	exit;
}

$karyawanNo = isset($_POST['karyawanNo']) && is_numeric($_POST['karyawanNo']) ? (int) $_POST['karyawanNo'] : null;
$nama = isset($_POST['nama']) ? trim($_POST['nama']) : '';
$kontak = isset($_POST['kontak']) ? trim($_POST['kontak']) : '';

if ($karyawanNo === null || $nama === '' || $kontak === '') {
	echo 'Data tidak lengkap.';
	exit;
}

$stmt = mysqli_prepare($conn, "UPDATE karyawan SET nama = ?, kontak = ? WHERE karyawanNo = ?");
if (!$stmt) { die('Prepare gagal: ' . mysqli_error($conn)); }
mysqli_stmt_bind_param($stmt, 'ssi', $nama, $kontak, $karyawanNo);
$ok = mysqli_stmt_execute($stmt);
mysqli_stmt_close($stmt);

header('Location: karyawan_tambah.php');
exit;

?>
