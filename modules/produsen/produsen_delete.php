<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../helpers/auth_helper.php';

// Require admin access
requireRole('admin');

if (!isset($_GET['produsenNo'])) {
  header('Location: produsen_list.php');
  exit;
}

$ProdusenNo = $_GET['produsenNo'];
if (!is_numeric($ProdusenNo)) {
  header('Location: produsen_list.php');
  exit;
}

$ProdusenNo = (int) $ProdusenNo;

try {
    // Step 1: Delete detail_resep yang mereferensi stok yang mereferensi produsen ini
    $stmt1 = mysqli_prepare($conn, 
        "DELETE FROM detail_resep WHERE StokNo IN 
         (SELECT StokNo FROM stok WHERE ProdusenNo = ?)");
    if (!$stmt1) throw new Exception('Prepare delete detail_resep gagal: ' . mysqli_error($conn));
    mysqli_stmt_bind_param($stmt1, 'i', $ProdusenNo);
    if (!mysqli_stmt_execute($stmt1)) throw new Exception('Delete detail_resep gagal: ' . mysqli_stmt_error($stmt1));
    mysqli_stmt_close($stmt1);
    
    // Step 2: Delete stok yang mereferensi produsen ini
    $stmt2 = mysqli_prepare($conn, "DELETE FROM stok WHERE ProdusenNo = ?");
    if (!$stmt2) throw new Exception('Prepare delete stok gagal: ' . mysqli_error($conn));
    mysqli_stmt_bind_param($stmt2, 'i', $ProdusenNo);
    if (!mysqli_stmt_execute($stmt2)) throw new Exception('Delete stok gagal: ' . mysqli_stmt_error($stmt2));
    mysqli_stmt_close($stmt2);
    
    // Step 3: Delete produsen itu sendiri
    $stmt3 = mysqli_prepare($conn, "DELETE FROM produsen WHERE ProdusenNo = ?");
    if (!$stmt3) throw new Exception('Prepare delete produsen gagal: ' . mysqli_error($conn));
    mysqli_stmt_bind_param($stmt3, 'i', $ProdusenNo);
    if (!mysqli_stmt_execute($stmt3)) throw new Exception('Delete produsen gagal: ' . mysqli_stmt_error($stmt3));
    mysqli_stmt_close($stmt3);
    
    header('Location: produsen_list.php');
    exit;
} catch (Exception $e) {
    die('Error saat menghapus produsen: ' . htmlspecialchars($e->getMessage()));
}
?>
