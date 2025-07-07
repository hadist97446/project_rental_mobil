<?php
// Sertakan file konfigurasi untuk koneksi database dan session
include 'config.php';

// Inisialisasi variabel pesan error
$error_message = '';

// Cek apakah ada request POST (form login disubmit)
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Ambil data email dan password dari form
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Validasi input dasar
    if (empty($email) || empty($password)) {
        $error_message = "Email dan password harus diisi.";
    } else {
        // Siapkan query untuk mencari pengguna berdasarkan email
        // Menggunakan prepared statement untuk mencegah SQL Injection
        $stmt = $conn->prepare("SELECT id, nama_lengkap, email, password_hash, role FROM pengguna WHERE email = ?");
        $stmt->bind_param("s", $email); // 's' menandakan tipe data string
        $stmt->execute(); // Jalankan query
        $result = $stmt->get_result(); // Ambil hasil query

        // Cek apakah ada pengguna ditemukan dengan email tersebut
        if ($result->num_rows == 1) {
            $user = $result->fetch_assoc(); // Ambil data pengguna dalam bentuk associative array
            
            // Verifikasi password yang dimasukkan dengan hash password di database
            if (password_verify($password, $user['password_hash'])) {
                // Login berhasil, simpan informasi pengguna ke session
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['nama_lengkap'];
                $_SESSION['email'] = $user['email'];
                // Jika role tidak terdefinisi di DB, default ke 'user'
                $_SESSION['role'] = $user['role'] ?? 'user'; 
                
                // Redirect pengguna berdasarkan role mereka
                if ($_SESSION['role'] === 'admin') {
                    header("Location: admin/dashboard.php"); // Arahkan ke dashboard admin
                } else {
                    header("Location: index.php"); // Arahkan ke halaman utama/beranda publik
                }
                exit; // Hentikan eksekusi script setelah redirect
            } else {
                // Password salah
                $error_message = "Password salah.";
            }
        } else {
            // Email tidak terdaftar
            $error_message = "Email tidak terdaftar.";
        }
        $stmt->close(); // Tutup prepared statement
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Rental Mobil</title>
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

        /* Styling untuk container login (kartu transparan) */
        .login-container {
            background-color: rgba(255, 255, 255, 0.1); /* Latar belakang transparan */
            padding: 40px; /* Padding di dalam kartu */
            border-radius: 15px; /* Sudut membulat */
            box-shadow: 0 8px 32px 0 rgba(0, 0, 0, 0.37); /* Bayangan yang lebih menonjol */
            width: 350px; /* Lebar kartu */
            text-align: center; /* Pusatkan teks di dalam kartu */
            backdrop-filter: blur(10px); /* Efek blur pada elemen di belakang kartu */
            -webkit-backdrop-filter: blur(10px); /* Dukungan untuk Safari */
            border: 1px solid rgba(255, 255, 255, 0.2); /* Border transparan */
            box-sizing: border-box; /* Pastikan padding dan border termasuk dalam lebar/tinggi */
            position: relative; /* Tambahkan ini agar z-index bekerja relatif terhadap body */
            z-index: 10; /* Pastikan form di atas animasi latar depan lainnya */
        }

        /* Styling untuk judul H2 di dalam kartu login */
        .login-container h2 {
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

        /* Styling untuk input text, email, password */
        .form-group input {
            width: 100%; /* Lebar 100% */
            padding: 12px; /* Padding di dalam input */
            border: none; /* Hilangkan border default */
            border-radius: 8px; /* Sudut membulat */
            background-color: rgba(255, 255, 255, 0.25); /* Latar belakang transparan */
            color: #fff; /* Warna teks input putih */
            box-sizing: border-box; /* Pastikan padding dan border termasuk dalam lebar/tinggi */
            font-size: 1.1em; /* Ukuran font input */
            transition: background-color 0.3s ease, box-shadow 0.3s ease; /* Transisi untuk hover/focus */
        }

        /* Placeholder text color */
        .form-group input::placeholder {
            color: #ccc;
            opacity: 0.8; /* Sedikit transparan */
        }

        /* Efek saat input difokuskan */
        .form-group input:focus {
            background-color: rgba(255, 255, 255, 0.4); /* Lebih solid saat fokus */
            outline: none; /* Hilangkan outline default browser */
            box-shadow: 0 0 0 3px rgba(255, 255, 255, 0.4); /* Bayangan fokus */
        }

        /* Styling untuk tombol Login */
        .login-button {
            background-color: #2196F3; /* Warna biru */
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
        .login-button:hover {
            background-color: #0b7dda; /* Biru lebih gelap saat hover */
            transform: translateY(-2px); /* Efek sedikit naik */
        }

        /* Styling untuk link pendaftaran */
        .register-link {
            color: #ddd; /* Warna teks abu-abu terang */
            display: block; /* Membuat link menjadi blok */
            margin-top: 25px; /* Margin atas */
            text-decoration: none; /* Hilangkan underline */
            text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.5); /* Bayangan teks */
            font-size: 1.05em; /* Ukuran font */
            transition: color 0.3s ease; /* Transisi hover */
        }

        /* Efek saat link di hover */
        .register-link:hover {
            color: #fff; /* Warna teks putih saat hover */
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

        /* Styling untuk pesan error */
        .error-message {
            color: #ffeb3b; /* Warna kuning terang */
            margin-bottom: 20px; /* Margin bawah */
            text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.5); /* Bayangan teks */
            font-weight: 500; /* Tebal font */
            background-color: rgba(220, 53, 69, 0.7); /* Latar belakang merah transparan */
            padding: 10px; /* Padding pesan error */
            border-radius: 8px; /* Sudut membulat */
        }

        /* Responsif untuk layar kecil */
        @media (max-width: 480px) {
            .login-container {
                width: 90%; /* Lebar kartu 90% di layar kecil */
                padding: 30px; /* Padding disesuaikan */
            }
            .login-container h2 {
                font-size: 2em;
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <h2>Login</h2>
        <?php if (!empty($error_message)): ?>
            <p class="error-message"><?php echo htmlspecialchars($error_message); ?></p>
        <?php endif; ?>
        <form action="login.php" method="POST">
            <div class="form-group">
                <label for="email">Email:</label>
                <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" required placeholder="Masukkan email Anda">
            </div>
            <div class="form-group">
                <label for="password">Password:</label>
                <input type="password" id="password" name="password" required placeholder="Masukkan password Anda">
            </div>
            <button type="submit" class="login-button">Login</button>
        </form>
        <a href="register.php" class="register-link">Belum punya akun? Daftar di sini.</a>
    </div>
        <div class="background-animated-element">
        <img src="https://blogger.googleusercontent.com/img/b/R29vZ2xl/AVvXsEjTzJ2deKKkvDi1p6I2Xx3gtjZMic5g5yiVtR0TQLPSQ8GtcldtdMwVVG0OagdceQnfK0Gm2bbrhYCu1h1-IXPU66yWdpmpE_tSbUznuZRbEkmwS51GYtfEtVAYFy_MbI0W_z8Pm6fnf4U/s1600/Naruto-shippuden-vs-Sasuke_5.gif" /></div>
    </div>
    
</body>
</html>