<?php
// Konfigurasi Database
define('DB_SERVER', 'localhost'); // Biasanya localhost
define('DB_USERNAME', 'root');   // Username database Anda (default XAMPP/WAMP: root)
define('DB_PASSWORD', '');       // Password database Anda (default XAMPP/WAMP: kosong)
define('DB_NAME', 'rental_mobil_db'); // Nama database yang sudah Anda buat

// Membuat koneksi ke database MySQL
$conn = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);

// Mengecek koneksi
if ($conn->connect_error) {
    die("Koneksi database gagal: " . $conn->connect_error);
}

// Memulai session PHP
session_start();
?>