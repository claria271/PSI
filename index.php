<?php
// index.php
session_start();
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>PSI Surabaya</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    body {
      font-family: 'Poppins', sans-serif;
      background: #f2f2f2;
      color: #222;
      overflow-x: hidden;
    }

    /* HEADER */
    header {
      background: linear-gradient(to right, #ffffff, #000000);
      padding: 15px 50px;
      display: flex;
      justify-content: space-between;
      align-items: center;
      position: sticky;
      top: 0;
      z-index: 100;
      animation: slideDown 1s ease-out;
    }

    header img {
      height: 40px;
    }

    nav a, nav .dropdown-toggle {
      margin: 0 15px;
      text-decoration: none;
      font-weight: bold;
      color: #fff;
      transition: 0.3s;
    }

    nav a:hover, nav .dropdown-toggle:hover {
      color: #ff4b4b;
    }

    /* HERO SECTION */
    .hero {
      background: url('assets/image/index.jpeg') center/cover no-repeat;
      height: 100vh;
      display: flex;
      justify-content: center;
      align-items: center;
      position: relative;
      animation: fadeIn 2s ease-in-out;
    }

    .hero::before {
      content: "";
      position: absolute;
      inset: 0;
      background: rgba(0, 0, 0, 0.4);
      z-index: 1;
    }

    .hero-content {
      position: relative;
      z-index: 2;
      background: rgba(255, 255, 255, 0.03); /* 90% transparan */
      backdrop-filter: blur(6px);
      width: 80%;
      height: 90vh;
      display: flex;
      flex-direction: column;
      justify-content: center;
      align-items: center;
      border-radius: 20px;
      text-align: center;
      box-shadow: 0 8px 25px rgba(0,0,0,0.3);
      animation: slideUp 1.5s ease-out;
    }

    .hero-content h1 {
      font-weight: 700;
      color: #fff;
      margin-bottom: 10px;
      font-size: 2.5rem;
    }

    .hero-content p {
      font-size: 18px;
      color: #f1f1f1;
      margin-bottom: 25px;
    }

    /* Tombol */
    .hero-content .btn {
      border-radius: 30px;
      padding: 10px 30px;
      font-weight: bold;
      transition: all 0.3s ease;
    }

    .btn-login {
      background-color: #000;
      color: #fff;
    }

    .btn-login:hover {
      background-color: #ff4b4b;
      color: #fff;
      transform: scale(1.05);
    }

    .btn-register {
      border: 2px solid #fff;
      color: #fff;
    }

    .btn-register:hover {
      background-color: #fff;
      color: #000;
      transform: scale(1.05);
    }

    /* SECTION ISI */
    .content {
      text-align: center;
      padding: 60px 20px;
      animation: fadeIn 2.2s ease-in-out;
    }

    .content p {
      max-width: 700px;
      margin: auto;
      line-height: 1.8;
      color: #333;
    }

    .gallery {
      display: flex;
      justify-content: center;
      gap: 30px;
      margin-top: 40px;
      flex-wrap: wrap;
    }

    .gallery img {
      width: 280px;
      border-radius: 10px;
      transition: transform 0.4s ease, box-shadow 0.4s ease;
      animation: fadeIn 2.4s ease-in-out;
    }

    .gallery img:hover {
      transform: scale(1.05);
      box-shadow: 0 5px 20px rgba(0,0,0,0.2);
    }

    /* FOOTER */
    footer {
      background: linear-gradient(to right, #ffffff, #000000);
      color: #fff;
      text-align: center;
      padding: 20px 10px;
      margin-top: 50px;
      animation: slideUp 1.5s ease-in;
    }

    footer .social-icons a {
      color: #fff;
      margin: 0 10px;
      font-size: 20px;
      text-decoration: none;
      transition: 0.3s;
    }

    footer .social-icons a:hover {
      color: #ff4b4b;
    }

    /* ANIMASI */
    @keyframes fadeIn {
      from { opacity: 0; }
      to { opacity: 1; }
    }

    @keyframes slideUp {
      from { transform: translateY(60px); opacity: 0; }
      to { transform: translateY(0); opacity: 1; }
    }

    @keyframes slideDown {
      from { transform: translateY(-40px); opacity: 0; }
      to { transform: translateY(0); opacity: 1; }
    }

    /* RESPONSIVE */
    @media (max-width: 768px) {
      .hero-content {
        width: 90%;
        height: auto;
        padding: 30px 20px;
      }
      .hero-content h1 {
        font-size: 1.8rem;
      }
    }
  </style>
</head>
<body>

  <!-- HEADER -->
  <header>
    <div class="logo">
      <img src="assets/image/logo.png" alt="PSI Logo">
    </div>
    <nav>
      <a href="tentang.php">Tentang</a>
      <a href="kontak.php">Kontak</a>
      <?php if (isset($_SESSION['user_id'])): ?>
        <!-- Jika sudah login -->
        <a href="user/profil.php">Profil</a>
      <?php else: ?>
        <div class="dropdown d-inline">
          <a class="dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">Profil</a>
          <ul class="dropdown-menu dropdown-menu-end">
            <li><a class="dropdown-item" href="login.php">Login</a></li>
            <li><a class="dropdown-item" href="register.php">Register</a></li>
          </ul>
        </div>
      <?php endif; ?>
    </nav>
  </header>

  <!-- HERO -->
  <section class="hero">
    <div class="hero-content">
      <h1>Selamat Datang di Website PSI</h1>
      <p>Temukan makna, tumbuhkan karya</p>
      <div class="d-flex justify-content-center gap-3">
        <a href="login.php" class="btn btn-login">Login</a>
        <a href="register.php" class="btn btn-register">Register</a>
      </div>
    </div>
  </section>

  <!-- KONTEN -->
  <section class="content">
    <p>
      Kita percaya, solidaritas bukan sekadar kata. Melalui program bantuan dana sosial, 
      PSI berupaya meringankan beban masyarakat yang membutuhkan dukungan, 
      dengan proses yang terbuka, cepat, dan tanpa diskriminasi.
    </p>

    <div class="gallery">
      <img src="assets/image/index1.jpeg" alt="Kegiatan PSI">
      <img src="assets/image/index2.jpeg" alt="Anggota PSI">
    </div>
  </section>

  <!-- FOOTER -->
  <footer>
    <div class="social-icons">
      <a href="#"><i class="bi bi-linkedin"></i></a>
      <a href="#"><i class="bi bi-instagram"></i></a>
      <a href="#"><i class="bi bi-envelope-fill"></i></a>
      <a href="#"><i class="bi bi-telephone-fill"></i></a>
      <a href="#"><i class="bi bi-facebook"></i></a>
    </div>
    <p class="mt-2">Hak cipta © 2025 - Partai Solidaritas Indonesia</p>
  </footer>

  <!-- Bootstrap & Icons -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
