<?php
// dashboard.php
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
