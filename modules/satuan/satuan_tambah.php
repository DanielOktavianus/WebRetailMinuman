<?php
require_once __DIR__ . '/../../config/database.php';

// Handle form submit for insert
$successMessage = '';
$errorMessage = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama_satuan = isset($_POST['nama_satuan']) ? trim($_POST['nama_satuan']) : '';
    
    if (empty($nama_satuan)) {
        $errorMessage = 'Nama satuan harus diisi.';
    } else {
        $stmt = mysqli_prepare($conn, "INSERT INTO satuan (nama_satuan) VALUES (?)");
        if (!$stmt) {
            $errorMessage = 'Prepare gagal: ' . mysqli_error($conn);
        } else {
            mysqli_stmt_bind_param($stmt, 's', $nama_satuan);
            if (mysqli_stmt_execute($stmt)) {
                $successMessage = 'Satuan berhasil ditambahkan.';
                $_POST = [];
            } else {
                if (strpos(mysqli_stmt_error($stmt), 'UNIQUE') !== false) {
                    $errorMessage = 'Satuan sudah ada.';
                } else {
                    $errorMessage = 'Gagal menyimpan: ' . mysqli_stmt_error($stmt);
                }
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
	<title>Satuan — Tambah</title>
	<link rel="stylesheet" href="../../assets/css/main.css">
	<link rel="stylesheet" href="../../assets/css/components.css">
	<link rel="stylesheet" href="../../assets/css/modules.css">
</head>
<body>
	<div class="app-layout">
		<?php include __DIR__ . '/../../components/sidebar.php'; ?>
		<main class="main-content module-satuan">
			<div class="container">
				<div class="card">
					<h2>Tambah Satuan</h2>
					<?php if ($successMessage): ?>
						<div class="alert success"><?php echo htmlspecialchars($successMessage); ?></div>
					<?php endif; ?>
					<?php if ($errorMessage): ?>
						<div class="alert error"><?php echo htmlspecialchars($errorMessage); ?></div>
					<?php endif; ?>
					
					<form method="post" action="">
						<div class="mt-12">
							<label for="nama_satuan">Nama Satuan</label>
							<input id="nama_satuan" name="nama_satuan" class="form-input" type="text" required placeholder="Contoh: Gram, Mili Liter, Butir, Siung, Kg" value="<?php echo isset($_POST['nama_satuan']) ? htmlspecialchars($_POST['nama_satuan']) : ''; ?>">
						</div>

						<div class="form-actions">
							<button class="btn" type="submit">Simpan</button>
						</div>
					</form>
				</div>

				<div class="card" style="margin-top:18px">
					<h2>Daftar Satuan</h2>
					<?php
					$query = "SELECT SatuanNo, nama_satuan FROM satuan ORDER BY SatuanNo ASC";
					$res = mysqli_query($conn, $query);
					if ($res && mysqli_num_rows($res) > 0) {
						echo '<table style="width:100%;border-collapse:collapse">';
						echo '<thead><tr style="background:#f0f0f0"><th style="text-align:left;padding:10px;border-bottom:1px solid #ccc">#</th><th style="text-align:left;padding:10px;border-bottom:1px solid #ccc">SatuanNo</th><th style="text-align:left;padding:10px;border-bottom:1px solid #ccc">Nama Satuan</th><th style="text-align:right;padding:10px;border-bottom:1px solid #ccc">Aksi</th></tr></thead>';
						echo '<tbody>';
						$i = 1;
						while ($row = mysqli_fetch_assoc($res)) {
							$id = htmlspecialchars($row['SatuanNo']);
							$nama = htmlspecialchars($row['nama_satuan']);
							echo '<tr style="border-bottom:1px solid #eee">';
							echo "<td style=\"padding:10px;vertical-align:top\">" . ($i++) . "</td>";
							echo "<td style=\"padding:10px;vertical-align:top\">{$id}</td>";
							echo "<td style=\"padding:10px;vertical-align:top\">{$nama}</td>";
							echo "<td style=\"padding:10px;vertical-align:top;text-align:right\">";
							echo "<a class=\"btn\" href=\"satuan_edit.php?SatuanNo={$id}\" style=\"padding:6px 10px;font-size:13px;background:#f59e0b;color:white;text-decoration:none;border-radius:4px;margin-right:4px\">Edit</a> ";
							echo "<a class=\"btn\" href=\"satuan_delete.php?SatuanNo={$id}\" onclick=\"return confirm('Yakin hapus satuan ini?')\" style=\"padding:6px 10px;font-size:13px;background:#ef4444;color:white;text-decoration:none;border-radius:4px\">Hapus</a>";
							echo "</td>";
							echo '</tr>';
						}
						echo '</tbody></table>';
					} else {
						echo '<p>Belum ada satuan.</p>';
					}
					?>
				</div>
			</div>
		</main>
	</div>
</body>
</html>
