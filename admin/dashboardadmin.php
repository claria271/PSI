<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../user/login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Dashboard Admin - Sistem Entri Data Keluarga</title>
  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
      font-family: 'Poppins', sans-serif;
    }

    body {
      background: #f2f2f2;
      color: #333;
      display: flex;
      flex-direction: column;
      min-height: 100vh;
    }

    /* Header PSI */
    header {
      background: linear-gradient(to right, #ffffff, #000000);
      display: flex;
      align-items: center;
      justify-content: flex-start;
      padding: 10px 30px;
      gap: 15px;
    }

    header img {
      height: 45px;
    }

    header h1 {
      color: #fff;
      font-size: 18px;
      font-weight: 600;
    }

    /* Layout utama */
    .main {
      display: flex;
      flex: 1;
      background: #e6e6e6;
    }

    /* Sidebar */
    .sidebar {
      width: 220px;
      background: linear-gradient(to bottom, #e0e0e0, #6b6b6b);
      padding: 25px 15px;
      display: flex;
      flex-direction: column;
      align-items: center;
      border-right: 1px solid #ccc;
    }

    .sidebar img {
      height: 80px;
      margin-bottom: 15px;
    }

    .sidebar .admin-box {
      text-align: center;
      background: #dcdcdc;
      padding: 10px;
      border-radius: 8px;
      width: 100%;
      font-weight: bold;
      margin-bottom: 20px;
    }

    .sidebar nav a {
      display: block;
      width: 100%;
      padding: 10px;
      margin: 5px 0;
      border-radius: 6px;
      text-decoration: none;
      font-weight: 500;
      color: #000;
      background: #c4c4c4;
      transition: 0.3s;
      text-align: center;
    }

    .sidebar nav a:hover,
    .sidebar nav a.active {
      background: #ff4b4b;
      color: #fff;
    }

    /* Konten utama */
    .content {
      flex: 1;
      padding: 25px;
    }

    .content h2 {
      background: #fff;
      border-radius: 10px;
      padding: 15px;
      box-shadow: 0 2px 6px rgba(0,0,0,0.15);
      font-size: 18px;
      font-weight: bold;
      margin-bottom: 25px;
    }

    /* Kartu data */
    .cards {
      display: flex;
      justify-content: space-between;
      flex-wrap: wrap;
      gap: 20px;
      margin-bottom: 25px;
    }

    .card {
      flex: 1;
      min-width: 180px;
      background: linear-gradient(to bottom, #d9d9d9, #8c8c8c);
      padding: 20px;
      border-radius: 10px;
      text-align: center;
      color: #fff;
      font-weight: 600;
      box-shadow: 0 2px 6px rgba(0,0,0,0.2);
    }

    /* Section Tambah Data */
    .data-section {
      background: linear-gradient(to bottom, #e0e0e0, #6b6b6b);
      border-radius: 10px;
      padding: 30px;
      display: flex;
      flex-direction: column;
      align-items: center;
      gap: 15px;
    }

    .data-section button {
      background: #ff4b4b;
      border: none;
      color: #fff;
      font-size: 16px;
      padding: 12px 25px;
      border-radius: 8px;
      cursor: pointer;
      font-weight: 600;
      transition: 0.3s;
    }

    .data-section button:hover {
      background: #e03c3c;
    }

    footer {
      text-align: center;
      padding: 15px 5%;
      background: linear-gradient(to right, #ffffff, #000000);
      color: #fff;
      font-size: 14px;
      border-top: 1px solid #ccc;
    }

    footer img {
      height: 20px;
      vertical-align: middle;
      margin-left: 5px;
      filter: brightness(0) invert(1);
    }
  </style>
</head>
<body>
  <header>
    <img src="assets/image/logo.png" alt="Logo PSI">
    <h1>ADMIN DASHBOARD</h1>
  </header>

  <div class="main">
    <!-- Sidebar -->
    <aside class="sidebar">
      <img src="assets/image/usericon.png" alt="Admin Icon">
      <div class="admin-box">[Nama Admin]</div>
      <nav>
        <a href="#" class="active">Dashboard</a>
        <a href="#">Data Keluarga</a>
        <a href="#">Hasil Verifikasi</a>
        <a href="#">Laporan</a>
        <a href="#">Logout</a>
      </nav>
    </aside>

    <!-- Konten utama -->
    <section class="content">
      <h2>Dashboard Admin – Sistem Entri Data Keluarga</h2>

      <!-- Kartu Data -->
      <div class="cards">
        <div class="card">[Total Data Keluarga]</div>
        <div class="card">[Diatas UMR]</div>
        <div class="card">[Dibawah UMR]</div>
        <div class="card">[Hasil Verifikasi]</div>
      </div>

      <!-- Tambah Data Section -->
      <div class="data-section">
        <button>[Tambah Data]</button>
        <div style="width: 60%; height: 50px; background: rgba(255,255,255,0.6); border-radius: 8px;"></div>
      </div>
    </section>
  </div>

  <footer>
    <img src="assets/image/logodprd.png" alt="Logo DPRD">
    <img src="assets/image/psiputih.png" alt="Logo PSI">
    Hak cipta © 2025 - Partai Solidaritas Indonesia
  </footer>
</body>
</html>
