<?php
// kontak.php
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>PSI - Kontak</title>

  <!-- Tambahkan Font Awesome untuk ikon -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
      font-family: 'Poppins', sans-serif;
    }
    body {
      background: #f2f2f2;
      color: #000;
    }

    /* === HEADER === */
    header {
      background: linear-gradient(to right, #ffffff, #000000);
      padding: 12px 40px;
      display: flex;
      justify-content: space-between;
      align-items: center;
    }

    header img {
      height: 40px;
    }

    nav a {
      margin-left: 20px;
      color: #fff;
      text-decoration: none;
      font-weight: 600;
      transition: 0.3s;
    }

    nav a:hover,
    nav a.active {
      color: #ff4b4b;
    }

    /* === Container Utama === */
    .container {
      max-width: 1000px;
      margin: 40px auto;
      background: #fff;
      border-radius: 8px;
      padding: 30px 40px;
      box-shadow: 0 4px 10px rgba(0,0,0,0.15);
    }

    .title {
      font-size: 22px;
      font-weight: bold;
      color: #333;
      margin-bottom: 25px;
    }

    .content {
      display: flex;
      gap: 30px;
      flex-wrap: wrap;
      align-items: flex-start;
    }

    /* === Map === */
    .map {
      flex: 1;
      min-width: 350px;
      height: 250px;
      border-radius: 8px;
      overflow: hidden;
    }

    .map iframe {
      width: 100%;
      height: 100%;
      border: 0;
    }

    /* === Info Kontak === */
    .info {
      flex: 1;
      min-width: 300px;
    }

    .info p {
      margin-bottom: 8px;
      font-size: 16px;
      color: #333;
    }

    /* Kotak PSI.id */
    .web-box {
      background: #d9d9d9;
      color: #555;
      padding: 12px 15px;
      margin-top: 20px;
      border-radius: 6px;
      font-weight: 600;
      font-size: 16px;
      display: inline-block;
    }

    /* === Ikon Sosial Media === */
    .social-icons {
      margin-top: 20px;
      display: flex;
      gap: 12px;
    }

    .social-icons a {
      display: flex;
      align-items: center;
      justify-content: center;
      width: 36px;
      height: 36px;
      border-radius: 50%;
      background: #333;
      color: #fff;
      font-size: 18px;
      text-decoration: none;
      transition: 0.3s;
    }

    .social-icons a:hover {
      background: #ff4b4b;
    }

    /* === Footer === */
    footer {
      margin-top: 60px;
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
      margin-left: 5px;
      filter: brightness(0) invert(1);
    }

    @media (max-width: 768px) {
      .content {
        flex-direction: column;
      }
      .map {
        height: 220px;
      }
    }
  </style>
</head>
<body>

  <!-- Header -->
  <header>
    <div class="logo">
      <img src="../assets/image/logo.png" alt="PSI Logo">
    </div>
    <nav>
      <a href="dashboard.php">Beranda</a>
      <a href="kontak.php" class="active">Kontak</a>
      <a href="profil.php">Profil</a>
    </nav>
  </header>

  <!-- Konten -->
  <div class="container">
    <div class="title">Peta</div>

    <div class="content">
      <div class="map">
        <iframe
          src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3951.885879286427!2d112.74004047424264!3d-7.913127779629628!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x2dd7fa2c0f2c2f69%3A0x63e4cc7b9eb35f33!2sDPRD%20Kota%20Surabaya!5e0!3m2!1sid!2sid!4v1696921874995!5m2!1sid!2sid"
          allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade">
        </iframe>
      </div>

      <div class="info">
        <p><strong>Gedung DPRD Kota Surabaya</strong></p>
        <p>Jalan Yos Sudarso No 18–22</p>
        <p>Surabaya 6022</p>
        <p>Telp/WA: 0822-0102-40555</p>
        <p>Email: fraksipsisurabaya@gmail.com</p>

        <div class="web-box">www.psi.id</div>

        <div class="social-icons">
          <!-- LinkedIn -->
          <a href="" target="_blank" title="LinkedIn PSI">
            <i class="fab fa-linkedin-in"></i>
          </a>
          <!-- Email -->
          <a href="mailto:fraksipsisurabaya@gmail.com" title="Kirim Email">
            <i class="fas fa-envelope"></i>
          </a>
          <!-- WhatsApp -->
          <a href="https://wa.me/6282201024055" target="_blank" title="Chat WhatsApp">
            <i class="fab fa-whatsapp"></i>
          </a>
          <!-- Telepon -->
          <a href="tel:+6282201024055" title="Hubungi via Telepon">
            <i class="fas fa-phone"></i>
          </a>
          <!-- Facebook (ini yang kamu minta) -->
          <a href="https://www.facebook.com/share/17LizCopS2/?mibextid=wwXIfr" target="_blank" title="Kunjungi Facebook PSI Surabaya">
            <i class="fab fa-facebook-f"></i>
          </a>
        </div>
      </div>
    </div>
  </div>

  <!-- Footer -->
  <footer>
    <img src="../assets/image/logodprd.png" alt="Logo DPRD">
    <img src="../assets/image/psiputih.png" alt="Logo PSI">
    Hak cipta © 2025 - Partai Solidaritas Indonesia
  </footer>

</body>
</html>
