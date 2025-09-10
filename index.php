<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Dashboard - Entri Data Bantuan</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            background: #f5f5f5;
        }
        .navbar {
            background: #e0e0e0;
            padding: 15px 40px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .navbar img {
            height: 40px;
        }
        .menu a {
            margin-left: 20px;
            text-decoration: none;
            color: #333;
            font-weight: bold;
        }
        .menu a:hover {
            color: #ff0000;
        }
        .container {
            padding: 30px;
        }
        .welcome {
            background: white;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            box-shadow: 0 0 10px rgba(0,0,0,0.05);
        }
        .welcome h2 {
            margin: 0;
            margin-bottom: 10px;
        }
        .stats {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 15px;
            margin-top: 20px;
        }
        .stat-card {
            background: white;
            padding: 20px;
            border-radius: 10px;
            text-align: center;
            font-weight: bold;
            box-shadow: 0 0 10px rgba(0,0,0,0.05);
        }
        .footer {
            background: #ccc;
            padding: 15px;
            text-align: center;
            margin-top: 30px;
        }
        .logout {
            float: right;
            margin-top: -40px;
            margin-right: 20px;
        }
        .logout a {
            color: white;
            background: red;
            padding: 6px 12px;
            text-decoration: none;
            border-radius: 6px;
        }
        .logout a:hover {
            background: darkred;
        }
    </style>
</head>
<body>

    <!-- Navbar -->
    <div class="navbar">
        <div class="logo">
            <img src="logo.png" alt="PSI">
        </div>
        <div class="menu">
            <a href="index.php">Beranda</a>
            <a href="#">Tambah Data</a>
            <a href="#">Kehadiran</a>
            <a href="#">Tentang</a>
            <a href="#">Kontak</a>
        </div>
    </div>

    <!-- Container -->
    <div class="container">
        <div class="welcome">
            <h2>Selamat Datang di Website PSI - Entri Data Bantuan</h2>
            <p>
                Kami hadir untuk menghadirkan kemudahan, kecepatan, dan transparansi
                dalam pengelolaan data bantuan.<br>
                Dengan sistem yang terintegrasi, setiap informasi tercatat dengan rapi,
                akurat, dan dapat diakses secara aman.<br>
                Mari bersama wujudkan pelayanan yang lebih baik melalui teknologi yang terpercaya.
            </p>
        </div>

        <h3>Statistik</h3>
        <div class="stats">
            <div class="stat-card">Total Penerima Bantuan</div>
            <div class="stat-card">Jumlah Data Terkumpul</div>
            <div class="stat-card">Wilayah Data</div>
            <div class="stat-card">Gaji di bawah UMR</div>
            <div class="stat-card">Gaji di atas UMR</div>
            <div class="stat-card">Lainnya</div>
        </div>
    </div>

    <!-- Footer -->
    <div class="footer">
        <p>Website PSI - Entri Data Bantuan membantu pencatatan bantuan jadi lebih transparan dan efisien.</p>
        <small>Hak cipta &copy; 2025 - Partai Solidaritas Indonesia</small>
        <div class="logout">
            <a href="logout.php">Logout</a>
        </div>
    </div>

</body>
</html>
