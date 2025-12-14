<?php
// laporan.php (versi filter: Riwayat (hari) utk Bulanan, Tahun utk Tahunan, Kategori)
session_start();
include '../koneksi/config.php';

// Pastikan admin login
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../user/login.php");
    exit();
}

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
$conn->set_charset('utf8mb4');

// helper escape (CUMA ADA 1 KALI DI SINI)
function e($s) {
    return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');
}

// Ambil data admin
$admin = null;
if (!empty($_SESSION['alamat_email'])) {
    $stmt = $conn->prepare("SELECT * FROM login WHERE alamat_email = ? LIMIT 1");
    $stmt->bind_param('s', $_SESSION['alamat_email']);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($res->num_rows > 0) $admin = $res->fetch_assoc();
} elseif (!empty($_SESSION['username'])) {
    $stmt = $conn->prepare("SELECT * FROM login WHERE username = ? LIMIT 1");
    $stmt->bind_param('s', $_SESSION['username']);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($res->num_rows > 0) $admin = $res->fetch_assoc();
}

if (!$admin) {
    header("Location: ../user/login.php");
    exit();
}

$adminName  = !empty($admin['nama_lengkap']) ? $admin['nama_lengkap'] : (!empty($admin['username']) ? $admin['username'] : 'Admin');
$adminPhoto = !empty($admin['foto']) ? '../uploads/' . $admin['foto'] : '../assets/image/admin_photo.jpg';

// UMR per orang (Surabaya 2025)
define('UMR_PERSON', 4725479);

// --- FILTERS KHUSUS UNTUK MASING-MASING CARD ---
// Bulanan (prefix: m_) → range = HARI terakhir
$m_kategori = isset($_GET['m_kategori']) ? trim($_GET['m_kategori']) : ''; // dibawah / diatas / ''
$m_range    = isset($_GET['m_range'])    ? trim($_GET['m_range'])    : ''; // 1,3,7,14,30 (hari terakhir)

// Tahunan (prefix: y_) → range = TAHUN (2025/2026/2027)
$y_kategori = isset($_GET['y_kategori']) ? trim($_GET['y_kategori']) : '';
$y_range    = isset($_GET['y_range'])    ? trim($_GET['y_range'])    : ''; // 2025,2026,2027

// helper: build WHERE clause based on provided filter array
function build_where_clause($conn, $filters) {
    $conds = [];

    // mode: 'days' untuk range hari, 'year' untuk filter tahun
    $mode = isset($filters['mode']) ? $filters['mode'] : 'days';

    if ($mode === 'year') {
        // RANGE = tahun (2025/2026/2027)
        if (!empty($filters['range'])) {
            $year = (int)$filters['range'];
            $conds[] = "YEAR(created_at) = $year";
        }
    } else {
        // RANGE = hari terakhir
        if (!empty($filters['range'])) {
            $allowed = ['1','3','7','14','30'];
            if (in_array($filters['range'], $allowed, true)) {
                $days = (int)$filters['range'];
                // DATE(created_at) biar aman kalau ada jam
                $conds[] = "DATE(created_at) >= (CURDATE() - INTERVAL $days DAY)";
            }
        }
    }

    // KATEGORI (UMR per orang)
    if (!empty($filters['kategori'])) {
        $umr = intval($filters['umr']);
        if ($filters['kategori'] === 'dibawah') {
            $conds[] = "( (total_penghasilan / NULLIF(jumlah_anggota,0)) < $umr )";
        } elseif ($filters['kategori'] === 'diatas') {
            $conds[] = "( (total_penghasilan / NULLIF(jumlah_anggota,0)) >= $umr )";
        }
    }

    if (count($conds) > 0) return "WHERE " . implode(" AND ", $conds);
    return "";
}

// --- QUERY UNTUK CARD BULANAN ---
$filters_month = [
    'mode'     => 'days',      // range dalam HARI
    'range'    => $m_range,
    'kategori' => $m_kategori,
    'umr'      => UMR_PERSON
];
$where_month = build_where_clause($conn, $filters_month);
$sql_month   = "SELECT * FROM keluarga $where_month ORDER BY created_at DESC";
$res_month   = $conn->query($sql_month);

