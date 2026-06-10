<?php
require_once __DIR__ . '/../../config/database.php';

if (!isset($_GET['SatuanNo']) || !is_numeric($_GET['SatuanNo'])) {
    header('Location: satuan_tambah.php');
    exit;
}

$SatuanNo = (int) $_GET['SatuanNo'];

try {
    // Step 1: Delete detail_resep yang mereferensi satuan ini
    $stmt1 = mysqli_prepare($conn, "DELETE FROM detail_resep WHERE SatuanNo = ?");
    if (!$stmt1) throw new Exception('Prepare delete detail_resep gagal: ' . mysqli_error($conn));
    mysqli_stmt_bind_param($stmt1, 'i', $SatuanNo);
    if (!mysqli_stmt_execute($stmt1)) throw new Exception('Delete detail_resep gagal: ' . mysqli_stmt_error($stmt1));
    mysqli_stmt_close($stmt1);
    
    // Step 2: Delete stok yang mereferensi satuan ini
    $stmt2 = mysqli_prepare($conn, "DELETE FROM stok WHERE SatuanNo = ?");
    if (!$stmt2) throw new Exception('Prepare delete stok gagal: ' . mysqli_error($conn));
    mysqli_stmt_bind_param($stmt2, 'i', $SatuanNo);
    if (!mysqli_stmt_execute($stmt2)) throw new Exception('Delete stok gagal: ' . mysqli_stmt_error($stmt2));
    mysqli_stmt_close($stmt2);
    
    // Step 3: Delete satuan itu sendiri
    $stmt3 = mysqli_prepare($conn, "DELETE FROM satuan WHERE SatuanNo = ?");
    if (!$stmt3) throw new Exception('Prepare delete satuan gagal: ' . mysqli_error($conn));
    mysqli_stmt_bind_param($stmt3, 'i', $SatuanNo);
    if (!mysqli_stmt_execute($stmt3)) throw new Exception('Delete satuan gagal: ' . mysqli_stmt_error($stmt3));
    mysqli_stmt_close($stmt3);
    
    header('Location: satuan_tambah.php?deleted=1');
    exit;
} catch (Exception $e) {
    die('Error saat menghapus satuan: ' . htmlspecialchars($e->getMessage()));
}
?>
