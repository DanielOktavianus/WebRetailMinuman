<?php
require_once __DIR__ . '/../../config/database.php';

$successMessage   = '';
$errorMessage     = '';

// ResepNo — dari URL atau dari POST
$selectedResepNo  = isset($_GET['ResepNo'])  && is_numeric($_GET['ResepNo'])
    ? (int) $_GET['ResepNo']
    : (isset($_POST['ResepNo']) && is_numeric($_POST['ResepNo']) ? (int) $_POST['ResepNo'] : null);

// UkuranNo — dipertahankan setelah submit supaya bisa lanjut input ukuran yang sama
$selectedUkuranNo = isset($_POST['UkuranNo']) && is_numeric($_POST['UkuranNo'])
    ? (int) $_POST['UkuranNo'] : null;

// Daftar ukuran
$ukuranList = [];
$ukuranQ    = mysqli_query($conn, "SELECT UkuranNo, nama_ukuran FROM ukuran ORDER BY UkuranNo ASC");
while ($u = mysqli_fetch_assoc($ukuranQ)) $ukuranList[$u['UkuranNo']] = $u['nama_ukuran'];

/* ── PROSES SIMPAN ─────────────────────────────────────────────── */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ResepNo   = isset($_POST['ResepNo'])  && is_numeric($_POST['ResepNo'])  ? (int) $_POST['ResepNo']  : null;
    $UkuranNo  = isset($_POST['UkuranNo']) && is_numeric($_POST['UkuranNo']) ? (int) $_POST['UkuranNo'] : null;
    $StokNos   = isset($_POST['StokNo'])   && is_array($_POST['StokNo'])     ? $_POST['StokNo']         : [];
    $SatuanNos = isset($_POST['SatuanNo']) && is_array($_POST['SatuanNo'])   ? $_POST['SatuanNo']       : [];
    $jumlahs   = isset($_POST['jumlah'])   && is_array($_POST['jumlah'])     ? $_POST['jumlah']         : [];

    if ($ResepNo === null) {
        $errorMessage = 'Resep harus dipilih.';
    } elseif ($UkuranNo === null) {
        $errorMessage = 'Ukuran harus dipilih.';
    } elseif (empty($StokNos)) {
        $errorMessage = 'Minimal harus ada 1 bahan.';
    } else {
        $successCount = 0;
        $skipCount    = 0;

        for ($i = 0; $i < count($StokNos); $i++) {
            $StokNo   = isset($StokNos[$i])   && is_numeric($StokNos[$i])   ? (int)   $StokNos[$i]   : null;
            $SatuanNo = isset($SatuanNos[$i]) && is_numeric($SatuanNos[$i]) ? (int)   $SatuanNos[$i] : null;
            $jumlah   = isset($jumlahs[$i])   && is_numeric($jumlahs[$i]) && (float)$jumlahs[$i] > 0
                            ? (float) $jumlahs[$i] : null;

            if ($StokNo === null || $SatuanNo === null || $jumlah === null) { $skipCount++; continue; }

            $stmt = mysqli_prepare($conn,
                "INSERT INTO detail_resep (ResepNo, UkuranNo, StokNo, SatuanNo, jumlah)
                 VALUES (?, ?, ?, ?, ?)
                 ON DUPLICATE KEY UPDATE SatuanNo = VALUES(SatuanNo), jumlah = VALUES(jumlah)");
            if (!$stmt) { $skipCount++; continue; }
            mysqli_stmt_bind_param($stmt, 'iiiid', $ResepNo, $UkuranNo, $StokNo, $SatuanNo, $jumlah);
            mysqli_stmt_execute($stmt) ? $successCount++ : $skipCount++;
            mysqli_stmt_close($stmt);
        }

        $ukuranNama = htmlspecialchars($ukuranList[$UkuranNo] ?? '');
        if ($successCount > 0) {
            $successMessage = "{$successCount} bahan berhasil disimpan untuk ukuran <strong>{$ukuranNama}</strong>."
                . ($skipCount > 0 ? " ({$skipCount} baris dilewati)" : '');
            $selectedUkuranNo = $UkuranNo; // tetap pilih ukuran yang sama
            $_POST = [];
        } else {
            $errorMessage = 'Tidak ada data tersimpan. Pastikan bahan dan jumlah diisi dengan benar.';
        }
    }
}

