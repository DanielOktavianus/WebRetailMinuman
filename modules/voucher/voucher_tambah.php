<?php
require_once __DIR__ . '/../../config/database.php';

$successMessage = '';
$errorMessage = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	$nama_voucher = isset($_POST['nama_voucher']) ? trim($_POST['nama_voucher']) : '';
	$nilai_diskon = isset($_POST['nilai_diskon']) && is_numeric($_POST['nilai_diskon']) ? (float) $_POST['nilai_diskon'] : null;

	if ($nama_voucher === '' || $nilai_diskon === null || $nilai_diskon < 0) {
		$errorMessage = 'Nama Voucher dan Nilai Diskon harus diisi dengan benar.';
	} else {
		$stmt = mysqli_prepare($conn, "INSERT INTO voucher (nama_voucher, nilai_diskon) VALUES (?, ?)");
		if (!$stmt) {
			$errorMessage = 'Prepare gagal: ' . mysqli_error($conn);
		} else {
			mysqli_stmt_bind_param($stmt, 'sd', $nama_voucher, $nilai_diskon);
			$ok = mysqli_stmt_execute($stmt);
			if ($ok) {
				$successMessage = 'Voucher berhasil ditambahkan.';
				$_POST = array();
			} else {
				$errorMessage = 'Gagal menyimpan: ' . mysqli_error($conn);
			}
			mysqli_stmt_close($stmt);
		}
	}
}
?>
<!doctype html>
<html lang="id">
<head>
	<meta charset="utf-8" />
	<meta name="viewport" content="width=device-width,initial-scale=1" />
	<title>Voucher — Tambah</title>
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
					<h2>Tambah Voucher</h2>
					<?php if ($successMessage): ?>
						<div class="alert success"><?php echo htmlspecialchars($successMessage); ?></div>
					<?php endif; ?>
					<?php if ($errorMessage): ?>
						<div class="alert error"><?php echo htmlspecialchars($errorMessage); ?></div>
					<?php endif; ?>
					<form method="POST" action="">
						<div class="mt-12">
						<label for="nama_voucher">Nama Voucher</label>
						<input id="nama_voucher" name="nama_voucher" class="form-input" type="text" required placeholder="Contoh: Diskon 10%, Flash Sale, etc" value="<?php echo isset($_POST['nama_voucher']) ? htmlspecialchars($_POST['nama_voucher']) : ''; ?>">
					</div>

					<div class="mt-12">
							<label for="nilai_diskon">Nilai Diskon (Rp)</label>
							<input id="nilai_diskon" name="nilai_diskon" class="form-input" type="number" step="0.01" min="0" required placeholder="Contoh: 5000, 10000" value="<?php echo isset($_POST['nilai_diskon']) ? htmlspecialchars($_POST['nilai_diskon']) : ''; ?>">
						</div>

						<div class="form-actions">
							<button type="submit" class="btn">Simpan</button>
							<button type="reset" class="btn">Reset</button>
						</div>
					</form>
				</div>

				<div class="card" style="margin-top:18px">
					<h2>Daftar Voucher</h2>
					<?php
					$voucherRes = mysqli_query($conn, "SELECT voucherNo, nama_voucher, nilai_diskon FROM voucher ORDER BY voucherNo DESC");
					if ($voucherRes && mysqli_num_rows($voucherRes) > 0) {
						echo '<table style="width:100%;border-collapse:collapse">';
						echo '<thead><tr><th style="text-align:left;padding:8px">#</th><th style="text-align:left;padding:8px">Nama Voucher</th><th style="text-align:left;padding:8px">Nilai Diskon</th><th style="text-align:right;padding:8px">Aksi</th></tr></thead>';
						echo '<tbody>';
						$i = 1;
						while ($row = mysqli_fetch_assoc($voucherRes)) {
							$id = htmlspecialchars($row['voucherNo']);
							$nama = htmlspecialchars($row['nama_voucher']);
							$diskon = number_format($row['nilai_diskon'], 2, ',', '.');
							echo "<tr>";
							echo "<td style=\"padding:8px;vertical-align:top\">" . $i++ . "</td>";
							echo "<td style=\"padding:8px;vertical-align:top\">{$nama}</td>";
							echo "<td style=\"padding:8px;vertical-align:top;color:#b91c1c;font-weight:600\">- Rp {$diskon}</td>";
							echo "<td style=\"padding:8px;vertical-align:top;text-align:right\"><a class=\"btn\" href=\"voucher_edit.php?voucherNo={$id}\">Edit</a> <a class=\"btn\" style=\"background:#ef4444\" href=\"voucher_delete.php?voucherNo={$id}\" onclick=\"return confirm('Hapus voucher ini?')\">Hapus</a></td>";
							echo "</tr>\n";
						}
						echo '</tbody></table>';
					} else {
						echo '<p>Tidak ada data voucher.</p>';
					}
					?>
				</div>
			</div>
		</main>
	</div>
</body>
</html>
