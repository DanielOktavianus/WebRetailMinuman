const fs = require('fs');
const path = require('path');
const file = path.join(__dirname, 'dashboard', 'dashboard.php');
let lines = fs.readFileSync(file, 'utf8').split(/\r?\n/);
let start = lines.findIndex(l => l.includes('$menuTerlaris = mysqli_query($conn,'));
let end = lines.findIndex((l, i) => i > start && l.includes("} else {"));
if (start === -1 || end === -1) {
  console.error('Unable to locate broken menuTerlaris block');
  process.exit(1);
}
let replacement = [
  '\t\t\t\t\t\t$menuTerlaris = mysqli_query($conn,',
  '\t\t\t\t\t\t\t"SELECT m.nama_menu, u.nama_ukuran,',
  '\t\t\t\t\t\t\t\tSUM(dt.jumlah) AS total_terjual,',
  '\t\t\t\t\t\t\t\tSUM(dt.jumlah * dt.harga_satuan) AS total_harga',
  '\t\t\t\t\t\t\t FROM detail_transaksi dt',
  '\t\t\t\t\t\t\t LEFT JOIN varian_menu vm ON dt.VarianMenuNo = vm.VarianMenuNo',
  '\t\t\t\t\t\t\t LEFT JOIN menu m          ON vm.MenuNo      = m.MenuNo',
  '\t\t\t\t\t\t\t LEFT JOIN ukuran u         ON vm.ukuranNo   = u.UkuranNo',
  '\t\t\t\t\t\t\t LEFT JOIN transaksi t      ON dt.transaksiNo = t.transaksiNo',
  '\t\t\t\t\t\t\t WHERE 1=1 {$pfWhere}',
  '\t\t\t\t\t\t\t GROUP BY m.MenuNo, vm.VarianMenuNo',
  '\t\t\t\t\t\t\t ORDER BY total_terjual DESC',
  '\t\t\t\t\t\t\t LIMIT 10");',
  '\t\t\t\t\t\tif ($menuTerlaris && mysqli_num_rows($menuTerlaris) > 0) {',
  '\t\t\t\t\t\t\techo \'<table class="dashboard-table">\';',
  '\t\t\t\t\t\t\techo \'<thead><tr>\';',
  '\t\t\t\t\t\t\techo \'<th>#</th><th>Menu</th><th>Ukuran</th>\';',
  '\t\t\t\t\t\t\techo \'<th style="text-align:right">Total Terjual</th>\';',
  '\t\t\t\t\t\t\techo \'<th style="text-align:right">Total Pendapatan</th>\';',
  '\t\t\t\t\t\t\techo \'</tr></thead><tbody>\';',
  '\t\t\t\t\t\t\t$rank = 1;',
  '\t\t\t\t\t\t\twhile ($m = mysqli_fetch_assoc($menuTerlaris)) {',
  '\t\t\t\t\t\t\t\t$medal = $rank === 1 ? \'🥇\' : ($rank === 2 ? \'🥈\' : ($rank === 3 ? \'🥉\' : $rank));',
  '\t\t\t\t\t\t\t\techo \'<tr>\';',
  '\t\t\t\t\t\t\t\techo \'<td style="text-align:center;font-weight:bold">\' . $medal . \'</td>\';',
  '\t\t\t\t\t\t\t\techo \'<td><strong>\' . htmlspecialchars($m[\'nama_menu\']) . \'</strong></td>\';',
  '\t\t\t\t\t\t\t\techo \'<td>\' . htmlspecialchars($m[\'nama_ukuran\'] ?? \'-\') . \'</td>\';',
  '\t\t\t\t\t\t\t\techo \'<td style="text-align:right">\' . (int)$m[\'total_terjual\'] . \' item</td>\';',
  '\t\t\t\t\t\t\t\techo \'<td style="text-align:right">\' . rupiah($m[\'total_harga\']) . \'</td>\';',
  '\t\t\t\t\t\t\t\techo \'</tr>\';',
  '\t\t\t\t\t\t\t\t$rank++;',
  '\t\t\t\t\t\t\t}',
  '\t\t\t\t\t\t\techo \'</tbody></table>\';',
  '\t\t\t\t\t\t} else {',
];
lines.splice(start, end - start, ...replacement);
fs.writeFileSync(file, lines.join('\r\n'));
console.log('dashboard.php fixed');
