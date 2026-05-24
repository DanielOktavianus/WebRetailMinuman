<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../helpers/auth_helper.php';

requireRole('admin');

$successMessage = '';
$errorMessage = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	$nama = isset($_POST['nama']) ? trim($_POST['nama']) : '';
	$kontak = isset($_POST['kontak']) ? trim($_POST['kontak']) : '';

	if ($nama === '' || $kontak === '') {
		$errorMessage = 'Nama dan Kontak harus diisi.';
	} else {
		$stmt = mysqli_prepare($conn, "INSERT INTO karyawan (nama, kontak) VALUES (?, ?)");
		if (!$stmt) {
			$errorMessage = 'Prepare gagal: ' . mysqli_error($conn);
		} else {
			mysqli_stmt_bind_param($stmt, 'ss', $nama, $kontak);
			$ok = mysqli_stmt_execute($stmt);
			if ($ok) {
				$successMessage = 'Karyawan berhasil ditambahkan.';
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
	<title>Karyawan — Tambah</title>
	<link rel="stylesheet" href="../../assets/css/main.css">
	<link rel="stylesheet" href="../../assets/css/components.css">
	<link rel="stylesheet" href="../../assets/css/modules.css">
</head>
<body>
	<div class="app-layout">
		<?php include __DIR__ . '/../../components/sidebar.php'; ?>
		<main class="main-content module-karyawan">
			<div class="container">
				<div class="card">
					<h2>Tambah Karyawan</h2>
					<?php if ($successMessage): ?>
						<div class="alert success"><?php echo htmlspecialchars($successMessage); ?></div>
					<?php endif; ?>
					<?php if ($errorMessage): ?>
						<div class="alert error"><?php echo htmlspecialchars($errorMessage); ?></div>
					<?php endif; ?>
					<form method="POST" action="">
						<div class="mt-12">
							<label for="nama">Nama</label>
							<input id="nama" name="nama" class="form-input" type="text" required placeholder="Nama Karyawan" value="<?php echo isset($_POST['nama']) ? htmlspecialchars($_POST['nama']) : ''; ?>">
						</div>

						<div class="mt-12">
							<label for="kontak">Kontak</label>
							<input id="kontak" name="kontak" class="form-input" type="text" required placeholder="Nomor Telepon / WA" value="<?php echo isset($_POST['kontak']) ? htmlspecialchars($_POST['kontak']) : ''; ?>">
						</div>

						<div class="form-actions">
							<button type="submit" class="btn">Simpan</button>
							<button type="reset" class="btn">Reset</button>
						</div>
					</form>
				</div>

				<div class="card" style="margin-top:18px">
					<h2>Daftar Karyawan</h2>
					<?php
					$karyawanRes = mysqli_query($conn, "SELECT karyawanNo, nama, kontak FROM karyawan ORDER BY karyawanNo DESC");
					if ($karyawanRes && mysqli_num_rows($karyawanRes) > 0) {
						echo '<table style="width:100%;border-collapse:collapse">';
						echo '<thead><tr><th style="text-align:left;padding:8px">#</th><th style="text-align:left;padding:8px">Nama</th><th style="text-align:left;padding:8px">Kontak</th><th style="text-align:right;padding:8px">Aksi</th></tr></thead>';
						echo '<tbody>';
						$i = 1;
						while ($row = mysqli_fetch_assoc($karyawanRes)) {
							$id = htmlspecialchars($row['karyawanNo']);
							$nama = htmlspecialchars($row['nama']);
							$kontak = htmlspecialchars($row['kontak']);
							echo "<tr>";
							echo "<td style=\"padding:8px;vertical-align:top\">" . $i++ . "</td>";
							echo "<td style=\"padding:8px;vertical-align:top\">{$nama}</td>";
							echo "<td style=\"padding:8px;vertical-align:top\">{$kontak}</td>";
							echo "<td style=\"padding:8px;vertical-align:top;text-align:right\"><a class=\"btn\" href=\"karyawan_edit.php?karyawanNo={$id}\">Edit</a> <a class=\"btn\" style=\"background:#ef4444\" href=\"karyawan_delete.php?karyawanNo={$id}\" onclick=\"return confirm('Hapus karyawan ini?')\">Hapus</a></td>";
							echo "</tr>\n";
						}
						echo '</tbody></table>';
					} else {
						echo '<p>Tidak ada data karyawan.</p>';
					}
					?>
				</div>
			</div>
		</main>
	</div>
</body>
</html>
