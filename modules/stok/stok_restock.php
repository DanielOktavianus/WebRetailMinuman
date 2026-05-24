<?php
require_once __DIR__ . '/../../config/database.php';

$successMessage = '';
$errorMessage   = '';

$StokNo = isset($_GET['StokNo']) && is_numeric($_GET['StokNo']) ? (int) $_GET['StokNo'] : 0;

// Proses restock
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $StokNo   = isset($_POST['StokNo'])  && is_numeric($_POST['StokNo'])  ? (int)   $_POST['StokNo']  : 0;
    $tambah   = isset($_POST['tambah'])  && is_numeric($_POST['tambah'])  && (float) $_POST['tambah'] > 0
                    ? (float) $_POST['tambah'] : null;

    if ($StokNo <= 0) {
        $errorMessage = 'Stok tidak valid.';
    } elseif ($tambah === null) {
        $errorMessage = 'Jumlah tambah harus diisi dan lebih dari 0.';
    } else {
        $stmt = mysqli_prepare($conn,
            "UPDATE stok SET jumlah_stok = jumlah_stok + ? WHERE StokNo = ?");
        if (!$stmt) {
            $errorMessage = 'Prepare gagal: ' . mysqli_error($conn);
        } else {
            mysqli_stmt_bind_param($stmt, 'di', $tambah, $StokNo);
            if (mysqli_stmt_execute($stmt)) {
                mysqli_stmt_close($stmt);
                header('Location: stok_tambah.php?restock=ok');
                exit;
            } else {
                $errorMessage = 'Gagal restock: ' . mysqli_stmt_error($stmt);
                mysqli_stmt_close($stmt);
            }
        }
    }
}

// Ambil data stok
$stok = null;
if ($StokNo > 0) {
    $stmt = mysqli_prepare($conn,
        "SELECT s.StokNo, b.nama_bahan, sat.nama_satuan, s.jumlah_stok, s.batas_minimum
         FROM stok s
         LEFT JOIN bahan b   ON s.BahanNo   = b.BahanNo
         LEFT JOIN satuan sat ON s.SatuanNo = sat.SatuanNo
         WHERE s.StokNo = ? LIMIT 1");
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, 'i', $StokNo);
        mysqli_stmt_execute($stmt);
        $stok = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
        mysqli_stmt_close($stmt);
    }
}

if (!$stok) {
    header('Location: stok_tambah.php');
    exit;
}
?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <title>Restock — <?php echo htmlspecialchars($stok['nama_bahan'] ?? '-'); ?></title>
    <link rel="stylesheet" href="../../assets/css/main.css">
    <link rel="stylesheet" href="../../assets/css/components.css">
    <link rel="stylesheet" href="../../assets/css/modules.css">
</head>
<body>
    <div class="app-layout">
        <?php include __DIR__ . '/../../components/sidebar.php'; ?>
        <main class="main-content module-stok">
            <div class="container">
                <div class="card" style="max-width:480px">
                    <h2>+ Restock Bahan</h2>

                    <?php if ($errorMessage): ?>
                        <div class="alert error"><?php echo htmlspecialchars($errorMessage); ?></div>
                    <?php endif; ?>

                    <!-- Info stok saat ini -->
                    <div style="background:#f0f9ff;border:1px solid #bae6fd;border-radius:8px;padding:16px;margin-bottom:20px">
                        <div style="font-size:18px;font-weight:700;color:#0f172a;margin-bottom:8px">
                            <?php echo htmlspecialchars($stok['nama_bahan'] ?? '-'); ?>
                        </div>
                        <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;font-size:14px">
                            <div>
                                <span style="color:#6b7280">Stok saat ini</span><br>
                                <strong style="font-size:20px;color:#1e40af">
                                    <?php echo ($stok['jumlah_stok'] + 0); ?>
                                </strong>
                                <span style="color:#6b7280"><?php echo htmlspecialchars($stok['nama_satuan'] ?? ''); ?></span>
                            </div>
                            <div>
                                <span style="color:#6b7280">Batas minimum</span><br>
                                <strong style="font-size:20px;color:#92400e">
                                    <?php echo ($stok['batas_minimum'] + 0); ?>
                                </strong>
                                <span style="color:#6b7280"><?php echo htmlspecialchars($stok['nama_satuan'] ?? ''); ?></span>
                            </div>
                        </div>
                    </div>

                    <!-- Form restock -->
                    <form method="post" action="">
                        <input type="hidden" name="StokNo" value="<?php echo $stok['StokNo']; ?>">

                        <label for="tambah">
                            Jumlah yang ditambahkan
                            <span style="color:#6b7280;font-weight:400">
                                (<?php echo htmlspecialchars($stok['nama_satuan'] ?? ''); ?>)
                            </span>
                        </label>
                        <input id="tambah" name="tambah" class="form-input" type="number"
                            step="0.01" min="0.01" required placeholder="Contoh: 5, 10, 100"
                            style="font-size:18px;text-align:right"
                            autofocus>

                        <?php
                        // Preview hasil setelah restock (pakai JS)
                        $satuanNama = htmlspecialchars($stok['nama_satuan'] ?? '');
                        $stokNow    = (float) $stok['jumlah_stok'];
                        ?>
                        <div id="preview" style="display:none;background:#f0fdf4;border:1px solid #bbf7d0;border-radius:6px;padding:12px;margin-top:10px;font-size:14px">
                            Setelah restock: <strong id="previewVal"></strong> <?php echo $satuanNama; ?>
                        </div>

                        <div class="form-actions" style="margin-top:20px">
                            <button class="btn" type="submit" style="background:#16a34a">✓ Tambah Stok</button>
                            <a class="btn" href="stok_tambah.php" style="background:#6b7280">Batal</a>
                        </div>
                    </form>

                    <script>
                    const stokNow = <?php echo $stokNow; ?>;
                    document.getElementById('tambah').addEventListener('input', function () {
                        const val = parseFloat(this.value) || 0;
                        const preview = document.getElementById('preview');
                        const previewVal = document.getElementById('previewVal');
                        if (val > 0) {
                            preview.style.display = 'block';
                            previewVal.textContent = (stokNow + val).toLocaleString('id-ID');
                        } else {
                            preview.style.display = 'none';
                        }
                    });
                    </script>
                </div>
            </div>
        </main>
    </div>
</body>
</html>
