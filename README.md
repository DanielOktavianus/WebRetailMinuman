# WebRetailMinuman

Project ini merupakan aplikasi web berbasis PHP dan MySQL untuk membantu manajemen bisnis restoran/minuman, terutama untuk proses pengelolaan menu, stok bahan, resep, transaksi, dan akses pengguna berdasarkan role.

Project ini dibuat untuk kebutuhan pembelajaran dan portofolio, bukan produk SaaS yang sudah dipasarkan ke pelanggan.

## Fitur utama

- Login dan autentikasi pengguna
- Role-based access control (admin dan karyawan)
- Manajemen menu dan varian menu
- Manajemen bahan baku dan stok
- Pengelolaan supplier/produsen
- Manajemen resep dan detail resep
- Proses transaksi penjualan
- Dashboard ringkasan penjualan dan stok rendah
- PWA (Progressive Web App) basic support

## Stack teknologi

- PHP 8+
- MySQL
- JavaScript
- HTML/CSS
- Service Worker untuk fitur PWA

## Struktur proyek

- `auth/` — login, logout, proses autentikasi
- `config/` — konfigurasi aplikasi dan database
- `dashboard/` — halaman dashboard utama
- `helpers/` — helper umum seperti auth dan format
- `modules/` — fitur utama aplikasi berdasarkan modul
- `components/` — header, navbar, sidebar, footer
- `assets/` — CSS, JS, gambar, dan aset frontend
- `sql/` — script SQL pendukung

## Persyaratan

- XAMPP / PHP local server
- MySQL
- Browser modern

## Setup lokal

1. Clone project ke folder server Anda, misalnya di XAMPP:
   `C:/xampp/htdocs/WebRetailMinuman`
2. Buat database MySQL sesuai kebutuhan project.
3. Sesuaikan konfigurasi koneksi database di `config/database.php`.
4. Pastikan base path sesuai environment lokal:
   - default local: `/WebRetailMinuman`
5. Jalankan aplikasi dari browser:
   `http://localhost/WebRetailMinuman/`

## Catatan penting

- Project ini masih bersifat web app yang dipersiapkan untuk kebutuhan demo dan pembelajaran.
- Beberapa file dan dokumentasi tambahan masih dibuat untuk keperluan analisis dan testing.
- Nama project dan URL GitHub sudah disesuaikan ke `WebRetailMinuman`.

## Status project

Project ini sedang dalam tahap pengembangan dan penyesuaian fitur, dengan fokus pada fungsi operasi restoran dan manajemen data internal.
