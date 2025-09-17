<?php
// tentang.php
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>PSI - Tentang</title>
  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }
    body {
      font-family: Arial, sans-serif;
      background: #f9f9f9;
      color: #333;
    }
    header {
      background: #dcdcdc;
      padding: 10px 20px;
      display: flex;
      justify-content: space-between;
      align-items: center;
    }
    header img {
      height: 40px;
    }
    nav a {
      margin-left: 20px;
      text-decoration: none;
      color: #333;
      font-weight: bold;
    }
    nav a.active {
      color: #000;
      border-bottom: 2px solid #000;
    }
    .container {
      max-width: 800px;
      margin: 20px auto;
      padding: 20px;
      background: #fff;
      border-radius: 8px;
      box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    }
    h2 {
      color: #000;
      margin-bottom: 10px;
    }
    h2 span {
      color: #c00;
    }
    .icon {
      display: inline-block;
      font-size: 20px;
      margin-left: 8px;
    }
    .content {
      display: flex;
      flex-direction: column;
      gap: 20px;
    }
    .circle {
      width: 200px;
      height: 200px;
      border-radius: 50%;
      background: #ccc;
      margin: 0 auto;
    }
    p {
      line-height: 1.6;
      text-align: justify;
    }
    footer {
      margin-top: 30px;
      padding: 15px;
      text-align: center;
      background: #e6e6e6;
      font-size: 14px;
      color: #555;
      border-top: 1px solid #ccc;
    }
    footer img {
      height: 20px;
      vertical-align: middle;
      margin-left: 5px;
    }
  </style>
</head>
<body>
  <header>
    <div class="logo">
      <img src="logo.png" alt="PSI Logo"> <!-- Ganti dengan logo asli -->
    </div>
    <nav>
      <a href="dashboard.php">Beranda</a>
      <a href="tambah.php">Tambah Data</a>
      <a href="kehadiran.php">Kehadiran</a>
      <a href="tentang.php" class="active">Tentang</a>
      <a href="kontak.php">Kontak</a>
    </nav>
  </header>

  <div class="container">
    <h2>PSI Peduli - Hadir Untuk Melayani Masyarakat 
      <span class="icon">❤️</span>
    </h2>
    <div class="content">
      <p>
        Kita percaya, solidaritas bukan sekadar kata. Melalui program bantuan dana sosial,
        PSI berupaya meringankan beban masyarakat yang membutuhkan, dengan proses yang terbuka,
        cepat, dan tanpa diskriminasi.
      </p>
      <div class="circle"></div>
      <p>
        Sebagai bagian dari komitmen untuk mendukung masyarakat, PSI menghadirkan program bantuan
        sosial yang dikelola dengan prinsip keterbukaan, keadilan, dan akuntabilitas. Melalui sistem
        pendataan yang terintegrasi, kami memastikan bahwa setiap bantuan dapat diterima tepat sasaran
        dan bermanfaat bagi yang membutuhkan.
      </p>
      <div class="circle"></div>
    </div>
  </div>

  <footer>
    PSI meyakini bahwa solidaritas adalah kekuatan utama dalam membangun masyarakat yang lebih
    sejahtera. Melalui program bantuan sosial, PSI berkomitmen untuk selalu hadir dengan cara
    yang terbuka, adil, dan akunta
