<?php
require_once __DIR__ . '/../../config/database.php';

$UkuranNo = isset($_GET['UkuranNo']) && is_numeric($_GET['UkuranNo']) ? (int) $_GET['UkuranNo'] : null;

if ($UkuranNo === null) {
    header('Location: ukuran_tambah.php');
    exit;
}

// Cek apakah ukuran masih dipakai di varian_menu (FK RESTRICT)
$check = mysqli_prepare($conn, "SELECT COUNT(*) AS c FROM varian_menu WHERE ukuranNo = ?");
mysqli_stmt_bind_param($check, 'i', $UkuranNo);
mysqli_stmt_execute($check);
$row = mysqli_fetch_assoc(mysqli_stmt_get_result($check));
mysqli_stmt_close($check);

if ($row['c'] > 0) {
    die('Ukuran tidak bisa dihapus karena masih digunakan oleh <strong>' . $row['c'] .
        '</strong> varian menu. Hapus varian menu terkait terlebih dahulu. <a href="ukuran_tambah.php">Kembali</a>');
}

$stmt = mysqli_prepare($conn, "DELETE FROM ukuran WHERE UkuranNo = ?");
if (!$stmt) die('Prepare gagal: ' . mysqli_error($conn));
mysqli_stmt_bind_param($stmt, 'i', $UkuranNo);
mysqli_stmt_execute($stmt);
mysqli_stmt_close($stmt);

header('Location: ukuran_tambah.php');
exit;
?>
