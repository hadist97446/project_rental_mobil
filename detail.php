<?php
include 'config.php';

// Cek apakah pengguna sudah login
if (!isset($_SESSION['user_id'])) {
    // Jika belum login, arahkan ke halaman login
    header("Location: login.php");
    exit;
}

$car_id = $_GET['id'] ?? 0; // Ambil ID mobil dari URL
$car_details = null; // Variabel untuk menyimpan detail mobil
$message = ''; // Variabel untuk pesan sukses/error
$message_type = ''; // Tipe pesan (alert/success)

// Ambil detail mobil dari database berdasarkan ID
if ($car_id > 0) {
    $stmt = $conn->prepare("SELECT * FROM mobil WHERE id = ?");
    $stmt->bind_param("i", $car_id); // 'i' untuk integer
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $car_details = $result->fetch_assoc();
    } else {
        $message = "Mobil tidak ditemukan.";
        $message_type = "alert";
    }
    $stmt->close();
} else {
    $message = "ID Mobil tidak valid.";
    $message_type = "alert";
}

// Proses pemesanan ketika form disubmit
if ($_SERVER["REQUEST_METHOD"] == "POST" && $car_details) {
    $tanggal_mulai = $_POST['tanggal_mulai'];
    $tanggal_selesai = $_POST['tanggal_selesai'];
    $id_pengguna = $_SESSION['user_id']; // Ambil ID pengguna dari session
    $id_mobil = $car_details['id'];

    // Validasi tanggal
    if (empty($tanggal_mulai) || empty($tanggal_selesai)) {
        $message = "Tanggal mulai dan tanggal selesai harus diisi.";
        $message_type = "alert";
    } elseif (strtotime($tanggal_mulai) < strtotime(date('Y-m-d'))) {
        $message = "Tanggal mulai tidak boleh di masa lalu.";
        $message_type = "alert";
    } elseif (strtotime($tanggal_selesai) < strtotime($tanggal_mulai)) {
        $message = "Tanggal selesai tidak boleh sebelum tanggal mulai.";
        $message_type = "alert";
    } else {
        // Hitung durasi sewa
        $start_date = new DateTime($tanggal_mulai);
        $end_date = new DateTime($tanggal_selesai);
        $interval = $start_date->diff($end_date);
        $durasi_hari = $interval->days + 1; // Termasuk hari pertama

        // Hitung total harga
        $total_harga = $durasi_hari * $car_details['harga_per_hari'];

        // Cek ketersediaan mobil pada rentang tanggal tersebut
        // Memastikan mobil tidak dipesan pada tanggal yang tumpang tindih
        $stmt_check = $conn->prepare("SELECT COUNT(*) FROM pemesanan WHERE id_mobil = ? AND
                                     ((tanggal_mulai <= ? AND tanggal_selesai >= ?) OR
                                      (tanggal_mulai <= ? AND tanggal_selesai >= ?)) AND
                                     status_pemesanan IN ('Pending', 'Dikonfirmasi')");
        // Parameter: id_mobil (i), tanggal_selesai (s), tanggal_mulai (s), tanggal_mulai (s), tanggal_mulai (s)
        $stmt_check->bind_param("issss", $id_mobil, $tanggal_selesai, $tanggal_mulai, $tanggal_mulai, $tanggal_mulai);
        $stmt_check->execute();
        $stmt_check->bind_result($count_konflik);
        $stmt_check->fetch();
        $stmt_check->close();

        if ($count_konflik > 0) {
            $message = "Mobil tidak tersedia pada rentang tanggal yang dipilih. Silakan pilih tanggal lain.";
            $message_type = "alert";
        } else {
            // Masukkan data pemesanan ke database
            $stmt_insert = $conn->prepare("INSERT INTO pemesanan (id_pengguna, id_mobil, tanggal_mulai, tanggal_selesai, total_harga, status_pemesanan) VALUES (?, ?, ?, ?, ?, 'Pending')");
            // Parameter: id_pengguna (i), id_mobil (i), tanggal_mulai (s), tanggal_selesai (s), total_harga (d), status_pemesanan (s)
            $stmt_insert->bind_param("iisds", $id_pengguna, $id_mobil, $tanggal_mulai, $tanggal_selesai, $total_harga);

            if ($stmt_insert->execute()) {
                $message = "Pemesanan berhasil dibuat! Menunggu konfirmasi admin. Total Harga: Rp " . number_format($total_harga, 0, ',', '.');
                $message_type = "success";
            } else {
                $message = "Error saat membuat pemesanan: " . $stmt_insert->error;
                $message_type = "alert";
            }
            $stmt_insert->close();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Mobil & Pemesanan</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <header>
        <div class="container">
            <div id="branding">
                <h1>Rental Mobil</h1>
            </div>
            <nav>
                <ul>
                    <li><a href="index.php">Beranda</a></li>
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <li><a href="my_bookings.php">Pemesanan Saya</a></li>
                        <li><a href="logout.php">Logout</a></li>
                        <li>Halo, <?php echo htmlspecialchars($_SESSION['username']); ?></li>
                    <?php else: ?>
                        <li><a href="login.php">Login</a></li>
                        <li><a href="register.php">Daftar</a></li>
                    <?php endif; ?>
                    <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                        <li><a href="admin/dashboard.php">Admin Panel</a></li>
                    <?php endif; ?>
                </ul>
            </nav>
        </div>
    </header>

    <div class="container">
        <?php if (!empty($message)): ?>
            <div class="<?php echo $message_type; ?>"><?php echo $message; ?></div>
        <?php endif; ?>

        <?php if ($car_details): ?>
            <div class="card">
                <h2><?php echo htmlspecialchars($car_details['merek'] . " " . $car_details['model']); ?></h2>
                <img src="<?php echo htmlspecialchars($car_details['gambar_url'] ?: 'https://via.placeholder.com/500x300?text=Gambar+Mobil'); ?>" alt="<?php echo htmlspecialchars($car_details['merek'] . " " . $car_details['model']); ?>" style="max-width: 100%; height: auto;">
                <p><strong>Tahun:</strong> <?php echo htmlspecialchars($car_details['tahun']); ?></p>
                <p><strong>Transmisi:</strong> <?php echo htmlspecialchars($car_details['transmisi']); ?></p>
                <p><strong>Jumlah Kursi:</strong> <?php echo htmlspecialchars($car_details['jumlah_kursi']); ?></p>
                <p><strong>Harga per Hari:</strong> Rp <?php echo number_format($car_details['harga_per_hari'], 0, ',', '.'); ?></p>
                <p><strong>Deskripsi:</strong> <?php echo nl2br(htmlspecialchars($car_details['deskripsi'])); ?></p>
                <p><strong>Status Ketersediaan:</strong> <?php echo htmlspecialchars($car_details['status_ketersediaan']); ?></p>

                <?php if ($car_details['status_ketersediaan'] === 'Tersedia'): ?>
                    <h3>Form Pemesanan</h3>
                    <?php if (isset($_SESSION['username'])): // AWAL TAMBAHAN KODE ?>
                        <div class="form-group">
                            <label for="nama_pemesan">Nama Pemesan:</label>
                            <input type="text" id="nama_pemesan" name="nama_pemesan"
                                   value="<?php echo htmlspecialchars($_SESSION['username']); ?>" readonly>
                        </div>
                    <?php endif; // AKHIR TAMBAHAN KODE ?>
                    <form action="detail.php?id=<?php echo htmlspecialchars($car_id); ?>" method="POST">
                        <div class="form-group">
                            <label for="tanggal_mulai">Tanggal Mulai Sewa:</label>
                            <input type="date" id="tanggal_mulai" name="tanggal_mulai" required min="<?php echo date('Y-m-d'); ?>">
                        </div>
                        <div class="form-group">
                            <label for="tanggal_selesai">Tanggal Selesai Sewa:</label>
                            <input type="date" id="tanggal_selesai" name="tanggal_selesai" required min="<?php echo date('Y-m-d'); ?>">
                        </div>
                        <button type="submit" class="btn btn-primary">Pesan Sekarang</button>
                    </form>
                <?php else: ?>
                    <p style="color: red;">Mobil ini tidak tersedia untuk disewa saat ini.</p>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <p>Mobil tidak ditemukan atau ID tidak valid.</p>
        <?php endif; ?>
    </div>

    <footer>
        <p>&copy; <?php echo date("Y"); ?> Rental Mobil. All rights reserved.</p>
    </footer>
</body>
</html>
<?php
$conn->close();
?>