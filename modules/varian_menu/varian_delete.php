<?php
require_once __DIR__ . '/../../config/database.php';

$VarianMenuNo = isset($_GET['VarianMenuNo']) && is_numeric($_GET['VarianMenuNo']) ? (int) $_GET['VarianMenuNo'] : null;

if ($VarianMenuNo === null) {
    header('Location: varian_tambah.php');
    exit;
}

try {
    // Step 1: Delete detail_transaksi yang mereferensi varian_menu ini
    $stmt1 = mysqli_prepare($conn, "DELETE FROM detail_transaksi WHERE VarianMenuNo = ?");
    if (!$stmt1) throw new Exception('Prepare delete detail_transaksi gagal: ' . mysqli_error($conn));
    mysqli_stmt_bind_param($stmt1, 'i', $VarianMenuNo);
    if (!mysqli_stmt_execute($stmt1)) throw new Exception('Delete detail_transaksi gagal: ' . mysqli_stmt_error($stmt1));
    mysqli_stmt_close($stmt1);
    
    // Step 2: Delete varian_menu itu sendiri
    $stmt2 = mysqli_prepare($conn, "DELETE FROM varian_menu WHERE VarianMenuNo = ?");
    if (!$stmt2) throw new Exception('Prepare delete varian_menu gagal: ' . mysqli_error($conn));
    mysqli_stmt_bind_param($stmt2, 'i', $VarianMenuNo);
    if (!mysqli_stmt_execute($stmt2)) throw new Exception('Delete varian_menu gagal: ' . mysqli_stmt_error($stmt2));
    mysqli_stmt_close($stmt2);
    
    header('Location: varian_tambah.php?deleted=1');
    exit;
} catch (Exception $e) {
    die('Error saat menghapus varian menu: ' . htmlspecialchars($e->getMessage()));
}
?>
