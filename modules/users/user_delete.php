<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../helpers/auth_helper.php';

requireRole('admin');

$usernameNo = isset($_GET['usernameNo']) && is_numeric($_GET['usernameNo']) ? (int) $_GET['usernameNo'] : null;

if ($usernameNo === null) {
	echo 'ID tidak valid.';
	exit;
}

$stmt = mysqli_prepare($conn, "DELETE FROM data_user WHERE usernameNo = ?");
if (!$stmt) { die('Prepare gagal: ' . mysqli_error($conn)); }
mysqli_stmt_bind_param($stmt, 'i', $usernameNo);
$ok = mysqli_stmt_execute($stmt);
mysqli_stmt_close($stmt);

header('Location: user_tambah.php');
exit;

?>
