<?php
session_start();
if (!isset($_SESSION['alamat_email'])) {
    header("Location: login.php");
    exit;
}
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

    /* === HEADER dengan gradasi putih ke hitam === */
    header {
      background: linear-gradient(to right, #ffffff, #000000);
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

    /* Navigasi */
    nav a {
      margin: 0 15px;
      text-decoration: none;
      font-weight: bold;
      color: #fff;
      transition: 0.3s;
    }

    nav a:hover {
      color: #ff4b4b;
    }

    /* === CONTAINER ATAS & BAWAH === */
    .container-atas, .container-bawah {
      width: 90%;
      max-width: 1200px;
      margin: 30px auto;
      display: flex;
      gap: 20px;
      align-items: stretch;
    }

    .kolom {
      flex: 1;
      display: flex;
      flex-direction: column;
      gap: 20px;
    }

    .kolom-tengah { flex: 2; }
    .kolom-kanan { flex: 1; }
    .kolom-kiri-bawah { flex: 1; }
    .kolom-kanan-bawah { flex: 2; }

    /* Box umum */
    .box {
      background: #fff;
      border-radius: 10px;
      padding: 25px;
      box-shadow: 0 2px 2px rgba(0,0,0,0.1);
      flex: 1;
      display: flex;
      flex-direction: column;
    }

    .box h2 {
      margin-bottom: 15px;
      font-size: 20px;
    }

    .box p {
      font-size: 14px;
      line-height: 1.6;
    }

    /* Foto Placeholder */
    .foto {
      background: #ddd;
      border-radius: 8px;
      display: flex;
      align-items: center;
      justify-content: center;
      font-weight: bold;
      color: #555;
      flex: 1;
      min-height: 200px;
    }

    /* Statistik */
    .stats {
      text-align: center;
      flex: 1;
      display: flex;
      flex-direction: column;
    }

    .stats h2 {
      margin-bottom: 15px;
    }

    .stat-boxes, .stat-boxes-bottom {
      display: grid;
      gap: 15px;
      flex: 1;
    }

    .stat-boxes {
      grid-template-columns: repeat(3, 1fr);
    }

    .stat-boxes-bottom {
      grid-template-columns: repeat(2, 1fr);
      margin-top: 15px;
    }

    .stat-box {
      background: #eee;
      border-radius: 8px;
      padding: 20px 10px;
      font-weight: bold;
      font-size: 14px;
      display: flex;
      align-items: center;
      justify-content: center;
      min-height: 120px;
    }

    /* Kutipan */
    .quote {
      display: block;
      text-align: center;
      margin: 5% auto;
      color: #555;
      font-style: italic;
      max-width: 900px;
      line-height: 1.6;
    }

   footer {
  margin-top: 0;
  padding: 15px 5%;
  text-align: center;
  background: linear-gradient(to right, #ffffff, #000000); /* gradasi sama seperti header */
  font-size: 14px;
  color: #fff;
  border-top: 1px solid #ccc;
}

footer img {
  height: 20px;
  vertical-align: middle;
  margin-left: 5px;
  filter: brightness(0) invert(1); /* agar logo tetap terlihat di background gelap */
}

footer img:first-child {
  height: 20px;
}

    /* Responsive */
    @media (max-width: 900px) {
      .container-atas, .container-bawah {
        flex-direction: column;
      }
    }
  </style>
</head>
<body>

<header>
  <div class="logo">
    <img src="../assets/image/logo.png" alt="PSI">
  </div>
  <nav>
    <a href="dashboard.php">Beranda</a>
    <a href="tambahdata.php">Tambah Data</a>
    <a href="tentang.php">Tentang</a>
    <a href="kontak.php">Kontak</a>
    <a href="profil.php">profil</a>
  </nav>
</header>

<!-- Container Atas -->
<div class="container-atas">
  <div class="kolom kolom-tengah">
    <div class="box">
      <h2>Selamat Datang di Website PSI<br>Entri Data Bantuan</h2>
      <p>
        Kami hadir untuk menghadirkan kemudahan, kecepatan, dan transparansi dalam pengelolaan data bantuan.
        Dengan sistem yang terintegrasi, setiap informasi tercatat dengan rapi, akurat, dan dapat diakses secara aman.
        Mari bersama wujudkan pelayanan yang lebih baik melalui teknologi yang terpercaya.
      </p>
    </div>
  </div>

  <div class="kolom kolom-kanan">
    <div class="foto">[Foto]</div>
  </div>
</div>

<!-- Container Bawah -->
<div class="container-bawah">
  <div class="kolom kolom-kiri-bawah">
    <div class="foto">[Foto]</div>
    <div class="foto">[Foto]</div>
  </div>

  <div class="kolom kolom-kanan-bawah">
    <div class="box stats">
      <h2>Statistik</h2>
      <div class="stat-boxes">
        <div class="stat-box">[Total Penerima Bantuan]</div>
        <div class="stat-box">[Jumlah Data Terkumpul]</div>
        <div class="stat-box">[Wilayah Data]</div>
      </div>
      <div class="stat-boxes-bottom">
        <div class="stat-box">[Gaji dibawah UMR]</div>
        <div class="stat-box">[Gaji diatas UMR]</div>
      </div>
    </div>
  </div>
</div>

<i class="quote">
  "Website PSI - Entri Data Bantuan membantu pencatatan bantuan jadi lebih transparan dan efisien."
</i>

<footer>
  <img src="../assets/image/logodprd.png" alt="dprd Logo">
  <img src="../assets/image/psiputih.png" alt="PSI Logo">
  Hak cipta © 2025 - Partai Solidaritas Indonesia
</footer>

</body>
</html>
