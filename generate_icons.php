<?php
$src = __DIR__ . '/assets/img/original.png';
if (!file_exists($src)) { die('original.png tidak ditemukan'); }

$img = imagecreatefrompng($src);
if (!$img) { die('Gagal baca gambar'); }

foreach ([192, 512] as $size) {
    $out = imagecreatetruecolor($size, $size);
    imagealphablending($out, false);
    imagesavealpha($out, true);
    $transparent = imagecolorallocatealpha($out, 0, 0, 0, 127);
    imagefill($out, 0, 0, $transparent);
    imagecopyresampled($out, $img, 0, 0, 0, 0, $size, $size, imagesx($img), imagesy($img));
    imagepng($out, __DIR__ . "/assets/img/icon-{$size}.png");
    imagedestroy($out);
    echo "✅ icon-{$size}.png berhasil dibuat<br>";
}
imagedestroy($img);
echo '<br><strong>Selesai! Hapus file ini setelah selesai.</strong>';
?>
