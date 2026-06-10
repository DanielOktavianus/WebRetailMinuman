<?php
require_once __DIR__ . '/../../config/database.php';

// Handle form submit for insert
$successMessage = '';
$errorMessage = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama_bahan = isset($_POST['nama_bahan']) ? trim($_POST['nama_bahan']) : '';
    
    if (empty($nama_bahan)) {
        $errorMessage = 'Nama bahan harus diisi.';
    } else {
        // Cek duplikat
        $cek = mysqli_prepare($conn, "SELECT BahanNo FROM bahan WHERE LOWER(nama_bahan) = LOWER(?) LIMIT 1");
        mysqli_stmt_bind_param($cek, 's', $nama_bahan);
        mysqli_stmt_execute($cek);
        mysqli_stmt_store_result($cek);
        if (mysqli_stmt_num_rows($cek) > 0) {
            $errorMessage = 'Bahan "' . htmlspecialchars($nama_bahan) . '" sudah ada di daftar.';
        } else {
            $stmt = mysqli_prepare($conn, "INSERT INTO bahan (nama_bahan) VALUES (?)");
            if (!$stmt) {
                $errorMessage = 'Prepare gagal: ' . mysqli_error($conn);
            } else {
                mysqli_stmt_bind_param($stmt, 's', $nama_bahan);
                if (mysqli_stmt_execute($stmt)) {
                    $successMessage = 'Bahan berhasil ditambahkan.';
                    $_POST = [];
                } else {
                    $errorMessage = 'Gagal menyimpan: ' . mysqli_stmt_error($stmt);
                }
                mysqli_stmt_close($stmt);
            }
        }
        mysqli_stmt_close($cek);
    }
}
?>
<!doctype html>
<html lang="id">
<head>
	<meta charset="utf-8" />
	<meta name="viewport" content="width=device-width,initial-scale=1" />
	<title>Bahan — Tambah</title>
	<link rel="stylesheet" href="../../assets/css/main.css">
	<link rel="stylesheet" href="../../assets/css/components.css">
	<link rel="stylesheet" href="../../assets/css/modules.css">
</head>
<body>
	<div class="app-layout">
		<?php include __DIR__ . '/../../components/sidebar.php'; ?>
		<main class="main-content module-bahan">
			<div class="container">
				<div class="card">
					<h2>Tambah Bahan</h2>
					<?php if ($successMessage): ?>
						<div class="alert success"><?php echo htmlspecialchars($successMessage); ?></div>
					<?php endif; ?>
					<?php if ($errorMessage): ?>
						<div class="alert error"><?php echo htmlspecialchars($errorMessage); ?></div>
					<?php endif; ?>
					
					<form method="post" action="">
						<div class="mt-12">
							<label for="nama_bahan">Nama Bahan</label>
							<input id="nama_bahan" name="nama_bahan" class="form-input" type="text" required value="<?php echo isset($_POST['nama_bahan']) ? htmlspecialchars($_POST['nama_bahan']) : ''; ?>">
						</div>
						<div class="form-actions">
							<button class="btn" type="submit">Simpan</button>
						</div>
					</form>
				</div>

				<div class="card" style="margin-top:18px">
					<h2>Daftar Bahan</h2>
					<?php
					$query = "SELECT b.BahanNo, b.nama_bahan
					          FROM bahan b
					          ORDER BY b.BahanNo DESC";
					$res = mysqli_query($conn, $query);
					if ($res && mysqli_num_rows($res) > 0) {
						echo '<table style="width:100%;border-collapse:collapse">';
						echo '<thead><tr style="background:#f0f0f0"><th style="text-align:left;padding:10px;border-bottom:1px solid #ccc">#</th><th style="text-align:left;padding:10px;border-bottom:1px solid #ccc">Nama Bahan</th><th style="text-align:right;padding:10px;border-bottom:1px solid #ccc">Aksi</th></tr></thead>';
						echo '<tbody>';
						$i = 1;
						while ($row = mysqli_fetch_assoc($res)) {
							$id = htmlspecialchars($row['BahanNo']);
							$nama = htmlspecialchars($row['nama_bahan']);
							echo '<tr style="border-bottom:1px solid #eee">';
							echo "<td style=\"padding:10px;vertical-align:top\">" . ($i++) . "</td>";
							echo "<td style=\"padding:10px;vertical-align:top\"><strong>{$nama}</strong></td>";
							echo "<td style=\"padding:10px;vertical-align:top;text-align:right\">";
							echo "<a class=\"btn\" href=\"bahan_edit.php?BahanNo={$id}\" style=\"padding:6px 10px;font-size:13px;background:#f59e0b;color:white;text-decoration:none;border-radius:4px;margin-right:4px\">Edit</a> ";
							echo "<a class=\"btn\" href=\"bahan_delete.php?BahanNo={$id}\" onclick=\"return confirm('Yakin hapus bahan ini?')\" style=\"padding:6px 10px;font-size:13px;background:#ef4444;color:white;text-decoration:none;border-radius:4px\">Hapus</a>";
							echo "</td>";
							echo '</tr>';
						}
						echo '</tbody></table>';
					} else {
						echo '<p>Belum ada bahan.</p>';
					}
					?>
				</div>
			</div>
		</main>
	</div>
</body>
</html>
