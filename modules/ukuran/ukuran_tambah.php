<?php
require_once __DIR__ . '/../../config/database.php';

$successMessage = '';
$errorMessage = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama_ukuran = isset($_POST['nama_ukuran']) ? trim($_POST['nama_ukuran']) : '';

    if (empty($nama_ukuran)) {
        $errorMessage = 'Nama ukuran harus diisi.';
    } else {
        $stmt = mysqli_prepare($conn, "INSERT INTO ukuran (nama_ukuran) VALUES (?)");
        if (!$stmt) {
            $errorMessage = 'Prepare gagal: ' . mysqli_error($conn);
        } else {
            mysqli_stmt_bind_param($stmt, 's', $nama_ukuran);
            if (mysqli_stmt_execute($stmt)) {
                $successMessage = 'Ukuran "' . htmlspecialchars($nama_ukuran) . '" berhasil ditambahkan.';
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
    <title>Ukuran — Tambah</title>
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
                    <h2>Tambah Ukuran</h2>
                    <?php if ($successMessage): ?>
                        <div class="alert success"><?php echo htmlspecialchars($successMessage); ?></div>
                    <?php endif; ?>
                    <?php if ($errorMessage): ?>
                        <div class="alert error"><?php echo htmlspecialchars($errorMessage); ?></div>
                    <?php endif; ?>

                    <form method="post" action="">
                        <div class="mt-12">
                            <label for="nama_ukuran">Nama Ukuran</label>
                            <input id="nama_ukuran" name="nama_ukuran" class="form-input" type="text"
                                required placeholder="Contoh: Kecil, Besar, Medium"
                                value="<?php echo isset($_POST['nama_ukuran']) ? htmlspecialchars($_POST['nama_ukuran']) : ''; ?>">
                        </div>
                        <div class="form-actions">
                            <button class="btn" type="submit">Simpan</button>
                        </div>
                    </form>
                </div>

                <div class="card" style="margin-top:18px">
                    <h2>Daftar Ukuran</h2>
                    <?php
                    $res = mysqli_query($conn, "SELECT UkuranNo, nama_ukuran FROM ukuran ORDER BY UkuranNo ASC");
                    if ($res && mysqli_num_rows($res) > 0):
                        echo '<table style="width:100%;border-collapse:collapse">';
                        echo '<thead><tr style="background:#f0f0f0">
                            <th style="text-align:left;padding:10px;border-bottom:1px solid #ccc">#</th>
                            <th style="text-align:left;padding:10px;border-bottom:1px solid #ccc">UkuranNo</th>
                            <th style="text-align:left;padding:10px;border-bottom:1px solid #ccc">Nama Ukuran</th>
                            <th style="text-align:right;padding:10px;border-bottom:1px solid #ccc">Aksi</th>
                        </tr></thead><tbody>';
                        $i = 1;
                        while ($row = mysqli_fetch_assoc($res)):
                            $id   = htmlspecialchars($row['UkuranNo']);
                            $nama = htmlspecialchars($row['nama_ukuran']);
                            echo "<tr style=\"border-bottom:1px solid #eee\">";
                            echo "<td style=\"padding:10px\">" . ($i++) . "</td>";
                            echo "<td style=\"padding:10px\">{$id}</td>";
                            echo "<td style=\"padding:10px\">{$nama}</td>";
                            echo "<td style=\"padding:10px;text-align:right\">";
                            echo "<a class=\"btn\" href=\"ukuran_edit.php?UkuranNo={$id}\" style=\"padding:6px 10px;font-size:13px;background:#f59e0b;color:white;text-decoration:none;border-radius:4px;margin-right:4px\">Edit</a>";
                            echo "<a class=\"btn\" href=\"ukuran_delete.php?UkuranNo={$id}\" onclick=\"return confirm('Yakin hapus ukuran ini?')\" style=\"padding:6px 10px;font-size:13px;background:#ef4444;color:white;text-decoration:none;border-radius:4px\">Hapus</a>";
                            echo "</td></tr>";
                        endwhile;
                        echo '</tbody></table>';
                    else:
                        echo '<p>Belum ada ukuran.</p>';
                    endif;
                    ?>
                </div>
            </div>
        </main>
    </div>
</body>
</html>
