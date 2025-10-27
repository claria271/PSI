<?php
session_start();
include 'koneksi/config.php';

// Pastikan user sudah login
if (!isset($_SESSION['alamat_email'])) {
    header("Location: login.php");
    exit;
}

// Ambil data user dari database
$email = $_SESSION['alamat_email'];
$sql = "SELECT * FROM login WHERE alamat_email='$email'";
$result = $conn->query($sql);
$user = $result->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Profil User</title>
  <style>
    body {
      font-family: Arial, sans-serif;
      background-color: #fff;
      margin: 0;
      padding: 0;
    }
    header {
      background: linear-gradient(90deg, #aaa, #000);
      color: white;
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 10px 40px;
    }
    header img {
      height: 40px;
    }
    header nav a {
      color: white;
      margin-left: 20px;
      text-decoration: none;
    }
    .container {
      width: 50%;
      margin: 50px auto;
    }
    .profile-box {
      background: linear-gradient(#e6e6e6, #a6a6a6);
      padding: 20px;
      border-radius: 10px;
    }
    .profile-header {
      display: flex;
      align-items: center;
      justify-content: space-between;
    }
    .profile-header img {
      width: 60px;
      height: 60px;
    }
    .profile-header h3, .profile-header p {
      margin: 5px 0;
    }
    .btn {
      width: 100%;
      background: #ccc;
      border: none;
      padding: 10px;
      margin-top: 15px;
      border-radius: 10px;
      cursor: pointer;
      font-weight: bold;
    }
    .btn:hover {
      background: #aaa;
    }
    footer {
      background: linear-gradient(90deg, #ccc, #000);
      color: white;
      text-align: center;
      padding: 10px;
      position: fixed;
      bottom: 0;
      width: 100%;
    }
  </style>
</head>
<body>
  <header>
    <img src="assets/image/psi2.jpg" alt="Logo PSI">
    <nav>
      <a href="index.php">Beranda</a>
      <a href="kontak.php">Kontak</a>
      <a href="profil.php"><b>Profil</b></a>
    </nav>
  </header>

  <div class="container">
    <div class="profile-box">
      <div class="profile-header">
        <div>
          <h3><?= htmlspecialchars($user['nama_lengkap']); ?></h3>
          <p><?= htmlspecialchars($user['alamat_email']); ?></p>
        </div>
        <a href="edit_profil.php" style="text-decoration:none;">✏️</a>
      </div>

      <button class="btn" onclick="window.location.href='edit_profil.php'">Edit Data</button>
      <button class="btn" onclick="window.location.href='logout.php'">Keluar</button>
    </div>
  </div>

  <footer>
    Hak cipta © 2025 - Partai Solidaritas Indonesia
  </footer>
</body>
</html>
