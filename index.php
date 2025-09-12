<?php
// index.php
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Website PSI - Entri Data Bantuan</title>
  <style>
    * {
      margin: 0; padding: 0;
      box-sizing: border-box;
    }
    body {
      font-family: Arial, sans-serif;
      background: #f9f9f9;
      color: #333;
    }
    header {
      background: #fff;
      border-bottom: 1px solid #ddd;
      padding: 12px 40px;
      display: flex;
      align-items: center;
      justify-content: space-between;
      position: sticky;
      top: 0;
      z-index: 100;
    }
    header .logo {
      display: flex;
      align-items: center;
      gap: 10px;
    }
    header img {
      height: 40px;
    }
    nav a {
      margin: 0 15px;
      text-decoration: none;
      font-weight: bold;
      color: #333;
    }
    nav a:hover {
      color: #c00;
    }

    /* Layout utama */
    .main-grid {
      display: grid;
      grid-template-columns: 200px 1fr 200px;
      gap: 20px;
      padding: 30px;
    }
    .sub-grid {
      display: grid;
      gap: 20px;
    }
    .foto {
      background: #ddd;
      border-radius: 8px;
      display: flex;
      align-items: center;
      justify-content: center;
      font-weight: bold;
      color: #555;
      height: 200px;
    }
    .foto.tinggi {
      height: 420px; /* biar tinggi seperti desain kanan */
    }

    /* Box umum */
    .box {
      background: #fff;
      border-radius: 10px;
      padding: 60px;
      box-shadow: 0 2px 2px rgba(0,0,0,0.1);
    }
    .box h2 {
      margin-bottom: 20px;
    }

    /* Statistik */
    .stats {
      text-align: center;
    }
    .stat-boxes {
      display: grid;
      grid-template-columns: repeat(3, 1fr);
      gap: 20px;
      margin-top: 20px;
    }
    .stat-boxes-bottom {
      display: grid;
      grid-template-columns: repeat(2, 1fr);
      gap: 20px;
      margin-top: 20px;
      justify-content: center;
    }
    .stat-box {
      background: #eee;
      border-radius: 8px;
      padding: 40px 10px;
      font-weight: bold;
      font-size: 14px;
    }

    footer {
      background: #f1f1f1;
      text-align: center;
      padding: 20px;
      margin-top: 30px;
      font-size: 14px;
      color: #444;
    }
    footer i {
      display: block;
      margin-bottom: 8px;
    }
  </style>
</head>
<body>

<header>
  <div class="logo">
    <img src="assets/image/logo.png" alt="PSI">
    <span><strong>PSI</strong></span>
  </div>
  <nav>
    <a href="#">Beranda</a>
    <a href="#">Tambah Data</a>
    <a href="#">Kehadiran</a>
    <a href="#">Tentang</a>
    <a href="#">Kontak</a>
  </nav>
</header>

<div class="main-grid">
  <!-- Kolom kiri -->
  <div class="sub-grid">
    <div class="foto">[Foto]</div>
    <div class="foto">[Foto]</div>
  </div>

  <!-- Kolom tengah -->
  <div class="sub-grid">
    <div class="box">
      <h2>Selamat Datang di Website PSI<br>Entri Data Bantuan</h2>
      <p>
        Kami hadir untuk menghadirkan kemudahan, kecepatan, dan transparansi dalam pengelolaan data bantuan.<br><br>
        Dengan sistem yang terintegrasi, setiap informasi tercatat dengan rapi, akurat, dan dapat diakses secara aman.<br><br>
        Mari bersama wujudkan pelayanan yang lebih baik melalui teknologi yang terpercaya.
      </p>
    </div>

    <div class="box stats">
      <h2>Statistik</h2>
      <div class="stat-boxes">
        <div class="stat-box">[Total Penerima Bantuan]</div>
        <div class="stat-box">[Jumlah Data Terkumpul]</div>
        <div class="stat-box">[Wilayah Data]</div>
      </div>
      <div class="stat-boxes-bottom">
        <div class="stat-box">[Gaji dibawah UMR]</div>
        <div class="stat-box">[Gaji dibawah UMR]</div>
      </div>
    </div>
  </div>

  <!-- Kolom kanan -->
  <div class="sub-grid">
    <div class="foto tinggi">[Foto]</div>
  </div>
</div>

<footer>
  <i>"Website PSI - Entri Data Bantuan membantu pencatatan bantuan jadi lebih transparan dan efisien."</i>
  Hak cipta © 2025 - Partai Solidaritas Indonesia
</footer>

</body>
</html>
