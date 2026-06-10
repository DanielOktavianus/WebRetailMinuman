<?php
require_once __DIR__ . '/../../config/database.php';

if (!isset($_GET['BahanNo']) || !is_numeric($_GET['BahanNo'])) {
    header('Location: bahan_tambah.php');
    exit;
}

$BahanNo = (int) $_GET['BahanNo'];

try {
    // Step 1: Delete detail_resep yang mereferensi stok yang mereferensi bahan ini
    $stmt1 = mysqli_prepare($conn, 
        "DELETE FROM detail_resep WHERE StokNo IN 
         (SELECT StokNo FROM stok WHERE BahanNo = ?)");
    if (!$stmt1) throw new Exception('Prepare delete detail_resep gagal: ' . mysqli_error($conn));
    mysqli_stmt_bind_param($stmt1, 'i', $BahanNo);
    if (!mysqli_stmt_execute($stmt1)) throw new Exception('Delete detail_resep gagal: ' . mysqli_stmt_error($stmt1));
    mysqli_stmt_close($stmt1);
    
    // Step 2: Delete stok yang mereferensi bahan ini
    $stmt2 = mysqli_prepare($conn, "DELETE FROM stok WHERE BahanNo = ?");
    if (!$stmt2) throw new Exception('Prepare delete stok gagal: ' . mysqli_error($conn));
    mysqli_stmt_bind_param($stmt2, 'i', $BahanNo);
    if (!mysqli_stmt_execute($stmt2)) throw new Exception('Delete stok gagal: ' . mysqli_stmt_error($stmt2));
    mysqli_stmt_close($stmt2);
    
    // Step 3: Delete bahan itu sendiri
    $stmt3 = mysqli_prepare($conn, "DELETE FROM bahan WHERE BahanNo = ?");
    if (!$stmt3) throw new Exception('Prepare delete bahan gagal: ' . mysqli_error($conn));
    mysqli_stmt_bind_param($stmt3, 'i', $BahanNo);
    if (!mysqli_stmt_execute($stmt3)) throw new Exception('Delete bahan gagal: ' . mysqli_stmt_error($stmt3));
    mysqli_stmt_close($stmt3);
    
    header('Location: bahan_tambah.php?deleted=1');
    exit;
} catch (Exception $e) {
    die('Error saat menghapus bahan: ' . htmlspecialchars($e->getMessage()));
}
?>
