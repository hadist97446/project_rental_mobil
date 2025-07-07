<?php
include 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$bookings = [];

$sql = "SELECT p.id, m.merek, m.model, m.tahun, p.tanggal_mulai, p.tanggal_selesai, p.total_harga, p.status_pemesanan, p.tanggal_pemesanan
        FROM pemesanan p
        JOIN mobil m ON p.id_mobil = m.id
        WHERE p.id_pengguna = ?
        ORDER BY p.tanggal_pemesanan DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $bookings[] = $row;
}
$stmt->close();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pemesanan Saya</title>
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
                    <li><a href="my_bookings.php">Pemesanan Saya</a></li>
                    <li><a href="logout.php">Logout</a></li>
                    <li>Halo, <?php echo htmlspecialchars($_SESSION['username']); ?></li>
                    <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                        <li><a href="admin/dashboard.php">Admin Panel</a></li>
                    <?php endif; ?>
                </ul>
            </nav>
        </div>
    </header>

    <div class="container">
        <h2>Riwayat Pemesanan Saya</h2>
        <?php if (empty($bookings)): ?>
            <p>Anda belum memiliki riwayat pemesanan.</p>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>ID Pemesanan</th>
                        <th>Mobil</th>
                        <th>Tanggal Mulai</th>
                        <th>Tanggal Selesai</th>
                        <th>Total Harga</th>
                        <th>Status</th>
                        <th>Tanggal Pesan</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($bookings as $booking): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($booking['id']); ?></td>
                            <td><?php echo htmlspecialchars($booking['merek'] . " " . $booking['model'] . " (" . $booking['tahun'] . ")"); ?></td>
                            <td><?php echo htmlspecialchars($booking['tanggal_mulai']); ?></td>
                            <td><?php echo htmlspecialchars($booking['tanggal_selesai']); ?></td>
                            <td>Rp <?php echo number_format($booking['total_harga'], 0, ',', '.'); ?></td>
                            <td><?php echo htmlspecialchars($booking['status_pemesanan']); ?></td>
                            <td><?php echo htmlspecialchars($booking['tanggal_pemesanan']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
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