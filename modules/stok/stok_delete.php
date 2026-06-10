<?php
require_once __DIR__ . '/../../config/database.php';

if (!isset($_GET['StokNo']) || !is_numeric($_GET['StokNo'])) {
    header('Location: stok_tambah.php');
    exit;
}

$StokNo = (int) $_GET['StokNo'];

try {
    // Step 1: Delete detail_resep yang mereferensi stok ini
    $stmt1 = mysqli_prepare($conn, "DELETE FROM detail_resep WHERE StokNo = ?");
    if (!$stmt1) throw new Exception('Prepare delete detail_resep gagal: ' . mysqli_error($conn));
    mysqli_stmt_bind_param($stmt1, 'i', $StokNo);
    if (!mysqli_stmt_execute($stmt1)) throw new Exception('Delete detail_resep gagal: ' . mysqli_stmt_error($stmt1));
    mysqli_stmt_close($stmt1);
    
    // Step 2: Delete stok itu sendiri
    $stmt2 = mysqli_prepare($conn, "DELETE FROM stok WHERE StokNo = ?");
    if (!$stmt2) throw new Exception('Prepare delete stok gagal: ' . mysqli_error($conn));
    mysqli_stmt_bind_param($stmt2, 'i', $StokNo);
    if (!mysqli_stmt_execute($stmt2)) throw new Exception('Delete stok gagal: ' . mysqli_stmt_error($stmt2));
    mysqli_stmt_close($stmt2);
    
    header('Location: stok_tambah.php?deleted=1');
    exit;
} catch (Exception $e) {
    die('Error saat menghapus stok: ' . htmlspecialchars($e->getMessage()));
}
?>
