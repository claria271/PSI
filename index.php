<?php 
// index.php
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Brooklyn - Portfolio</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">

  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>

  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
      font-family: "Poppins", sans-serif;
    }

    body {
      background: #ffffff;
      color: #111827;
    }

    /* NAVBAR GRADIENT */
    header {
      width: 100%;
      background: linear-gradient(to right, #ffffff, #000000);
      padding: 14px 48px;
      display: flex;
      justify-content: space-between;
      align-items: center;
      position: sticky;
      top: 0;
      z-index: 20;
      box-shadow: 0 4px 18px rgba(15,23,42,0.20);
    }

    .nav-left {
      display: flex;
      align-items: center;
      gap: 10px;
    }

    .nav-logo-image img {
      height: 34px;
    }

    nav {
      display: flex;
      gap: 24px;
    }

    nav a {
      color: white;
      position: relative;
      font-weight: 500;
    }

    nav a::after {
      content: "";
      position: absolute;
      width: 0;
      height: 2px;
      left: 0;
      bottom: -4px;
      background: #dc2626;
      transition: .3s;
    }

    nav a:hover::after,
    nav a.active::after {
      width: 100%;
    }

    /* HERO */
    .hero {
      padding: 20px 40px 100px;
      display: flex;
      justify-content: center;
      background: #fff;
      position: relative;
    }

    .hero-inner {
      max-width: 1180px;
      width: 100%;
      display: grid;
      grid-template-columns: 1.65fr 0.95fr 1.2fr;
      gap: 28px;
      align-items: center;
    }

    /* NAMA BESAR */
    .hero-big-name {
      position: absolute;
      top: 20px;
      left: 50%;
      transform: translateX(-50%);
      font-size: 140px;
      font-weight: 800;
      color: #c4c4c4;
      opacity: .12;
      letter-spacing: 8px;
      white-space: nowrap;
    }

    /* KOTAK KIRI */
    .hero-card-left {
      background: #fff;
      border-radius: 26px;
      padding: 55px;
      border: 1px solid #e5e7eb;
      box-shadow: 0 18px 40px rgba(148,163,184,.35);
      margin-top: 140px;
      z-index: 3;
    }

    .hero-card-left .tag {
      font-size: 12px;
      font-weight: 700;
      color: #dc2626;
      letter-spacing: .15em;
      margin-bottom: 10px;
      text-transform: uppercase;
    }

    .hero-card-left h2 {
      font-size: 26px;
      font-weight: 700;
      margin-bottom: 14px;
      line-height: 1.4;
    }

    .hero-card-left p {
      font-size: 14px;
      line-height: 1.7;
      margin-bottom: 14px;
      color: #4b5563;
    }

    .hero-left-buttons {
      margin-top: 18px;
      display: flex;
      gap: 12px;
      flex-wrap: wrap;
    }

    /* BUTTON PSI */
    .btn-login,
    .btn-daftar,
    .btn-aduan {
      padding: 10px 20px;
      border-radius: 999px;
      border: 2px solid #dc2626;
      background: black;
      color: white;
      font-size: 14px;
      font-weight: 600;
      display: flex;
      align-items: center;
      gap: 8px;
      cursor: pointer;
      transition: .25s;
    }

    .btn-login:hover,
    .btn-daftar:hover,
    .btn-aduan:hover {
      background: #dc2626;
      transform: translateY(-2px);
    }

    /* FOTO */
    .hero-photo-wrapper {
      text-align: center;
      position: relative;
    }

    .photo-bg-blur {
      position: absolute;
      top: 120px;
      width: 360px;
      height: 390px;
      background: rgba(0,0,0,.33);
      filter: blur(30px);
      border-radius: 45% 45% 35% 35%;
      z-index: 1;
    }

    .hero-photo-wrapper img {
      width: 490px;
      position: relative;
      z-index: 2;
    }

    .hero-title-under {
      margin-top: -20px;
      margin-left: 45px;
      font-size: 18px;
      font-weight: 700;
    }

    /* KANAN — SUDAH DITURUNKAN LAGI (70px) */
    .hero-right h3 {
      font-size: 15px;
      font-weight: 600;
      letter-spacing: .2em;
      color: #6b7280;
      margin-bottom: 10px;
      margin-top: 70px;   /* 🔥 turun lagi */
    }

    .hero-right h1 {
      font-size: 30px;
      font-weight: 800;
      line-height: 1.45;
      margin-bottom: 14px;
    }

    .hero-right h1 span {
      color: #dc2626;
    }

    .hero-right p {
      font-size: 15px;
      line-height: 1.7;
      margin-bottom: 16px;
    }

    .hero-metric .value {
      font-size: 32px;
      font-weight: 800;
      color: #dc2626;
    }

  </style>
</head>

<body>

<header>
  <div class="nav-left">
    <div class="nav-logo-image">
      <img src="assets/image/logo.png">
    </div>
    <div class="nav-brand">Josiah Michael</div>
  </div>

  <nav>
    <a class="active">Home</a>
    <a>Services</a>
    <a>Case Studies</a>
    <a>About</a>
    <a>Resources</a>
    <a>Contact</a>
  </nav>
</header>

<section class="hero">

  <div class="hero-big-name">JOSIAH MICHAEL</div>

  <div class="hero-inner">

    <!-- KIRI -->
    <div class="hero-card-left">

      <div class="tag">JOSIAH MICHAEL</div>

      <h2>
         Ketua Fraksi Partai Solidaritas Indonesia (PSI).
      </h2>

      <p>yang menjabat sebagai Anggota Dewan Perwakilan Rakyat.</p>
      <p>DPRD Kota Surabaya, di mana ia dikenal vokal.</p>
      <p>dalam mengawal isu-isu lokal seperti pertanahan eigendom verponding.</p>
      <p>dan pembangunan wilayah Surabaya Barat.</p>

      <div class="hero-left-buttons">
        <a href="login.php"><button class="btn-login"><i class="fa-solid fa-right-to-bracket"></i> Login</button></a>
        <a href="register.php"><button class="btn-daftar"><i class="fa-solid fa-user-plus"></i> Daftar</button></a>
        <a href="aduan.php"><button class="btn-aduan"><i class="fa-solid fa-envelope"></i> Form Aduan</button></a>
      </div>

    </div>

    <!-- FOTO -->
    <div class="hero-photo-wrapper">
      <div class="photo-bg-blur"></div>
      <img src="assets/josiah2.png">
      <div class="hero-title-under">KETUA FRAKSI PSI SURABAYA</div>
    </div>

    <!-- KANAN -->
    <div class="hero-right">

      <h3>Langkah berikutnya</h3>

      <h1>
        Sistem pendataan keluarga <br>
        <span>yang membantu perkembangan wilayah Anda.</span>
      </h1>

      <p>Website ini memastikan setiap data keluarga tercatat aman dan rapi.</p>

      <div class="hero-metric">
        <div class="value">100%</div>
        <div>Target keluarga tercatat lengkap.</div>
      </div>

    </div>

  </div>
</section>

</body>
</html>
