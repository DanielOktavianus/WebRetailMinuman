<?php
require_once __DIR__ . '/../../config/database.php';

// Handle form submit for insert
$successMessage = '';
$errorMessage = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama_menu = isset($_POST['nama_menu']) ? trim($_POST['nama_menu']) : '';
    
    if (empty($nama_menu)) {
        $errorMessage = 'Nama menu harus diisi.';
    } else {
        $stmt = mysqli_prepare($conn, "INSERT INTO menu (nama_menu) VALUES (?)");
        if (!$stmt) {
            $errorMessage = 'Prepare gagal: ' . mysqli_error($conn);
        } else {
            mysqli_stmt_bind_param($stmt, 's', $nama_menu);
            if (mysqli_stmt_execute($stmt)) {
                $successMessage = 'Menu berhasil ditambahkan.';
                $_POST = [];
            } else {
                $errorMessage = 'Gagal menyimpan: ' . mysqli_stmt_error($stmt);
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
	<title>Menu — Tambah</title>
	<link rel="stylesheet" href="../../assets/css/main.css">
	<link rel="stylesheet" href="../../assets/css/components.css">
	<link rel="stylesheet" href="../../assets/css/modules.css">
</head>
<body>
	<div class="app-layout">
		<?php include __DIR__ . '/../../components/sidebar.php'; ?>
		<main class="main-content module-menu">
			<div class="container">
				<div class="card">
					<h2>Tambah Menu</h2>
					<?php if ($successMessage): ?>
						<div class="alert success"><?php echo htmlspecialchars($successMessage); ?></div>
					<?php endif; ?>
					<?php if ($errorMessage): ?>
						<div class="alert error"><?php echo htmlspecialchars($errorMessage); ?></div>
					<?php endif; ?>
					
					<form method="post" action="">
						<div class="mt-12">
							<label for="nama_menu">Nama Menu</label>
							<input id="nama_menu" name="nama_menu" class="form-input" type="text" required placeholder="Contoh: Teh poci original, dll" value="<?php echo isset($_POST['nama_menu']) ? htmlspecialchars($_POST['nama_menu']) : ''; ?>">
						</div>

						<div class="form-actions">
							<button class="btn" type="submit">Simpan</button>
						</div>
					</form>
				</div>

				<div class="card" style="margin-top:18px">
					<h2>Daftar Menu</h2>
					<?php
					$query = "SELECT MenuNo, nama_menu FROM menu ORDER BY MenuNo DESC";
					$res = mysqli_query($conn, $query);
					if ($res && mysqli_num_rows($res) > 0) {
						echo '<table style="width:100%;border-collapse:collapse">';
						echo '<thead><tr style="background:#f0f0f0"><th style="text-align:left;padding:10px;border-bottom:1px solid #ccc">#</th><th style="text-align:left;padding:10px;border-bottom:1px solid #ccc">MenuNo</th><th style="text-align:left;padding:10px;border-bottom:1px solid #ccc">Nama Menu</th><th style="text-align:right;padding:10px;border-bottom:1px solid #ccc">Aksi</th></tr></thead>';
						echo '<tbody>';
						$i = 1;
						while ($row = mysqli_fetch_assoc($res)) {
							$id = htmlspecialchars($row['MenuNo']);
							$nama = htmlspecialchars($row['nama_menu']);
							echo '<tr style="border-bottom:1px solid #eee">';
							echo "<td style=\"padding:10px;vertical-align:top\">" . ($i++) . "</td>";
							echo "<td style=\"padding:10px;vertical-align:top\">{$id}</td>";
							echo "<td style=\"padding:10px;vertical-align:top\">{$nama}</td>";
							echo "<td style=\"padding:10px;vertical-align:top;text-align:right\">";						
							echo "<a class=\"btn\" href=\"../resep/resep_tambah.php?MenuNo={$id}\" style=\"padding:6px 10px;font-size:13px;background:#10b981;color:white;text-decoration:none;border-radius:4px;margin-right:4px\">Isi Resep</a> ";							echo "<a class=\"btn\" href=\"menu_edit.php?MenuNo={$id}\" style=\"padding:6px 10px;font-size:13px;background:#f59e0b;color:white;text-decoration:none;border-radius:4px;margin-right:4px\">Edit</a> ";
							echo "<a class=\"btn\" href=\"menu_delete.php?MenuNo={$id}\" onclick=\"return confirm('Yakin hapus menu ini?')\" style=\"padding:6px 10px;font-size:13px;background:#ef4444;color:white;text-decoration:none;border-radius:4px\">Hapus</a>";
							echo "</td>";
							echo '</tr>';
						}
						echo '</tbody></table>';
					} else {
						echo '<p>Belum ada menu.</p>';
					}
					?>
				</div>
			</div>
		</main>
	</div>
</body>
</html>
