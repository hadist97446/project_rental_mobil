# Sistem Informasi Manajemen Perpustakaan

Sebuah aplikasi berbasis web untuk mengelola inventaris buku, anggota, dan transaksi peminjaman di perpustakaan. Dikembangkan menggunakan framework CodeIgniter 4.

## Daftar Isi
- [Tentang Proyek](#tentang-proyek)
- [Fitur Utama](#fitur-utama)
- [Teknologi Digunakan](#teknologi-digunakan)
- [Struktur Database](#struktur-database)
- [Diagram Sistem](#diagram-sistem)
  - [Use Case Diagram](#use-case-diagram)
  - [Sequence Diagram (Contoh)](#sequence-diagram-contoh)
  - [ERD (Entity-Relationship Diagram)](#erd-entity-relationship-diagram)
  - [LRS (Logical Record Structure)](#lrs-logical-record-structure)
- [Panduan Instalasi Lokal](#panduan-instalasi-lokal)
- [Panduan Penggunaan](#panduan-penggunaan)
- [Kontributor](#kontributor)
- [Lisensi](#lisensi)

## Tentang Proyek

Proyek Sistem Informasi Manajemen Perpustakaan ini dikembangkan untuk mempermudah proses pengelolaan operasional perpustakaan, mulai dari pencatatan data master (buku, anggota, admin, kategori, rak), hingga manajemen transaksi peminjaman dan pengembalian buku. Sistem ini dirancang untuk meningkatkan efisiensi, akurasi data, dan memberikan pengalaman yang lebih baik bagi staff perpustakaan dan anggota.

## Fitur Utama

### Untuk Administrator (Staff Perpustakaan):
- **Login & Logout:** Akses aman ke panel administrasi.
- **Manajemen Data Admin:** Menambah, mengedit, menghapus, dan melihat data staff admin.
- **Manajemen Data Anggota:** Menambah, mengedit, menghapus, dan melihat data anggota perpustakaan.
- **Manajemen Data Kategori Buku:** Mengelola kategori buku.
- **Manajemen Data Rak Buku:** Mengelola lokasi rak buku.
- **Manajemen Data Buku:** Menambah, mengedit, menghapus buku, termasuk upload cover buku dan e-book (PDF).
- **Transaksi Peminjaman:** Memproses peminjaman buku dengan keranjang sementara dan generate QR Code untuk transaksi.
- **Melihat Data Transaksi Peminjaman:** Melihat riwayat semua transaksi peminjaman.
- **Dashboard Admin:** Ringkasan statistik perpustakaan.

### Untuk Pengguna (Anggota Perpustakaan):
- **Registrasi & Login:** Membuat dan masuk ke akun anggota.
- **Melihat Daftar Buku:** Menjelajahi koleksi buku yang tersedia.
- **Melihat Detail Buku:** Mengakses informasi lengkap tentang buku (deskripsi, ketersediaan, cover, e-book).
- **Melihat Riwayat Peminjaman:** Melacak status peminjaman buku mereka sendiri.

## Teknologi Digunakan

* **Framework:** CodeIgniter 4
* **Bahasa Pemrograman:** PHP
* **Database:** MySQL / MariaDB
* **Front-end:** HTML, CSS (Bootstrap), JavaScript (jQuery, SweetAlert2, Bootstrap Table)
* **Library Tambahan:** Endroid/QrCode (untuk generate QR Code)
* **Web Server:** Apache (via XAMPP/WAMP/Laragon) atau PHP built-in server (`php spark serve`)

## Struktur Database

Berikut adalah skema database (`perpus_db`) yang digunakan dalam proyek ini.

```sql
-- TABEL tbl_admin
CREATE TABLE `tbl_admin` (
  `id_admin` varchar(6) NOT NULL,
  `nama_admin` varchar(50) NOT NULL,
  `username_admin` varchar(20) NOT NULL,
  `password_admin` varchar(255) NOT NULL,
  `akses_level` enum('1','2','3') NOT NULL,
  `is_delete_admin` enum('0','1') NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id_admin`)
);

-- TABEL tbl_anggota
CREATE TABLE `tbl_anggota` (
  `id_anggota` INT(11) NOT NULL AUTO_INCREMENT, -- ID Anggota (Integer, Auto-Increment)
  `nama_anggota` varchar(50) NOT NULL,
  `jenis_kelamin` enum('L','P') NOT NULL,
  `no_tlp` varchar(13) NOT NULL,
  `alamat` varchar(100) NOT NULL,
  `email` varchar(50) NOT NULL,
  `password_anggota` varchar(255) NOT NULL,
  `is_delete_anggota` enum('0','1') NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id_anggota`),
  UNIQUE KEY `email` (`email`)
);

-- TABEL tbl_buku
CREATE TABLE `tbl_buku` (
  `id_buku` varchar(6) NOT NULL, -- ID Buku (Contoh: BKU001)
  `judul_buku` varchar(200) NOT NULL,
  `pengarang` varchar(50) NOT NULL,
  `penerbit` varchar(50) NOT NULL,
  `tahun` varchar(6) NOT NULL,
  `jumlah_eksemplar` int(3) NOT NULL,
  `id_kategori` int(6) NOT NULL,
  `keterangan` varchar(500) NOT NULL,
  `id_rak` int(6) NOT NULL,
  `cover_buku` varchar(255) NOT NULL,
  `e_book` varchar(255) NOT NULL,
  `is_delete_buku` enum('0','1') NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id_buku`)
);

-- TABEL tbl_kategori
CREATE TABLE `tbl_kategori` (
  `id_kategori` int(6) NOT NULL AUTO_INCREMENT,
  `nama_kategori` varchar(50) NOT NULL,
  `is_delete_kategori` enum('0','1') NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id_kategori`)
);

-- TABEL tbl_rak
CREATE TABLE `tbl_rak` (
  `id_rak` int(6) NOT NULL AUTO_INCREMENT,
  `nama_rak` varchar(50) NOT NULL,
  `is_delete_rak` enum('0','1') NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id_rak`)
);

-- TABEL tbl_peminjaman1 (Transaksi Peminjaman Utama)
CREATE TABLE `tbl_peminjaman1` (
  `no_peminjaman` VARCHAR(15) NOT NULL, -- PK: Contoh YMDHis
  `id_anggota` INT(12) NOT NULL,        -- FK ke tbl_anggota.id_anggota
  `tgl_pinjam` DATE NOT NULL,
  `total_pinjam` INT(3) NOT NULL,
  `id_admin` VARCHAR(6) NOT NULL,       -- FK ke tbl_admin.id_admin
  `status_transaksi` ENUM('Selesai', 'Berjalan') NOT NULL,
  `status_ambil_buku` ENUM('Belum Diambil', 'Sudah Diambil') NOT NULL DEFAULT 'Belum Diambil',
  `qr_code` VARCHAR(255) NOT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`no_peminjaman`)
);

-- TABEL tbl_peminjaman (Transaksi Peminjaman Detail)
CREATE TABLE `tbl_peminjaman` (
  `id` INT AUTO_INCREMENT PRIMARY KEY, -- PK unik untuk setiap baris detail
  `no_peminjaman` VARCHAR(15) NOT NULL, -- FK ke tbl_peminjaman1.no_peminjaman
  `id_buku` VARCHAR(6) NOT NULL,        -- FK ke tbl_buku.id_buku
  `status_pinjam` ENUM('Sedang Dipinjam', 'Sudah Dikembalikan') NOT NULL DEFAULT 'Sedang Dipinjam',
  `perpanjangan` INT(6) NOT NULL DEFAULT 0,
  `tgl_kembali` DATE NOT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- TABEL tbl_temp_peminjaman (Keranjang Peminjaman Sementara)
CREATE TABLE `tbl_temp_peminjaman` (
  `id` INT AUTO_INCREMENT PRIMARY KEY, -- PK unik untuk setiap item sementara
  `id_anggota` INT(12) NOT NULL,       -- FK ke tbl_anggota.id_anggota
  `id_buku` VARCHAR(6) NOT NULL,       -- FK ke tbl_buku.id_buku
  `jumlah_temp` INT(3) NOT NULL DEFAULT 1,
  UNIQUE (`id_anggota`, `id_buku`)     -- Memastikan satu buku unik per anggota di keranjang
);

-- FOREIGN KEY CONSTRAINTS
ALTER TABLE `tbl_peminjaman1`
  ADD CONSTRAINT `fk_peminjaman1_anggota` FOREIGN KEY (`id_anggota`) REFERENCES `tbl_anggota` (`id_anggota`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_peminjaman1_admin` FOREIGN KEY (`id_admin`) REFERENCES `tbl_admin` (`id_admin`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `tbl_peminjaman`
  ADD CONSTRAINT `fk_peminjaman_utama` FOREIGN KEY (`no_peminjaman`) REFERENCES `tbl_peminjaman1` (`no_peminjaman`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_peminjaman_buku` FOREIGN KEY (`id_buku`) REFERENCES `tbl_buku` (`id_buku`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `tbl_temp_peminjaman`
  ADD CONSTRAINT `fk_temp_anggota` FOREIGN KEY (`id_anggota`) REFERENCES `tbl_anggota` (`id_anggota`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_temp_buku` FOREIGN KEY (`id_buku`) REFERENCES `tbl_buku` (`id_buku`) ON DELETE CASCADE ON UPDATE CASCADE;
