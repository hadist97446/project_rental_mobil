<?php
// Sertakan file konfigurasi untuk koneksi database dan session
include 'config.php';

// Inisialisasi variabel pesan
$message = '';
$message_type = ''; // Untuk menentukan kelas CSS pesan (misal: success, error)

// Cek jika ada request POST (form pendaftaran disubmit)
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Ambil data dari form
    $nama_lengkap = $_POST['nama_lengkap'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $nomor_telepon = $_POST['nomor_telepon'];
    $alamat = $_POST['alamat'];

    // Validasi input dasar
    if (empty($nama_lengkap) || empty($email) || empty($password)) {
        $message = "Nama lengkap, email, dan password harus diisi.";
        $message_type = "error-message"; // Kelas untuk pesan error
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "Format email tidak valid.";
        $message_type = "error-message";
    } else {
        // Cek apakah email sudah terdaftar di database
        $stmt_check = $conn->prepare("SELECT id FROM pengguna WHERE email = ?");
        $stmt_check->bind_param("s", $email);
        $stmt_check->execute();
        $stmt_check->store_result(); // Simpan hasil untuk mendapatkan jumlah baris

        if ($stmt_check->num_rows > 0) {
            $message = "Email ini sudah terdaftar. Silakan gunakan email lain atau login.";
            $message_type = "error-message";
        } else {
            // Hash password sebelum disimpan ke database untuk keamanan
            $password_hash = password_hash($password, PASSWORD_DEFAULT);

            // Siapkan query untuk memasukkan data pengguna baru
            $stmt_insert = $conn->prepare("INSERT INTO pengguna (nama_lengkap, email, password_hash, nomor_telepon, alamat) VALUES (?, ?, ?, ?, ?)");
            // 'sssss' menandakan semua parameter adalah string
            $stmt_insert->bind_param("sssss", $nama_lengkap, $email, $password_hash, $nomor_telepon, $alamat);

            // Eksekusi query
            if ($stmt_insert->execute()) {
                $message = "Pendaftaran berhasil! Anda sekarang bisa <a href='login.php' style='color: #fff; text-decoration: underline;'>login</a>.";
                $message_type = "success-message"; // Kelas untuk pesan sukses
            } else {
                $message = "Error saat pendaftaran: " . $stmt_insert->error;
                $message_type = "error-message";
            }
            $stmt_insert->close(); // Tutup statement
        }
        $stmt_check->close(); // Tutup statement
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Akun Baru - Rental Mobil</title>
    <style>
        /* Mengatur font dasar, menghilangkan margin default, dan membuat body mengisi seluruh viewport */
        body {
            font-family: 'Inter', sans-serif; /* Menggunakan font Inter */
            margin: 0;
            display: flex;
            justify-content: center; /* Pusatkan konten secara horizontal */
            align-items: center; /* Pusatkan konten secara vertikal */
            min-height: 100vh; /* Tinggi minimum 100% dari tinggi viewport */
            /* Latar belakang gradient yang akan dianimasikan */
            background: linear-gradient(45deg, #4CAF50, #2196F3, #9C27B0, #FF9800);
            background-size: 400% 400%; /* Ukuran background lebih besar dari viewport untuk animasi */
            animation: changeBackground 10s infinite ease-in-out; /* Menerapkan animasi latar belakang */
            color: #fff; /* Warna teks default putih */
            overflow: hidden; /* Mencegah scrollbar jika ada elemen di luar viewport */
            border-radius: 8px; /* Sudut membulat pada body (jika container body adalah card) */
        }

        /* Animasi untuk perubahan warna latar belakang */
        @keyframes changeBackground {
            0% {background-position: 0 50%;} /* Mulai dari kiri tengah */
            50% {background-position: 100% 50%;} /* Bergerak ke kanan tengah */
            100% {background-position: 0 50%;} /* Kembali ke kiri tengah */
        }

        /* Styling untuk container pendaftaran (kartu transparan) */
        .register-container { /* Diubah dari .login-container menjadi .register-container */
            background-color: rgba(255, 255, 255, 0.1); /* Latar belakang transparan */
            padding: 40px; /* Padding di dalam kartu */
            border-radius: 15px; /* Sudut membulat */
            box-shadow: 0 8px 32px 0 rgba(0, 0, 0, 0.37); /* Bayangan yang lebih menonjol */
            width: 400px; /* Lebar kartu disesuaikan untuk form yang lebih panjang */
            text-align: center; /* Pusatkan teks di dalam kartu */
            backdrop-filter: blur(10px); /* Efek blur pada elemen di belakang kartu */
            -webkit-backdrop-filter: blur(10px); /* Dukungan untuk Safari */
            border: 1px solid rgba(255, 255, 255, 0.2); /* Border transparan */
            box-sizing: border-box; /* Pastikan padding dan border termasuk dalam lebar/tinggi */
            position: relative; /* Tambahkan ini agar z-index bekerja relatif terhadap body */
            z-index: 10; /* Pastikan form di atas animasi latar depan lainnya */
        }

        /* Styling untuk judul H2 di dalam kartu pendaftaran */
        .register-container h2 {
            color: #fff; /* Warna teks putih */
            margin-bottom: 30px; /* Margin bawah */
            text-shadow: 2px 2px 5px rgba(0, 0, 0, 0.6); /* Bayangan teks untuk keterbacaan */
            font-size: 2.2em; /* Ukuran font lebih besar */
        }

        /* Styling untuk setiap grup form (label + input) */
        .form-group {
            margin-bottom: 25px; /* Margin bawah lebih besar antar grup */
            text-align: left; /* Rata kiri untuk label dan input */
        }

        /* Styling untuk label form */
        .form-group label {
            display: block; /* Membuat label menjadi blok agar input di baris baru */
            color: #eee; /* Warna teks label */
            margin-bottom: 8px; /* Margin bawah label */
            font-weight: 600; /* Tebal font */
            text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.5); /* Bayangan teks */
        }

        /* Styling untuk input text, email, password, textarea */
        .form-group input[type="text"],
        .form-group input[type="password"],
        .form-group input[type="email"],
        .form-group textarea { /* Tambah textarea */
            width: 100%; /* Lebar 100% */
            padding: 12px; /* Padding di dalam input */
            border: none; /* Hilangkan border default */
            border-radius: 8px; /* Sudut membulat */
            background-color: rgba(255, 255, 255, 0.25); /* Latar belakang transparan */
            color: #fff; /* Warna teks input putih */
            box-sizing: border-box; /* Pastikan padding dan border termasuk dalam lebar/tinggi */
            font-size: 1.1em; /* Ukuran font input */
            transition: background-color 0.3s ease, box-shadow 0.3s ease; /* Transisi untuk hover/focus */
            resize: vertical; /* Izinkan textarea diresize vertikal */
            min-height: 40px; /* Tinggi minimum textarea */
        }

        /* Placeholder text color */
        .form-group input::placeholder,
        .form-group textarea::placeholder { /* Tambah textarea */
            color: #ccc;
            opacity: 0.8; /* Sedikit transparan */
        }

        /* Efek saat input/textarea difokuskan */
        .form-group input:focus,
        .form-group textarea:focus { /* Tambah textarea */
            background-color: rgba(255, 255, 255, 0.4); /* Lebih solid saat fokus */
            outline: none; /* Hilangkan outline default browser */
            box-shadow: 0 0 0 3px rgba(255, 255, 255, 0.4); /* Bayangan fokus */
        }

        /* Styling untuk tombol Daftar */
        .register-button { /* Diubah dari .login-button menjadi .register-button */
            background-color: #28a745; /* Warna hijau */
            color: white; /* Warna teks putih */
            border: none; /* Hilangkan border */
            padding: 15px 25px; /* Padding tombol */
            border-radius: 8px; /* Sudut membulat */
            cursor: pointer; /* Kursor berubah saat di hover */
            width: 100%; /* Lebar 100% */
            font-size: 1.2em; /* Ukuran font */
            font-weight: 700; /* Tebal font */
            transition: background-color 0.3s ease, transform 0.2s ease; /* Transisi hover/active */
            margin-top: 15px; /* Margin atas */
        }

        /* Efek saat tombol di hover */
        .register-button:hover {
            background-color: #218838; /* Hijau lebih gelap saat hover */
            transform: translateY(-2px); /* Efek sedikit naik */
        }

        /* Styling untuk link login */
        .login-link { /* Diubah dari .register-link menjadi .login-link */
            color: #ddd; /* Warna teks abu-abu terang */
            display: block; /* Membuat link menjadi blok */
            margin-top: 25px; /* Margin atas */
            text-decoration: none; /* Hilangkan underline */
            text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.5); /* Bayangan teks */
            font-size: 1.05em; /* Ukuran font */
            transition: color 0.3s ease; /* Transisi hover */
        }

        /* Efek saat link di hover */
        .login-link:hover {
            color: #fff; /* Warna teks putih saat hover */
        }

        /* Styling untuk pesan sukses/error */
        .success-message { /* Kelas untuk pesan sukses */
            color: #d4edda; /* Warna hijau muda */
            background-color: rgba(40, 167, 69, 0.7); /* Latar belakang hijau transparan */
            border-color: #c3e6cb;
            padding: 10px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-weight: 500;
            text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.5);
        }

        .error-message { /* Kelas untuk pesan error */
            color: #ffeb3b; /* Warna kuning terang */
            margin-bottom: 20px; /* Margin bawah */
            text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.5); /* Bayangan teks */
            font-weight: 500; /* Tebal font */
            background-color: rgba(220, 53, 69, 0.7); /* Latar belakang merah transparan */
            padding: 10px; /* Padding pesan error */
            border-radius: 8px; /* Sudut membulat */
        }

        /* STYLE untuk elemen animasi latar belakang agar memenuhi layar */
        .background-animated-element {
            position: absolute; /* Mengisi seluruh parent (body) */
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 1; /* Pastikan berada di bawah form pendaftaran */
            overflow: hidden; /* Mencegah scrollbar jika gambar lebih besar */
        }
        .background-animated-element img {
            width: 100%;
            height: 100%;
            object-fit: cover; /* Penting! Memastikan gambar mengisi penuh area tanpa terdistorsi, mungkin terpotong */
            object-position: center; /* Pusatkan gambar */
            display: block; /* Menghilangkan spasi ekstra di bawah gambar */
        }

        /* Responsif untuk layar kecil */
        @media (max-width: 480px) {
            .register-container {
                width: 90%; /* Lebar kartu 90% di layar kecil */
                padding: 30px; /* Padding disesuaikan */
            }
            .register-container h2 {
                font-size: 2em;
            }
        }
    </style>
