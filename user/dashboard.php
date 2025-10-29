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
      font-family: 'Poppins', sans-serif;
      background: #e9e9e9;
      color: #333;
      line-height: 1.6;
    }

    /* === HEADER === */
    header {
      background: linear-gradient(to right, #ffffff, #000000);
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
      color: #fff;
      transition: 0.3s;
    }

    nav a:hover {
      color: #ff4b4b;
    }

    /* === CONTAINER PALING ATAS (tidak diubah) === */
    .top-content {
      max-width: 90%;
      margin: 20px auto;
      padding: 20px;
      display: flex;
      align-items: flex-start;
      justify-content: flex-start;
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
      height: 160px;
    }

    .underline {
      width: 100%;
      height: 5px;
      background: red;
      margin-top: 10px;
    }

    /* === SECTION UMUM === */
    .section {
      background: #fff;
      max-width: 90%;
      margin: 30px auto;
      border-radius: 12px;
      box-shadow: 0 4px 10px rgba(0,0,0,0.1);
      padding: 30px;
      opacity: 0;
      transform: translateY(40px);
      animation: fadeUp 1s ease forwards;
    }

    /* Animasi muncul */
    @keyframes fadeUp {
      to {
        opacity: 1;
        transform: translateY(0);
      }
    }

    .section:nth-of-type(2) {
      animation-delay: 0.3s;
    }

    .section:nth-of-type(3) {
      animation-delay: 0.6s;
    }

    .section:nth-of-type(4) {
      animation-delay: 0.9s;
    }

    /* === FLEX LAYOUT === */
    .flex {
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 30px;
      flex-wrap: wrap;
    }

    .flex .text {
      flex: 1;
      min-width: 250px;
    }

    .text p {
      text-align: justify;
    }

    /* === GANTI LINGKARAN JADI PERSEGI === */
    .image-box {
      width: 200px;
      height: 200px;
      border-radius: 10px;
      background: #ccc;
      box-shadow: inset 0 0 10px rgba(0,0,0,0.15);
      flex-shrink: 0;
      background-size: cover;
      background-position: center;
    }

    /* === LEADER SECTION (bagian bawah tetap) === */
    .leader-section {
      display: flex;
      justify-content: space-around;
      flex-wrap: wrap;
      gap: 20px;
      margin-top: 40px;
    }

    .leader-card {
      background: #f0f0f0;
      border-radius: 10px;
      width: 28%;
      text-align: center;
      padding: 15px;
      box-shadow: 0 2px 8px rgba(0,0,0,0.1);
      transition: transform 0.3s;
    }

    .leader-card:hover {
      transform: scale(1.05);
    }

    .leader-card .photo {
      width: 100%;
      aspect-ratio: 1/1;
      border-radius: 10px;
      background: #ccc;
      margin-bottom: 10px;
    }

    .leader-card h3 {
      font-size: 16px;
      font-weight: 600;
    }

    /* === FOOTER === */
    footer {
      margin-top: 40px;
      padding: 15px 5%;
      text-align: center;
      background: linear-gradient(to right, #ffffff, #000000);
      font-size: 14px;
      color: #fff;
      border-top: 1px solid #ccc;
    }

    footer img {
      height: 20px;
      vertical-align: middle;
      margin: 0 5px;
      filter: brightness(0) invert(1);
    }
  </style>
</head>
<body>
  <header>
    <div class="logo">
      <img src="../assets/image/logo.png" alt="PSI Logo">
    </div>
    <nav>
      <a href="tentang.php">Tentang</a>
      <a href="kontak.php">Kontak</a>
      <a href="profil.php">Profil</a>
    </nav>
  </header>

  <!-- CONTAINER PALING ATAS (tidak diubah) -->
  <div class="top-content">
    <div class="top-inner">
      <h1>
        PSI Peduli - Hadir Untuk Melayani Masyarakat 
        <img src="../assets/image/hati.png" alt="Hati">
      </h1>
      <div class="underline"></div>
    </div>
  </div>

  <!-- SECTION 1 -->
  <section class="section">
    <div class="flex">
      <div class="text">
        <p>
          Kita percaya, solidaritas bukan sekadar kata. Melalui program bantuan dana sosial,
          PSI berupaya meringankan beban masyarakat yang membutuhkan, dengan proses yang terbuka,
          cepat, dan tanpa diskriminasi.
        </p>
      </div>
      <div class="image-box" style="background-image: url('../assets/image/psi1.jpg');"></div>
    </div>
  </section>

  <!-- SECTION 2 -->
  <section class="section">
    <div class="flex">
      <div class="image-box" style="background-image: url('../assets/image/psi2.jpg');"></div>
      <div class="text">
        <p>
          Sebagai bagian dari komitmen untuk mendukung masyarakat, PSI menghadirkan program bantuan
          sosial yang dikelola dengan prinsip keterbukaan, keadilan, dan akuntabilitas. Melalui sistem
          pendataan yang terintegrasi, kami memastikan bahwa setiap bantuan dapat diterima tepat sasaran
          dan bermanfaat bagi yang membutuhkan.
        </p>
      </div>
    </div>
  </section>

  <!-- SECTION 3 (tetap sama seperti sebelumnya) -->
  <section class="section">
    <p>
      PSI meyakini bahwa solidaritas adalah kekuatan utama dalam membangun kehidupan yang lebih 
      baik bagi masyarakat. Melalui program bantuan sosial, PSI berkomitmen untuk hadir dengan cara
      yang terbuka, adil, dan akuntabel.
    </p>

    <div class="leader-section">
      <div class="leader-card">
        <div class="photo"></div>
        <h3>Ketua Fraksi PSI</h3>
      </div>
      <div class="leader-card">
        <div class="photo"></div>
        <h3>Wakil Ketua Fraksi PSI</h3>
      </div>
      <div class="leader-card">
        <div class="photo"></div>
        <h3>Fairuz Fraksi</h3>
      </div>
    </div>
  </section>

  <footer>
    <img src="../assets/image/logodprd.png" alt="DPRD Logo">
    <img src="../assets/image/psiputih.png" alt="PSI Logo">
    Hak cipta © 2025 - Partai Solidaritas Indonesia
  </footer>
</body>
</html>
