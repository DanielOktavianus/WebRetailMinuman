<?php
require_once __DIR__ . '/../../config/database.php';

$voucherNo = isset($_GET['voucherNo']) && is_numeric($_GET['voucherNo']) ? (int) $_GET['voucherNo'] : null;

if ($voucherNo === null) {
	echo 'ID voucher tidak valid.';
	exit;
}

$stmt = mysqli_prepare($conn, "SELECT voucherNo, nama_voucher, nilai_diskon FROM voucher WHERE voucherNo = ?");
mysqli_stmt_bind_param($stmt, 'i', $voucherNo);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$row = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

if (!$row) {
	echo 'Data voucher tidak ditemukan.';
	exit;
}
?>
<!doctype html>
<html lang="id">
<head>
	<meta charset="utf-8" />
	<meta name="viewport" content="width=device-width,initial-scale=1" />
	<title>Voucher — Edit</title>
	<link rel="stylesheet" href="../../assets/css/main.css">
	<link rel="stylesheet" href="../../assets/css/components.css">
	<link rel="stylesheet" href="../../assets/css/modules.css">
</head>
<body>
	<div class="app-layout">
		<?php include __DIR__ . '/../../components/sidebar.php'; ?>
		<main class="main-content module-voucher">
			<div class="container">
				<div class="card">
					<h2>Edit Voucher</h2>
					<form method="POST" action="voucher_update.php">
						<input type="hidden" name="voucherNo" value="<?php echo htmlspecialchars($row['voucherNo']); ?>">

						<div class="mt-12">
							<label for="nama_voucher">Nama Voucher</label>
							<input id="nama_voucher" name="nama_voucher" class="form-input" type="text" required value="<?php echo htmlspecialchars($row['nama_voucher']); ?>">
						</div>

						<div class="mt-12">
							<label for="nilai_diskon">Nilai Diskon (Rp)</label>
							<input id="nilai_diskon" name="nilai_diskon" class="form-input" type="number" step="0.01" min="0" required value="<?php echo htmlspecialchars($row['nilai_diskon']); ?>">
						</div>

						<div class="form-actions">
							<button type="submit" class="btn">Simpan</button>
							<a href="voucher_tambah.php" class="btn">Batal</a>
						</div>
					</form>
				</div>
						</div>
					</form>
				</div>
			</div>
		</main>
	</div>
</body>
</html>
