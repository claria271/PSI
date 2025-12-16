<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Media Sosial - JOSIAH MICHAEL</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">

  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
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
      overflow-x: hidden;
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
      transition: .25s;
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

    /* HERO SECTION */
    .hero-social {
      padding: 100px 40px 80px;
      background: linear-gradient(135deg, #ffffff 0%, #f9fafb 50%, #ffffff 100%);
      position: relative;
      overflow: hidden;
    }

    .hero-social::before {
      content: "";
      position: absolute;
      top: -50%;
      left: -10%;
      width: 500px;
      height: 500px;
      background: radial-gradient(circle, rgba(220,38,38,0.08), transparent 70%);
      border-radius: 50%;
      pointer-events: none;
    }

    .hero-social::after {
      content: "";
      position: absolute;
      bottom: -30%;
      right: -5%;
      width: 400px;
      height: 400px;
      background: radial-gradient(circle, rgba(0,0,0,0.06), transparent 70%);
      border-radius: 50%;
      pointer-events: none;
    }

    .hero-content {
      max-width: 1180px;
      margin: 0 auto;
      text-align: center;
      position: relative;
      z-index: 2;
    }

    .hero-tag {
      display: inline-flex;
      align-items: center;
      gap: 8px;
      padding: 10px 20px;
      background: rgba(220,38,38,0.10);
      border: 1px solid rgba(220,38,38,0.20);
      border-radius: 999px;
      color: #dc2626;
      font-size: 13px;
      font-weight: 700;
      letter-spacing: 0.05em;
      margin-bottom: 20px;
      animation: fadeInDown 0.8s ease;
    }

    .hero-title {
      font-size: 56px;
      font-weight: 900;
      line-height: 1.2;
      margin-bottom: 20px;
      color: #0f172a;
      animation: fadeInUp 0.8s ease 0.2s backwards;
    }

    .hero-title span {
      background: linear-gradient(135deg, #dc2626, #991b1b);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      background-clip: text;
    }

    .hero-desc {
      font-size: 18px;
      line-height: 1.8;
      color: #64748b;
      max-width: 700px;
      margin: 0 auto;
      animation: fadeInUp 0.8s ease 0.4s backwards;
    }

    /* SOCIAL GRID */
    .social-section {
      padding: 80px 40px 100px;
      background: #ffffff;
    }

    .social-inner {
      max-width: 1180px;
      margin: 0 auto;
    }

    .section-header {
      text-align: center;
      margin-bottom: 50px;
    }

    .section-title {
      font-size: 42px;
      font-weight: 900;
      margin-bottom: 16px;
      color: #0f172a;
    }

    .section-subtitle {
      font-size: 16px;
      color: #64748b;
      max-width: 600px;
      margin: 0 auto;
      line-height: 1.7;
    }

    .social-grid {
      display: grid;
      grid-template-columns: repeat(2, 1fr);
      gap: 28px;
      margin-top: 40px;
      max-width: 900px;
      margin-left: auto;
      margin-right: auto;
    }

    .social-card {
      background: #ffffff;
      border: 1px solid rgba(15,23,42,0.08);
      border-radius: 20px;
      padding: 40px 35px;
      box-shadow: 0 10px 40px rgba(15,23,42,0.06);
      transition: all 0.35s cubic-bezier(0.4, 0, 0.2, 1);
      position: relative;
      overflow: hidden;
      display: flex;
      flex-direction: column;
    }

    .social-card::before {
      content: "";
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      height: 4px;
      background: linear-gradient(90deg, transparent, currentColor, transparent);
      opacity: 0;
      transition: opacity 0.35s;
    }

    .social-card:hover {
      transform: translateY(-8px);
      box-shadow: 0 20px 60px rgba(15,23,42,0.12);
      border-color: currentColor;
    }

    .social-card:hover::before {
      opacity: 1;
    }

    /* Platform Colors */
    .social-card.facebook { color: #1877f2; }
    .social-card.instagram { color: #E4405F; }
    .social-card.whatsapp { color: #25D366; }
    .social-card.tiktok { color: #000000; }

    .social-icon {
      width: 70px;
      height: 70px;
      border-radius: 16px;
      display: grid;
      place-items: center;
      font-size: 32px;
      margin-bottom: 24px;
      background: rgba(15,23,42,0.04);
      color: currentColor;
      transition: all 0.35s;
    }

    .social-card:hover .social-icon {
      background: currentColor;
      color: white;
      transform: scale(1.08) rotate(5deg);
    }

    .social-name {
      font-size: 24px;
      font-weight: 800;
      margin-bottom: 10px;
      color: #0f172a;
    }

    .social-username {
      font-size: 14px;
      color: #6b7280;
      margin-bottom: 16px;
      font-weight: 500;
    }

    .social-desc {
      font-size: 15px;
      line-height: 1.7;
      color: #64748b;
      margin-bottom: 24px;
      flex: 1;
    }

    .social-btn {
      width: 100%;
      padding: 14px 24px;
      border-radius: 12px;
      border: 2px solid currentColor;
      background: transparent;
      color: currentColor;
      font-weight: 800;
      font-size: 15px;
      cursor: pointer;
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 10px;
      transition: all 0.35s;
      text-align: center;
    }

    .social-btn:hover {
      background: currentColor;
      color: white;
      transform: translateY(-2px);
    }

    /* ANIMATIONS */
    @keyframes fadeInDown {
      from {
        opacity: 0;
        transform: translateY(-20px);
      }
      to {
        opacity: 1;
        transform: translateY(0);
      }
    }

    @keyframes fadeInUp {
      from {
        opacity: 0;
        transform: translateY(20px);
      }
      to {
        opacity: 1;
        transform: translateY(0);
      }
    }

    .reveal {
      opacity: 0;
      transform: translateY(30px);
      transition: all 0.8s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .reveal.show {
      opacity: 1;
      transform: translateY(0);
    }

    /* FOOTER */
    .footer {
      padding: 40px 40px;
      background: linear-gradient(to right, #ffffff, #f3f4f6);
      border-top: 1px solid rgba(15,23,42,0.08);
    }

    .footer-inner {
      max-width: 1180px;
      margin: 0 auto;
      text-align: center;
    }

    .footer-text {
      font-size: 14px;
      color: #6b7280;
      margin-bottom: 12px;
    }

    .footer-social {
      display: flex;
      justify-content: center;
      gap: 16px;
      margin-top: 20px;
    }

    .footer-social a {
      width: 40px;
      height: 40px;
      border-radius: 10px;
      display: grid;
      place-items: center;
      background: rgba(220,38,38,0.08);
      color: #dc2626;
      transition: all 0.3s;
      border: 1px solid rgba(220,38,38,0.14);
    }

    .footer-social a:hover {
      background: #dc2626;
      color: white;
      transform: translateY(-2px);
    }

    /* RESPONSIVE */
    @media (max-width: 768px) {
      header {
        padding: 14px 20px;
      }

      nav {
        display: none;
      }

      .hero-social {
        padding: 70px 20px 50px;
      }

      .hero-title {
        font-size: 36px;
      }

      .hero-desc {
        font-size: 16px;
      }

      .social-section {
        padding: 60px 20px 80px;
      }

      .section-title {
        font-size: 32px;
      }

      .social-grid {
        grid-template-columns: 1fr;
        gap: 20px;
      }

      .footer {
        padding: 30px 20px;
      }
    }
  </style>
</head>

<body>

<header>
  <div class="nav-left">
    <div class="nav-logo-image">
      <img src="assets/image/logo.png" alt="Logo">
    </div>
    <div class="nav-brand">Josiah Michael</div>
  </div>

  <nav>
    <a href="index.php">Tentang Josiah</a>
    <a href="index.php#news">Berita</a>
    <a href="sosmed.php" class="active">Media Sosial</a>
  </nav>
</header>

<!-- HERO SECTION -->
<section class="hero-social">
  <div class="hero-content">
    <div class="hero-tag">
      <i class="fa-solid fa-share-nodes"></i>
      Terhubung Dengan Kami
    </div>
    
    <h1 class="hero-title">
      Mari Terhubung di<br>
      <span>Media Sosial</span>
    </h1>
    
    <p class="hero-desc">
      Ikuti perkembangan terbaru, berita, dan kegiatan kami melalui berbagai platform media sosial. 
      Mari berdiskusi dan berkontribusi untuk kemajuan Surabaya bersama-sama.
    </p>
  </div>
</section>

<!-- SOCIAL MEDIA GRID -->
<section class="social-section" id="social">
  <div class="social-inner">
    
    <div class="section-header reveal">
      <h2 class="section-title">Platform Media Sosial</h2>
      <p class="section-subtitle">
        Temukan kami di berbagai platform media sosial dan dapatkan update terbaru setiap hari
      </p>
    </div>

    <div class="social-grid">
      
      <!-- FACEBOOK -->
      <div class="social-card facebook reveal">
        <div class="social-icon">
          <i class="fa-brands fa-facebook-f"></i>
        </div>
        <h3 class="social-name">Facebook</h3>
        <div class="social-username">@liem.k.siong</div>
        <p class="social-desc">
          Ikuti halaman Facebook untuk mendapatkan berita terkini, foto kegiatan, dan interaksi langsung dengan masyarakat. Dapatkan update kegiatan dan program kerja kami setiap hari.
        </p>
        <a href="https://www.facebook.com/liem.k.siong?mibextid=LQQJ4d" target="_blank" rel="noopener" class="social-btn">
          <i class="fa-brands fa-facebook-f"></i>
          Kunjungi Facebook
        </a>
      </div>

      <!-- INSTAGRAM -->
      <div class="social-card instagram reveal">
        <div class="social-icon">
          <i class="fa-brands fa-instagram"></i>
        </div>
        <h3 class="social-name">Instagram</h3>
        <div class="social-username">@josiahmichael.id</div>
        <p class="social-desc">
          Follow Instagram untuk melihat momen kegiatan, story updates, dan foto-foto dokumentasi program kerja kami. Jangan lewatkan konten menarik setiap hari.
        </p>
        <a href="https://www.instagram.com/josiahmichael.id/" target="_blank" rel="noopener" class="social-btn">
          <i class="fa-brands fa-instagram"></i>
          Kunjungi Instagram
        </a>
      </div>

      <!-- WHATSAPP -->
      <div class="social-card whatsapp reveal">
        <div class="social-icon">
          <i class="fa-brands fa-whatsapp"></i>
        </div>
        <h3 class="social-name">WhatsApp</h3>
        <div class="social-username">+62 812-1750-1502</div>
        <p class="social-desc">
          Hubungi kami langsung melalui WhatsApp untuk konsultasi, pengaduan, atau diskusi mengenai aspirasi masyarakat. Kami siap melayani dengan cepat dan responsif.
        </p>
        <a href="https://api.whatsapp.com/send/?phone=%2B6281217501502&text=Hallo+pak+Josiah+Michael.+Ada+yang+ingin+kami+sampaikan+kepada+Bapak.&type=phone_number&app_absent=0" target="_blank" rel="noopener" class="social-btn">
          <i class="fa-brands fa-whatsapp"></i>
          Chat WhatsApp
        </a>
      </div>

      <!-- TIKTOK -->
      <div class="social-card tiktok reveal">
        <div class="social-icon">
          <i class="fa-brands fa-tiktok"></i>
        </div>
        <h3 class="social-name">TikTok</h3>
        <div class="social-username">@josiahmichael.id</div>
        <p class="social-desc">
          Follow TikTok untuk konten video pendek, behind the scenes, dan informasi edukatif dalam format yang menarik dan mudah dipahami oleh semua kalangan.
        </p>
        <a href="https://www.tiktok.com/@josiahmichael.id" target="_blank" rel="noopener" class="social-btn">
          <i class="fa-brands fa-tiktok"></i>
          Kunjungi TikTok
        </a>
      </div>

    </div>
  </div>
</section>

<!-- FOOTER -->
<footer class="footer">
  <div class="footer-inner">
    <div class="footer-text">
      © 2024 Josiah Michael - Ketua Fraksi PSI Surabaya
    </div>
    <div class="footer-text">
      Melayani dengan Hati untuk Kemajuan Surabaya
    </div>
    
    <div class="footer-social">
      <a href="https://www.facebook.com/liem.k.siong?mibextid=LQQJ4d" target="_blank" rel="noopener" title="Facebook">
        <i class="fa-brands fa-facebook-f"></i>
      </a>
      <a href="https://www.instagram.com/josiahmichael.id/" target="_blank" rel="noopener" title="Instagram">
        <i class="fa-brands fa-instagram"></i>
      </a>
      <a href="https://api.whatsapp.com/send/?phone=%2B6281217501502" target="_blank" rel="noopener" title="WhatsApp">
        <i class="fa-brands fa-whatsapp"></i>
      </a>
      <a href="https://www.tiktok.com/@josiahmichael.id" target="_blank" rel="noopener" title="TikTok">
        <i class="fa-brands fa-tiktok"></i>
      </a>
    </div>
  </div>
</footer>

<script>
  // Scroll reveal animation
  const observerOptions = {
    threshold: 0.1,
    rootMargin: '0px 0px -50px 0px'
  };

  const observer = new IntersectionObserver((entries) => {
    entries.forEach((entry, index) => {
      if (entry.isIntersecting) {
        setTimeout(() => {
          entry.target.classList.add('show');
        }, index * 100);
        observer.unobserve(entry.target);
      }
    });
  }, observerOptions);

  document.querySelectorAll('.reveal').forEach(el => {
    observer.observe(el);
  });

  // Smooth scroll
  document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function (e) {
      e.preventDefault();
      const target = document.querySelector(this.getAttribute('href'));
      if (target) {
        target.scrollIntoView({
          behavior: 'smooth',
          block: 'start'
        });
      }
    });
  });
</script>

</body>
</html>