# Apotek Gading

## Identitas
- Nama : Fadhil Afiq Badruzzaman
- NIM  : 20240801119
- Matkul : Pemrograman Web
- Dosen Pengampu : JEFRY SUNUPURWA ASRI, S.Kom., M.Kom.

## Deskripsi Proyek
Sistem Informasi Manajemen Apotek Gading adalah aplikasi berbasis web untuk mendukung operasional apotek. Proyek ini dibuat menggunakan framework Laravel dengan panel admin Filament, Livewire, dan layanan basis data MariaDB di dalam lingkungan Docker.

Aplikasi ini dirancang untuk digitalisasi proses bisnis apotek yang meliputi:
- manajemen data obat, kategori, dan supplier
- pencatatan obat masuk beserta batch dan tanggal kedaluwarsa
- transaksi Point of Sale (POS) dengan dukungan barcode
- monitoring stok dan kedaluwarsa obat
- dashboard operasional dan laporan penjualan
- manajemen pengguna dan hak akses (RBAC)

## Fitur Utama
- Autentikasi pengguna dan role-based access control
- Dashboard operasional real-time
- CRUD data obat, kategori, supplier
- Pencatatan obat masuk batch-wise
- Modul POS untuk transaksi penjualan
- Pencarian obat dan scan barcode
- Monitoring stok minimum dan obat mendekati kedaluwarsa
- Laporan penjualan dan operasional (PDF/Excel)
- Pengaturan identitas apotek untuk struk dan tampilan sistem

## Teknologi yang Digunakan
- Laravel
- Filament
- Livewire
- Tailwind CSS
- MariaDB
- Docker
- PHP
- JavaScript
- Composer
- Node.js / NPM

## Struktur Proyek
- `src/` : kode aplikasi Laravel
- `docker-compose.yml` : konfigurasi container Docker
- `php/` : konfigurasi PHP dan entrypoint Docker
- `nginx/` : konfigurasi web server
- `Doc/` : dokumentasi proyek

## Dokumen Referensi
Dokumen berikut menjadi referensi utama untuk pengembangan dan penulisan laporan proyek ini:
- `Doc/BRD apotekgading.docx`
- `Doc/PRD ApotekGading.docx`
- `Doc/Laporan proyek akhir final.docx`

## Ringkasan Dokumen
- BRD menjelaskan kebutuhan bisnis dan latar belakang masalah operasional apotek yang masih manual, serta solusi digital dengan fitur pengelolaan stok, transaksi, dan pelaporan.
- PRD menguraikan kebutuhan produk secara rinci, termasuk visi, scope, user persona, user story, dan requirement untuk modul autentikasi, data obat, POS, monitoring, dan laporan.
- Laporan akhir menjabarkan metodologi penelitian, implementasi sistem, pengujian Black Box dan UAT, serta kesimpulan dan saran pengembangan.

## Cara Menjalankan (Ringkas)
1. Jalankan container Docker:
   ```sh
   docker-compose up -d
   ```
2. Masuk ke container aplikasi atau jalankan composer install:
   ```sh
   docker exec -it <php-container> bash
   composer install
   npm install
   npm run build
   php artisan migrate --seed
   ```
3. Buka aplikasi di browser sesuai konfigurasi Nginx atau container.

## Catatan
- Pastikan lingkungan Docker sudah aktif sebelum menjalankan aplikasi.
- Gunakan dokumen di folder `Doc/` sebagai referensi lengkap untuk kebutuhan fungsional dan non-fungsional.
- Proyek ini berfokus pada pengelolaan apotek, transaksi kasir, stok obat, serta pembuatan laporan operasional.