</head>
<body>
    <div class="register-container">
        <h2>Daftar Akun Baru</h2>
        <?php if (!empty($message)): ?>
            <p class="<?php echo htmlspecialchars($message_type); ?>"><?php echo $message; ?></p>
        <?php endif; ?>
        <form action="register.php" method="POST">
            <div class="form-group">
                <label for="nama_lengkap">Nama Lengkap:</label>
                <input type="text" id="nama_lengkap" name="nama_lengkap" required placeholder="Masukkan nama lengkap Anda">
            </div>
            <div class="form-group">
                <label for="email">Email:</label>
                <input type="email" id="email" name="email" required placeholder="Masukkan email Anda">
            </div>
            <div class="form-group">
                <label for="password">Password:</label>
                <input type="password" id="password" name="password" required placeholder="Buat password Anda">
            </div>
            <div class="form-group">
                <label for="nomor_telepon">Nomor Telepon:</label>
                <input type="text" id="nomor_telepon" name="nomor_telepon" placeholder="Masukkan nomor telepon (opsional)">
            </div>
            <div class="form-group">
                <label for="alamat">Alamat:</label>
                <textarea id="alamat" name="alamat" placeholder="Masukkan alamat Anda (opsional)"></textarea>
            </div>
            <button type="submit" class="register-button">Daftar</button>
        </form>
        <a href="login.php" class="login-link">Sudah punya akun? Login di sini.</a>
    </div>

    <div class="background-animated-element">
        <img src="https://blogger.googleusercontent.com/img/b/R29vZ2xl/AVvXsEjTzJ2deKKkvDi1p6I2Xx3gtjZMic5g5yiVtR0TQLPSQ8GtcldtdMwVVG0OagdceQnfK0Gm2bbrhYCu1h1-IXPU66yWdpmpE_tSbUznuZRbEkmwS51GYtfEtVAYFy_MbI0W_z8Pm6fnf4U/s1600/Naruto-shippuden-vs-Sasuke_5.gif" /></div>
    </div>
</body>
</html>
