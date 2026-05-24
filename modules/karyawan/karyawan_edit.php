<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../helpers/auth_helper.php';

requireRole('admin');

$karyawanNo = isset($_GET['karyawanNo']) && is_numeric($_GET['karyawanNo']) ? (int) $_GET['karyawanNo'] : null;

if ($karyawanNo === null) {
	echo 'ID karyawan tidak valid.';
	exit;
}

$stmt = mysqli_prepare($conn, "SELECT karyawanNo, nama, kontak FROM karyawan WHERE karyawanNo = ?");
mysqli_stmt_bind_param($stmt, 'i', $karyawanNo);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$row = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

if (!$row) {
	echo 'Data karyawan tidak ditemukan.';
	exit;
}
?>
<!doctype html>
<html lang="id">
<head>
	<meta charset="utf-8" />
	<meta name="viewport" content="width=device-width,initial-scale=1" />
	<title>Karyawan — Edit</title>
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
					<h2>Edit Karyawan</h2>
					<form method="POST" action="karyawan_update.php">
						<input type="hidden" name="karyawanNo" value="<?php echo htmlspecialchars($row['karyawanNo']); ?>">

						<div class="mt-12">
							<label for="nama">Nama</label>
							<input id="nama" name="nama" class="form-input" type="text" required value="<?php echo htmlspecialchars($row['nama']); ?>">
						</div>

						<div class="mt-12">
							<label for="kontak">Kontak</label>
							<input id="kontak" name="kontak" class="form-input" type="text" required value="<?php echo htmlspecialchars($row['kontak']); ?>">
						</div>

						<div class="form-actions">
							<button type="submit" class="btn">Simpan</button>
							<a href="karyawan_tambah.php" class="btn">Batal</a>
						</div>
					</form>
				</div>
			</div>
		</main>
	</div>
</body>
</html>
