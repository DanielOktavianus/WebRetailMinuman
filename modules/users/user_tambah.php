<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../helpers/auth_helper.php';

requireRole('admin');

$successMessage = '';
$errorMessage = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	$karyawanNo = isset($_POST['karyawanNo']) && is_numeric($_POST['karyawanNo']) ? (int) $_POST['karyawanNo'] : null;
	$username = isset($_POST['username']) ? trim($_POST['username']) : '';
	$password = isset($_POST['password']) ? trim($_POST['password']) : '';
	$jabatan = isset($_POST['jabatan']) ? trim($_POST['jabatan']) : '';
	$role = isset($_POST['role']) ? trim($_POST['role']) : 'karyawan';

	if ($karyawanNo === null || $username === '' || $password === '' || $jabatan === '') {
		$errorMessage = 'Semua field harus diisi.';
	} elseif (!in_array($role, ['admin', 'karyawan'])) {
		$errorMessage = 'Role tidak valid.';
	} else {
		// Hash password
		$hashedPassword = password_hash($password, PASSWORD_BCRYPT);
		
		$stmt = mysqli_prepare($conn, "INSERT INTO data_user (karyawanNo, username, password, jabatan, role) VALUES (?, ?, ?, ?, ?)");
		if (!$stmt) {
			$errorMessage = 'Prepare gagal: ' . mysqli_error($conn);
		} else {
			mysqli_stmt_bind_param($stmt, 'issss', $karyawanNo, $username, $hashedPassword, $jabatan, $role);
			$ok = mysqli_stmt_execute($stmt);
			if ($ok) {
				$successMessage = 'User berhasil ditambahkan.';
				$_POST = array();
			} else {
				$errorMessage = 'Gagal menyimpan: ' . mysqli_error($conn);
			}
			mysqli_stmt_close($stmt);
		}
	}
}

// Ambil daftar karyawan
$karyawanResult = mysqli_query($conn, "SELECT karyawanNo, nama FROM karyawan ORDER BY nama ASC");
?>
<!doctype html>
<html lang="id">
<head>
	<meta charset="utf-8" />
	<meta name="viewport" content="width=device-width,initial-scale=1" />
	<title>User — Tambah</title>
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
					<h2>Tambah User</h2>
					<?php if ($successMessage): ?>
						<div class="alert success"><?php echo htmlspecialchars($successMessage); ?></div>
					<?php endif; ?>
					<?php if ($errorMessage): ?>
						<div class="alert error"><?php echo htmlspecialchars($errorMessage); ?></div>
					<?php endif; ?>
					<form method="POST" action="">
						<div class="mt-12">
							<label for="karyawanNo">Karyawan</label>
							<select id="karyawanNo" name="karyawanNo" class="form-input" required>
								<option value="">-- Pilih Karyawan --</option>
								<?php if ($karyawanResult): while ($row = mysqli_fetch_assoc($karyawanResult)): ?>
									<option value="<?php echo $row['karyawanNo']; ?>" <?php echo (isset($_POST['karyawanNo']) && $_POST['karyawanNo'] == $row['karyawanNo']) ? 'selected' : ''; ?>>
										<?php echo htmlspecialchars($row['nama']); ?>
									</option>
								<?php endwhile; endif; ?>
							</select>
						</div>

						<div class="mt-12">
							<label for="username">Username</label>
							<input id="username" name="username" class="form-input" type="text" required placeholder="username" value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>">
						</div>

						<div class="mt-12">
							<label for="password">Password</label>
							<input id="password" name="password" class="form-input" type="password" required placeholder="••••••••">
						</div>

						<div class="mt-12">
							<label for="jabatan">Jabatan</label>
							<input id="jabatan" name="jabatan" class="form-input" type="text" required placeholder="Contoh: Admin, Manager, Cashier" value="<?php echo isset($_POST['jabatan']) ? htmlspecialchars($_POST['jabatan']) : ''; ?>">
						</div>

					<div class="mt-12">
						<label for="role">Role</label>
						<select id="role" name="role" class="form-input" required>
							<option value="karyawan" <?php echo (isset($_POST['role']) && $_POST['role'] === 'karyawan') || !isset($_POST['role']) ? 'selected' : ''; ?>>Karyawan (Limited Access)</option>
							<option value="admin" <?php echo isset($_POST['role']) && $_POST['role'] === 'admin' ? 'selected' : ''; ?>>Admin (Full Access)</option>
						</select>
					</div>
							<button type="submit" class="btn">Simpan</button>
							<button type="reset" class="btn">Reset</button>
						</div>
					</form>
				</div>

				<div class="card" style="margin-top:18px">
					<h2>Daftar User</h2>
					<?php
					$userQuery = "SELECT du.usernameNo, du.username, du.jabatan, du.role, k.nama as nama_karyawan
					             FROM data_user du
					             LEFT JOIN karyawan k ON du.karyawanNo = k.karyawanNo
					             ORDER BY du.usernameNo DESC";
					$userRes = mysqli_query($conn, $userQuery);
					if ($userRes && mysqli_num_rows($userRes) > 0) {
						echo '<table style="width:100%;border-collapse:collapse">';
						echo '<thead><tr><th style="text-align:left;padding:8px">#</th><th style="text-align:left;padding:8px">Username</th><th style="text-align:left;padding:8px">Karyawan</th><th style="text-align:left;padding:8px">Jabatan</th><th style="text-align:left;padding:8px">Role</th><th style="text-align:right;padding:8px">Aksi</th></tr></thead>';
						echo '<tbody>';
						$i = 1;
						while ($row = mysqli_fetch_assoc($userRes)) {
							$id = htmlspecialchars($row['usernameNo']);
							$username = htmlspecialchars($row['username']);
							$nama_karyawan = htmlspecialchars($row['nama_karyawan']);
							$jabatan = htmlspecialchars($row['jabatan']);
							$role = htmlspecialchars($row['role']);
							$roleBadge = strtoupper($role);
							$roleBg = $role === 'admin' ? '#667eea' : '#10b981';
							echo "<tr>";
							echo "<td style=\"padding:8px;vertical-align:top\">" . $i++ . "</td>";
							echo "<td style=\"padding:8px;vertical-align:top\">{$username}</td>";
							echo "<td style=\"padding:8px;vertical-align:top\">{$nama_karyawan}</td>";
							echo "<td style=\"padding:8px;vertical-align:top\">{$jabatan}</td>";
							echo "<td style=\"padding:8px;vertical-align:top\"><span style=\"background:{$roleBg};color:white;padding:4px 8px;border-radius:4px;font-size:12px;font-weight:bold\">{$roleBadge}</span></td>";
							echo "<td style=\"padding:8px;vertical-align:top;text-align:right\"><a class=\"btn\" href=\"user_edit.php?usernameNo={$id}\">Edit</a> <a class=\"btn\" style=\"background:#ef4444\" href=\"user_delete.php?usernameNo={$id}\" onclick=\"return confirm('Hapus user ini?')\">Hapus</a></td>";
							echo "</tr>\n";
						}
						echo '</tbody></table>';
					} else {
						echo '<p>Tidak ada data user.</p>';
					}
					?>
				</div>
			</div>
		</main>
	</div>
</body>
</html>
