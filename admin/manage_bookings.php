<?php
include '../config.php';

// Cek apakah user sudah login dan role-nya admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}
// Cek apakah user sudah login dan role-nya admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

$message = '';
$message_type = '';

// Handle update status pemesanan
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['booking_id']) && isset($_POST['status_pemesanan'])) {
    $booking_id = $_POST['booking_id'];
    $new_status = $_POST['status_pemesanan'];

    $stmt = $conn->prepare("UPDATE pemesanan SET status_pemesanan = ? WHERE id = ?");
    $stmt->bind_param("si", $new_status, $booking_id);
    if ($stmt->execute()) {
        $message = "Status pemesanan berhasil diperbarui.";
        $message_type = "success";
    } else {
        $message = "Error: " . $stmt->error;
        $message_type = "alert";
    }
    $stmt->close();
    // Redirect untuk menghindari resubmit form
    header("Location: manage_bookings.php?msg=" . urlencode($message) . "&type=" . urlencode($message_type));
    exit;
}

// Ambil pesan dari URL setelah redirect
if (isset($_GET['msg']) && isset($_GET['type'])) {
    $message = urldecode($_GET['msg']);
    $message_type = urldecode($_GET['type']);
}


// Ambil semua pemesanan dari database
$sql = "SELECT p.id, u.nama_lengkap AS nama_penyewa, m.merek, m.model, 
               p.tanggal_mulai, p.tanggal_selesai, p.total_harga, p.status_pemesanan, p.tanggal_pemesanan
        FROM pemesanan p
        JOIN pengguna u ON p.id_pengguna = u.id
        JOIN mobil m ON p.id_mobil = m.id
        ORDER BY p.tanggal_pemesanan DESC";

$result = $conn->query($sql);
$bookings = [];
while ($row = $result->fetch_assoc()) {
    $bookings[] = $row;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Pemesanan - Admin</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <header>
        <div class="container">
            <div id="branding">
                <h1>Admin Panel</h1>
            </div>
            <nav>
                <ul>
                    <li><a href="../index.php">Beranda Publik</a></li>
                    <li><a href="dashboard.php">Dashboard</a></li>
                    <li><a href="manage_cars.php">Manajemen Mobil</a></li>
                    <li><a href="manage_bookings.php">Manajemen Pemesanan</a></li>
                    <li><a href="../logout.php">Logout (<?php echo htmlspecialchars($_SESSION['username']); ?>)</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <div class="container">
        <h2>Manajemen Pemesanan</h2>

        <?php if (!empty($message)): ?>
            <div class="<?php echo $message_type; ?>"><?php echo $message; ?></div>
        <?php endif; ?>

        <?php if (empty($bookings)): ?>
            <p>Belum ada pemesanan yang masuk.</p>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>ID Pemesanan</th>
                        <th>Penyewa</th>
                        <th>Mobil</th>
                        <th>Mulai</th>
                        <th>Selesai</th>
                        <th>Total Harga</th>
                        <th>Status</th>
                        <th>Tanggal Pesan</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($bookings as $booking): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($booking['id']); ?></td>
                            <td><?php echo htmlspecialchars($booking['nama_penyewa']); ?></td>
                            <td><?php echo htmlspecialchars($booking['merek'] . " " . $booking['model']); ?></td>
                            <td><?php echo htmlspecialchars($booking['tanggal_mulai']); ?></td>
                            <td><?php echo htmlspecialchars($booking['tanggal_selesai']); ?></td>
                            <td>Rp <?php echo number_format($booking['total_harga'], 0, ',', '.'); ?></td>
                            <td><?php echo htmlspecialchars($booking['status_pemesanan']); ?></td>
                            <td><?php echo htmlspecialchars($booking['tanggal_pemesanan']); ?></td>
                            <td>
                                <form action="manage_bookings.php" method="POST" style="display: inline;">
                                    <input type="hidden" name="booking_id" value="<?php echo htmlspecialchars($booking['id']); ?>">
                                    <select name="status_pemesanan" onchange="this.form.submit()">
                                        <option value="Pending" <?php echo ($booking['status_pemesanan'] == 'Pending') ? 'selected' : ''; ?>>Pending</option>
                                        <option value="Dikonfirmasi" <?php echo ($booking['status_pemesanan'] == 'Dikonfirmasi') ? 'selected' : ''; ?>>Dikonfirmasi</option>
                                        <option value="Selesai" <?php echo ($booking['status_pemesanan'] == 'Selesai') ? 'selected' : ''; ?>>Selesai</option>
                                        <option value="Dibatalkan" <?php echo ($booking['status_pemesanan'] == 'Dibatalkan') ? 'selected' : ''; ?>>Dibatalkan</option>
                                    </select>
                                </form>
                            </td>
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