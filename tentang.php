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
      background: #999999; /* navbar abu lebih terang */
      border-bottom: 1px solid #ddd;
      padding: 12px 5%; /* gap samping 5% */
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
      color: #fff;
    }
    nav a:hover {
      color: #ffcccc;
    }

    /* Container umum */
    .container {
      max-width: 90%;
      margin: 0 auto;
      padding: 20px;
      display: flex;
      align-items: center;
      justify-content: center;
      background: #fff;
      border-radius: 8px;
      box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    }

    /* Container paling atas */
    .top-content {
      max-width: 90%;
      margin: 20px auto; /* gap atas-bawah */
      padding: 20px;
      display: flex;
      align-items: flex-start;
      justify-content: flex-start;
      height: 30%;
      background: #fff;
      box-shadow: none;
      border-radius: 8px;
    }
    .top-inner {
      width: 40%;
      text-align: left;
    }
    .top-content h1 {
      display: flex;
      align-items: center;
      gap: 10px;
    }
    .top-content img {
      height: 160px; /* hati besar */
    }
    .underline {
      width: 100%;
      height: 5px;
      background: red;
      margin-top: 10px;
    }

    /* Container 1 & 2 */
    .container.flex {
      gap: 5%;
      margin-top: 20px;
    }
    .text {
      width: 40%;
      padding: 10px;
    }
    .text p {
      line-height: 1.6;
      text-align: justify;
    }
    .circle {
      width: 40%;
      aspect-ratio: 1/1;
      border-radius: 50%;
      background: #ccc;
    }

    /* Container 3 */
    .container3 {
      max-width: 90%;
      margin: 20px auto;
      padding: 20px;
      display: flex;
      align-items: center;
      justify-content: space-between;
      background-color: #5b5b5bff; /* lebih cerah dari sebelumnya */
      color: #000;
      border-radius: 8px;
    }
    .container3 .text {
      width: 70%;
    }
    .container3 img {
      width: 20%;
      max-height: 100px;
      object-fit: contain;
    }

    footer {
      margin-top: 0;
      padding: 15px 5%;
      text-align: center;
      background: #999999; /* sama dengan navbar */
      font-size: 14px;
      color: #fff;
      border-top: 1px solid #ccc;
    }
    footer img {
      height: 20px;
      vertical-align: middle;
      margin-left: 5px;
    }
    footer img:first-child {
      height: 20px; /* samakan dengan logo PSI putih */
    }
  </style>
</head>
<body>
  <header>
    <div class="logo">
      <img src="assets/image/logo.png" alt="PSI Logo">
    </div>
    <nav>
      <a href="dashboard.php">Beranda</a>
      <a href="tambah.php">Tambah Data</a>
      <a href="kehadiran.php">Kehadiran</a>
      <a href="tentang.php">Tentang</a>
      <a href="kontak.php">Kontak</a>
    </nav>
  </header>

  <!-- Container paling atas -->
  <div class="top-content">
    <div class="top-inner">
      <h1>
        PSI Peduli - Hadir Untuk Melayani Masyarakat 
        <img src="assets/image/hati.png" alt="Hati">
      </h1>
      <div class="underline"></div>
    </div>
  </div>

  <!-- Container 1 -->
  <div class="container flex">
    <div class="text">
      <p>
        Kita percaya, solidaritas bukan sekadar kata. Melalui program bantuan dana sosial,
        PSI berupaya meringankan beban masyarakat yang membutuhkan, dengan proses yang terbuka,
        cepat, dan tanpa diskriminasi.
      </p>
    </div>
    <div class="circle"></div>
  </div>

  <!-- Container 2 -->
  <div class="container flex">
    <div class="circle"></div>
    <div class="text">
      <p>
        Sebagai bagian dari komitmen untuk mendukung masyarakat, PSI menghadirkan program bantuan
        sosial yang dikelola dengan prinsip keterbukaan, keadilan, dan akuntabilitas. Melalui sistem
        pendataan yang terintegrasi, kami memastikan bahwa setiap bantuan dapat diterima tepat sasaran
        dan bermanfaat bagi yang membutuhkan.
      </p>
    </div>
  </div>

  <!-- Container 3 -->
  <div class="container3">
    <div class="text">
      <p>
        PSI meyakini bahwa solidaritas adalah kekuatan utama dalam membangun masyarakat yang lebih 
        sejahtera. Melalui program bantuan sosial, PSI berkomitmen untuk selalu hadir dengan cara
        yang terbuka, adil, dan akuntabel.
      </p>
    </div>
    <img src="assets/image/logo.png" alt="Logo DPRD">
  </div>

  <footer>
    <img src="assets/image/logodprd.png" alt="dprd Logo">
    <img src="assets/image/psiputih.png" alt="PSI Logo">
    Hak cipta © 2025 - Partai Solidaritas Indonesia
  </footer>
</body>
</html>
