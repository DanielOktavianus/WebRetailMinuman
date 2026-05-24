<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../helpers/auth_helper.php';

// Require admin access
requireRole('admin');
?>
<!doctype html>
<html lang="id">
<head>
	<meta charset="utf-8" />
	<meta name="viewport" content="width=device-width,initial-scale=1" />
	<title>Produsen — Edit</title>
	<link rel="stylesheet" href="../../assets/css/main.css">
	<link rel="stylesheet" href="../../assets/css/components.css">
	<link rel="stylesheet" href="../../assets/css/modules.css">
</head>
<body>
	<div class="app-layout">
		<?php include __DIR__ . '/../../components/sidebar.php'; ?>
		<main class="main-content">
			<div class="container">
				<div class="card">
					<h2>Edit Produsen</h2>
					<?php
					require_once __DIR__ . '/../../config/database.php';
					if (!isset($_GET['produsenNo']) || !is_numeric($_GET['produsenNo'])) {
						echo '<p>ID tidak valid.</p>';
					} else {
						$id = (int) $_GET['produsenNo'];
						$stmt = mysqli_prepare($conn, "SELECT ProdusenNo, Nama_Produsen, Alamat, Kontak FROM produsen WHERE ProdusenNo = ? LIMIT 1");
						mysqli_stmt_bind_param($stmt, 'i', $id);
						mysqli_stmt_execute($stmt);
						$res = mysqli_stmt_get_result($stmt);
						if ($res && mysqli_num_rows($res) === 1) {
							$row = mysqli_fetch_assoc($res);
							$nama = htmlspecialchars($row['Nama_Produsen']);
							$alamat = htmlspecialchars($row['Alamat']);
							$kontak = htmlspecialchars($row['Kontak']);
							?>
							<form method="post" action="produsen_update.php">
								<input type="hidden" name="ProdusenNo" value="<?php echo $id; ?>">
								<div class="form-group">
									<label>Nama Produsen</label>
									<input class="form-input" type="text" name="Nama_Produsen" value="<?php echo $nama; ?>" required>
								</div>
								<div class="form-group">
									<label>Alamat</label>
									<textarea class="form-input" name="Alamat" required><?php echo $alamat; ?></textarea>
								</div>
								<div class="form-group">
									<label>Kontak</label>
									<input class="form-input" type="text" name="Kontak" value="<?php echo $kontak; ?>">
								</div>
								<div class="form-actions">
									<button class="btn" type="submit">Simpan Perubahan</button>
									<a class="btn" href="produsen_list.php">Batal</a>
								</div>
							</form>
							<?php
						} else {
							echo '<p>Data tidak ditemukan.</p>';
						}
						mysqli_stmt_close($stmt);
					}
					?>
				</div>
			</div>
		</main>
	</div>
</body>
</html>

