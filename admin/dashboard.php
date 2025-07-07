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
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
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
        <h2>Selamat Datang, Admin <?php echo htmlspecialchars($_SESSION['username']); ?>!</h2>
        <p>Gunakan menu di atas untuk mengelola data rental mobil.</p>
        <p>Anda bisa:</p>
        <div style="margin-top: 20px;"> <!-- Menambah div untuk menampung tombol dengan margin atas -->
            <a href="manage_cars.php" class="btn btn-primary" style="margin-right: 15px;">Menambah, Mengedit, Menghapus Mobil</a>
            <a href="manage_bookings.php" class="btn btn-primary">Mengelola Status Pemesanan</a>
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