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
	<title>Produsen — List</title>
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
				  <h2>Tambah Produsen</h2>
				  <form method="post" action="produsen_save.php">
				    <div class="row">
				      <div>
				        <label for="produsenNo">Produsen No</label>
				        <input id="produsenNo" name="produsenNo" type="text" placeholder="auto (auto-increment)" readonly class="form-input">
				      </div>
				    </div>

				    <div class="mt-12">
				      <label for="nama">Nama Produsen</label>
				      <input id="nama" name="nama" class="form-input" type="text" required placeholder="Nama Produsen">
				    </div>

				    <div class="mt-12">
				      <label for="alamat">Alamat</label>
				      <textarea id="alamat" name="alamat" rows="3" class="form-input" placeholder="Alamat"></textarea>
				    </div>

				    <div class="mt-12">
				      <label for="kontak">Kontak</label>
				      <input id="kontak" name="kontak" class="form-input" type="text" placeholder="Telepon/Email">
				    </div>

				    <div class="form-actions">
				      <button class="btn" type="submit">Simpan</button>
				    </div>
				  </form>
				</div>
				<div class="card" style="margin-top:18px">
				  <h2>Daftar Produsen</h2>
				  <?php
				  require_once __DIR__ . '/../../config/database.php';
				  $sql = "SELECT ProdusenNo, Nama_Produsen, Alamat, Kontak FROM produsen ORDER BY ProdusenNo DESC";
				  $res = mysqli_query($conn, $sql);
				  if ($res && mysqli_num_rows($res) > 0) {
				    echo '<table style="width:100%;border-collapse:collapse">';
				    echo '<thead><tr><th style="text-align:left;padding:8px">#</th><th style="text-align:left;padding:8px">Nama Produsen</th><th style="text-align:left;padding:8px">Alamat</th><th style="text-align:left;padding:8px">Kontak</th><th style="text-align:right;padding:8px">Aksi</th></tr></thead>';
				    echo '<tbody>';
				    while ($row = mysqli_fetch_assoc($res)) {
				      $id = htmlspecialchars($row['ProdusenNo']);
				      $nama = htmlspecialchars($row['Nama_Produsen']);
				      $alamat = htmlspecialchars($row['Alamat']);
				      $kontak = htmlspecialchars($row['Kontak']);
				      echo "<tr>\n";
				      echo "<td style=\"padding:8px;vertical-align:top\">{$id}</td>";
				      echo "<td style=\"padding:8px;vertical-align:top\">{$nama}</td>";
				      echo "<td style=\"padding:8px;vertical-align:top\">{$alamat}</td>";
				      echo "<td style=\"padding:8px;vertical-align:top\">{$kontak}</td>";
				      echo "<td style=\"padding:8px;vertical-align:top;text-align:right\"><a class=\"btn\" href=\"produsen_edit.php?produsenNo={$id}\">Edit</a> <a class=\"btn\" style=\"background:#ef4444\" href=\"produsen_delete.php?produsenNo={$id}\" onclick=\"return confirm('Hapus produsen ini?')\">Hapus</a></td>";
				      echo "</tr>\n";
				    }
				    echo '</tbody></table>';
				  } else {
				    echo '<p>Tidak ada data produsen.</p>';
				  }
				  ?>
				</div>
			</div>
		</main>
	</div>
</body>
</html>
