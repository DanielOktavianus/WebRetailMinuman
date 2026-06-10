<?php
require_once __DIR__ . '/../../config/database.php';

$successMessage = '';
$errorMessage   = '';
$warningMessage = '';

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
        $dupNames     = [];

        for ($i = 0; $i < count($StokNos); $i++) {
            $StokNo   = isset($StokNos[$i])   && is_numeric($StokNos[$i])   ? (int)   $StokNos[$i]   : null;
            $SatuanNo = isset($SatuanNos[$i]) && is_numeric($SatuanNos[$i]) ? (int)   $SatuanNos[$i] : null;
            $jumlah   = isset($jumlahs[$i])   && is_numeric($jumlahs[$i]) && (float)$jumlahs[$i] > 0
                            ? (float) $jumlahs[$i] : null;

            if ($StokNo === null || $SatuanNo === null || $jumlah === null) { $skipCount++; continue; }

            // Cek apakah bahan ini sudah ada untuk resep + ukuran yang sama
            $cek = mysqli_prepare($conn,
                "SELECT 1 FROM detail_resep WHERE ResepNo = ? AND UkuranNo = ? AND StokNo = ? LIMIT 1");
            mysqli_stmt_bind_param($cek, 'iii', $ResepNo, $UkuranNo, $StokNo);
            mysqli_stmt_execute($cek);
            mysqli_stmt_store_result($cek);
            $sudahAda = mysqli_stmt_num_rows($cek) > 0;
            mysqli_stmt_close($cek);

            if ($sudahAda) {
                $namaQ   = mysqli_query($conn,
                    "SELECT b.nama_bahan FROM stok s LEFT JOIN bahan b ON s.BahanNo = b.BahanNo WHERE s.StokNo = {$StokNo} LIMIT 1");
                $namaRow = $namaQ ? mysqli_fetch_assoc($namaQ) : null;
                $dupNames[] = htmlspecialchars($namaRow['nama_bahan'] ?? "Bahan #$StokNo");
                continue;
            }

            $stmt = mysqli_prepare($conn,
                "INSERT INTO detail_resep (ResepNo, UkuranNo, StokNo, SatuanNo, jumlah) VALUES (?, ?, ?, ?, ?)");
            if (!$stmt) { $skipCount++; continue; }
            mysqli_stmt_bind_param($stmt, 'iiiid', $ResepNo, $UkuranNo, $StokNo, $SatuanNo, $jumlah);
            mysqli_stmt_execute($stmt) ? $successCount++ : $skipCount++;
            mysqli_stmt_close($stmt);
        }

        $ukuranNama = htmlspecialchars($ukuranList[$UkuranNo] ?? '');

        if (!empty($dupNames)) {
            $dupList        = implode(', ', $dupNames);
            $warningMessage = "Bahan berikut sudah ada untuk ukuran <strong>{$ukuranNama}</strong>: "
                            . "<strong>{$dupList}</strong>. "
                            . "Gunakan tombol <strong>Edit</strong> di tabel bawah untuk mengubah jumlahnya.";
        }

        if ($successCount > 0) {
            $successMessage = "{$successCount} bahan berhasil disimpan untuk ukuran <strong>{$ukuranNama}</strong>."
                . ($skipCount > 0 ? " ({$skipCount} baris dilewati)" : '');
            $selectedUkuranNo = $UkuranNo;
            $_POST = [];
        } elseif (empty($dupNames)) {
            $errorMessage = 'Tidak ada data tersimpan. Pastikan bahan dan jumlah diisi dengan benar.';
        }
    }
}

/* ── DATA QUERY ─────────────────────────────────────────────────── */
// Semua resep (ResepNo → nama_menu)
$resepArr = [];
$resepQ   = mysqli_query($conn,
    "SELECT r.ResepNo, m.nama_menu FROM resep r LEFT JOIN menu m ON r.MenuNo = m.MenuNo ORDER BY m.nama_menu ASC");
if ($resepQ) while ($r = mysqli_fetch_assoc($resepQ)) $resepArr[(int)$r['ResepNo']] = $r['nama_menu'];

$selectedMenuNama = $selectedResepNo ? ($resepArr[$selectedResepNo] ?? '') : '';

