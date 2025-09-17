<?php
// index.php
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>PSI</title>
  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body {
      background-color: #f8f9fa; /* warna background abu-abu terang */
    }
    .hero-section {
      min-height: 100vh; /* full tinggi layar */
      display: flex;
      align-items: center;
    }
  </style>
</head>
<body>
  <div class="container hero-section">
    <div class="row flex-lg-row-reverse align-items-center g-5 py-5">
      <!-- Bagian gambar -->
      <!-- Bagian gambar -->
<div class="col-12 col-sm-8 col-lg-6 text-center">
  <img src="assets/image/psi.png" 
       alt="PSI" 
       class="img-fluid mx-auto d-block" 
       style="max-width: 350px; height: auto;">
</div>

      <!-- Bagian teks -->
      <div class="col-lg-6">
        <h1 class="display-4 fw-bold lh-1 mb-4">Selamat Datang di Website PSI Kota Surabaya</h1>
        <p class="lead mb-4">
          Kita percaya, solidaritas bukan sekadar kata. Melalui program bantuan dana sosial, 
          PSI berupaya meringankan beban masyarakat yang membutuhkan, dengan proses yang terbuka, cepat, 
          dan tanpa diskriminasi.
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