/* ── DATA DROPDOWN ─────────────────────────────────────────────── */
$resepResult  = mysqli_query($conn,
    "SELECT r.ResepNo, m.nama_menu
     FROM resep r LEFT JOIN menu m ON r.MenuNo = m.MenuNo
     ORDER BY m.nama_menu ASC");
$stokResult = mysqli_query($conn,
    "SELECT s.StokNo, b.nama_bahan, s.SatuanNo, sat.nama_satuan
     FROM stok s
     LEFT JOIN bahan b    ON s.BahanNo  = b.BahanNo
     LEFT JOIN satuan sat ON s.SatuanNo = sat.SatuanNo
     ORDER BY b.nama_bahan ASC");

$stokArray = [];
if ($stokResult) while ($r = mysqli_fetch_assoc($stokResult)) $stokArray[] = $r;
?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <title>Detail Resep — Tambah</title>
    <link rel="stylesheet" href="../../assets/css/main.css">
    <link rel="stylesheet" href="../../assets/css/components.css">
    <link rel="stylesheet" href="../../assets/css/modules.css">
    <style>
        .bahan-table { width:100%; border-collapse:collapse; }
        .bahan-table th { background:#e8e8e8; padding:10px; text-align:left; border:1px solid #ddd; font-size:13px; }
        .bahan-table td { padding:8px; border:1px solid #ddd; vertical-align:middle; }
        .bahan-table select,
        .bahan-table input[type=number] { width:100%; padding:6px; box-sizing:border-box; border:1px solid #d1d5db; border-radius:4px; }
        .form-header { display:grid; grid-template-columns:1fr 1fr; gap:16px; margin-bottom:20px; }
        @media(max-width:640px){ .form-header { grid-template-columns:1fr; } }
    </style>
</head>
<body>
    <div class="app-layout">
        <?php include __DIR__ . '/../../components/sidebar.php'; ?>
        <main class="main-content module-detail-resep">
            <div class="container">

                <!-- ── FORM TAMBAH ── -->
                <div class="card">
                    <h2>Tambah Detail Resep</h2>

                    <?php if ($successMessage): ?>
                        <div class="alert success"><?php echo $successMessage; ?></div>
                    <?php endif; ?>
                    <?php if ($errorMessage): ?>
                        <div class="alert error"><?php echo htmlspecialchars($errorMessage); ?></div>
                    <?php endif; ?>

                    <form method="post" action="">

                        <!-- Resep & Ukuran -->
                        <div class="form-header">
                            <div>
                                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:4px">
                                    <label for="ResepNo" style="margin:0">Resep (Menu) <span style="color:red">*</span></label>
                                    <a href="../resep/resep_tambah.php"
                                       style="font-size:12px;color:#3b82f6;text-decoration:none;font-weight:600">
                                       + Tambah Resep Baru
                                    </a>
                                </div>
                                <select id="ResepNo" name="ResepNo" class="form-input" required
                                    <?php echo $selectedResepNo ? 'disabled' : 'onchange="if(this.value) window.location.href=\'detail_resep_tambah.php?ResepNo=\'+this.value"'; ?>>
                                    <option value="">-- Pilih Resep --</option>
                                    <?php if ($resepResult): while ($row = mysqli_fetch_assoc($resepResult)): ?>
                                        <option value="<?php echo $row['ResepNo']; ?>"
                                            <?php echo ($selectedResepNo == $row['ResepNo']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($row['nama_menu']); ?>
                                        </option>
                                    <?php endwhile; endif; ?>
                                </select>
                                <?php if ($selectedResepNo): ?>
                                    <input type="hidden" name="ResepNo" value="<?php echo $selectedResepNo; ?>">
                                    <a href="detail_resep_tambah.php"
                                       style="display:inline-block;margin-top:4px;font-size:12px;color:#6b7280;text-decoration:underline">
                                       ↩ Ganti resep
                                    </a>
                                <?php endif; ?>
                            </div>

                            <div>
                                <label for="UkuranNo">Ukuran <span style="color:red">*</span></label>
                                <select id="UkuranNo" name="UkuranNo" class="form-input" required>
                                    <option value="">-- Pilih Ukuran --</option>
                                    <?php foreach ($ukuranList as $uid => $uname): ?>
                                        <option value="<?php echo $uid; ?>"
                                            <?php echo ($selectedUkuranNo == $uid) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($uname); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <!-- Tabel bahan -->
                        <div style="background:#f9fafb;border-radius:6px;padding:16px">
                            <h3 style="margin:0 0 4px">Daftar Bahan</h3>
                            <p style="color:#6b7280;font-size:13px;margin:0 0 14px">
                                Satuan <strong>otomatis mengikuti satuan stok</strong> — pastikan stok sudah pakai satuan yang benar (gram, ml, dll).
                            </p>
                            <table class="bahan-table" id="itemsTable">
                                <thead>
                                    <tr>
                                        <th style="width:50%">Bahan (Stok)</th>
                                        <th style="width:20%;text-align:center">Satuan</th>
                                        <th style="width:20%">Jumlah</th>
                                        <th style="width:10%;text-align:center">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody id="itemsBody">
                                    <tr class="item-row">
                                        <td>
                                            <select name="StokNo[]" class="stok-select" required>
                                                <option value="">-- Pilih Bahan --</option>
                                                <?php foreach ($stokArray as $r): ?>
                                                    <option value="<?php echo $r['StokNo']; ?>"><?php echo htmlspecialchars($r['nama_bahan'] ?? '-'); ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </td>
                                        <td style="text-align:center">
                                            <span class="satuan-label" style="font-weight:600;color:#374151">—</span>
                                            <input type="hidden" name="SatuanNo[]" class="satuan-hidden">
                                        </td>
                                        <td>
                                            <input type="number" name="jumlah[]" step="0.01" min="0.01"
                                                placeholder="0" style="text-align:right" required>
                                        </td>
                                        <td style="text-align:center">
                                            <button type="button" class="btn-remove"
                                                style="padding:6px 10px;background:#ef4444;color:white;border:none;border-radius:4px;cursor:pointer">✕</button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                            <button type="button" id="addRowBtn" class="btn"
                                style="margin-top:10px;background:#3b82f6">+ Tambah Bahan</button>
                        </div>

                        <div class="form-actions" style="margin-top:20px">
                            <button class="btn" type="submit">Simpan</button>
                            <a class="btn" href="../resep/resep_tambah.php" style="background:#6b7280">← Kembali</a>
                        </div>
                    </form>

                    <script>
                    const stokOpts = `<?php foreach ($stokArray as $r) echo '<option value="'.$r['StokNo'].'">'.htmlspecialchars($r['nama_bahan']??'-').'</option>'; ?>`;

                    // StokNo → SatuanNo (untuk hidden input value)
                    const stokToSatuanNo = {<?php foreach ($stokArray as $r) echo '"'.$r['StokNo'].'":'.(int)$r['SatuanNo'].','; ?>};

                    // StokNo → nama_satuan (untuk label tampilan)
                    const stokToSatuanNama = {<?php foreach ($stokArray as $r) echo '"'.$r['StokNo'].'":"'.addslashes(htmlspecialchars($r['nama_satuan']??'-')).'",'; ?>};

                    function newRow() {
                        const tr = document.createElement('tr');
                        tr.className = 'item-row';
                        tr.innerHTML = `
                            <td><select name="StokNo[]" class="stok-select" required
                                style="width:100%;padding:6px;border:1px solid #d1d5db;border-radius:4px">
                                <option value="">-- Pilih Bahan --</option>${stokOpts}</select></td>
                            <td style="text-align:center">
                                <span class="satuan-label" style="font-weight:600;color:#374151">—</span>
                                <input type="hidden" name="SatuanNo[]" class="satuan-hidden">
                            </td>
                            <td><input type="number" name="jumlah[]" step="0.01" min="0.01" placeholder="0" required
                                style="width:100%;padding:6px;text-align:right;border:1px solid #d1d5db;border-radius:4px;box-sizing:border-box"></td>
                            <td style="text-align:center"><button type="button" class="btn-remove"
                                style="padding:6px 10px;background:#ef4444;color:white;border:none;border-radius:4px;cursor:pointer">✕</button></td>`;
                        attachListeners(tr);
                        document.getElementById('itemsBody').appendChild(tr);
                    }

                    function attachListeners(row) {
                        row.querySelector('.btn-remove').addEventListener('click', function () {
                            if (document.querySelectorAll('.item-row').length > 1) row.remove();
                            else alert('Minimal 1 bahan!');
                        });
                        row.querySelector('.stok-select').addEventListener('change', function () {
                            const satuanNo   = stokToSatuanNo[this.value]   || '';
                            const satuanNama = stokToSatuanNama[this.value] || '—';
                            row.querySelector('.satuan-label').textContent = satuanNama;
                            row.querySelector('.satuan-hidden').value      = satuanNo;
                        });
                    }

                    document.getElementById('addRowBtn').addEventListener('click', newRow);
                    document.querySelectorAll('.item-row').forEach(attachListeners);
                    </script>
                </div>

                <!-- ── STATUS KESIAPAN BAHAN PER UKURAN ── -->
                <?php if ($selectedResepNo):
                    $stmtStatus = mysqli_prepare($conn,
                        "SELECT u.UkuranNo, u.nama_ukuran,
                                (SELECT COUNT(*) FROM detail_resep dr
                                 WHERE dr.ResepNo = ? AND dr.UkuranNo = u.UkuranNo) AS jml_bahan
                         FROM varian_menu vm
                         LEFT JOIN ukuran u ON vm.ukuranNo = u.UkuranNo
                         LEFT JOIN resep r  ON vm.MenuNo   = r.MenuNo
                         WHERE r.ResepNo = ?
                         GROUP BY u.UkuranNo, u.nama_ukuran
                         ORDER BY u.UkuranNo ASC");
                    mysqli_stmt_bind_param($stmtStatus, 'ii', $selectedResepNo, $selectedResepNo);
                    mysqli_stmt_execute($stmtStatus);
                    $statusRes = mysqli_stmt_get_result($stmtStatus);
                    $statusRows = [];
                    while ($sr = mysqli_fetch_assoc($statusRes)) $statusRows[] = $sr;
                    mysqli_stmt_close($stmtStatus);

                    if (!empty($statusRows)):
                ?>
                <div class="card" style="margin-top:18px;padding:16px">
                    <h3 style="margin:0 0 12px;font-size:15px;color:#374151">📋 Status Kesiapan Bahan per Ukuran</h3>
                    <div style="display:flex;flex-wrap:wrap;gap:10px">
                        <?php foreach ($statusRows as $sr):
                            $namaUkuran = htmlspecialchars($sr['nama_ukuran'] ?? '-');
                            $jml        = (int) $sr['jml_bahan'];
                            $ukNo       = (int) $sr['UkuranNo'];
                            if ($jml > 0): ?>
                                <div style="display:flex;align-items:center;gap:8px;padding:10px 16px;
                                            background:#d1fae5;border:1px solid #6ee7b7;border-radius:8px">
                                    <span style="font-size:18px">✅</span>
                                    <div>
                                        <div style="font-weight:700;color:#065f46"><?php echo $namaUkuran; ?></div>
                                        <div style="font-size:12px;color:#047857"><?php echo $jml; ?> bahan sudah diisi</div>
                                    </div>
                                </div>
                            <?php else: ?>
                                <div style="display:flex;align-items:center;gap:8px;padding:10px 16px;
                                            background:#fee2e2;border:1px solid #fca5a5;border-radius:8px">
                                    <span style="font-size:18px">❌</span>
                                    <div>
                                        <div style="font-weight:700;color:#991b1b"><?php echo $namaUkuran; ?></div>
                                        <div style="font-size:12px">
                                            <a href="?ResepNo=<?php echo $selectedResepNo; ?>"
                                               onclick="document.getElementById('UkuranNo').value='<?php echo $ukNo; ?>'; return false;"
                                               style="color:#dc2626;text-decoration:underline;cursor:pointer"
                                               id="isi-ukuran-<?php echo $ukNo; ?>">
                                               Belum ada bahan — Pilih ukuran ini ↑
                                            </a>
                                        </div>
                                    </div>
                                </div>
                                <script>
                                document.getElementById('isi-ukuran-<?php echo $ukNo; ?>').addEventListener('click', function(e) {
                                    e.preventDefault();
                                    document.getElementById('UkuranNo').value = '<?php echo $ukNo; ?>';
                                    document.getElementById('UkuranNo').scrollIntoView({behavior:'smooth', block:'center'});
                                    document.getElementById('UkuranNo').style.outline = '2px solid #ef4444';
                                    setTimeout(() => document.getElementById('UkuranNo').style.outline = '', 2000);
                                });
                                </script>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; endif; ?>

                <!-- ── DAFTAR BAHAN TERSIMPAN ── -->
                <div class="card" style="margin-top:18px">
                    <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:8px">
                        <h2 style="margin:0">Daftar Bahan Resep</h2>
                        <div style="display:flex;gap:8px;align-items:center">
                            <?php if ($selectedResepNo): ?>
                                <a href="detail_resep_tambah.php"
                                   style="font-size:13px;color:#6b7280;text-decoration:underline">
                                   ☰ Lihat semua
                                </a>
                            <?php endif; ?>
                            <button type="button" id="toggleDaftar"
                                onclick="
                                    const el = document.getElementById('daftarContent');
                                    const open = el.style.display !== 'none';
                                    el.style.display = open ? 'none' : 'block';
                                    this.textContent = open ? '▼ Tampilkan' : '▲ Sembunyikan';
                                "
                                style="padding:6px 14px;background:#e5e7eb;border:none;border-radius:6px;
                                       cursor:pointer;font-size:13px;font-weight:600;color:#374151">
                                ▼ Tampilkan
                            </button>
                        </div>
                    </div>
                    <div id="daftarContent" style="display:none;margin-top:14px">
                    <?php
                    // Query: semua data, atau filter per resep kalau dipilih
                    $whereClause = $selectedResepNo ? "WHERE dr.ResepNo = {$selectedResepNo}" : '';
                    $sqlList = "SELECT dr.DetailResepNo, dr.ResepNo,
                                       m.nama_menu, u.nama_ukuran, u.UkuranNo,
                                       b.nama_bahan, sat.nama_satuan, dr.jumlah
                                FROM detail_resep dr
                                LEFT JOIN resep r    ON dr.ResepNo  = r.ResepNo
                                LEFT JOIN menu m     ON r.MenuNo    = m.MenuNo
                                LEFT JOIN ukuran u   ON dr.UkuranNo = u.UkuranNo
                                LEFT JOIN stok st    ON dr.StokNo   = st.StokNo
                                LEFT JOIN bahan b    ON st.BahanNo  = b.BahanNo
                                LEFT JOIN satuan sat ON dr.SatuanNo = sat.SatuanNo
                                {$whereClause}
                                ORDER BY m.nama_menu ASC, u.UkuranNo ASC, b.nama_bahan ASC";
                    $res = mysqli_query($conn, $sqlList);

                    if ($res && mysqli_num_rows($res) > 0):
                        // Kelompokkan: menu → ukuran → bahan[]
                        $grouped = [];
                        while ($row = mysqli_fetch_assoc($res)) {
                            $menu   = $row['nama_menu']   ?? '-';
                            $ukuran = $row['nama_ukuran'] ?? '-';
                            $grouped[$menu][$ukuran][] = $row;
                        }

                        $menuIdx = 0;
                        foreach ($grouped as $namaMenu => $ukuranGroup):
                            $menuIdx++;
                            $menuId = 'menu-group-' . $menuIdx;
                            // Default: kalau ada filter resep → buka semua, kalau tidak → tutup semua
                            $defaultDisplay = $selectedResepNo ? 'block' : 'none';
                            $defaultArrow   = $selectedResepNo ? '▲' : '▼';

                            // Header menu + tombol toggle
                            echo '<div style="background:#eff6ff;border-left:4px solid #3b82f6;
                                              padding:10px 14px;margin:16px 0 0;border-radius:0 6px 6px 0;
                                              display:flex;justify-content:space-between;align-items:center">';
                            echo '<strong style="font-size:15px;color:#1e40af">📋 ' . htmlspecialchars($namaMenu) . '</strong>';
                            echo '<button type="button"
                                    id="btn-' . $menuId . '"
                                    onclick="
                                        var el = document.getElementById(\'' . $menuId . '\');
                                        var open = el.style.display !== \'none\';
                                        el.style.display = open ? \'none\' : \'block\';
                                        this.textContent = open ? \'▼\' : \'▲\';
                                    "
                                    style="padding:3px 10px;background:#dbeafe;border:1px solid #93c5fd;
                                           border-radius:4px;cursor:pointer;font-size:13px;color:#1e40af;font-weight:700">
                                    ' . $defaultArrow . '
                                </button>';
                            echo '</div>';

                            echo '<div id="' . $menuId . '" style="display:' . $defaultDisplay . ';margin-bottom:8px">';

                            foreach ($ukuranGroup as $namaUkuran => $bahanList):
                                echo '<h4 style="margin:10px 0 6px;color:#374151;font-size:13px;font-weight:600">📐 Ukuran: '
                                    . htmlspecialchars($namaUkuran) . '</h4>';
                                echo '<table style="width:100%;border-collapse:collapse;margin-bottom:12px">';
                                echo '<thead><tr style="background:#f3f4f6">';
                                echo '<th style="padding:7px 8px;text-align:left;border-bottom:1px solid #ddd;font-size:12px">#</th>';
                                echo '<th style="padding:7px 8px;text-align:left;border-bottom:1px solid #ddd;font-size:12px">Bahan</th>';
                                echo '<th style="padding:7px 8px;text-align:left;border-bottom:1px solid #ddd;font-size:12px">Satuan</th>';
                                echo '<th style="padding:7px 8px;text-align:right;border-bottom:1px solid #ddd;font-size:12px">Jumlah</th>';
                                echo '<th style="padding:7px 8px;text-align:right;border-bottom:1px solid #ddd;font-size:12px">Aksi</th>';
                                echo '</tr></thead><tbody>';

                                foreach ($bahanList as $idx => $row):
                                    $id     = $row['DetailResepNo'];
                                    $bahan  = htmlspecialchars($row['nama_bahan']  ?? '-');
                                    $satuan = htmlspecialchars($row['nama_satuan'] ?? '-');
                                    $jumlah = $row['jumlah'] + 0;
                                    $resepNo = (int) $row['ResepNo'];
                                    echo "<tr style=\"border-bottom:1px solid #eee\">";
                                    echo "<td style=\"padding:7px 8px\">" . ($idx + 1) . "</td>";
                                    echo "<td style=\"padding:7px 8px\"><strong>{$bahan}</strong></td>";
                                    echo "<td style=\"padding:7px 8px\">{$satuan}</td>";
                                    echo "<td style=\"padding:7px 8px;text-align:right\">{$jumlah}</td>";
                                    echo "<td style=\"padding:7px 8px;text-align:right;white-space:nowrap\">";
                                    echo "<a href=\"detail_resep_edit.php?DetailResepNo={$id}\"
                                            style=\"padding:4px 8px;font-size:12px;background:#f59e0b;color:white;text-decoration:none;border-radius:4px;margin-right:3px\">Edit</a>";
                                    echo "<a href=\"detail_resep_delete_bahan.php?ids={$id}&ResepNo={$resepNo}\"
                                            onclick=\"return confirm('Hapus bahan ini?')\"
                                            style=\"padding:4px 8px;font-size:12px;background:#ef4444;color:white;text-decoration:none;border-radius:4px\">Hapus</a>";
                                    echo "</td></tr>";
                                endforeach;

                                echo '</tbody></table>';
                            endforeach;
                            echo '</div>'; // /menu-group
                        endforeach;

                    else:
                        echo '<p style="color:#6b7280;text-align:center;padding:20px 0">Belum ada bahan resep yang tersimpan.</p>';
                    endif;
                    ?>
                    </div><!-- /daftarContent -->
                </div>

            </div>
        </main>
    </div>
    <?php if ($selectedResepNo): ?>
    <script>
        // Kalau sudah ada resep dipilih, buka daftar otomatis
        document.addEventListener('DOMContentLoaded', function () {
            document.getElementById('daftarContent').style.display = 'block';
            document.getElementById('toggleDaftar').textContent = '▲ Sembunyikan';
        });
    </script>
    <?php endif; ?>
</body>
</html>
