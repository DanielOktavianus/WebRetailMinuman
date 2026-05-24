<?php
require_once __DIR__ . '/../../config/database.php';

$successMessage = '';
$errorMessage = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	$nama_metode = isset($_POST['nama_metode']) ? trim($_POST['nama_metode']) : '';

	if ($nama_metode === '') {
		$errorMessage = 'Nama metode harus diisi.';
	} else {
		$stmt = mysqli_prepare($conn, "INSERT INTO metode_pembayaran (nama_metode) VALUES (?)");
		mysqli_stmt_bind_param($stmt, 's', $nama_metode);
		$ok = mysqli_stmt_execute($stmt);
		if ($ok) {
			$successMessage = 'Metode pembayaran berhasil ditambahkan.';
			$_POST = array();
		} else {
			$errorMessage = 'Gagal menyimpan: ' . mysqli_error($conn);
		}
		mysqli_stmt_close($stmt);
	}
}
?>
<!doctype html>
<html lang="id">
<head>
	<meta charset="utf-8" />
	<meta name="viewport" content="width=device-width,initial-scale=1" />
	<title>Metode Pembayaran — Tambah</title>
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
					<h2>Tambah Metode Pembayaran</h2>
					<?php if ($successMessage): ?>
						<div class="alert success"><?php echo htmlspecialchars($successMessage); ?></div>
					<?php endif; ?>
					<?php if ($errorMessage): ?>
						<div class="alert error"><?php echo htmlspecialchars($errorMessage); ?></div>
					<?php endif; ?>
					<form method="POST" action="">
						<div class="mt-12">
							<label for="nama_metode">Nama Metode</label>
							<input id="nama_metode" name="nama_metode" class="form-input" type="text" required placeholder="Contoh: Transfer Bank, Tunai" value="<?php echo isset($_POST['nama_metode']) ? htmlspecialchars($_POST['nama_metode']) : ''; ?>">
						</div>

						<div class="form-actions">
							<button type="submit" class="btn">Simpan</button>
							<button type="reset" class="btn">Reset</button>
						</div>
					</form>
				</div>

				<div class="card" style="margin-top:20px">
					<h3>Data Metode Pembayaran</h3>
					<?php
					$metodeRes = mysqli_query($conn, "SELECT metode_pembayaranNo, nama_metode FROM metode_pembayaran ORDER BY metode_pembayaranNo DESC");
					if ($metodeRes && mysqli_num_rows($metodeRes) > 0) {
						echo '<table style="width:100%;border-collapse:collapse">';
						echo '<thead><tr><th style="text-align:left;padding:8px">#</th><th style="text-align:left;padding:8px">Metode</th><th style="text-align:right;padding:8px">Aksi</th></tr></thead>';
						echo '<tbody>';
						$i = 1;
						while ($row = mysqli_fetch_assoc($metodeRes)) {
							$id = htmlspecialchars($row['metode_pembayaranNo']);
							$nama = htmlspecialchars($row['nama_metode']);
							echo "<tr>";
							echo "<td style=\"padding:8px;vertical-align:top\">" . $i++ . "</td>";
							echo "<td style=\"padding:8px;vertical-align:top\">{$nama}</td>";
							echo "<td style=\"padding:8px;vertical-align:top;text-align:right\"><a class=\"btn\" href=\"metode_edit.php?metode_pembayaranNo={$id}\">Edit</a> <a class=\"btn\" style=\"background:#ef4444\" href=\"metode_delete.php?metode_pembayaranNo={$id}\" onclick=\"return confirm('Hapus metode ini?')\">Hapus</a></td>";
							echo "</tr>\n";
						}
						echo '</tbody></table>';
					} else {
						echo '<p>Tidak ada data metode pembayaran.</p>';
					}
					?>
				</div>
			</div>
		</main>
	</div>
</body>
</html>
