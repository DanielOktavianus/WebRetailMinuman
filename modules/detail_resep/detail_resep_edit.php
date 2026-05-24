<?php
require_once __DIR__ . '/../../config/database.php';

$DetailResepNo = isset($_GET['DetailResepNo']) && is_numeric($_GET['DetailResepNo']) ? (int) $_GET['DetailResepNo'] : 0;
if ($DetailResepNo <= 0) { header('Location: detail_resep_tambah.php'); exit; }

$stmt = mysqli_prepare($conn,
    "SELECT DetailResepNo, ResepNo, UkuranNo, StokNo, SatuanNo, jumlah
     FROM detail_resep WHERE DetailResepNo = ? LIMIT 1");
mysqli_stmt_bind_param($stmt, 'i', $DetailResepNo);
mysqli_stmt_execute($stmt);
$detail = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
mysqli_stmt_close($stmt);

if (!$detail) die('Data tidak ditemukan. <a href="detail_resep_tambah.php">Kembali</a>');

$resepResult  = mysqli_query($conn,
    "SELECT r.ResepNo, m.nama_menu FROM resep r LEFT JOIN menu m ON r.MenuNo = m.MenuNo ORDER BY m.nama_menu ASC");
$ukuranResult = mysqli_query($conn, "SELECT UkuranNo, nama_ukuran FROM ukuran ORDER BY UkuranNo ASC");
$stokResult   = mysqli_query($conn,
    "SELECT s.StokNo, b.nama_bahan, s.SatuanNo FROM stok s LEFT JOIN bahan b ON s.BahanNo = b.BahanNo ORDER BY b.nama_bahan ASC");
$satuanResult = mysqli_query($conn, "SELECT SatuanNo, nama_satuan FROM satuan ORDER BY nama_satuan ASC");

$stokArray = [];
if ($stokResult) while ($r = mysqli_fetch_assoc($stokResult)) $stokArray[] = $r;
?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <title>Detail Resep — Edit</title>
    <link rel="stylesheet" href="../../assets/css/main.css">
    <link rel="stylesheet" href="../../assets/css/components.css">
    <link rel="stylesheet" href="../../assets/css/modules.css">
</head>
<body>
    <div class="app-layout">
        <?php include __DIR__ . '/../../components/sidebar.php'; ?>
        <main class="main-content module-detail-resep">
            <div class="container">
                <div class="card">
                    <h2>Edit Detail Resep</h2>
                    <form method="post" action="detail_resep_update.php">
                        <input type="hidden" name="DetailResepNo" value="<?php echo $detail['DetailResepNo']; ?>">

                        <div class="row">
                            <div>
                                <label for="ResepNo">Resep (Menu)</label>
                                <select id="ResepNo" name="ResepNo" class="form-input" required>
                                    <option value="">-- Pilih Resep --</option>
                                    <?php if ($resepResult): while ($row = mysqli_fetch_assoc($resepResult)): ?>
                                        <option value="<?php echo $row['ResepNo']; ?>"
                                            <?php echo ($detail['ResepNo'] == $row['ResepNo']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($row['nama_menu']); ?>
                                        </option>
                                    <?php endwhile; endif; ?>
                                </select>
                            </div>
                            <div>
                                <label for="UkuranNo">Ukuran</label>
                                <select id="UkuranNo" name="UkuranNo" class="form-input" required>
                                    <option value="">-- Pilih Ukuran --</option>
                                    <?php if ($ukuranResult): while ($row = mysqli_fetch_assoc($ukuranResult)): ?>
                                        <option value="<?php echo $row['UkuranNo']; ?>"
                                            <?php echo ($detail['UkuranNo'] == $row['UkuranNo']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($row['nama_ukuran']); ?>
                                        </option>
                                    <?php endwhile; endif; ?>
                                </select>
                            </div>
                        </div>

                        <div class="row mt-12">
                            <div>
                                <label for="StokNo">Bahan (Stok)</label>
                                <select id="StokNo" name="StokNo" class="form-input" required>
                                    <option value="">-- Pilih Bahan --</option>
                                    <?php foreach ($stokArray as $row): ?>
                                        <option value="<?php echo $row['StokNo']; ?>"
                                            <?php echo ($detail['StokNo'] == $row['StokNo']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($row['nama_bahan'] ?? '-'); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div>
                                <label for="SatuanNo">Satuan</label>
                                <select id="SatuanNo" name="SatuanNo" class="form-input" required>
                                    <option value="">-- Pilih Satuan --</option>
                                    <?php if ($satuanResult): while ($row = mysqli_fetch_assoc($satuanResult)): ?>
                                        <option value="<?php echo $row['SatuanNo']; ?>"
                                            <?php echo ($detail['SatuanNo'] == $row['SatuanNo']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($row['nama_satuan']); ?>
                                        </option>
                                    <?php endwhile; endif; ?>
                                </select>
                            </div>
                        </div>

                        <div class="mt-12">
                            <label for="jumlah">Jumlah</label>
                            <input id="jumlah" name="jumlah" class="form-input" type="number"
                                step="0.01" min="0.01" required value="<?php echo htmlspecialchars($detail['jumlah']); ?>">
                        </div>

                        <div class="form-actions">
                            <button class="btn" type="submit">Update</button>
                            <a class="btn" href="detail_resep_tambah.php?ResepNo=<?php echo $detail['ResepNo']; ?>"
                                style="background:#6b7280">Batal</a>
                        </div>
                    </form>
                </div>
            </div>
        </main>
        <script>
        const stokToSatuan = {<?php foreach ($stokArray as $r) echo $r['StokNo'].':'.$r['SatuanNo'].','; ?>};
        document.getElementById('StokNo').addEventListener('change', function() {
            if (stokToSatuan[this.value]) document.getElementById('SatuanNo').value = stokToSatuan[this.value];
        });
        </script>
    </div>
</body>
</html>
