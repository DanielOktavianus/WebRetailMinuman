<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../helpers/auth_helper.php';

requireRole('admin');

$usernameNo = isset($_GET['usernameNo']) && is_numeric($_GET['usernameNo']) ? (int) $_GET['usernameNo'] : null;

if ($usernameNo === null) {
	echo 'ID user tidak valid.';
	exit;
}

$stmt = mysqli_prepare($conn, "SELECT usernameNo, karyawanNo, username, jabatan, role FROM data_user WHERE usernameNo = ?");
mysqli_stmt_bind_param($stmt, 'i', $usernameNo);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$row = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

if (!$row) {
	echo 'Data user tidak ditemukan.';
	exit;
}

// Ambil daftar karyawan
$karyawanResult = mysqli_query($conn, "SELECT karyawanNo, nama FROM karyawan ORDER BY nama ASC");
?>
<!doctype html>
<html lang="id">
<head>
	<meta charset="utf-8" />
	<meta name="viewport" content="width=device-width,initial-scale=1" />
	<title>User — Edit</title>
	<link rel="stylesheet" href="../../assets/css/main.css">
	<link rel="stylesheet" href="../../assets/css/components.css">
	<link rel="stylesheet" href="../../assets/css/modules.css">
</head>
<body>
	<div class="app-layout">
		<?php include __DIR__ . '/../../components/sidebar.php'; ?>
		<main class="main-content module-user">
			<div class="container">
				<div class="card">
					<h2>Edit User</h2>
					<form method="POST" action="user_update.php">
						<input type="hidden" name="usernameNo" value="<?php echo htmlspecialchars($row['usernameNo']); ?>">

						<div class="mt-12">
							<label for="karyawanNo">Karyawan</label>
							<select id="karyawanNo" name="karyawanNo" class="form-input" required>
								<option value="">-- Pilih Karyawan --</option>
								<?php if ($karyawanResult): while ($k = mysqli_fetch_assoc($karyawanResult)): ?>
									<option value="<?php echo $k['karyawanNo']; ?>" <?php echo ($k['karyawanNo'] == $row['karyawanNo']) ? 'selected' : ''; ?>>
										<?php echo htmlspecialchars($k['nama']); ?>
									</option>
								<?php endwhile; endif; ?>
							</select>
						</div>

						<div class="mt-12">
							<label for="username">Username</label>
							<input id="username" name="username" class="form-input" type="text" required value="<?php echo htmlspecialchars($row['username']); ?>">
						</div>

						<div class="mt-12">
							<label for="jabatan">Jabatan</label>
							<input id="jabatan" name="jabatan" class="form-input" type="text" required value="<?php echo htmlspecialchars($row['jabatan']); ?>">
						</div>

					<div class="mt-12">
						<label for="role">Role</label>
						<select id="role" name="role" class="form-input" required>
							<option value="karyawan" <?php echo ($row['role'] === 'karyawan') ? 'selected' : ''; ?>>Karyawan (Limited Access)</option>
							<option value="admin" <?php echo ($row['role'] === 'admin') ? 'selected' : ''; ?>>Pemilik (Full Access)</option>
						</select>
					</div>
							<button type="submit" class="btn">Simpan</button>
							<a href="user_tambah.php" class="btn">Batal</a>
						</div>
					</form>
				</div>
			</div>
		</main>
	</div>
</body>
</html>
