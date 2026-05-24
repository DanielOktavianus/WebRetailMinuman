<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../helpers/auth_helper.php';

requireRole('admin');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
	header('Location: user_tambah.php');
	exit;
}

$usernameNo = isset($_POST['usernameNo']) && is_numeric($_POST['usernameNo']) ? (int) $_POST['usernameNo'] : null;
$karyawanNo = isset($_POST['karyawanNo']) && is_numeric($_POST['karyawanNo']) ? (int) $_POST['karyawanNo'] : null;
$username = isset($_POST['username']) ? trim($_POST['username']) : '';
$jabatan = isset($_POST['jabatan']) ? trim($_POST['jabatan']) : '';
$role = isset($_POST['role']) ? trim($_POST['role']) : 'karyawan';

if ($usernameNo === null || $karyawanNo === null || $username === '' || $jabatan === '') {
	echo 'Data tidak lengkap.';
	exit;
}

if (!in_array($role, ['admin', 'karyawan'])) {
	echo 'Role tidak valid.';
	exit;
}

$stmt = mysqli_prepare($conn, "UPDATE data_user SET karyawanNo = ?, username = ?, jabatan = ?, role = ? WHERE usernameNo = ?");
if (!$stmt) { die('Prepare gagal: ' . mysqli_error($conn)); }
mysqli_stmt_bind_param($stmt, 'isssi', $karyawanNo, $username, $jabatan, $role, $usernameNo);
$ok = mysqli_stmt_execute($stmt);
mysqli_stmt_close($stmt);

header('Location: user_tambah.php');
exit;

?>
