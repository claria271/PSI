<?php
session_start();
include '../koneksi/config.php';

// Pastikan admin login
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../user/login.php");
    exit();
}

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
$conn->set_charset('utf8mb4');

// Helper aman untuk output HTML
function e($str) {
    return htmlspecialchars((string)$str, ENT_QUOTES, 'UTF-8');
}

// Ambil data admin berdasarkan session
$admin = null;

if (!empty($_SESSION['alamat_email'])) {
    $stmt = $conn->prepare("SELECT * FROM login WHERE alamat_email = ? LIMIT 1");
    $stmt->bind_param('s', $_SESSION['alamat_email']);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($res->num_rows > 0) {
        $admin = $res->fetch_assoc();
    }
} elseif (!empty($_SESSION['username'])) {
    $stmt = $conn->prepare("SELECT * FROM login WHERE username = ? LIMIT 1");
    $stmt->bind_param('s', $_SESSION['username']);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($res->num_rows > 0) {
        $admin = $res->fetch_assoc();
    }
}

if (!$admin) {
    header("Location: ../user/login.php");
    exit();
}

$adminName = !empty($admin['nama_lengkap'])
    ? $admin['nama_lengkap']
    : (!empty($admin['username']) ? $admin['username'] : 'Admin');

$adminPhoto = !empty($admin['foto'])
    ? '../uploads/' . $admin['foto']
    : '../assets/image/admin_photo.jpg';

// ================== QUERY DATA UNTUK DASHBOARD ================== //

// UMR per orang
define('UMR_PERSON', 4725479);

// 1. TOTAL KELUARGA
$queryTotal = "SELECT COUNT(*) as total FROM keluarga";
$resultTotal = mysqli_query($conn, $queryTotal);
$totalKeluarga = mysqli_fetch_assoc($resultTotal)['total'];

// 2. KELUARGA DIBAWAH UMR (rata-rata per orang < UMR)
$queryDibawah = "SELECT COUNT(*) as total 
                 FROM keluarga 
                 WHERE (total_penghasilan / NULLIF(jumlah_anggota, 0)) < " . UMR_PERSON;
$resultDibawah = mysqli_query($conn, $queryDibawah);
$dibawahUMR = mysqli_fetch_assoc($resultDibawah)['total'];

// 3. KELUARGA DIATAS UMR (rata-rata per orang >= UMR)
$queryDiatas = "SELECT COUNT(*) as total 
                FROM keluarga 
                WHERE (total_penghasilan / NULLIF(jumlah_anggota, 0)) >= " . UMR_PERSON;
$resultDiatas = mysqli_query($conn, $queryDiatas);
$diatasUMR = mysqli_fetch_assoc($resultDiatas)['total'];

// 4. DATA LINE CHART - Jumlah keluarga per bulan (12 bulan terakhir)
$lineChartData = [];
for ($i = 11; $i >= 0; $i--) {
    $month = date('Y-m', strtotime("-$i months"));
    $queryMonth = "SELECT COUNT(*) as total 
                   FROM keluarga 
                   WHERE DATE_FORMAT(created_at, '%Y-%m') = '$month'";
    $resultMonth = mysqli_query($conn, $queryMonth);
    $lineChartData[] = mysqli_fetch_assoc($resultMonth)['total'];
}

// 5. DATA BAR CHART - Dibawah vs Diatas UMR per bulan (4 bulan terakhir)
$barChartDibawah = [];
$barChartDiatas = [];
for ($i = 3; $i >= 0; $i--) {
    $month = date('Y-m', strtotime("-$i months"));
    
    // Dibawah UMR
    $queryBarDibawah = "SELECT COUNT(*) as total 
                        FROM keluarga 
                        WHERE DATE_FORMAT(created_at, '%Y-%m') = '$month'
                        AND (total_penghasilan / NULLIF(jumlah_anggota, 0)) < " . UMR_PERSON;
    $resultBarDibawah = mysqli_query($conn, $queryBarDibawah);
    $barChartDibawah[] = mysqli_fetch_assoc($resultBarDibawah)['total'];
    
    // Diatas UMR
    $queryBarDiatas = "SELECT COUNT(*) as total 
                       FROM keluarga 
                       WHERE DATE_FORMAT(created_at, '%Y-%m') = '$month'
                       AND (total_penghasilan / NULLIF(jumlah_anggota, 0)) >= " . UMR_PERSON;
    $resultBarDiatas = mysqli_query($conn, $queryBarDiatas);
    $barChartDiatas[] = mysqli_fetch_assoc($resultBarDiatas)['total'];
}

