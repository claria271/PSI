<?php
// kontak.php
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>PSI - Kontak</title>

  <!-- Google Font Poppins -->
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

  <style>
    *{margin:0;padding:0;box-sizing:border-box;font-family:'Poppins',sans-serif}
    body{background:#f2f2f2;color:#000}
    a{text-decoration:none;color:inherit}

    /* =======================
       NAVBAR (SAMA KAYAK DASHBOARD)
    ======================= */
    .dash-navbar{
      height:68px;
      padding:0 44px;
      display:flex;
      align-items:center;
      justify-content:space-between;
      background:linear-gradient(to right,#000000 0%,#5b5b5b 45%,#ffffff 100%);
      position:sticky;
      top:0;
      z-index:50;
      box-shadow:0 10px 28px rgba(0,0,0,.20);
    }

    .dash-left img{
      height:62px; /* LOGO BESAR */
      filter:drop-shadow(0 4px 10px rgba(0,0,0,.35));
    }

    .dash-menu{
      display:flex;
      gap:30px;
    }

    .dash-menu a{
      color:#000000; /* TEKS KANAN HITAM */
      font-weight:600;
      font-size:15px;
      position:relative;
      padding-bottom:6px;
    }

    .dash-menu a::after{
      content:"";
      position:absolute;
      left:0;
      bottom:0;
      width:0;
      height:2px;
      background:#dc2626;
      transition:.25s;
    }

    .dash-menu a:hover::after,
    .dash-menu a.active::after{
      width:100%;
    }

    .dash-menu a.active{
      color:#dc2626;
    }

    /* === Container Utama === */
    .container{
      max-width:1000px;
      margin:40px auto;
      background:#fff;
      border-radius:8px;
      padding:30px 40px;
      box-shadow:0 4px 10px rgba(0,0,0,0.15);
    }

    .title{
      font-size:22px;
      font-weight:800;
      color:#333;
      margin-bottom:25px;
    }

    .content{
      display:flex;
      gap:30px;
      flex-wrap:wrap;
      align-items:flex-start;
    }

    /* === Map === */
    .map{
      flex:1;
      min-width:350px;
      height:250px;
      border-radius:8px;
      overflow:hidden;
    }

    .map iframe{
      width:100%;
      height:100%;
      border:0;
    }

    /* === Info Kontak === */
    .info{
      flex:1;
      min-width:300px;
    }

    .info p{
      margin-bottom:8px;
      font-size:16px;
      color:#333;
    }

    /* Kotak PSI.id */
    .web-box{
      background:#d9d9d9;
      color:#555;
      padding:12px 15px;
      margin-top:20px;
      border-radius:6px;
      font-weight:700;
      font-size:16px;
      display:inline-block;
    }

    /* === Ikon Sosial Media === */
    .social-icons{
      margin-top:20px;
      display:flex;
      gap:12px;
      flex-wrap:wrap;
    }

    .social-icons a{
      display:flex;
      align-items:center;
      justify-content:center;
      width:36px;
      height:36px;
      border-radius:50%;
      background:#333;
      color:#fff;
      font-size:18px;
      transition:.3s;
    }

    .social-icons a:hover{
      background:#ff4b4b;
    }

    /* === Footer === */
    footer{
      margin-top:60px;
      padding:15px 5%;
      text-align:center;
      background:linear-gradient(to right,#ffffff,#000000);
      font-size:14px;
      color:#fff;
      border-top:1px solid #ccc;
    }

    footer img{
      height:20px;
      vertical-align:middle;
      margin-left:5px;
      filter:brightness(0) invert(1);
    }

    @media (max-width:768px){
      .content{flex-direction:column}
      .map{height:220px;min-width:100%}
      .dash-navbar{padding:0 18px}
      .dash-menu{gap:18px}
    }
  </style>
</head>
<body>

  <!-- NAVBAR -->
  <header class="dash-navbar">
    <div class="dash-left">
      <img src="../assets/image/logou.png" alt="PSI Logo">
    </div>

    <nav class="dash-menu">
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
          <a href="" target="_blank" title="LinkedIn PSI">
            <i class="fab fa-linkedin-in"></i>
          </a>
          <a href="mailto:fraksipsisurabaya@gmail.com" title="Kirim Email">
            <i class="fas fa-envelope"></i>
          </a>
          <a href="https://wa.me/6282201024055" target="_blank" title="Chat WhatsApp">
            <i class="fab fa-whatsapp"></i>
          </a>
          <a href="tel:+6282201024055" title="Hubungi via Telepon">
            <i class="fas fa-phone"></i>
          </a>
          <a href="https://www.facebook.com/share/17LizCopS2/?mibextid=wwXIfr" target="_blank" title="Kunjungi Facebook PSI Surabaya">
            <i class="fab fa-facebook-f"></i>
          </a>
        </div>
      </div>
    </div>
  </div>

  <!-- Footer -->


</body>
</html>
