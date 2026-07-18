# Apotek Gading

Apotek Gading adalah Sistem Informasi Manajemen Apotek berbasis Laravel 12 dengan Filament, Livewire, Docker, MariaDB, dan Midtrans. Aplikasi ini dirancang untuk mendukung operasional apotek secara digital mulai dari manajemen produk, transaksi POS, hingga laporan dan monitoring stok.

## Developer

- Nama: Fadhil Afiq Badruzzaman
- NIM: 20240801119
- Mata Kuliah: Pemrograman Web
- Dosen: JEFRY SUNUPURWA ASRI, S.Kom., M.Kom.

## Table of Contents

- [Live Demo](#live-demo)
- [Developer](#developer)
- [Deskripsi Proyek](#deskripsi-proyek)
- [Fitur](#fitur)
  - [Dashboard](#dashboard)
  - [Master Data](#master-data)
  - [Transaksi](#transaksi)
  - [POS](#pos)
  - [Monitoring](#monitoring)
  - [Laporan](#laporan)
  - [Pengaturan](#pengaturan)
  - [Autentikasi](#autentikasi)
- [Teknologi](#teknologi)
- [Struktur Folder](#struktur-folder)
- [Dokumentasi](#dokumentasi)
- [Instalasi Lokal](#instalasi-lokal)
- [Deployment ke Azure VPS](#deployment-ke-azure-vps)
- [Konfigurasi Domain IDWebHost](#konfigurasi-domain-idwebhost)
- [SSL Let's Encrypt](#ssl-lets-encrypt)
- [Reverse Proxy](#reverse-proxy)
- [Screenshot](#screenshot)
- [Catatan](#catatan)
- [Lisensi](#lisensi)

---

## Live Demo

- Aplikasi Apotek: https://menamu.my.id/
- Portfolio Developer: https://fadhil.menamu.my.id/
- GitHub ApotekGading: https://github.com/LuSiNa03/apotekgading.git
- GitHub Portfolio: https://github.com/LuSiNa03/uts.git

---

## Deskripsi Proyek

Apotek Gading mendukung proses bisnis apotek modern melalui fitur lengkap untuk:

- Manajemen Obat
- Supplier
- Kategori
- Penyakit
- Monitoring Stok
- Monitoring Expired
- Obat Masuk
- Point of Sale (POS)
- Barcode Scanner
- Midtrans Payment
- Dashboard
- Laporan
- Role Based Access Control

Aplikasi ini membantu operasional apotek dalam pengelolaan inventaris, pencatatan transaksi, pelaporan penjualan, dan pemantauan masa kedaluwarsa obat.

---

## Fitur

### Dashboard

- Ringkasan penjualan hari ini
- Notifikasi stok rendah
- Statistik obat kedaluwarsa
- Grafik transaksi dan pendapatan

### Master Data

- Manajemen obat
- Kategori obat
- Supplier
- Penyakit
- Kategori batch dan stok

### Transaksi

- Pencatatan obat masuk (batch-wise)
- Stok masuk dan pengelolaan batch
- Validasi tanggal kedaluwarsa

### POS

- Transaksi Point of Sale lengkap
- Dukungan barcode scanner
- Checkout cepat dengan Midtrans Snap
- Cetak struk dan riwayat penjualan

### Monitoring

- Pemantauan stok minimum
- Deteksi obat kadaluarsa
- Filter obat mendekati expired

### Laporan

- Laporan penjualan harian dan bulanan
- Eksport PDF/Excel
- Ringkasan operasional apotek

### Pengaturan

- Konfigurasi identitas apotek
- Pengaturan tampilan struk
- Pengelolaan opsi pembayaran

### Autentikasi

- Login pengguna
- Role-Based Access Control (RBAC)
- Hak akses admin dan staff

---

## Teknologi

- Laravel 12
- PHP 8.2
- Filament 4
- Livewire 3
- Tailwind CSS
- MariaDB
- Docker
- Docker Compose
- Nginx
- Midtrans Snap
- Composer
- Node.js
- Vite

---

## Struktur Folder

- `Doc/` : dokumentasi proyek, desain, dan referensi
- `db/` : data basis data MariaDB dan konfigurasi
- `nginx/` : konfigurasi web server Nginx dan SSL
- `php/` : konfigurasi PHP container dan entrypoint Docker
- `src/` : kode aplikasi Laravel
- `docker-compose.yml` : definisi layanan Docker Compose

---

## Dokumentasi

Folder `Doc/` berisi dokumentasi pendukung termasuk:

- BRD
- PRD
- Laporan Akhir
- Dokumentasi lainnya

---

## Instalasi Lokal

Ikuti langkah berikut untuk menjalankan aplikasi secara lokal.

```bash
git clone https://github.com/LuSiNa03/apotekgading.git
cd apotekgading

docker compose up -d

docker exec -it <php-container> bash
composer install
npm install
npm run build
php artisan key:generate
php artisan migrate --seed
php artisan storage:link
php artisan optimize
```

---

## Deployment ke Azure VPS

### Persiapan Azure

- Buat Virtual Machine Ubuntu Server
- Buka port `22`, `80`, dan `443`

### Install Docker

```bash
sudo apt update
sudo apt install docker.io docker-compose-v2 git -y
sudo systemctl enable docker
sudo systemctl start docker
```

### Clone Project

```bash
git clone https://github.com/LuSiNa03/apotekgading.git
cd apotekgading
```

### Jalankan Docker

```bash
docker compose up -d --build
```

### Install Laravel

```bash
composer install
php artisan migrate --seed
php artisan storage:link
php artisan optimize
```

---

## Konfigurasi Domain IDWebHost

Masuk ke: https://member.idwebhost.com/

Pilih:

- Domain → Kelola DNS

Tambahkan DNS berikut:

- Type: `A`
  - Host: `@`
  - Value: `IP Azure VPS`
- Type: `A`
  - Host: `www`
  - Value: `IP Azure VPS`
- Type: `A`
  - Host: `fadhil`
  - Value: `IP Azure VPS`

> Propagasi DNS biasanya membutuhkan waktu beberapa menit hingga beberapa jam.

---

## SSL Let's Encrypt

```bash
sudo apt install certbot
sudo certbot certonly --standalone \
  -d menamu.my.id \
  -d www.menamu.my.id \
  -d fadhil.menamu.my.id
```

Setelah sertifikat terpasang, restart Docker atau layanan Nginx yang digunakan.

---

## Reverse Proxy

- `https://menamu.my.id` mengarah ke aplikasi Apotek Gading
- `https://fadhil.menamu.my.id` mengarah ke portfolio Laravel

---

## Screenshot

> Tampilan aplikasi Apotek Gading akan ditampilkan di sini.

Silakan ganti placeholder dengan file screenshot nyata dari aplikasi. Contoh:

```markdown
![Tampilan Apotek Gading](./assets/apotekgading-screenshot.png)
```

---

## Catatan

- Pastikan Docker berjalan.
- Pastikan file `.env` telah dikonfigurasi.
- Pastikan SSL aktif.
- Pastikan Midtrans Client Key dan Server Key telah dikonfigurasi.

---

## Lisensi

Proyek ini dibuat untuk memenuhi tugas mata kuliah Pemrograman Web Universitas Esa Unggul. Untuk detail lisensi dan hak penggunaan, lihat file `LICENSE`.

© 2026 Fadhil Afiq Badruzzaman
