<?php
include 'config.php';

// Ambil data mobil dari database
$sql = "SELECT * FROM mobil WHERE status_ketersediaan = 'Tersedia'";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rental Mobil</title>
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
        <h2>Daftar Mobil Tersedia</h2>
        <div class="car-list">
            <?php
            if ($result->num_rows > 0) {
                while($row = $result->fetch_assoc()) {
                    echo "<div class='car-item'>";
                    echo "<img src='" . htmlspecialchars($row['gambar_url'] ?: 'https://via.placeholder.com/300x200?text=Gambar+Mobil') . "' alt='" . htmlspecialchars($row['merek'] . " " . $row['model']) . "'>";
                    echo "<h3>" . htmlspecialchars($row['merek'] . " " . $row['model']) . " (" . htmlspecialchars($row['tahun']) . ")</h3>";
                    echo "<p>Transmisi: " . htmlspecialchars($row['transmisi']) . "</p>";
                    echo "<p>Kursi: " . htmlspecialchars($row['jumlah_kursi']) . "</p>";
                    echo "<p>Harga: Rp " . number_format($row['harga_per_hari'], 0, ',', '.') . "/hari</p>";
                    echo "<a href='detail.php?id=" . htmlspecialchars($row['id']) . "' class='btn'>Detail & Pesan</a>";
                    echo "</div>";
                }
            } else {
                echo "<p>Tidak ada mobil tersedia saat ini.</p>";
            }
            ?>
        </div>
    </div>

    <footer>
        <p>&copy; <?php echo date("Y"); ?> Rental Mobil. All rights reserved.</p>
    </footer>
</body>
</html>
<?php
$conn->close();
?>