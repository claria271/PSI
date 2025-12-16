<?php
// user/dashboard.php
require_once __DIR__ . '/../koneksi/config.php';

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
$conn->set_charset('utf8mb4');

function e($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }

// ==========================
// AMBIL DATA BERITA (PUBLISH)
// ==========================
$beritaList = [];
try {
  $stmt = $conn->prepare("
    SELECT id, judul, ringkasan, link_berita, gambar, tanggal
    FROM berita
    WHERE status = 'publish'
    ORDER BY tanggal DESC, id DESC
    LIMIT 12
  ");
  $stmt->execute();
  $res = $stmt->get_result();
  while ($row = $res->fetch_assoc()) {
    $beritaList[] = $row;
  }
} catch (Throwable $e) {
  $beritaList = [];
}

// Lokasi gambar
$uploadDirUrl = '../uploads/berita/';
$fallbackImg  = 'https://images.unsplash.com/photo-1529107386315-e1a2ed48a620?auto=format&fit=crop&w=1400&q=60';

function formatTanggalIndo($ymd){
  if (!$ymd) return '';
  $bulan = [
    1=>'Januari',2=>'Februari',3=>'Maret',4=>'April',5=>'Mei',6=>'Juni',
    7=>'Juli',8=>'Agustus',9=>'September',10=>'Oktober',11=>'November',12=>'Desember'
  ];
  $ts = strtotime($ymd);
  if (!$ts) return e($ymd);
  return date('d',$ts).' '.($bulan[(int)date('m',$ts)]).' '.date('Y',$ts);
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>User Dashboard</title>

<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

<style>
*{margin:0;padding:0;box-sizing:border-box}
body{font-family:'Poppins',sans-serif;background:#fff;color:#111827}
a{text-decoration:none;color:inherit}

/* =======================
   NAVBAR
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
  height:62px; /* LOGO DIBESARKAN LAGI */
  filter:drop-shadow(0 4px 10px rgba(0,0,0,.35));
}

.dash-menu{
  display:flex;
  gap:30px;
}

.dash-menu a{
  color:#000000;
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

/* =======================
   CONTENT
======================= */
.wrap{
  max-width:1200px;
  margin:30px auto;
  padding:0 20px;
}

.head h2{
  font-size:28px;
  font-weight:800;
}

.muted{
  margin-top:6px;
  font-size:13px;
  color:#6b7280;
  max-width:720px;
}

/* =======================
   NEWS GRID
======================= */
.news-grid{
  margin-top:24px;
  display:grid;
  grid-template-columns:repeat(2,1fr);
  gap:22px;
}

.news-item{
  background:#fff;
  border-radius:14px;
  overflow:hidden;
  box-shadow:0 10px 30px rgba(15,23,42,.10);
  display:grid;
  grid-template-columns:1.05fr .95fr;
}

.thumb{background:#000}
.thumb img{
  width:100%;
  height:100%;
  object-fit:cover;
}

.content{
  padding:28px;
  display:flex;
  flex-direction:column;
  gap:12px;
}

.news-date{
  font-size:12px;
  font-weight:700;
  color:#6b7280;
}

.news-title{
  font-size:22px;
  font-weight:800;
  line-height:1.3;
}

.news-desc{
  font-size:14px;
  line-height:1.8;
  color:#4b5563;
}

.learnmore{
  margin-top:6px;
  display:inline-flex;
  align-items:center;
  gap:10px;
  font-weight:800;
  color:#0f172a;
  transition:.2s;
}
.learnmore:hover{
  color:#dc2626;
  transform:translateX(3px);
}

.empty{
  grid-column:1/-1;
  padding:18px;
  border-radius:14px;
  border:1px solid #e5e7eb;
  background:#fafafa;
  color:#6b7280;
  font-weight:700;
}

@media(max-width:980px){
  .news-grid{grid-template-columns:1fr}
  .news-item{grid-template-columns:1fr}
}
</style>
</head>

<body>

<header class="dash-navbar">
  <div class="dash-left">
    <img src="../assets/image/logou.png" alt="PSI Logo">
  </div>

  <nav class="dash-menu">
    <a href="dashboard.php" class="active">Beranda</a>
    <a href="kontak.php">Kontak</a>
    <a href="profil.php">Profil</a>
  </nav>
</header>

<div class="wrap">
  <div class="head">
    <h2>Berita Terbaru</h2>
    <div class="muted">
      Menampilkan berita dengan status <b>publish</b>.  
      Tombol <b>Learn More</b> menuju link berita.
    </div>
  </div>

  <div class="news-grid">
    <?php if($beritaList): foreach($beritaList as $b): 
      $img = $b['gambar'] ? $uploadDirUrl.rawurlencode($b['gambar']) : $fallbackImg;
      $ring = mb_substr($b['ringkasan'],0,180).(mb_strlen($b['ringkasan'])>180?'...':'');
    ?>
      <article class="news-item">
        <div class="thumb">
          <img src="<?=e($img)?>" onerror="this.src='<?=e($fallbackImg)?>'">
        </div>
        <div class="content">
          <div class="news-date"><?=formatTanggalIndo($b['tanggal'])?></div>
          <div class="news-title"><?=e($b['judul'])?></div>
          <div class="news-desc"><?=e($ring)?></div>
          <a class="learnmore" href="<?=e($b['link_berita'])?>" target="_blank">
            Learn More →
          </a>
        </div>
      </article>
    <?php endforeach; else: ?>
      <div class="empty">Belum ada berita publish.</div>
    <?php endif; ?>
  </div>
</div>

</body>
</html>
