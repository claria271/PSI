<?php
// laporan.php (final versi)
session_start();
include '../koneksi/config.php';

// Pastikan admin login
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../user/login.php");
    exit();
}

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
$conn->set_charset('utf8mb4');

// helper escape
function e($s) { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }

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
if (!$admin) { header("Location: ../user/login.php"); exit(); }

$adminName = !empty($admin['nama_lengkap']) ? $admin['nama_lengkap'] : (!empty($admin['username']) ? $admin['username'] : 'Admin');
$adminPhoto = !empty($admin['foto']) ? '../uploads/' . $admin['foto'] : '../assets/image/admin_photo.jpg';

// UMR per orang (Surabaya 2025)
define('UMR_PERSON', 4725479);

// Ambil daftar bulan/tahun unik dari created_at (dipakai di dropdown)
$bulanQ = $conn->query("SELECT DISTINCT MONTH(created_at) AS bulan FROM keluarga WHERE created_at IS NOT NULL ORDER BY bulan ASC");
$tahunQ = $conn->query("SELECT DISTINCT YEAR(created_at) AS tahun FROM keluarga WHERE created_at IS NOT NULL ORDER BY tahun DESC");

// --- FILTERS KHUSUS UNTUK MASING-MASING CARD ---
// Bulanan (prefix: m_)
$m_dapil    = isset($_GET['m_dapil']) ? trim($_GET['m_dapil']) : '';
$m_kategori = isset($_GET['m_kategori']) ? trim($_GET['m_kategori']) : ''; // dibawah / diatas / ''
$m_kenal    = isset($_GET['m_kenal']) ? trim($_GET['m_kenal']) : '';
$m_bulan    = isset($_GET['m_bulan']) ? trim($_GET['m_bulan']) : '';
$m_tahun    = isset($_GET['m_tahun']) ? trim($_GET['m_tahun']) : '';

// Tahunan (prefix: y_)
$y_dapil    = isset($_GET['y_dapil']) ? trim($_GET['y_dapil']) : '';
$y_kategori = isset($_GET['y_kategori']) ? trim($_GET['y_kategori']) : '';
$y_kenal    = isset($_GET['y_kenal']) ? trim($_GET['y_kenal']) : '';
$y_tahun    = isset($_GET['y_tahun']) ? trim($_GET['y_tahun']) : '';

// helper: build WHERE clause based on provided filter array
function build_where_clause($conn, $filters) {
    $conds = [];
    // dapil
    if (!empty($filters['dapil'])) {
        $safe = mysqli_real_escape_string($conn, $filters['dapil']);
        $conds[] = "dapil = '$safe'";
    }
    // kenal
    if ($filters['kenal'] !== '' && $filters['kenal'] !== null) {
        $safe = mysqli_real_escape_string($conn, $filters['kenal']);
        $conds[] = "kenal = '$safe'";
    }
    // kategori (per orang) -> gunakan NULLIF untuk hindari division by zero
    if (!empty($filters['kategori'])) {
        $umr = intval($filters['umr']);
        if ($filters['kategori'] === 'dibawah') {
            $conds[] = "( (total_penghasilan / NULLIF(jumlah_anggota,0)) < $umr )";
        } elseif ($filters['kategori'] === 'diatas') {
            $conds[] = "( (total_penghasilan / NULLIF(jumlah_anggota,0)) >= $umr )";
        }
    }
    // bulan & tahun
    if (!empty($filters['bulan'])) {
        $mb = intval($filters['bulan']);
        $conds[] = "MONTH(created_at) = $mb";
    }
    if (!empty($filters['tahun'])) {
        $yt = intval($filters['tahun']);
        $conds[] = "YEAR(created_at) = $yt";
    }

    if (count($conds) > 0) return "WHERE " . implode(" AND ", $conds);
    return "";
}

// --- QUERY UNTUK CARD BULANAN ---
$filters_month = [
    'dapil' => $m_dapil,
    'kategori' => $m_kategori,
    'kenal' => $m_kenal,
    'bulan' => $m_bulan,
    'tahun' => $m_tahun,
    'umr' => UMR_PERSON
];
$where_month = build_where_clause($conn, $filters_month);
$sql_month = "SELECT * FROM keluarga $where_month ORDER BY created_at DESC";
$res_month = $conn->query($sql_month);

// --- QUERY UNTUK CARD TAHUNAN ---
$filters_year = [
    'dapil' => $y_dapil,
    'kategori' => $y_kategori,
    'kenal' => $y_kenal,
    'bulan' => '', // tidak pakai bulan di yearly
    'tahun' => $y_tahun,
    'umr' => UMR_PERSON
];
$where_year = build_where_clause($conn, $filters_year);
$sql_year = "SELECT * FROM keluarga $where_year ORDER BY created_at DESC";
$res_year = $conn->query($sql_year);

