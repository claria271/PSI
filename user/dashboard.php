<?php

// user/dashboard.php
session_start();
include '../koneksi/config.php';

// Query untuk menghitung total data terverifikasi
$queryVerifikasi = "SELECT COUNT(*) as total FROM verifikasi";
$resultVerifikasi = mysqli_query($conn, $queryVerifikasi);
$totalVerifikasi = mysqli_fetch_assoc($resultVerifikasi)['total'];

// Query untuk data per dapil (opsional, untuk breakdown)
$queryPerDapil = "SELECT dapil, COUNT(*) as total 
                  FROM verifikasi 
                  WHERE dapil IS NOT NULL AND dapil != ''
                  GROUP BY dapil 
                  ORDER BY dapil";
$resultPerDapil = mysqli_query($conn, $queryPerDapil);
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>PSI - Dashboard</title>

  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    body {
      font-family: 'Poppins', sans-serif;
      background: #ffffff;
      color: #000000;
      line-height: 1.6;
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


    /* HERO */
    .hero {
      width: 100%;
      height: 420px;
      overflow: hidden;
      position: relative;
    }

    .hero-slide {
      position: absolute;
      width: 100%;
      height: 420px;
      top: 0;
      left: 0;
      opacity: 0;
      background-size: cover;
      background-position: center;
      transition: opacity 1.5s ease-in-out;
    }

    .hero-slide.active {
      opacity: 1;
    }

    /* TOP TITLE */
    .top-content {
      max-width: 90%;
      margin: 25px auto;
    }

    .underline {
      width: 100%;
      height: 5px;
      background: #ff0000;
      margin-top: 8px;
    }
/* === STATS SECTION (HITAM-PUTIH-MERAH) === */
.stats-section {
  width: 100%;
  margin: 0;
  padding: 80px 0;
  background: #000000; /* HITAM */
  position: relative;
  overflow: hidden;
}

/* Glow dekor merah */
.stats-section::before,
.stats-section::after {
  content: '';
  position: absolute;
  width: 450px;
  height: 450px;
  border-radius: 50%;
  background: radial-gradient(circle, rgba(255,0,0,0.15) 0%, transparent 70%);
  z-index: 1;
}
.stats-section::before {
  top: -200px;
  right: -100px;
}
.stats-section::after {
  bottom: -200px;
  left: -100px;
}

.stats-inner {
  max-width: 90%;
  margin: 0 auto;
  position: relative;
  z-index: 2;
}

/* JUDUL & SUBTITLE */
.stats-section h2 {
  font-size: 36px;
  color: #ffffff;   /* PUTIH */
  font-weight: 800;
  text-align: center;
}

.stats-section .subtitle {
  font-size: 17px;
  color: #e0e0e0;  /* ABU PUTIH */
  text-align: center;
  margin-bottom: 50px;
}

/* KOTAK STATISTIK */
.stats-container {
  display: flex;
  justify-content: center;
  align-items: stretch;
}

.stat-item {
  background: #111111;       /* HITAM tua */
  border-radius: 24px;
  padding: 50px 60px;
  width: 100%;
  text-align: center;
  border: 2px solid #ff0000; /* MERAH */
  box-shadow: 0 0 30px rgba(255, 0, 0, 0.25);
  position: relative;
  transition: 0.4s;
}

.stat-item:hover {
  transform: translateY(-6px);
  box-shadow: 0 0 50px rgba(255, 0, 0, 0.45);
}

/* ICON BOX */
.stat-icon-box {
  width: 80px;
  height: 80px;
  border-radius: 16px;
  background: #ff000015;
  border: 2px solid #ff0000;
  margin: 0 auto 25px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.stat-icon {
  font-size: 42px;
}

/* LABEL */
.stat-label {
  font-size: 16px;
  color: #ffffff;
  letter-spacing: 2px;
}

/* ANGKA BESAR */
.stat-number {
  font-size: 72px;
  font-weight: 900;
  background: linear-gradient(135deg, #ffffff 0%, #ffdddd 100%);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
}

.stat-number::after {
  content: '+';
  position: absolute;
  right: -35px;
  top: 0;
  font-size: 48px;
  color: #ff0000; /* MERAH */
}

/* DESKRIPSI */
.stat-desc {
  font-size: 15px;
  color: #bbbbbb;
}

/* BADGE */
.stat-change {
  background: #ff000020;
  color: #ff0000;
  border: 1px solid #ff4b4b;
  padding: 10px 22px;
  border-radius: 50px;
  display: inline-flex;
  gap: 8px;
  font-weight: 600;
}

.stats-badge {
  background: #ffffff;
  color: #000000;
  border: 2px solid #ff0000;
  padding: 12px 28px;
  border-radius: 50px;
  margin-top: 40px;
  font-weight: 700;
  box-shadow: 0 4px 15px rgba(255,0,0,0.3);
}

    /* === ROW OF TWO BOXES === */
    .section-row {
      max-width: 90%;
      margin: 30px auto;
      display: flex;
      gap: 25px;
    }

    .section-box {
      background: #ffffff;
      border: 2px solid #000000;
      border-radius: 12px;
      padding: 25px;
      flex: 1;
      display: flex;
      align-items: center;
      gap: 25px; /* space between image & text */
    }

    /* IMAGE ON LEFT */
    .circle-img {
      width: 120px;
      height: 120px;
      border-radius: 50%;
      background: #d9d9d9;
      flex-shrink: 0;
    }

    .section-box p {
      text-align: justify;
      margin: 0;
    }

    /* BOTTOM BOX */
    .bottom-box {
      max-width: 90%;
      margin: 40px auto;
      background: #f2f2f2;
      border-radius: 12px;
      padding: 25px 30px;
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 20px;
      border-left: 6px solid #ff0000;
    }

    .bottom-text {
      flex: 1;
      text-align: justify;
    }

    .bottom-logo {
      height: 28px;
      flex-shrink: 0;
    }

    footer {
      margin-top: 40px;
      padding: 15px;
      background: #000000;
      text-align: center;
      border-top: 2px solid #ff0000;
      color: #ffffff;
    }

    footer img {
      height: 20px;
      filter: brightness(0) invert(1);
      margin: 0 5px;
    }

    /* RESPONSIVE */
    @media(max-width: 768px) {
      .section-row {
        flex-direction: column;
      }
      .section-box {
        flex-direction: column;
        text-align: center;
      }
    }

  </style>
</head>

<body>

  <header>
    <img src="../assets/image/logo.png" alt="PSI Logo">
    <nav>
      <a href="dashboard.php" class="active">Dashboard</a>
      <a href="kontak.php">Kontak</a>
      <a href="profil.php">Profil</a>
    </nav>
  </header>

  <!-- HERO -->
<section class="hero">
  <div class="hero-slide active" style="background-image: url('../assets/image/index.jpeg');"></div>
  <div class="hero-slide" style="background-image: url('../assets/image/index1.jpeg');"></div>
  <div class="hero-slide" style="background-image: url('../assets/image/index2.jpeg');"></div>
</section>

<!-- 🔥 SECTION STATISTIK BARU -->
<section class="stats-section">
  <div class="stats-inner">
    <h2>Jumlah Keluarga yang Telah Dibantu oleh Tim Kami</h2>
    <p class="subtitle">Data Real-Time Keluarga yang Telah Terverifikasi dan Menerima Bantuan dari PSI</p>
    
    <div class="stats-container">
      <div class="stat-item">
        <div class="stat-icon-box">
          <div class="stat-icon">👨‍👩‍👧‍👦</div>
        </div>
        <div class="stat-label">Total Keluarga Terbantu</div>
        <div class="stat-number-wrapper">
          <div class="stat-number"><?php echo number_format($totalVerifikasi, 0, ',', '.'); ?></div>
        </div>
        <div class="stat-desc">
          Keluarga yang telah menerima bantuan dan pendampingan dari tim PSI
        </div>
        <div class="stat-change">Terverifikasi & Tervalidasi</div>
      </div>
    </div>

    <center>
      <div class="stats-badge">
        Data Diperbarui Secara Real-Time
      </div>
    </center>
  </div>
</section>
  
</section>
<div class="top-content">
  <h1>PSI Peduli - Hadir Untuk Melayani Masyarakat</h1>
  <div class="underline"></div>
</div>

  <!-- ✅ TWO BOXES SIDE BY SIDE WITH IMAGE LEFT -->
  <div class="section-row">

    <div class="section-box">
      <div class="circle-img"></div>
      <p>
        Kita percaya, solidaritas bukan sekadar kata. Melalui program bantuan dana sosial,
        PSI berupaya meringankan beban masyarakat yang membutuhkan dengan proses terbuka,
        cepat, dan tanpa diskriminasi.
      </p>
    </div>

    <div class="section-box">
      <div class="circle-img"></div>
      <p>
        Sebagai bagian dari komitmen untuk mendukung masyarakat, PSI menghadirkan program bantuan sosial
        yang dikelola dengan prinsip keterbukaan, keadilan, dan akuntabilitas. Melalui sistem pendataan
        yang terintegrasi, kami memastikan bantuan tepat sasaran dan bermanfaat.
      </p>
    </div>

  </div>

  <!-- BOTTOM BOX -->
  <section class="bottom-box">
    <div class="bottom-text">
      PSI meyakini bahwa solidaritas adalah kekuatan utama dalam membangun kehidupan yang lebih baik
      bagi masyarakat. Melalui program bantuan sosial, PSI berkomitmen untuk hadir dengan cara yang
      terbuka, adil, dan tanpa diskriminasi.
    </div>
    <img src="../assets/image/psiputih.png" class="bottom-logo" alt="PSI Logo">
  </section>

  <footer>
    <img src="../assets/image/logodprd.png" alt="DPRD Logo">
    <img src="../assets/image/psiputih.png" alt="PSI Logo">
    Hak cipta © 2025 - Partai Solidaritas Indonesia
  </footer>

  <script>
    const slides = document.querySelectorAll('.hero-slide');
    let current = 0;

    setInterval(() => {
      slides[current].classList.remove('active');
      current = (current + 1) % slides.length;
      slides[current].classList.add('active');
    }, 4000);
  </script>

</body>
</html>