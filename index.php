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

    a { text-decoration: none; color: inherit; }

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
      cursor: pointer;
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

    /* KANAN */
    .hero-right h3 {
      font-size: 15px;
      font-weight: 600;
      letter-spacing: .2em;
      color: #6b7280;
      margin-bottom: 10px;
      margin-top: 70px;
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

    /* =========================================================
       NEWS SECTION (NYAMBUNG DI BAWAH HERO)
    ========================================================= */
    .news-section {
      position: relative;
      padding: 70px 40px 90px;
      background: linear-gradient(to bottom, #ffffff 0%, #f3f4f6 40%, #ffffff 100%);
      overflow: hidden;
    }

    .news-section::before{
      content:"";
      position:absolute;
      inset:0;
      background:
        radial-gradient(circle at 20% 20%, rgba(220,38,38,.10), transparent 55%),
        radial-gradient(circle at 80% 30%, rgba(0,0,0,.10), transparent 60%),
        radial-gradient(circle at 50% 90%, rgba(220,38,38,.08), transparent 55%);
      pointer-events:none;
    }

    .news-inner{
      max-width: 1180px;
      margin: 0 auto;
      position: relative;
      z-index: 1;
    }

    .news-head{
      display:flex;
      justify-content:space-between;
      align-items:flex-end;
      gap:16px;
      margin-bottom: 22px;
    }

    .news-title{
      font-size: 28px;
      font-weight: 800;
      letter-spacing: .2px;
      color:#0f172a;
    }

    .news-sub{
      margin-top: 6px;
      font-size: 13px;
      color:#6b7280;
      line-height: 1.6;
      max-width: 680px;
    }

    .news-actions{
      display:flex;
      gap:10px;
      flex-wrap:wrap;
      align-items:center;
      justify-content:flex-end;
    }

    .news-chip{
      display:inline-flex;
      align-items:center;
      gap:8px;
      padding:10px 14px;
      border-radius:999px;
      background: rgba(255,255,255,.88);
      border: 1px solid rgba(15,23,42,.10);
      box-shadow: 0 10px 20px rgba(15,23,42,.08);
      font-size: 13px;
      font-weight: 700;
      cursor:pointer;
      transition:.25s;
      user-select:none;
    }

    .news-chip:hover{
      transform: translateY(-2px);
      border-color: rgba(220,38,38,.35);
      box-shadow: 0 14px 30px rgba(220,38,38,.12);
    }

    .news-grid{
      display:grid;
      grid-template-columns: repeat(4, 1fr);
      gap: 18px;
      margin-top: 16px;
    }

    .news-card{
      background: rgba(255,255,255,.95);
      border: 1px solid rgba(15,23,42,.10);
      border-radius: 18px;
      overflow:hidden;
      box-shadow: 0 10px 28px rgba(15,23,42,.10);
      transition: .25s;
      transform: translateY(0);
    }

    .news-card:hover{
      transform: translateY(-6px);
      border-color: rgba(220,38,38,.35);
      box-shadow: 0 16px 45px rgba(15,23,42,.14);
    }

    .news-thumb{
      height: 170px;
      position: relative;
      overflow:hidden;
      background: #111827;
    }

    .news-thumb img{
      width:100%;
      height:100%;
      object-fit: cover;
      transform: scale(1.02);
      transition: transform .35s;
      display:block;
    }

    .news-card:hover .news-thumb img{
      transform: scale(1.08);
    }

    .news-thumb::after{
      content:"";
      position:absolute;
      left:0; right:0; bottom:0;
      height: 60%;
      background: linear-gradient(to top, rgba(0,0,0,.65), transparent);
      opacity:.9;
      pointer-events:none;
    }

    .news-body{
      padding: 14px 14px 16px;
    }

    .news-date{
      font-size: 12px;
      color:#6b7280;
      font-weight:600;
      margin-bottom: 8px;
    }

    .news-h{
      font-size: 16px;
      font-weight: 800;
      line-height: 1.35;
      color:#0f172a;
      margin-bottom: 10px;
      min-height: 44px;
    }

    .news-desc{
      font-size: 13px;
      color:#4b5563;
      line-height: 1.6;
      margin-bottom: 12px;
      min-height: 42px;
    }

    .news-meta{
      display:flex;
      align-items:center;
      justify-content:space-between;
      gap:10px;
    }

    .news-tag{
      display:inline-flex;
      align-items:center;
      gap:6px;
      padding: 6px 10px;
      border-radius: 999px;
      font-size: 12px;
      font-weight: 700;
      background: rgba(220,38,38,.10);
      color:#b91c1c;
      border: 1px solid rgba(220,38,38,.18);
    }

    .news-read{
      font-size: 12px;
      font-weight: 800;
      color:#111827;
      display:inline-flex;
      align-items:center;
      gap:8px;
      padding: 8px 10px;
      border-radius: 12px;
      background: rgba(15,23,42,.04);
      border:1px solid rgba(15,23,42,.08);
      transition:.25s;
    }

    .news-read:hover{
      background: rgba(220,38,38,.10);
      border-color: rgba(220,38,38,.22);
      color:#b91c1c;
      transform: translateY(-1px);
    }

    /* =========================================================
       SCROLL REVEAL EFFECT
    ========================================================= */
    .reveal{
      opacity: 0;
      transform: translateY(18px);
      transition: opacity .75s ease, transform .75s ease;
      will-change: opacity, transform;
    }
    .reveal.show{
      opacity: 1;
      transform: translateY(0);
    }
    .reveal.delay-1{ transition-delay: .08s; }
    .reveal.delay-2{ transition-delay: .16s; }
    .reveal.delay-3{ transition-delay: .24s; }
    .reveal.delay-4{ transition-delay: .32s; }

    /* RESPONSIVE */
    @media (max-width: 1100px){
      .news-grid{ grid-template-columns: repeat(2, 1fr); }
    }
    @media (max-width: 650px){
      header{ padding: 14px 18px; }
      .hero{ padding: 20px 18px 80px; }
      .news-section{ padding: 55px 18px 80px; }
      .news-grid{ grid-template-columns: 1fr; }
      nav{ display:none; } /* biar ga numpuk di mobile */
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
    <a class="active">Tentang Josiah</a>
    <a>Berita</a>
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
        <a href="pengaduan.php"><button class="btn-aduan"><i class="fa-solid fa-envelope"></i> Form Aduan</button></a>
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

<!-- =========================
     NEWS SECTION (DI BAWAH HERO)
========================= -->
<section class="news-section" id="news">
  <div class="news-inner">

    <div class="news-head reveal">
      <div>
        <div class="news-title">Berita Terbaru</div>
        <div class="news-sub">
          Update kegiatan dan informasi terbaru. Tampilan dibuat nyambung dengan halaman utama dan akan muncul animasi saat kamu scroll.
        </div>
      </div>

      <div class="news-actions">
        <div class="news-chip" onclick="window.scrollTo({top: 0, behavior: 'smooth'});">
          <i class="fa-solid fa-arrow-up"></i> Ke Atas
        </div>
        <div class="news-chip" onclick="document.getElementById('news').scrollIntoView({behavior:'smooth'});">
          <i class="fa-solid fa-newspaper"></i> Lihat Berita
        </div>
      </div>
    </div>

    <div class="news-grid">
      <!-- Card 1 -->
      <article class="news-card reveal delay-1">
        <div class="news-thumb">
          <img src="assets/image/berita1.jpg" alt="Berita 1" onerror="this.src='https://images.unsplash.com/photo-1521295121783-8a321d551ad2?auto=format&fit=crop&w=1200&q=60';">
        </div>
        <div class="news-body">
          <div class="news-date">Desember 13, 2025</div>
          <div class="news-h">Prabowo Pastikan Pasokan Pangan Cukup di Lokasi Terdampak Bencana</div>
          <div class="news-desc">Koordinasi lintas lembaga dipastikan berjalan agar kebutuhan warga tetap terpenuhi.</div>
          <div class="news-meta">
            <div class="news-tag"><i class="fa-solid fa-bolt"></i> Update</div>
            <a class="news-read" href="#"><span>Baca</span> <i class="fa-solid fa-arrow-right"></i></a>
          </div>
        </div>
      </article>

      <!-- Card 2 -->
      <article class="news-card reveal delay-2">
        <div class="news-thumb">
          <img src="assets/image/berita2.jpg" alt="Berita 2" onerror="this.src='https://images.unsplash.com/photo-1529107386315-e1a2ed48a620?auto=format&fit=crop&w=1200&q=60';">
        </div>
        <div class="news-body">
          <div class="news-date">Desember 13, 2025</div>
          <div class="news-h">Target Pemulihan Listrik Dipercepat untuk Wilayah Terdampak</div>
          <div class="news-desc">Tim teknis disiagakan untuk mempercepat pemulihan layanan dasar di lokasi bencana.</div>
          <div class="news-meta">
            <div class="news-tag"><i class="fa-solid fa-plug"></i> Infrastruktur</div>
            <a class="news-read" href="#"><span>Baca</span> <i class="fa-solid fa-arrow-right"></i></a>
          </div>
        </div>
      </article>

      <!-- Card 3 -->
      <article class="news-card reveal delay-3">
        <div class="news-thumb">
          <img src="assets/image/berita3.jpg" alt="Berita 3" onerror="this.src='https://images.unsplash.com/photo-1580674285054-bed31e145f59?auto=format&fit=crop&w=1200&q=60';">
        </div>
        <div class="news-body">
          <div class="news-date">Desember 13, 2025</div>
          <div class="news-h">Apresiasi Gotong Royong: Semua Pihak Bersatu Hadapi Bencana</div>
          <div class="news-desc">Kolaborasi relawan, pemerintah, dan masyarakat jadi kunci percepatan penanganan.</div>
          <div class="news-meta">
            <div class="news-tag"><i class="fa-solid fa-people-group"></i> Sosial</div>
            <a class="news-read" href="#"><span>Baca</span> <i class="fa-solid fa-arrow-right"></i></a>
          </div>
        </div>
      </article>

      <!-- Card 4 -->
      <article class="news-card reveal delay-4">
        <div class="news-thumb">
          <img src="assets/image/berita4.jpg" alt="Berita 4" onerror="this.src='https://images.unsplash.com/photo-1469474968028-56623f02e42e?auto=format&fit=crop&w=1200&q=60';">
        </div>
        <div class="news-body">
          <div class="news-date">Desember 13, 2025</div>
          <div class="news-h">Tegas! Penindakan Pembalakan Liar Diperkuat di Sejumlah Titik</div>
          <div class="news-desc">Upaya pengawasan ditingkatkan untuk melindungi kawasan hutan dan lingkungan.</div>
          <div class="news-meta">
            <div class="news-tag"><i class="fa-solid fa-tree"></i> Lingkungan</div>
            <a class="news-read" href="#"><span>Baca</span> <i class="fa-solid fa-arrow-right"></i></a>
          </div>
        </div>
      </article>

      <!-- Card 5 -->
      <article class="news-card reveal delay-1">
        <div class="news-thumb">
          <img src="assets/image/berita5.jpg" alt="Berita 5" onerror="this.src='https://images.unsplash.com/photo-1551836022-d5d88e9218df?auto=format&fit=crop&w=1200&q=60';">
        </div>
        <div class="news-body">
          <div class="news-date">Desember 13, 2025</div>
          <div class="news-h">Solidaritas Nasional Menguat, Respons Bencana Terus Dipercepat</div>
          <div class="news-desc">Distribusi bantuan dan layanan kesehatan berjalan bertahap sesuai prioritas.</div>
          <div class="news-meta">
            <div class="news-tag"><i class="fa-solid fa-hand-holding-heart"></i> Kemanusiaan</div>
            <a class="news-read" href="#"><span>Baca</span> <i class="fa-solid fa-arrow-right"></i></a>
          </div>
        </div>
      </article>

      <!-- Card 6 -->
      <article class="news-card reveal delay-2">
        <div class="news-thumb">
          <img src="assets/image/berita6.jpg" alt="Berita 6" onerror="this.src='https://images.unsplash.com/photo-1520607162513-77705c0f0d4a?auto=format&fit=crop&w=1200&q=60';">
        </div>
        <div class="news-body">
          <div class="news-date">Desember 13, 2025</div>
          <div class="news-h">Posko Evakuasi Dipadati Warga, Dukungan Logistik Terus Ditambah</div>
          <div class="news-desc">Kebutuhan harian disuplai berkala, fokus pada kelompok rentan dan anak-anak.</div>
          <div class="news-meta">
            <div class="news-tag"><i class="fa-solid fa-box"></i> Logistik</div>
            <a class="news-read" href="#"><span>Baca</span> <i class="fa-solid fa-arrow-right"></i></a>
          </div>
        </div>
      </article>

      <!-- Card 7 -->
      <article class="news-card reveal delay-3">
        <div class="news-thumb">
          <img src="assets/image/berita7.jpg" alt="Berita 7" onerror="this.src='https://images.unsplash.com/photo-1500530855697-b586d89ba3ee?auto=format&fit=crop&w=1200&q=60';">
        </div>
        <div class="news-body">
          <div class="news-date">Desember 13, 2025</div>
          <div class="news-h">Kunjungan Lapangan: Evaluasi Infrastruktur dan Kebutuhan Warga</div>
          <div class="news-desc">Data lapangan dipakai untuk menyusun prioritas tindak lanjut pemulihan.</div>
          <div class="news-meta">
            <div class="news-tag"><i class="fa-solid fa-clipboard-check"></i> Monitoring</div>
            <a class="news-read" href="#"><span>Baca</span> <i class="fa-solid fa-arrow-right"></i></a>
          </div>
        </div>
      </article>

      <!-- Card 8 -->
      <article class="news-card reveal delay-4">
        <div class="news-thumb">
          <img src="assets/image/berita8.jpg" alt="Berita 8" onerror="this.src='https://images.unsplash.com/photo-1526481280695-3c687fd5432c?auto=format&fit=crop&w=1200&q=60';">
        </div>
        <div class="news-body">
          <div class="news-date">Desember 13, 2025</div>
          <div class="news-h">Seruan Doa dan Dukungan Masyarakat Menguat di Lokasi Evakuasi</div>
          <div class="news-desc">Warga saling menguatkan, kegiatan sosial dilakukan untuk menjaga semangat.</div>
          <div class="news-meta">
            <div class="news-tag"><i class="fa-solid fa-hands-praying"></i> Komunitas</div>
            <a class="news-read" href="#"><span>Baca</span> <i class="fa-solid fa-arrow-right"></i></a>
          </div>
        </div>
      </article>
    </div>

  </div>
</section>

<script>
  // Scroll reveal pakai IntersectionObserver (lebih halus & ringan)
  const reveals = document.querySelectorAll('.reveal');

  const io = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        entry.target.classList.add('show');
        io.unobserve(entry.target); // muncul sekali saja
      }
    });
  }, { threshold: 0.12 });

  reveals.forEach(el => io.observe(el));
</script>

</body>
</html>
