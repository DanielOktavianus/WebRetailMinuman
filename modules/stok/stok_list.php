<?php
// Stok list + add form
?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <title>Stok — List</title>
    <link rel="stylesheet" href="../../assets/css/main.css">
    <link rel="stylesheet" href="../../assets/css/components.css">
    <link rel="stylesheet" href="../../assets/css/modules.css">
</head>
<body>
    <div class="app-layout">
        <?php include __DIR__ . '/../../components/sidebar.php'; ?>
        <main class="main-content module-stok">
            <div class="container">
                <div class="card">
                    <h2>Tambah Stok</h2>
                    <form method="post" action="stok_save.php">
                        <div class="row">
                            <div>
                                <label for="StokNo">Stok No</label>
                                <input id="StokNo" name="StokNo" type="text" placeholder="auto (auto-increment)" readonly class="form-input">
                            </div>
                            <div>
                                <label for="ProdusenNo">Produsen</label>
                                <select id="ProdusenNo" name="ProdusenNo" class="form-input">
                                    <option value="">-- pilih produsen --</option>
                                    <?php
                                    require_once __DIR__ . '/../../config/database.php';
                                    $pq = mysqli_query($conn, "SELECT ProdusenNo, Nama_Produsen FROM produsen ORDER BY Nama_Produsen ASC");
                                    if ($pq) while ($p = mysqli_fetch_assoc($pq)) {
                                        $pid = (int)$p['ProdusenNo'];
                                        $pname = htmlspecialchars($p['Nama_Produsen']);
                                        echo "<option value=\"{$pid}\">{$pname}</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>

                        <div class="mt-12">
                            <label for="nama_stok">Nama Stok</label>
                            <input id="nama_stok" name="nama_stok" class="form-input" type="text" required placeholder="Nama Stok">
                        </div>

                        <div class="row mt-12">
                            <div>
                                <label for="jumlah_stok">Jumlah</label>
                                <input id="jumlah_stok" name="jumlah_stok" class="form-input" type="number" step="1" min="0" placeholder="0">
                            </div>
                            <div>
                                <label for="satuan">Satuan</label>
                                <input id="satuan" name="satuan" class="form-input" type="text" placeholder="pcs / kg / liter">
                            </div>
                        </div>

                        <div class="form-actions">
                            <button class="btn" type="submit">Simpan</button>
                        </div>
                    </form>
                </div>

                <div class="card" style="margin-top:18px">
                    <h2>Daftar Stok</h2>
                    <?php
                    // list stok with produsen name
                    $sql = "SELECT s.StokNo, s.nama_stok, s.jumlah_stok, s.satuan, s.ProdusenNo, p.Nama_Produsen FROM stok s LEFT JOIN produsen p ON s.ProdusenNo = p.ProdusenNo ORDER BY s.StokNo DESC";
                    $res = mysqli_query($conn, $sql);
                    if ($res && mysqli_num_rows($res) > 0) {
                        echo '<table style="width:100%;border-collapse:collapse">';
                        echo '<thead><tr><th style="text-align:left;padding:8px">#</th><th style="text-align:left;padding:8px">Nama Stok</th><th style="text-align:left;padding:8px">Produsen</th><th style="text-align:left;padding:8px">Jumlah</th><th style="text-align:left;padding:8px">Satuan</th><th style="text-align:right;padding:8px">Aksi</th></tr></thead>';
                        echo '<tbody>';
                        while ($row = mysqli_fetch_assoc($res)) {
                            $id = htmlspecialchars($row['StokNo']);
                            $nama = htmlspecialchars($row['nama_stok']);
                            $jumlah = htmlspecialchars($row['jumlah_stok']);
                            $satuan = htmlspecialchars($row['satuan']);
                            $pname = htmlspecialchars($row['Nama_Produsen']);
                            echo "<tr>\n";
                            echo "<td style=\"padding:8px;vertical-align:top\">{$id}</td>";
                            echo "<td style=\"padding:8px;vertical-align:top\">{$nama}</td>";
                            echo "<td style=\"padding:8px;vertical-align:top\">{$pname}</td>";
                            echo "<td style=\"padding:8px;vertical-align:top\">{$jumlah}</td>";
                            echo "<td style=\"padding:8px;vertical-align:top\">{$satuan}</td>";
                            echo "<td style=\"padding:8px;vertical-align:top;text-align:right\"><a class=\"btn\" href=\"stok_edit.php?StokNo={$id}\">Edit</a> <a class=\"btn\" style=\"background:#ef4444\" href=\"stok_delete.php?StokNo={$id}\" onclick=\"return confirm('Hapus stok ini?')\">Hapus</a></td>";
                            echo "</tr>\n";
                        }
                        echo '</tbody></table>';
                    } else {
                        echo '<p>Tidak ada data stok.</p>';
                    }
                    ?>
                </div>
            </div>
        </main>
    </div>
</body>
</html>
