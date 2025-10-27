<?php
// index.php
session_start(); // penting untuk mendeteksi status login
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>PSI</title>
  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body {
      background-color: #f8f9fa;
    }
    .hero-section {
      min-height: 100vh;
      display: flex;
      align-items: center;
    }

    /* Header dengan gradasi putih ke hitam */
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

    .dropdown-menu a {
      color: #000 !important;
    }

    .dropdown-menu a:hover {
      background-color: #f1f1f1;
    }
  </style>
</head>
<body>
  <header>
    <div class="logo">
      <img src="assets/image/logo.png" alt="PSI Logo">
    </div>
    <nav class="d-flex align-items-center">
      <a href="user/dashboard.php">Beranda</a>
      <a href="user/tambahdata.php">Tambah Data</a>
      <a href="user/tentang.php">Tentang</a>
      <a href="user/kontak.php">Kontak</a>

      <?php if (isset($_SESSION['user_id'])): ?>
        <!-- Jika sudah login -->
        <a href="user/profil.php">Profil</a>
      <?php else: ?>
        <!-- Jika belum login, tampilkan dropdown -->
        <div class="dropdown">
          <a class="dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
            Profil
          </a>
          <ul class="dropdown-menu dropdown-menu-end">
            <li><a class="dropdown-item" href="login.php">Login</a></li>
            <li><a class="dropdown-item" href="register.php">Register</a></li>
          </ul>
        </div>
      <?php endif; ?>
    </nav>
  </header>

  <div class="container hero-section">
    <div class="row flex-lg-row-reverse align-items-center g-5 py-5">
      <!-- Bagian gambar -->
      <div class="col-12 col-sm-8 col-lg-6 text-center">
        <img src="assets/image/psi.png" 
             alt="PSI" 
             class="img-fluid mx-auto d-block" 
             style="max-width: 350px; height: auto;">
      </div>

      <!-- Bagian teks -->
      <div class="col-lg-6">
        <h1 class="display-4 fw-bold lh-1 mb-4">
          Selamat Datang di Website PSI Kota Surabaya
        </h1>
        <p class="lead mb-4" style="text-align: justify;">
          Kita percaya, solidaritas bukan sekadar kata. Melalui program bantuan dana sosial, 
          PSI berupaya meringankan beban masyarakat yang membutuhkan dukungan, 
          dengan proses yang terbuka, cepat, dan tanpa diskriminasi.
        </p>
        <div class="d-grid gap-3 d-md-flex justify-content-md-start">
          <a href="login.php" class="btn btn-secondary btn-lg px-4">Login</a>
          <a href="register.php" class="btn btn-outline-secondary btn-lg px-4">Register</a>
        </div>
      </div>
    </div>
  </div>

  <!-- Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