// Untuk opsi dapil: gunakan yang kamu punya, contoh berikut tetap statis — ubah jika mau ambil dari DB.
$dapil_options = [
    '' => 'Semua Dapil',
    'Kota Surabaya 1' => 'Kota Surabaya 1',
    'Kota Surabaya 2' => 'Kota Surabaya 2',
    'Kota Surabaya 3' => 'Kota Surabaya 3',
    'Kota Surabaya 4' => 'Kota Surabaya 4',
    'Kota Surabaya 5' => 'Kota Surabaya 5'
];

// Untuk opsi kenal
$kenal_options = [
    '' => 'Semua',
    'Ya' => 'Ya',
    'Tidak pernah' => 'Tidak pernah',
    'Tidak' => 'Tidak'
];

?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Laporan - PSI</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <style>
    /* ---- style mengikuti halaman Data Keluarga (opsi C) ---- */
    *{box-sizing:border-box;margin:0;padding:0}
    body{font-family:'Poppins',sans-serif;background:#f5f6f8;color:#222}
    header{background:linear-gradient(90deg,#fff,#000);padding:12px 30px;display:flex;align-items:center;justify-content:space-between}
    header img{height:40px}
    .layout{display:flex;min-height:calc(100vh - 84px)}
    .sidebar{width:260px;padding:24px;background:linear-gradient(#d9d9d9,#8c8c8c);border-right:1px solid #ccc}
    .admin-profile{text-align:center;margin-bottom:22px}
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
    table{width:100%;border-collapse:collapse;min-width:1200px}
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
        <div class="admin-photo"><img src="<?php echo e($adminPhoto); ?>" alt="foto admin"></div>
        <div style="font-weight:600;color:#111"><?php echo e($adminName); ?></div>
      </div>
      <nav>
        <a href="dashboardadmin.php" style="display:block;padding:10px;background:#b5b5b5;border-radius:10px;margin-bottom:8px;text-decoration:none;color:#000;text-align:center">Dashboard</a>
        <a href="datakeluarga.php" style="display:block;padding:10px;background:#b5b5b5;border-radius:10px;margin-bottom:8px;text-decoration:none;color:#000;text-align:center">Data Keluarga</a>
        <a href="laporan.php" style="display:block;padding:10px;background:#ff4b4b;border-radius:10px;margin-bottom:8px;text-decoration:none;color:#fff;text-align:center">Laporan</a>
      </nav>
    </aside>

    <main class="content">
      <div class="page-header">
        <h2>Laporan</h2>
        <p style="color:#666;margin-top:6px">Pilih filter pada setiap card, lalu lihat preview data. Gunakan tombol download untuk ekspor PDF/Excel.</p>
      </div>

      <!-- CARD: LAPORAN BULANAN -->
      <div class="card">
        <div class="card-header">
          <div>
            <h3 style="margin:0 0 4px 0">Laporan Bulanan</h3>
            <div style="color:#666;font-size:13px">Filter: Dapil, Kategori (UMR per orang), Sumber Kenal, Bulan, Tahun</div>
          </div>

          <div>
            <form method="GET" style="display:flex;gap:8px;align-items:center;flex-wrap:wrap">
              <select name="m_dapil">
                <?php foreach($dapil_options as $k=>$v): ?>
                  <option value="<?php echo e($k); ?>" <?php echo ($m_dapil===$k ? 'selected' : ''); ?>><?php echo e($v); ?></option>
                <?php endforeach; ?>
              </select>

              <select name="m_kategori">
                <option value="">Semua</option>
                <option value="dibawah" <?php echo ($m_kategori==='dibawah'?'selected':''); ?>>Di bawah UMR</option>
                <option value="diatas"  <?php echo ($m_kategori==='diatas'?'selected':''); ?>>Di atas UMR</option>
              </select>

              <select name="m_kenal">
                <?php foreach($kenal_options as $k=>$v): ?>
                  <option value="<?php echo e($k); ?>" <?php echo ($m_kenal===$k ? 'selected' : ''); ?>><?php echo e($v); ?></option>
                <?php endforeach; ?>
              </select>

              <select name="m_bulan">
                <option value="">Semua Bulan</option>
                <?php 
                  // reset pointer and iterate unique months
                  mysqli_data_seek($bulanQ, 0);
                  while ($b = $bulanQ->fetch_assoc()):
                ?>
                  <option value="<?php echo e($b['bulan']); ?>" <?php echo ($m_bulan===$b['bulan'] ? 'selected':''); ?>>
                    <?php echo e(date("F", mktime(0,0,0,$b['bulan'],1))); ?>
                  </option>
                <?php endwhile; ?>
              </select>

              <select name="m_tahun">
                <option value="">Semua Tahun</option>
                <?php 
                  mysqli_data_seek($tahunQ, 0);
                  while ($t = $tahunQ->fetch_assoc()):
                ?>
                  <option value="<?php echo e($t['tahun']); ?>" <?php echo ($m_tahun===$t['tahun'] ? 'selected':'' ); ?>><?php echo e($t['tahun']); ?></option>
                <?php endwhile; ?>
              </select>

              <button type="submit" class="btn btn-primary fw-bold px-4 py-2"
        style="background:#0066ff; border:none; font-size:15px; box-shadow:0 3px 8px rgba(0,0,0,0.15);">
    Terapkan Filter
</button>

              <!-- Export links (sesuaikan nama file export jika diperlukan) -->
              <a class="btn btn-primary" href="export_bulanan_pdf.php?<?php echo http_build_query([
                  'dapil'=>$m_dapil,'kategori'=>$m_kategori,'kenal'=>$m_kenal,'bulan'=>$m_bulan,'tahun'=>$m_tahun
                ]); ?>" target="_blank">Download PDF</a>

              <a class="btn btn-primary" href="export_bulanan_excel.php?<?php echo http_build_query([
                  'dapil'=>$m_dapil,'kategori'=>$m_kategori,'kenal'=>$m_kenal,'bulan'=>$m_bulan,'tahun'=>$m_tahun
                ]); ?>" target="_blank">Download Excel</a>
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
                <th>Dapil</th>
                <th>Kecamatan</th>
                <th>Jumlah Anggota</th>
                <th>Jumlah Bekerja</th>
                <th>Total Penghasilan</th>
                <th>Rata-rata/Orang</th>
                <th>Kenal</th>
                <th>Sumber</th>
                <th>Kategori</th>
                <th>Created At</th>
                <th>Updated At</th>
              </tr>
            </thead>
            <tbody>
              <?php if ($res_month && $res_month->num_rows > 0): ?>
                <?php while ($row = $res_month->fetch_assoc()): 
                  $anggota = (int)$row['jumlah_anggota'];
                  $penghasilan = (float)$row['total_penghasilan'];
                  $per_orang = $anggota > 0 ? ($penghasilan / $anggota) : 0;
                  $kategori_label = ($per_orang < UMR_PERSON) ? "<span class='dibawah'>Dibawah UMR</span>" : "<span class='diatas'>Diatas UMR</span>";
                ?>
                  <tr>
                    <td><?php echo e($row['nama_lengkap']); ?></td>
                    <td><?php echo e($row['nik']); ?></td>
                    <td><?php echo e($row['no_wa']); ?></td>
                    <td><?php echo e($row['alamat']); ?></td>
                    <td><?php echo e($row['dapil']); ?></td>
                    <td><?php echo e($row['kecamatan']); ?></td>
                    <td><?php echo e($row['jumlah_anggota']); ?></td>
                    <td><?php echo e($row['jumlah_bekerja']); ?></td>
                    <td><?php echo e(number_format($penghasilan,0,',','.')); ?></td>
                    <td><?php echo e(number_format($per_orang,0,',','.')); ?></td>
                    <td><?php echo e($row['kenal']); ?></td>
                    <td><?php echo e($row['sumber']); ?></td>
                    <td><?php echo $kategori_label; ?></td>
                    <td><?php echo e($row['created_at']); ?></td>
                    <td><?php echo e($row['updated_at']); ?></td>
                  </tr>
                <?php endwhile; ?>
              <?php else: ?>
                <tr><td colspan="15" style="text-align:center;padding:12px">Tidak ada data untuk filter ini.</td></tr>
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
            <div style="color:#666;font-size:13px">Filter: Dapil, Kategori (UMR per orang), Sumber Kenal, Tahun</div>
          </div>

          <div>
            <form method="GET" style="display:flex;gap:8px;align-items:center;flex-wrap:wrap">
               <select name="m_dapil">
                <?php foreach($dapil_options as $k=>$v): ?>
                  <option value="<?php echo e($k); ?>" <?php echo ($m_dapil===$k ? 'selected' : ''); ?>><?php echo e($v); ?></option>
                <?php endforeach; ?>
              </select>


              <select name="y_kategori">
                <option value="">Semua</option>
                <option value="dibawah" <?php echo ($y_kategori==='dibawah'?'selected':''); ?>>Di bawah UMR</option>
                <option value="diatas"  <?php echo ($y_kategori==='diatas'?'selected':''); ?>>Di atas UMR</option>
              </select>

              <select name="y_kenal">
                <?php foreach($kenal_options as $k=>$v): ?>
                  <option value="<?php echo e($k); ?>" <?php echo ($y_kenal===$k ? 'selected' : ''); ?>><?php echo e($v); ?></option>
                <?php endforeach; ?>
              </select>

              <select name="y_tahun">
                <option value="">Semua Tahun</option>
                <?php 
                  // reset pointer and iterate years
                  mysqli_data_seek($tahunQ, 0);
                  while ($t = $tahunQ->fetch_assoc()):
                ?>
                  <option value="<?php echo e($t['tahun']); ?>" <?php echo ($y_tahun===$t['tahun'] ? 'selected' : ''); ?>><?php echo e($t['tahun']); ?></option>
                <?php endwhile; ?>
              </select>

              
<button type="submit" class="btn btn-primary fw-bold px-4 py-2"
        style="background:#0066ff; border:none; font-size:15px; box-shadow:0 3px 8px rgba(0,0,0,0.15);">
    Terapkan Filter
</button>

              <a class="btn btn-primary" href="export_tahunan_pdf.php?<?php echo http_build_query([
                  'dapil'=>$y_dapil,'kategori'=>$y_kategori,'kenal'=>$y_kenal,'tahun'=>$y_tahun
                ]); ?>" target="_blank">Download PDF</a>

              <a class="btn btn-primary" href="export_tahunan_excel.php?<?php echo http_build_query([
                  'dapil'=>$y_dapil,'kategori'=>$y_kategori,'kenal'=>$y_kenal,'tahun'=>$y_tahun
                ]); ?>" target="_blank">Download Excel</a>
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
                <th>Dapil</th>
                <th>Kecamatan</th>
                <th>Jumlah Anggota</th>
                <th>Jumlah Bekerja</th>
                <th>Total Penghasilan</th>
                <th>Rata-rata/Orang</th>
                <th>Kenal</th>
                <th>Sumber</th>
                <th>Kategori</th>
                <th>Created At</th>
                <th>Updated At</th>
              </tr>
            </thead>
            <tbody>
              <?php if ($res_year && $res_year->num_rows > 0): ?>
                <?php while ($row = $res_year->fetch_assoc()): 
                  $anggota = (int)$row['jumlah_anggota'];
                  $penghasilan = (float)$row['total_penghasilan'];
                  $per_orang = $anggota > 0 ? ($penghasilan / $anggota) : 0;
                  $kategori_label = ($per_orang < UMR_PERSON) ? "<span class='dibawah'>Dibawah UMR</span>" : "<span class='diatas'>Diatas UMR</span>";
                ?>
                  <tr>
                    <td><?php echo e($row['nama_lengkap']); ?></td>
                    <td><?php echo e($row['nik']); ?></td>
                    <td><?php echo e($row['no_wa']); ?></td>
                    <td><?php echo e($row['alamat']); ?></td>
                    <td><?php echo e($row['dapil']); ?></td>
                    <td><?php echo e($row['kecamatan']); ?></td>
                    <td><?php echo e($row['jumlah_anggota']); ?></td>
                    <td><?php echo e($row['jumlah_bekerja']); ?></td>
                    <td><?php echo e(number_format($penghasilan,0,',','.')); ?></td>
                    <td><?php echo e(number_format($per_orang,0,',','.')); ?></td>
                    <td><?php echo e($row['kenal']); ?></td>
                    <td><?php echo e($row['sumber']); ?></td>
                    <td><?php echo $kategori_label; ?></td>
                    <td><?php echo e($row['created_at']); ?></td>
                    <td><?php echo e($row['updated_at']); ?></td>
                  </tr>
                <?php endwhile; ?>
              <?php else: ?>
                <tr><td colspan="15" style="text-align:center;padding:12px">Tidak ada data untuk filter ini.</td></tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>

      <footer>
        <img src="../assets/image/logodprd.png" alt="DPRD Logo" style="height:20px;margin-right:8px;filter:brightness(0) invert(1)">
        <img src="../assets/image/psiputih.png" alt="PSI Logo" style="height:20px;filter:brightness(0) invert(1)">
        &nbsp; Hak cipta © <?php echo date('Y') ?> - Partai Solidaritas Indonesia
      </footer>

    </main>
  </div>
</body>
</html>
