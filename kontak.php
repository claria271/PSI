<?php
// kontak.php
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>PSI - Kontak</title>
  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }
    body {
      font-family: Arial, sans-serif;
      background: #f5f5f5;
      color: #000;
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

    /* Box utama */
    .container {
      max-width: 80%;
      margin: 40px auto;
      padding: 30px;
      background: #fff;
      border-radius: 12px;

      /* Efek 3D */
      box-shadow: 8px 8px 18px rgba(0,0,0,0.25),
                  -5px -5px 12px rgba(255,255,255,0.2);
    }
    .container p {
      margin-bottom: 10px;
      font-size: 16px;
      line-height: 1.6;
    }

    /* Kotak www.psi.id */
    .web-box {
      background: linear-gradient(145deg, #4d4d4d, #5c5c5c);
      color: #fff;
      padding: 14px 20px;
      margin-top: 20px;
      font-size: 16px;
      font-weight: bold;
      text-align: center;
      border-radius: 6px;
      width: 100%;
      display: block;
      box-shadow: inset 2px 2px 5px rgba(0,0,0,0.3),
                  inset -2px -2px 5px rgba(255,255,255,0.1);
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

  </style>
</head>
<body>
  <header>
    <div class="logo">
      <img src="assets/image/logo.png" alt="PSI Logo"> <!-- ganti logo asli -->
    </div>
    <nav>
      <a href="dashboard.php">Beranda</a>
      <a href="tambahdata.php">Tambah Data</a>
      <a href="tentang.php">Tentang</a>
      <a href="kontak.php" class="active">Kontak</a>
      <a href="profil.php">profil</a>
    </nav>
  </header>

  <div class="container">
    <p>Gedung DPRD Kota Surabaya</p>
    <p>Lantai 5 Ruang FPSI</p>
    <p>Jalan Yos Sudarso No. 18 - 22</p>
    <p>Surabaya 6022</p>
    <p>Tlp/WA: 0822 - 0102 - 40555</p>
    <p>email: fraksipsisurabaya@gmail.com</p>

    <div class="web-box">
      www.psi.id
    </div>
  </div>

  <footer>
    <img src="assets/image/logodprd.png" alt="dprd Logo">
    <img src="assets/image/psiputih.png" alt="PSI Logo">
    Hak cipta © 2025 - Partai Solidaritas Indonesia
  </footer>
</body>
</html>