// --- QUERY UNTUK CARD TAHUNAN ---
$filters_year = [
    'mode'     => 'year',      // range dalam TAHUN
    'range'    => $y_range,
    'kategori' => $y_kategori,
    'umr'      => UMR_PERSON
];
$where_year = build_where_clause($conn, $filters_year);
$sql_year   = "SELECT * FROM keluarga $where_year ORDER BY created_at DESC";
$res_year   = $conn->query($sql_year);

?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Laporan - PSI</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <style>
    *{box-sizing:border-box;margin:0;padding:0}
    body{font-family:'Poppins',sans-serif;background:#f5f6f8;color:#222}
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

    .layout{display:flex;min-height:calc(100vh - 84px)}
    /* === SIDEBAR === */
    .sidebar {
      width: 260px;
      padding: 30px 20px;
      background: linear-gradient(to bottom, #d9d9d9, #8c8c8c);
      border-right: 1px solid #ccc;
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
    .admin-photo{width:72px;height:72px;border-radius:50%;overflow:hidden;margin:0 auto 8px}
    .admin-photo img{width:100%;height:100%;object-fit:cover}
    .content{flex:1;padding:22px;overflow:auto}
    .page-header h2{font-size:22px;margin-bottom:6px}
    .card{background:#fff;border-radius:12px;padding:16px;margin-bottom:18px;box-shadow:0 2px 8px rgba(0,0,0,0.06)}
    .card-header{display:flex;justify-content:space-between;align-items:center;gap:10px;flex-wrap:wrap;margin-bottom:12px}
    .filters{display:flex;gap:8px;align-items:center;flex-wrap:wrap}
    .filters select{padding:8px;border:1px solid #ddd;border-radius:8px;background:#fbfbfb}
    .btn{padding:8px 12px;border-radius:8px;border:none;cursor:pointer}
    .btn-primary{background:#ff4b4b;color:#fff}
    .btn-muted{background:#f1f1f1;color:#333;border:1px solid #e6e6e6}
    .table-container{overflow:auto}
    table{width:100%;border-collapse:collapse;min-width:900px}
    th,td{padding:10px;border:1px solid #e6e6e6;font-size:13px;white-space:nowrap}
    thead th{background:#f7fafc;font-weight:600}
    tbody tr:nth-child(even){background:#fcfcfd}
    tbody tr:hover{background:#f3f6fb}
    .dibawah{color:#d32f2f;font-weight:600}
    .diatas{color:#2e7d32;font-weight:600}
    footer{padding:12px 5%;text-align:center;background:linear-gradient(90deg,#fff,#000);color:#fff;margin-top:12px}
    @media (max-width:900px){.layout{flex-direction:column}.sidebar{width:100%}}
  </style>
</head>
<body>
  <header>
    <div><img src="../assets/image/logo.png" alt="PSI Logo"></div>
    <div style="display:flex;align-items:center;gap:12px">
      <div style="text-align:right;color:#fff;font-weight:600"><?php echo e($adminName); ?></div>
      <div style="width:44px;height:44px;border-radius:8px;overflow:hidden"><img src="<?php echo e($adminPhoto); ?>" alt="admin"></div>
    </div>
  </header>

  <div class="layout">
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
        <a href="permintaanedit.php">📝 Kelola Edit User</a>
        <a href="datakeluarga.php">Data Keluarga</a>
        <a href="tambah_admin.php">➕ Tambah Admin</a>
        <a href="verifikasi.php">Hasil Verifikasi</a>
        <a href="laporan.php">Laporan</a>
        <a href="pengaduan_admin.php">Pengaduan</a>
        <a href="logoutadmin.php">Logout</a>
      </nav>
    </aside>

    <main class="content">
      <div class="page-header">
        <h2>Laporan</h2>
        <p style="color:#666;margin-top:6px">
          Pilih filter pada setiap card, lalu lihat preview data. Gunakan tombol download untuk ekspor PDF/Excel.
        </p>
      </div>

      <!-- CARD: LAPORAN BULANAN -->
      <div class="card">
        <div class="card-header">
          <div>
            <h3 style="margin:0 0 4px 0">Laporan Bulanan</h3>
            <div style="color:#666;font-size:13px">
              Filter: Riwayat Pengisian (Hari), Kategori (UMR per orang)
            </div>
          </div>

          <div>
            <form method="GET" style="display:flex;gap:8px;align-items:center;flex-wrap:wrap">

              <!-- RIWAYAT PENGISIAN (HARI) -->
              <select name="m_range">
                <option value="">Semua Riwayat</option>
                <option value="1"  <?php echo ($m_range === '1'  ? 'selected' : ''); ?>>1 Hari Terakhir</option>
                <option value="3"  <?php echo ($m_range === '3'  ? 'selected' : ''); ?>>3 Hari Terakhir</option>
                <option value="7"  <?php echo ($m_range === '7'  ? 'selected' : ''); ?>>7 Hari Terakhir</option>
                <option value="14" <?php echo ($m_range === '14' ? 'selected' : ''); ?>>14 Hari Terakhir</option>
                <option value="30" <?php echo ($m_range === '30' ? 'selected' : ''); ?>>30 Hari Terakhir</option>
              </select>

              <!-- KATEGORI UMR -->
              <select name="m_kategori">
                <option value="">Semua Kategori</option>
                <option value="dibawah" <?php echo ($m_kategori === 'dibawah' ? 'selected' : ''); ?>>Di bawah UMR</option>
                <option value="diatas"  <?php echo ($m_kategori === 'diatas'  ? 'selected' : ''); ?>>Di atas UMR</option>
              </select>

              <button type="submit" class="btn btn-primary fw-bold px-4 py-2"
                style="background:#0066ff; border:none; font-size:15px; box-shadow:0 3px 8px rgba(0,0,0,0.15);">
                Terapkan Filter
              </button>

              <a class="btn btn-primary" href="export_bulanan.php?<?php
                  echo http_build_query([
                      'range'    => $m_range,
                      'kategori' => $m_kategori,
                  ]);
              ?>" target="_blank">Download PDF</a>

              <a class="btn btn-primary" href="export_bulanan_excel.php?<?php
                  echo http_build_query([
                      'range'    => $m_range,
                      'kategori' => $m_kategori,
                  ]);
              ?>" target="_blank">Download Excel</a>
            </form>
          </div>
        </div>

        <div class="table-container" style="margin-top:12px">
          <table>
            <thead>
              <tr>
                <th>Nama Lengkap</th>
                <th>NIK</th>
                <th>No WA</th>
                <th>Alamat Lengkap</th>
                <th>Jumlah Anggota</th>
                <th>Jumlah Bekerja</th>
                <th>Total Penghasilan</th>
                <th>Rata-rata/Orang</th>
                <th>Kategori</th>
                <th>Created At</th>
                <th>Updated At</th>
              </tr>
            </thead>
            <tbody>
              <?php if ($res_month && $res_month->num_rows > 0): ?>
                <?php while ($row = $res_month->fetch_assoc()):
                  $anggota     = (int)$row['jumlah_anggota'];
                  $penghasilan = (float)$row['total_penghasilan'];
                  $per_orang   = $anggota > 0 ? ($penghasilan / $anggota) : 0;
                  $kategori_label = ($per_orang < UMR_PERSON)
                      ? "<span class='dibawah'>Dibawah UMR</span>"
                      : "<span class='diatas'>Diatas UMR</span>";
                ?>
                  <tr>
                    <td><?php echo e($row['nama_lengkap']); ?></td>
                    <td><?php echo e($row['nik']); ?></td>
                    <td><?php echo e($row['no_wa']); ?></td>
                    <td><?php echo e($row['alamat']); ?></td>
                    <td><?php echo e($row['jumlah_anggota']); ?></td>
                    <td><?php echo e($row['jumlah_bekerja']); ?></td>
                    <td><?php echo e(number_format($penghasilan,0,',','.')); ?></td>
                    <td><?php echo e(number_format($per_orang,0,',','.')); ?></td>
                    <td><?php echo $kategori_label; ?></td>
                    <td><?php echo e($row['created_at']); ?></td>
                    <td><?php echo e($row['updated_at']); ?></td>
                  </tr>
                <?php endwhile; ?>
              <?php else: ?>
                <tr><td colspan="11" style="text-align:center;padding:12px">Tidak ada data untuk filter ini.</td></tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>

      <!-- CARD: LAPORAN TAHUNAN -->
      <div class="card">
        <div class="card-header">
          <div>
            <h3 style="margin:0 0 4px 0">Laporan Tahunan</h3>
            <div style="color:#666;font-size:13px">
              Filter: Tahun, Kategori (UMR per orang)
            </div>
          </div>

          <div>
            <form method="GET" style="display:flex;gap:8px;align-items:center;flex-wrap:wrap">

              <!-- TAHUN (2025/2026/2027) -->
              <select name="y_range">
                <option value="">Semua Tahun</option>
                <option value="2025" <?php echo ($y_range === '2025' ? 'selected' : ''); ?>>2025</option>
                <option value="2026" <?php echo ($y_range === '2026' ? 'selected' : ''); ?>>2026</option>
                <option value="2027" <?php echo ($y_range === '2027' ? 'selected' : ''); ?>>2027</option>
              </select>

              <!-- KATEGORI UMR -->
              <select name="y_kategori">
                <option value="">Semua Kategori</option>
                <option value="dibawah" <?php echo ($y_kategori === 'dibawah' ? 'selected' : ''); ?>>Di bawah UMR</option>
                <option value="diatas"  <?php echo ($y_kategori === 'diatas'  ? 'selected' : ''); ?>>Di atas UMR</option>
              </select>

              <button type="submit"
                      class="btn btn-primary"
                      style="background:#0066ff; border:none; font-size:15px; box-shadow:0 3px 8px rgba(0,0,0,0.15);">
                Terapkan Filter
              </button>

              <a class="btn btn-primary" href="export_tahunan_pdf.php?<?php
                  echo http_build_query([
                      'range'    => $y_range,
                      'kategori' => $y_kategori,
                  ]);
              ?>" target="_blank">Download PDF</a>

              <a class="btn btn-primary" href="export_tahunan_excel.php?<?php
                  echo http_build_query([
                      'range'    => $y_range,
                      'kategori' => $y_kategori,
                  ]);
              ?>" target="_blank">Download Excel</a>
            </form>
          </div>
        </div>

        <div class="table-container" style="margin-top:12px">
          <table>
            <thead>
              <tr>
                <th>Nama Lengkap</th>
                <th>NIK</th>
                <th>No WA</th>
                <th>Alamat Lengkap</th>
                <th>Jumlah Anggota</th>
                <th>Jumlah Bekerja</th>
                <th>Total Penghasilan</th>
                <th>Rata-rata/Orang</th>
                <th>Kategori</th>
                <th>Created At</th>
                <th>Updated At</th>
              </tr>
            </thead>
            <tbody>
              <?php if ($res_year && $res_year->num_rows > 0): ?>
                <?php while ($row = $res_year->fetch_assoc()):
                  $anggota     = (int)$row['jumlah_anggota'];
                  $penghasilan = (float)$row['total_penghasilan'];
                  $per_orang   = $anggota > 0 ? ($penghasilan / $anggota) : 0;
                  $kategori_label = ($per_orang < UMR_PERSON)
                      ? "<span class='dibawah'>Dibawah UMR</span>"
                      : "<span class='diatas'>Diatas UMR</span>";
                ?>
                  <tr>
                    <td><?php echo e($row['nama_lengkap']); ?></td>
                    <td><?php echo e($row['nik']); ?></td>
                    <td><?php echo e($row['no_wa']); ?></td>
                    <td><?php echo e($row['alamat']); ?></td>
                    <td><?php echo e($row['jumlah_anggota']); ?></td>
                    <td><?php echo e($row['jumlah_bekerja']); ?></td>
                    <td><?php echo e(number_format($penghasilan,0,',','.')); ?></td>
                    <td><?php echo e(number_format($per_orang,0,',','.')); ?></td>
                    <td><?php echo $kategori_label; ?></td>
                    <td><?php echo e($row['created_at']); ?></td>
                    <td><?php echo e($row['updated_at']); ?></td>
                  </tr>
                <?php endwhile; ?>
              <?php else: ?>
                <tr><td colspan="11" style="text-align:center;padding:12px">Tidak ada data untuk filter ini.</td></tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>

      <footer>
        <img src="../assets/image/logodprd.png" alt="DPRD Logo"
             style="height:20px;margin-right:8px;filter:brightness(0) invert(1)">
        <img src="../assets/image/psiputih.png" alt="PSI Logo"
             style="height:20px;filter:brightness(0) invert(1)">
        &nbsp; Hak cipta © <?php echo date('Y'); ?> - Partai Solidaritas Indonesia
      </footer>

    </main>
  </div>
</body>
</html>