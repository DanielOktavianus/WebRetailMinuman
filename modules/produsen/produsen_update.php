<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../helpers/auth_helper.php';

// Require admin access
requireRole('admin');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  header('Location: produsen_list.php');
  exit;
}

$id = isset($_POST['ProdusenNo']) ? (int) $_POST['ProdusenNo'] : 0;
$nama = isset($_POST['Nama_Produsen']) ? trim($_POST['Nama_Produsen']) : '';
$alamat = isset($_POST['Alamat']) ? trim($_POST['Alamat']) : '';
$kontak = isset($_POST['Kontak']) ? trim($_POST['Kontak']) : '';

if ($id <= 0 || $nama === '') {
  header('Location: produsen_edit.php?produsenNo=' . $id);
  exit;
}

$stmt = mysqli_prepare($conn, "UPDATE produsen SET Nama_Produsen = ?, Alamat = ?, Kontak = ? WHERE ProdusenNo = ?");
mysqli_stmt_bind_param($stmt, 'sssi', $nama, $alamat, $kontak, $id);
mysqli_stmt_execute($stmt);
mysqli_stmt_close($stmt);

header('Location: produsen_list.php');
exit;

?>
