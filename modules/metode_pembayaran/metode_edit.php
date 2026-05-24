<?php
require_once __DIR__ . '/../../config/database.php';

$metode_pembayaranNo = isset($_GET['metode_pembayaranNo']) && is_numeric($_GET['metode_pembayaranNo']) ? (int) $_GET['metode_pembayaranNo'] : null;

if ($metode_pembayaranNo === null) {
	echo 'ID metode tidak valid.';
	exit;
}

$stmt = mysqli_prepare($conn, "SELECT metode_pembayaranNo, nama_metode FROM metode_pembayaran WHERE metode_pembayaranNo = ?");
mysqli_stmt_bind_param($stmt, 'i', $metode_pembayaranNo);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$row = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

if (!$row) {
	echo 'Data metode tidak ditemukan.';
	exit;
}
?>
<!doctype html>
<html lang="id">
<head>
	<meta charset="utf-8" />
	<meta name="viewport" content="width=device-width,initial-scale=1" />
	<title>Metode Pembayaran — Edit</title>
	<link rel="stylesheet" href="../../assets/css/main.css">
	<link rel="stylesheet" href="../../assets/css/components.css">
	<link rel="stylesheet" href="../../assets/css/modules.css">
</head>
<body>
	<div class="app-layout">
		<?php include __DIR__ . '/../../components/sidebar.php'; ?>
		<main class="main-content module-metode-pembayaran">
			<div class="container">
				<div class="card">
					<h2>Edit Metode Pembayaran</h2>
					<form method="POST" action="metode_update.php">
						<input type="hidden" name="metode_pembayaranNo" value="<?php echo htmlspecialchars($row['metode_pembayaranNo']); ?>">

						<div class="mt-12">
							<label for="nama_metode">Nama Metode</label>
							<input id="nama_metode" name="nama_metode" class="form-input" type="text" required value="<?php echo htmlspecialchars($row['nama_metode']); ?>">
						</div>

						<div class="form-actions">
							<button type="submit" class="btn">Simpan</button>
							<a href="metode_tambah.php" class="btn">Batal</a>
						</div>
					</form>
				</div>
			</div>
		</main>
	</div>
</body>
</html>