// 6. DATA PIE CHART - Jumlah keluarga per Dapil
$pieChartData = [];
$pieChartLabels = [];
$queryPie = "SELECT dapil, COUNT(*) as total 
             FROM keluarga 
             WHERE dapil IS NOT NULL AND dapil != ''
             GROUP BY dapil 
             ORDER BY dapil";
$resultPie = mysqli_query($conn, $queryPie);
while ($row = mysqli_fetch_assoc($resultPie)) {
    $pieChartLabels[] = $row['dapil'];
    $pieChartData[] = $row['total'];
}

// Jika tidak ada data dapil, buat dummy
if (empty($pieChartLabels)) {
    $pieChartLabels = ['Dapil 1', 'Dapil 2', 'Dapil 3', 'Dapil 4', 'Dapil 5'];
    $pieChartData = [0, 0, 0, 0, 0];
}

// 7. DATA UPDATE TERBARU - 10 data terakhir yang diupdate
$queryUpdates = "SELECT nama_lengkap, nik, alamat, dapil, updated_at 
                 FROM keluarga 
                 ORDER BY updated_at DESC 
                 LIMIT 10";
$resultUpdates = mysqli_query($conn, $queryUpdates);

// 8. DATA TAMBAH TERBARU - 10 data terakhir yang ditambahkan
$queryAdded = "SELECT nama_lengkap, nik, alamat, dapil, created_at 
               FROM keluarga 
               ORDER BY created_at DESC 
               LIMIT 10";
$resultAdded = mysqli_query($conn, $queryAdded);

// Generate label bulan untuk chart
$lineChartLabels = [];
for ($i = 11; $i >= 0; $i--) {
    $lineChartLabels[] = date('M', strtotime("-$i months"));
}

