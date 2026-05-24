<?php
require_once __DIR__ . '/../../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: menu_tambah.php');
    exit;
}

$MenuNo = isset($_POST['MenuNo']) && is_numeric($_POST['MenuNo']) ? (int) $_POST['MenuNo'] : null;
$nama_menu = isset($_POST['nama_menu']) ? trim($_POST['nama_menu']) : '';

if ($MenuNo === null || $nama_menu === '') {
    echo 'Data tidak lengkap.';
    exit;
}

$stmt = mysqli_prepare($conn, "UPDATE menu SET nama_menu = ? WHERE MenuNo = ?");
if (!$stmt) {
    die('Prepare gagal: ' . mysqli_error($conn));
}

mysqli_stmt_bind_param($stmt, 'si', $nama_menu, $MenuNo);
if (mysqli_stmt_execute($stmt)) {
    mysqli_stmt_close($stmt);
    header('Location: menu_tambah.php');
    exit;
} else {
    die('Update gagal: ' . mysqli_stmt_error($stmt));
}
?>