// Stok bahan untuk form
$stokArr = [];
$stokQ   = mysqli_query($conn,
    "SELECT s.StokNo, b.nama_bahan, s.SatuanNo, sat.nama_satuan
     FROM stok s
     LEFT JOIN bahan b    ON s.BahanNo  = b.BahanNo
     LEFT JOIN satuan sat ON s.SatuanNo = sat.SatuanNo
     ORDER BY b.nama_bahan ASC");
if ($stokQ) while ($r = mysqli_fetch_assoc($stokQ)) $stokArr[] = $r;

// Detail resep yang sudah tersimpan — group by ResepNo → UkuranNama → bahan[]
$whereForList = $selectedResepNo ? "WHERE dr.ResepNo = {$selectedResepNo}" : '';
$listRes = mysqli_query($conn,
    "SELECT dr.DetailResepNo, dr.ResepNo,
            m.nama_menu, u.nama_ukuran, u.UkuranNo,
            b.nama_bahan, sat.nama_satuan, dr.jumlah
     FROM detail_resep dr
     LEFT JOIN resep r    ON dr.ResepNo  = r.ResepNo
     LEFT JOIN menu m     ON r.MenuNo    = m.MenuNo
     LEFT JOIN ukuran u   ON dr.UkuranNo = u.UkuranNo
     LEFT JOIN stok st    ON dr.StokNo   = st.StokNo
     LEFT JOIN bahan b    ON st.BahanNo  = b.BahanNo
     LEFT JOIN satuan sat ON dr.SatuanNo = sat.SatuanNo
     {$whereForList}
     ORDER BY m.nama_menu ASC, u.UkuranNo ASC, b.nama_bahan ASC");

$grouped = []; // ResepNo → UkuranNama → bahan[]
if ($listRes) {
    while ($row = mysqli_fetch_assoc($listRes)) {
        $rNo    = (int)$row['ResepNo'];
        $ukuran = $row['nama_ukuran'] ?? '-';
        $grouped[$rNo][$ukuran][] = $row;
    }
}
?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <title>Detail Resep</title>
    <link rel="stylesheet" href="../../assets/css/main.css">
    <link rel="stylesheet" href="../../assets/css/components.css">
    <link rel="stylesheet" href="../../assets/css/modules.css">
    <style>
        .bahan-table { width:100%; border-collapse:collapse; }
        .bahan-table th { background:#e8e8e8; padding:10px; text-align:left; border:1px solid #ddd; font-size:13px; }
        .bahan-table td { padding:8px; border:1px solid #ddd; vertical-align:middle; }
        .bahan-table select,
        .bahan-table input[type=number] { width:100%; padding:6px; box-sizing:border-box; border:1px solid #d1d5db; border-radius:4px; }
    </style>
</head>
<body>
    <div class="app-layout">
        <?php include __DIR__ . '/../../components/sidebar.php'; ?>
        <main class="main-content module-detail-resep">
            <div class="container">

<?php if ($selectedResepNo): ?>
                <!-- ══ MODE FORM: tampil saat tombol "Isi Bahan" ditekan ══ -->
                <div class="card">
                    <div style="display:flex;align-items:center;gap:10px;margin-bottom:16px">
                        <a href="detail_resep_tambah.php"
                           style="padding:6px 12px;background:#e5e7eb;border-radius:6px;text-decoration:none;color:#374151;font-size:13px;font-weight:600">
                            ← Kembali
                        </a>
                        <h2 style="margin:0">Isi Bahan: <span style="color:#3b82f6"><?php echo htmlspecialchars($selectedMenuNama); ?></span></h2>
                    </div>

                    <?php if ($successMessage): ?>
                        <div class="alert success"><?php echo $successMessage; ?></div>
                    <?php endif; ?>
                    <?php if ($warningMessage): ?>
                        <div style="background:#fefce8;border:1px solid #fde047;color:#713f12;padding:12px 14px;border-radius:6px;margin-bottom:12px;font-size:13px">
                            ⚠️ <?php echo $warningMessage; ?>
                        </div>
                    <?php endif; ?>
                    <?php if ($errorMessage): ?>
                        <div class="alert error"><?php echo htmlspecialchars($errorMessage); ?></div>
                    <?php endif; ?>

                    <form method="post" action="">
                        <input type="hidden" name="ResepNo" value="<?php echo $selectedResepNo; ?>">

                        <div style="margin-bottom:16px">
                            <label for="UkuranNo" style="display:block;font-weight:600;margin-bottom:4px">
                                Ukuran <span style="color:red">*</span>
                            </label>
                            <select id="UkuranNo" name="UkuranNo" class="form-input" required style="max-width:300px">
                                <option value="">-- Pilih Ukuran --</option>
                                <?php foreach ($ukuranList as $uid => $uname): ?>
                                    <option value="<?php echo $uid; ?>"
                                        <?php echo ($selectedUkuranNo == $uid) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($uname); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Tabel bahan -->
                        <div style="background:#f9fafb;border-radius:6px;padding:16px">
                            <h3 style="margin:0 0 4px">Daftar Bahan</h3>
                            <p style="color:#6b7280;font-size:13px;margin:0 0 14px">
                                Satuan <strong>otomatis mengikuti satuan stok</strong>.
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
                                                <?php foreach ($stokArr as $r): ?>
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
                        </div>
                    </form>

                    <script>
                    const stokOpts = `<?php foreach ($stokArr as $r) echo '<option value="'.$r['StokNo'].'">'.htmlspecialchars($r['nama_bahan']??'-').'</option>'; ?>`;
                    const stokToSatuanNo   = {<?php foreach ($stokArr as $r) echo '"'.$r['StokNo'].'":'.(int)$r['SatuanNo'].','; ?>};
                    const stokToSatuanNama = {<?php foreach ($stokArr as $r) echo '"'.$r['StokNo'].'":"'.addslashes(htmlspecialchars($r['nama_satuan']??'-')).'",'; ?>};

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

                <!-- Bahan yang sudah tersimpan untuk resep ini -->
                <div class="card" style="margin-top:18px">
                    <h3 style="margin:0 0 14px">Bahan Tersimpan — <?php echo htmlspecialchars($selectedMenuNama); ?></h3>
                    <?php if (!empty($grouped[$selectedResepNo])): ?>
                        <?php foreach ($grouped[$selectedResepNo] as $namaUkuran => $bahanList): ?>
                            <h4 style="margin:0 0 6px;color:#374151;font-size:13px;font-weight:600">📐 Ukuran: <?php echo htmlspecialchars($namaUkuran); ?></h4>
                            <table style="width:100%;border-collapse:collapse;margin-bottom:16px">
                                <thead><tr style="background:#f3f4f6">
                                    <th style="padding:7px 8px;text-align:left;border-bottom:1px solid #ddd;font-size:12px">#</th>
                                    <th style="padding:7px 8px;text-align:left;border-bottom:1px solid #ddd;font-size:12px">Bahan</th>
                                    <th style="padding:7px 8px;text-align:left;border-bottom:1px solid #ddd;font-size:12px">Satuan</th>
                                    <th style="padding:7px 8px;text-align:right;border-bottom:1px solid #ddd;font-size:12px">Jumlah</th>
                                    <th style="padding:7px 8px;text-align:right;border-bottom:1px solid #ddd;font-size:12px">Aksi</th>
                                </tr></thead>
                                <tbody>
                                <?php foreach ($bahanList as $idx => $row):
                                    $id     = $row['DetailResepNo'];
                                    $bahan  = htmlspecialchars($row['nama_bahan']  ?? '-');
                                    $satuan = htmlspecialchars($row['nama_satuan'] ?? '-');
                                    $jumlah = $row['jumlah'] + 0;
                                    $resepNo = (int)$row['ResepNo'];
                                ?>
                                    <tr style="border-bottom:1px solid #eee">
                                        <td style="padding:7px 8px"><?php echo $idx + 1; ?></td>
                                        <td style="padding:7px 8px"><strong><?php echo $bahan; ?></strong></td>
                                        <td style="padding:7px 8px"><?php echo $satuan; ?></td>
                                        <td style="padding:7px 8px;text-align:right"><?php echo $jumlah; ?></td>
                                        <td style="padding:7px 8px;text-align:right;white-space:nowrap">
                                            <a href="detail_resep_edit.php?DetailResepNo=<?php echo $id; ?>"
                                               style="padding:4px 8px;font-size:12px;background:#f59e0b;color:white;text-decoration:none;border-radius:4px;margin-right:3px">Edit</a>
                                            <a href="detail_resep_delete_bahan.php?ids=<?php echo $id; ?>&ResepNo=<?php echo $resepNo; ?>"
                                               onclick="return confirm('Hapus bahan ini?')"
                                               style="padding:4px 8px;font-size:12px;background:#ef4444;color:white;text-decoration:none;border-radius:4px">Hapus</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                                </tbody>
                            </table>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p style="color:#6b7280;text-align:center;padding:16px 0">Belum ada bahan tersimpan untuk resep ini.</p>
                    <?php endif; ?>
                </div>

<?php else: ?>
                <!-- ══ MODE DAFTAR: tampilan default ══ -->
                <div class="card">
                    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px">
                        <h2 style="margin:0">Daftar Resep</h2>
                        <span style="font-size:13px;color:#6b7280"><?php echo count($resepArr); ?> resep terdaftar</span>
                    </div>

                    <?php if (empty($resepArr)): ?>
                        <p style="color:#6b7280;text-align:center;padding:24px 0">
                            Belum ada resep. <a href="../resep/resep_tambah.php" style="color:#3b82f6">+ Tambah Resep</a>
                        </p>
                    <?php else: ?>

                    <!-- Tombol Filter -->
                    <div style="display:flex;gap:8px;margin-bottom:16px">
                        <button onclick="filterResep('semua')" id="tab-semua"
                            style="padding:7px 18px;border-radius:20px;border:none;cursor:pointer;font-size:13px;font-weight:600;background:#3b82f6;color:white">
                            Semua <span id="count-semua">(<?php echo count($resepArr); ?>)</span>
                        </button>
                        <button onclick="filterResep('diisi')" id="tab-diisi"
                            style="padding:7px 18px;border-radius:20px;border:none;cursor:pointer;font-size:13px;font-weight:600;background:#e5e7eb;color:#374151">
                            Sudah Diisi <span id="count-diisi"></span>
                        </button>
                        <button onclick="filterResep('belum')" id="tab-belum"
                            style="padding:7px 18px;border-radius:20px;border:none;cursor:pointer;font-size:13px;font-weight:600;background:#e5e7eb;color:#374151">
                            Belum Diisi <span id="count-belum"></span>
                        </button>
                    </div>

                    <!-- Daftar Resep -->
                    <?php foreach ($resepArr as $resepNo => $namaMenu):
                        $jumlahBahan = 0;
                        if (!empty($grouped[$resepNo]))
                            foreach ($grouped[$resepNo] as $ukList) $jumlahBahan += count($ukList);
                        $hasDetail = !empty($grouped[$resepNo]);
                        $status    = $hasDetail ? 'diisi' : 'belum';
                    ?>
                        <div class="resep-row" data-status="<?php echo $status; ?>"
                             style="border:1px solid #e5e7eb;border-radius:8px;margin-bottom:10px;overflow:hidden">
                            <div style="display:flex;justify-content:space-between;align-items:center;padding:12px 16px;background:#f9fafb">
                                <div style="display:flex;align-items:center;gap:10px">
                                    <span style="font-size:15px;font-weight:600;color:#1f2937">
                                        📋 <?php echo htmlspecialchars($namaMenu); ?>
                                    </span>
                                    <span style="font-size:12px;padding:2px 8px;border-radius:10px;
                                        <?php echo $hasDetail ? 'color:#16a34a;background:#dcfce7' : 'color:#b45309;background:#fef3c7'; ?>">
                                        <?php echo $hasDetail ? $jumlahBahan . ' bahan' : 'Belum diisi'; ?>
                                    </span>
                                </div>
                                <div style="display:flex;gap:8px;align-items:center">
                                    <a href="detail_resep_tambah.php?ResepNo=<?php echo $resepNo; ?>"
                                       style="padding:6px 14px;background:#3b82f6;color:white;text-decoration:none;border-radius:6px;font-size:13px;font-weight:600">
                                        + Isi Bahan
                                    </a>
                                    <?php if ($hasDetail): ?>
                                    <button type="button"
                                        onclick="var el=document.getElementById('detail-<?php echo $resepNo; ?>');var open=el.style.display!=='none';el.style.display=open?'none':'block';this.textContent=open?'▼':'▲';"
                                        style="padding:5px 10px;background:#e5e7eb;border:none;border-radius:6px;cursor:pointer;font-size:13px;font-weight:700;color:#374151">▲</button>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <?php if ($hasDetail): ?>
                            <div id="detail-<?php echo $resepNo; ?>" style="display:block;padding:12px 16px;border-top:1px solid #e5e7eb">
                                <?php foreach ($grouped[$resepNo] as $namaUkuran => $bahanList): ?>
                                    <p style="font-size:12px;font-weight:600;color:#6b7280;margin:8px 0 4px">
                                        📐 Ukuran: <?php echo htmlspecialchars($namaUkuran); ?>
                                    </p>
                                    <table style="width:100%;border-collapse:collapse;margin-bottom:10px">
                                        <thead><tr style="background:#f3f4f6">
                                            <th style="padding:6px 8px;text-align:left;border-bottom:1px solid #ddd;font-size:12px">#</th>
                                            <th style="padding:6px 8px;text-align:left;border-bottom:1px solid #ddd;font-size:12px">Bahan</th>
                                            <th style="padding:6px 8px;text-align:left;border-bottom:1px solid #ddd;font-size:12px">Satuan</th>
                                            <th style="padding:6px 8px;text-align:right;border-bottom:1px solid #ddd;font-size:12px">Jumlah</th>
                                            <th style="padding:6px 8px;text-align:right;border-bottom:1px solid #ddd;font-size:12px">Aksi</th>
                                        </tr></thead>
                                        <tbody>
                                        <?php foreach ($bahanList as $bi => $row):
                                            $id     = $row['DetailResepNo'];
                                            $bahan  = htmlspecialchars($row['nama_bahan']  ?? '-');
                                            $satuan = htmlspecialchars($row['nama_satuan'] ?? '-');
                                            $jumlah = $row['jumlah'] + 0;
                                            $rNo    = (int)$row['ResepNo'];
                                        ?>
                                            <tr style="border-bottom:1px solid #f0f0f0">
                                                <td style="padding:6px 8px;font-size:13px"><?php echo $bi + 1; ?></td>
                                                <td style="padding:6px 8px;font-size:13px"><strong><?php echo $bahan; ?></strong></td>
                                                <td style="padding:6px 8px;font-size:13px"><?php echo $satuan; ?></td>
                                                <td style="padding:6px 8px;font-size:13px;text-align:right"><?php echo $jumlah; ?></td>
                                                <td style="padding:6px 8px;text-align:right;white-space:nowrap">
                                                    <a href="detail_resep_edit.php?DetailResepNo=<?php echo $id; ?>"
                                                       style="padding:3px 8px;font-size:12px;background:#f59e0b;color:white;text-decoration:none;border-radius:4px;margin-right:3px">Edit</a>
                                                    <a href="detail_resep_delete_bahan.php?ids=<?php echo $id; ?>&ResepNo=<?php echo $rNo; ?>"
                                                       onclick="return confirm('Hapus bahan ini?')"
                                                       style="padding:3px 8px;font-size:12px;background:#ef4444;color:white;text-decoration:none;border-radius:4px">Hapus</a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                <?php endforeach; ?>
                            </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>

                    <script>
                    function filterResep(filter) {
                        const rows   = document.querySelectorAll('.resep-row');
                        let cntDiisi = 0, cntBelum = 0;
                        rows.forEach(r => {
                            const s = r.dataset.status;
                            if (s === 'diisi') cntDiisi++;
                            else cntBelum++;
                            r.style.display = (filter === 'semua' || r.dataset.status === filter) ? '' : 'none';
                        });
                        document.getElementById('count-diisi').textContent = '(' + cntDiisi + ')';
                        document.getElementById('count-belum').textContent = '(' + cntBelum + ')';
                        ['semua','diisi','belum'].forEach(t => {
                            const btn = document.getElementById('tab-' + t);
                            btn.style.background = t === filter ? '#3b82f6' : '#e5e7eb';
                            btn.style.color      = t === filter ? 'white'   : '#374151';
                        });
                    }
                    filterResep('semua');
                    </script>

                    <?php endif; ?>
                </div>
<?php endif; ?>

            </div>
        </main>
    </div>
</body>
</html>