$barChartLabels = [];
for ($i = 3; $i >= 0; $i--) {
    $barChartLabels[] = date('M', strtotime("-$i months"));
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Dashboard Admin - PSI</title>
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    body {
      font-family: 'Poppins', sans-serif;
      background: #ffffff;
      color: #333;
      line-height: 1.6;
    }

    /* === HEADER === */
    header {
      background: linear-gradient(to right, #ffffff, #000000);
      padding: 12px 40px;
      display: flex;
      align-items: center;
      justify-content: space-between;
      position: sticky;
      top: 0;
      z-index: 100;
      box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    }

    header .logo {
      display: flex;
      align-items: center;
      gap: 10px;
    }

    header img {
      height: 40px;
    }

    nav a {
      margin: 0 15px;
      text-decoration: none;
      font-weight: bold;
      color: #fff;
      transition: 0.3s;
    }

    nav a:hover,
    nav a.active {
      color: #ff4b4b;
    }

    /* === MAIN LAYOUT === */
    .main {
      display: flex;
      min-height: calc(100vh - 130px);
    }

    /* === SIDEBAR === */
    .sidebar {
      width: 260px;
      flex: 0 0 260px;
      padding: 30px 20px;
      background: linear-gradient(to bottom, #d9d9d9, #8c8c8c);
      border-right: 1px solid #ccc;
      overflow-y: auto;
    }

    .admin-profile {
      text-align: center;
      margin-bottom: 30px;
      position: relative;
    }

    .admin-photo {
      width: 70px;
      height: 70px;
      background: #bbb;
      border-radius: 50%;
      margin: 0 auto 12px;
      box-shadow: 0 4px 10px rgba(0, 0, 0, 0.15);
      overflow: hidden;
      cursor: pointer;
      transition: all 0.3s;
    }

    .admin-photo:hover {
      transform: scale(1.05);
      box-shadow: 0 6px 15px rgba(255, 0, 0, 0.3);
    }

    .admin-photo img {
      width: 100%;
      height: 100%;
      object-fit: cover;
    }

    .admin-name {
      color: #000;
      font-weight: 600;
      font-size: 15px;
      padding: 10px 15px;
      background: #cfcfcf;
      border-radius: 10px;
      cursor: pointer;
      transition: all 0.3s;
    }

    .admin-name:hover {
      background: #ff4b4b;
      color: #fff;
      transform: translateY(-2px);
      box-shadow: 0 4px 12px rgba(255, 75, 75, 0.3);
    }

    .sidebar nav a {
      display: block;
      padding: 12px 16px;
      margin: 8px 0;
      text-decoration: none;
      color: #000;
      background: #b5b5b5;
      border-radius: 10px;
      transition: all 0.3s;
      font-weight: 500;
      font-size: 14px;
      text-align: center;
    }

    .sidebar nav a:hover,
    .sidebar nav a.active {
      background: #ff4b4b;
      color: #fff;
      transform: translateX(5px);
      box-shadow: 0 4px 12px rgba(255, 75, 75, 0.3);
    }

    /* === CONTENT === */
    .content {
      flex: 1;
      padding: 30px;
    }

    .page-header {
      margin-bottom: 30px;
    }

    .page-header h2 {
      color: #000;
      font-size: 28px;
      font-weight: 700;
      margin-bottom: 5px;
    }

    .page-header p {
      color: #666;
      font-size: 14px;
    }

    /* === STATS CARDS === */
    .stats-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
      gap: 20px;
      margin-bottom: 30px;
    }

    .stat-card {
      background: linear-gradient(135deg, rgba(255, 255, 255, 0.95) 0%, rgba(255, 200, 200, 0.3) 100%);
      backdrop-filter: blur(10px);
      border: 1px solid rgba(255, 0, 0, 0.15);
      border-radius: 16px;
      padding: 20px 25px;
      display: flex;
      align-items: center;
      gap: 18px;
      transition: all 0.3s;
      box-shadow: 0 2px 8px rgba(255, 0, 0, 0.1);
    }

    .stat-card:hover {
      transform: translateY(-4px);
      box-shadow: 0 6px 20px rgba(255, 0, 0, 0.2);
      background: linear-gradient(135deg, rgba(255, 255, 255, 0.98) 0%, rgba(255, 180, 180, 0.4) 100%);
    }

    .stat-icon {
      width: 55px;
      height: 55px;
      background: linear-gradient(135deg, rgba(255, 0, 0, 0.1), rgba(255, 100, 100, 0.15));
      border-radius: 12px;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 26px;
      flex-shrink: 0;
    }

    .stat-content {
      flex: 1;
    }

    .stat-label {
      color: #666;
      font-size: 12px;
      font-weight: 600;
      margin-bottom: 4px;
      text-transform: uppercase;
    }

    .stat-value {
      color: #000;
      font-size: 28px;
      font-weight: 700;
      line-height: 1;
      margin-bottom: 6px;
    }

    .stat-change {
      display: inline-flex;
      align-items: center;
      gap: 4px;
      font-size: 12px;
      font-weight: 600;
      padding: 3px 8px;
      border-radius: 6px;
    }

    .stat-change.up {
      background: rgba(52, 211, 153, 0.15);
      color: #059669;
      border: 1px solid rgba(52, 211, 153, 0.3);
    }

    .stat-change.down {
      background: rgba(239, 68, 68, 0.15);
      color: #dc2626;
      border: 1px solid rgba(239, 68, 68, 0.3);
    }

    /* === CHARTS === */
    .charts-grid {
      display: grid;
      grid-template-columns: 2fr 1fr;
      gap: 20px;
      margin-bottom: 30px;
    }

    .chart-box {
      background: #fff;
      border: 1px solid #e0e0e0;
      border-radius: 16px;
      padding: 20px;
      box-shadow: 0 2px 8px rgba(0,0,0,0.08);
      transition: all 0.3s;
    }

    .chart-box:hover {
      transform: translateY(-2px);
      box-shadow: 0 6px 20px rgba(0, 0, 0, 0.1);
    }

    .chart-box h3 {
      color: #000;
      font-size: 14px;
      font-weight: 700;
      margin-bottom: 15px;
      text-transform: uppercase;
      letter-spacing: 0.5px;
    }

    .chart-small-grid {
      display: flex;
      flex-direction: column;
      gap: 20px;
    }

    .chart-container {
      position: relative;
      height: 280px;
    }

    .chart-container-small {
      position: relative;
      height: 180px;
    }

    /* === ACTIVITY CARDS === */
    .activity-grid {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 20px;
      margin-bottom: 30px;
    }

    .activity-card {
      background: #fff;
      border: 1px solid #e0e0e0;
      border-radius: 16px;
      padding: 20px;
      box-shadow: 0 2px 8px rgba(0,0,0,0.08);
      transition: all 0.3s;
      max-height: 500px;
      display: flex;
      flex-direction: column;
    }

    .activity-card:hover {
      transform: translateY(-2px);
      box-shadow: 0 6px 20px rgba(0, 0, 0, 0.1);
    }

    .activity-header {
      display: flex;
      align-items: center;
      gap: 10px;
      margin-bottom: 15px;
      padding-bottom: 10px;
      border-bottom: 2px solid #f0f0f0;
    }

    .activity-icon {
      width: 35px;
      height: 35px;
      background: linear-gradient(135deg, rgba(255, 0, 0, 0.1), rgba(255, 100, 100, 0.15));
      border-radius: 8px;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 18px;
    }

    .activity-header h3 {
      color: #000;
      font-size: 14px;
      font-weight: 700;
      text-transform: uppercase;
      letter-spacing: 0.5px;
      margin: 0;
    }

    .activity-list {
      flex: 1;
      overflow-y: auto;
      padding-right: 5px;
    }

    .activity-item {
      padding: 12px;
      margin-bottom: 8px;
      background: #f9f9f9;
      border-radius: 8px;
      border-left: 3px solid #ff0000;
      transition: all 0.2s;
    }

    .activity-item:hover {
      background: #f5f5f5;
      transform: translateX(3px);
    }

    .activity-item.added {
      border-left-color: #4CAF50;
    }

    .activity-item-header {
      display: flex;
      justify-content: space-between;
      align-items: start;
      margin-bottom: 5px;
    }

    .activity-name {
      font-weight: 600;
      color: #000;
      font-size: 13px;
    }

    .activity-time {
      font-size: 11px;
      color: #999;
      white-space: nowrap;
    }

    .activity-details {
      font-size: 12px;
      color: #666;
      line-height: 1.4;
    }

    .activity-details .nik {
      color: #ff0000;
      font-weight: 500;
    }

    .activity-badge {
      display: inline-block;
      padding: 2px 8px;
      background: rgba(255, 0, 0, 0.1);
      color: #ff0000;
      border-radius: 4px;
      font-size: 10px;
      font-weight: 600;
      margin-top: 4px;
    }

    .no-activity {
      text-align: center;
      padding: 30px;
      color: #999;
      font-size: 13px;
    }

    .no-activity .icon {
      font-size: 40px;
      margin-bottom: 10px;
      opacity: 0.3;
    }

    /* === FOOTER === */
    footer {
      margin-top: 20px;
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
      margin: 0 5px;
      filter: brightness(0) invert(1);
    }

    /* === SCROLLBAR === */
    ::-webkit-scrollbar {
      width: 8px;
    }

    ::-webkit-scrollbar-track {
      background: #f1f1f1;
    }

    ::-webkit-scrollbar-thumb {
      background: #888;
      border-radius: 4px;
    }

    ::-webkit-scrollbar-thumb:hover {
      background: #555;
    }

    /* === RESPONSIVE === */
    @media (max-width: 1024px) {
      .charts-grid {
        grid-template-columns: 1fr;
      }
      
      .activity-grid {
        grid-template-columns: 1fr;
      }
    }

    @media (max-width: 768px) {
      .sidebar {
        width: 220px;
      }
      
      .stats-grid {
        grid-template-columns: 1fr;
      }
      
      .activity-grid {
        grid-template-columns: 1fr;
      }
    }
  </style>
</head>
<body>
  <!-- HEADER -->
  <header>
    <div class="logo">
      <img src="../assets/image/logo.png" alt="PSI Logo">
    </div>
  </header>

  <div class="main">
    <!-- SIDEBAR -->
    <aside class="sidebar">
      <div class="admin-profile">
        <div class="admin-photo" onclick="window.location.href='profil_admin.php'">
          <img 
            src="<?php echo e($adminPhoto); ?>" 
            alt="Admin Photo"
            onerror="this.src='data:image/svg+xml,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' viewBox=\'0 0 100 100\'%3E%3Ccircle cx=\'50\' cy=\'50\' r=\'50\' fill=\'%23bbb\'/%3E%3Ctext x=\'50\' y=\'60\' font-size=\'40\' text-anchor=\'middle\' fill=\'%23666\'%3E👤%3C/text%3E%3C/svg%3E';"
          >
        </div>
        <div class="admin-name" onclick="window.location.href='profil_admin.php'">
          <?php echo e($adminName); ?>
        </div>
      </div>
      <nav>
        <a href="#" class="active">Dashboard</a>
        <a href="datakeluarga.php">Data Keluarga</a>
        <a href="tambah_admin.php">➕ Tambah Admin</a>
        <a href="verifikasi.php">Hasil Verifikasi</a>
        <a href="laporan.php">Laporan</a>
        <a href="../user/logout.php">Logout</a>
      </nav>
    </aside>

    <!-- CONTENT -->
    <section class="content">
      <div class="page-header">
        <h2>Dashboard Admin</h2>
        <p>Selamat datang kembali, <?php echo e($adminName); ?>! Berikut ringkasan data terkini.</p>
      </div>

      <!-- STATS CARDS -->
      <div class="stats-grid">
        <div class="stat-card">
          <div class="stat-icon">👨‍👩‍👧‍👦</div>
          <div class="stat-content">
            <div class="stat-label">Total Keluarga</div>
            <div class="stat-value"><?php echo number_format($totalKeluarga, 0, ',', '.'); ?></div>
            <div class="stat-change up">Real-time Data</div>
          </div>
        </div>

        <div class="stat-card">
          <div class="stat-icon">📉</div>
          <div class="stat-content">
            <div class="stat-label">Dibawah UMR</div>
            <div class="stat-value"><?php echo number_format($dibawahUMR, 0, ',', '.'); ?></div>
            <div class="stat-change down"><?php echo $totalKeluarga > 0 ? round(($dibawahUMR/$totalKeluarga)*100, 1) : 0; ?>%</div>
          </div>
        </div>

        <div class="stat-card">
          <div class="stat-icon">📈</div>
          <div class="stat-content">
            <div class="stat-label">Diatas UMR</div>
            <div class="stat-value"><?php echo number_format($diatasUMR, 0, ',', '.'); ?></div>
            <div class="stat-change up"><?php echo $totalKeluarga > 0 ? round(($diatasUMR/$totalKeluarga)*100, 1) : 0; ?>%</div>
          </div>
        </div>
      </div>

      <!-- CHARTS -->
      <div class="charts-grid">
        <div class="chart-box">
          <h3>Jumlah Data Keluarga (12 Bulan Terakhir)</h3>
          <div class="chart-container">
            <canvas id="chartLine"></canvas>
          </div>
        </div>
        <div class="chart-small-grid">
          <div class="chart-box">
            <h3>Status UMR (4 Bulan Terakhir)</h3>
            <div class="chart-container-small">
              <canvas id="chartBar"></canvas>
            </div>
          </div>
          <div class="chart-box">
            <h3>Data Per Dapil</h3>
            <div class="chart-container-small">
              <canvas id="chartPie"></canvas>
            </div>
          </div>
        </div>
      </div>

      <!-- ACTIVITY CARDS -->
      <div class="activity-grid">
        <!-- DATA UPDATE TERBARU -->
        <div class="activity-card">
          <div class="activity-header">
            <div class="activity-icon">🔄</div>
            <h3>Data Terupdate</h3>
          </div>
          <div class="activity-list">
            <?php if (mysqli_num_rows($resultUpdates) > 0): ?>
              <?php while ($update = mysqli_fetch_assoc($resultUpdates)): ?>
                <?php
                  $timeAgo = '';
                  $timestamp = strtotime($update['updated_at']);
                  $diff = time() - $timestamp;
                  
                  if ($diff < 60) {
                    $timeAgo = 'Baru saja';
                  } elseif ($diff < 3600) {
                    $timeAgo = floor($diff / 60) . ' menit lalu';
                  } elseif ($diff < 86400) {
                    $timeAgo = floor($diff / 3600) . ' jam lalu';
                  } else {
                    $timeAgo = floor($diff / 86400) . ' hari lalu';
                  }
                ?>
                <div class="activity-item">
                  <div class="activity-item-header">
                    <div class="activity-name"><?php echo e($update['nama_lengkap']); ?></div>
                    <div class="activity-time"><?php echo $timeAgo; ?></div>
                  </div>
                  <div class="activity-details">
                    NIK: <span class="nik"><?php echo e($update['nik']); ?></span><br>
                    <?php echo e(substr($update['alamat'], 0, 50)); ?><?php echo strlen($update['alamat']) > 50 ? '...' : ''; ?>
                    <?php if (!empty($update['dapil'])): ?>
                      <div class="activity-badge"><?php echo e($update['dapil']); ?></div>
                    <?php endif; ?>
                  </div>
                </div>
              <?php endwhile; ?>
            <?php else: ?>
              <div class="no-activity">
                <div class="icon">📝</div>
                Belum ada update data
              </div>
            <?php endif; ?>
          </div>
        </div>

        <!-- DATA TAMBAH TERBARU -->
        <div class="activity-card">
          <div class="activity-header">
            <div class="activity-icon">➕</div>
            <h3>Data Ditambahkan</h3>
          </div>
          <div class="activity-list">
            <?php if (mysqli_num_rows($resultAdded) > 0): ?>
              <?php while ($added = mysqli_fetch_assoc($resultAdded)): ?>
                <?php
                  $timeAgo = '';
                  $timestamp = strtotime($added['created_at']);
                  $diff = time() - $timestamp;
                  
                  if ($diff < 60) {
                    $timeAgo = 'Baru saja';
                  } elseif ($diff < 3600) {
                    $timeAgo = floor($diff / 60) . ' menit lalu';
                  } elseif ($diff < 86400) {
                    $timeAgo = floor($diff / 3600) . ' jam lalu';
                  } else {
                    $timeAgo = floor($diff / 86400) . ' hari lalu';
                  }
                ?>
                <div class="activity-item added">
                  <div class="activity-item-header">
                    <div class="activity-name"><?php echo e($added['nama_lengkap']); ?></div>
                    <div class="activity-time"><?php echo $timeAgo; ?></div>
                  </div>
                  <div class="activity-details">
                    NIK: <span class="nik"><?php echo e($added['nik']); ?></span><br>
                    <?php echo e(substr($added['alamat'], 0, 50)); ?><?php echo strlen($added['alamat']) > 50 ? '...' : ''; ?>
                    <?php if (!empty($added['dapil'])): ?>
                      <div class="activity-badge"><?php echo e($added['dapil']); ?></div>
                    <?php endif; ?>
                  </div>
                </div>
              <?php endwhile; ?>
            <?php else: ?>
              <div class="no-activity">
                <div class="icon">📋</div>
                Belum ada data ditambahkan
              </div>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </section>
  </div>

  <!-- FOOTER -->
  <footer>
    <img src="../assets/image/logodprd.png" alt="DPRD Logo">
    <img src="../assets/image/psiputih.png" alt="PSI Logo">
    Hak cipta © 2025 - Partai Solidaritas Indonesia
  </footer>

  <script>
    Chart.defaults.color = '#666';
    Chart.defaults.borderColor = '#e5e5e5';

    // Line Chart - Data dari PHP
    const ctx1 = document.getElementById('chartLine');
    const lineData = <?php echo json_encode($lineChartData); ?>;
    const maxLineValue = Math.max(...lineData, 10); // minimal 10
    
    // Tentukan step size otomatis berdasarkan nilai maksimal
    let lineStepSize = 5;
    if (maxLineValue > 100) {
      lineStepSize = Math.ceil(maxLineValue / 20 / 5) * 5; // kelipatan 5
    } else if (maxLineValue > 50) {
      lineStepSize = 10;
    }
    
    new Chart(ctx1, {
      type: 'line',
      data: {
        labels: <?php echo json_encode($lineChartLabels); ?>,
        datasets: [{
          label: 'Jumlah Keluarga',
          data: lineData,
          borderColor: '#ff0000',
          backgroundColor: 'rgba(255, 0, 0, 0.05)',
          fill: true,
          tension: 0.4,
          borderWidth: 2,
          pointRadius: 3,
          pointHoverRadius: 6,
          pointBackgroundColor: '#ff0000',
          pointHoverBackgroundColor: '#ff0000',
          pointBorderColor: '#fff',
          pointBorderWidth: 1
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: { display: false },
          tooltip: {
            backgroundColor: 'rgba(0, 0, 0, 0.8)',
            padding: 10,
            cornerRadius: 6,
            titleFont: { size: 13 },
            bodyFont: { size: 12 }
          }
        },
        scales: {
          y: {
            beginAtZero: true,
            grid: {
              color: '#f0f0f0',
              drawBorder: false
            },
            ticks: { 
              color: '#666',
              font: { size: 11 },
              stepSize: lineStepSize,
              callback: function(value) {
                return Number.isInteger(value) ? value : null;
              }
            }
          },
          x: {
            grid: {
              display: false,
              drawBorder: false
            },
            ticks: { 
              color: '#666',
              font: { size: 11 }
            }
          }
        }
      }
    });

    // Bar Chart - Data dari PHP
    const ctx2 = document.getElementById('chartBar');
    const barDataDibawah = <?php echo json_encode($barChartDibawah); ?>;
    const barDataDiatas = <?php echo json_encode($barChartDiatas); ?>;
    const maxBarValue = Math.max(...barDataDibawah, ...barDataDiatas, 10); // minimal 10
    
    // Tentukan step size otomatis berdasarkan nilai maksimal
    let barStepSize = 5;
    if (maxBarValue > 100) {
      barStepSize = Math.ceil(maxBarValue / 15 / 5) * 5; // kelipatan 5
    } else if (maxBarValue > 50) {
      barStepSize = 10;
    }
    
    new Chart(ctx2, {
      type: 'bar',
      data: {
        labels: <?php echo json_encode($barChartLabels); ?>,
        datasets: [
          {
            label: 'Dibawah UMR',
            data: barDataDibawah,
            backgroundColor: '#ff0000',
            borderRadius: 6,
            borderSkipped: false
          },
          {
            label: 'Diatas UMR',
            data: barDataDiatas,
            backgroundColor: '#008000',
            borderRadius: 6,
            borderSkipped: false
          }
        ]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: {
            position: 'bottom',
            labels: {
              color: '#666',
              padding: 10,
              usePointStyle: true,
              pointStyle: 'circle',
              font: { size: 11 }
            }
          }
        },
        scales: {
          y: {
            beginAtZero: true,
            grid: {
              color: '#f0f0f0',
              drawBorder: false
            },
            ticks: { 
              color: '#666',
              font: { size: 10 },
              stepSize: barStepSize,
              callback: function(value) {
                return Number.isInteger(value) ? value : null;
              }
            }
          },
          x: {
            grid: {
              display: false,
              drawBorder: false
            },
            ticks: { 
              color: '#666',
              font: { size: 10 }
            }
          }
        }
      }
    });

    // Pie Chart - Data dari PHP
    const ctx3 = document.getElementById('chartPie');
    new Chart(ctx3, {
      type: 'doughnut',
      data: {
        labels: <?php echo json_encode($pieChartLabels); ?>,
        datasets: [{
          data: <?php echo json_encode($pieChartData); ?>,
          backgroundColor: [
            '#f44336',
            '#2196f3',
            '#ff9800',
            '#4caf50',
            '#9c27b0'
          ],
          borderWidth: 2,
          borderColor: '#fff',
          hoverOffset: 6
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: {
            position: 'bottom',
            labels: {
              color: '#666',
              padding: 8,
              usePointStyle: true,
              pointStyle: 'circle',
              font: { size: 10 }
            }
          },
          tooltip: {
            backgroundColor: 'rgba(0, 0, 0, 0.8)',
            padding: 8,
            cornerRadius: 6,
            bodyFont: { size: 11 },
            callbacks: {
              label: function(context) {
                let label = context.label || '';
                if (label) {
                  label += ': ';
                }
                label += context.parsed + ' keluarga';
                return label;
              }
            }
          }
        },
        cutout: '60%'
      }
    });
  </script>
</body>
</html>