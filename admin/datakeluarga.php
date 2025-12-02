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

// Ambil data admin berdasarkan session (bisa dari email atau username)
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

// Jika tetap tidak ketemu, paksa logout
if (!$admin) {
    header("Location: ../login.php");
    exit();
}

// Tentukan nama & foto admin
$adminName = !empty($admin['nama_lengkap'])
    ? $admin['nama_lengkap']
    : (!empty($admin['username']) ? $admin['username'] : 'Admin');

$adminPhoto = !empty($admin['foto'])
    ? '../uploads/' . $admin['foto']
    : '../assets/image/admin_photo.jpg';

// ================== LOGIKA DATA KELUARGA ================== //

// UMR per orang (Surabaya 2025) - SAMA DENGAN LAPORAN.PHP
define('UMR_PERSON', 4725479);

// Ambil filter dari GET
$search      = isset($_GET['search']) ? trim($_GET['search']) : '';
$status_umr  = isset($_GET['status_umr']) ? $_GET['status_umr'] : ''; // '', Dibawah, Diatas
$dapil       = isset($_GET['dapil']) ? $_GET['dapil'] : '';
$kenal       = isset($_GET['kenal']) ? $_GET['kenal'] : '';

// Bangun query dengan kondisi dinamis
$conditions = [];

if ($search !== '') {
    $safe = mysqli_real_escape_string($conn, $search);
    $conditions[] = "(nama_lengkap LIKE '%$safe%' 
                  OR nik LIKE '%$safe%' 
                  OR no_wa LIKE '%$safe%')";
}

if ($dapil !== '') {
    $safeDapil = mysqli_real_escape_string($conn, $dapil);
    $conditions[] = "dapil = '$safeDapil'";
}

if ($kenal !== '') {
    $safeKenal = mysqli_real_escape_string($conn, $kenal);
    $conditions[] = "kenal = '$safeKenal'";
}

// Filter UMR berdasarkan UMR PER ORANG (bukan total keluarga)
if ($status_umr === 'Dibawah') {
    $umr = UMR_PERSON;
    $conditions[] = "( (total_penghasilan / NULLIF(jumlah_anggota,0)) < $umr )";
} elseif ($status_umr === 'Diatas') {
    $umr = UMR_PERSON;
    $conditions[] = "( (total_penghasilan / NULLIF(jumlah_anggota,0)) >= $umr )";
}

$query = "SELECT * FROM keluarga";

if (!empty($conditions)) {
    $query .= " WHERE " . implode(" AND ", $conditions);
}

$query .= " ORDER BY created_at DESC";

$result = mysqli_query($conn, $query);
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Data Keluarga - PSI</title>
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
      height: 100vh;
      display: flex;
      flex-direction: column;
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

    /* === LAYOUT: SIDEBAR + CONTENT === */
    .layout {
      flex: 1 1 auto;
      display: flex;
      min-height: 0;
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

    /* === CONTENT WRAPPER === */
    .content {
      flex: 1 1 auto;
      display: flex;
      flex-direction: column;
      background: #f9f9f9;
      padding: 25px 30px;
      min-width: 0;
      min-height: 0;
    }

    .page-header {
      flex: 0 0 auto;
      margin-bottom: 15px;
    }

    .page-header h2 {
      color: #000;
      font-size: 24px;
      font-weight: 700;
      margin-bottom: 5px;
    }

    .page-header p {
      color: #666;
      font-size: 14px;
    }

    .content-scroll {
      flex: 1 1 auto;
      overflow-y: auto;
      padding-right: 5px;
      min-height: 0;
    }

    /* === CARD, FILTER, TABEL === */
    .card {
      background: #fff;
      border-radius: 12px;
      box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
      padding: 20px;
      margin-bottom: 20px;
    }

    .card-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 15px;
      flex-wrap: wrap;
      gap: 10px;
    }

    .btn-tambah {
      background: #d32f2f;
      color: #fff;
      border: none;
      border-radius: 6px;
      padding: 8px 14px;
      cursor: pointer;
      font-weight: 600;
      transition: 0.3s;
    }

    .btn-tambah:hover {
      background: #b71c1c;
    }

    .filters {
      display: flex;
      align-items: center;
      gap: 10px;
      flex-wrap: wrap;
    }

    .filters input,
    .filters select {
      padding: 7px 10px;
      border: 1px solid #ccc;
      border-radius: 6px;
      font-size: 13px;
      background: #f5f5f5;
    }

    .table-container {
      width: 100%;
      overflow-x: auto;
    }

    table {
      width: 100%;
      border-collapse: collapse;
      min-width: 1300px;
      border-radius: 10px;
      overflow: hidden;
    }

    th, td {
      padding: 10px;
      border: 1px solid #ccc;
      font-size: 14px;
      text-align: left;
      white-space: nowrap;
    }

    th {
      background: #f2f2f2;
      font-weight: 600;
      position: sticky;
      top: 0;
      z-index: 1;
    }

    tr:nth-child(even) {
      background: #fafafa;
    }

    .dibawah {
      color: red;
      font-weight: 600;
    }

    .diatas {
      color: #2e7d32;
      font-weight: 600;
    }

    .aksi button {
      padding: 6px 10px;
      border: none;
      border-radius: 5px;
      cursor: pointer;
      margin-right: 4px;
      font-size: 13px;
      transition: 0.3s;
    }

    .aksi .edit {
      background: #2196F3;
      color: #fff;
    }

    .aksi .verifikasi {
      background: #4CAF50;
      color: #fff;
    }

    .aksi .hapus {
      background: #f44336;
      color: #fff;
    }

    .aksi button:hover {
      opacity: 0.8;
    }

    /* === FOOTER === */
    footer {
      flex: 0 0 auto;
      margin-top: 5px;
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

    /* SCROLLBAR */
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

    @media (max-width: 1024px) {
      .content {
        padding: 20px;
      }
    }

    @media (max-width: 768px) {
      .sidebar {
        width: 220px;
        flex: 0 0 220px;
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

  <div class="layout">
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
        <a href="dashboardadmin.php">Dashboard</a>
        <a href="datakeluarga.php" class="active">Data Keluarga</a>
        <a href="tambah_admin.php">➕ Tambah Admin</a>
        <a href="verifikasi.php">Hasil Verifikasi</a>
        <a href="laporan.php">Laporan</a>
        <a href="logout.php">Logout</a>
      </nav>
    </aside>

    <!-- CONTENT -->
    <div class="content">
      <div class="page-header">
        <h2>Data Keluarga</h2>
        <p>Kelola data keluarga yang telah terdaftar dalam sistem. UMR dihitung per orang: Rp <?php echo number_format(UMR_PERSON, 0, ',', '.'); ?></p>
      </div>

      <div class="content-scroll">
        <div class="card">
          <div class="card-header">
            <button class="btn-tambah" onclick="window.location.href='tambahdata.php'">+ Tambah Data</button>

            <form method="GET" class="filters">
              <input
                type="text"
                name="search"
                placeholder="Cari Pengguna, NIK, No HP"
                value="<?php echo e($search); ?>"
              >

              <select name="dapil" onchange="this.form.submit()">
                <option value="">Semua Dapil</option>
                <option value="Kota Surabaya 1" <?php echo $dapil === 'Kota Surabaya 1' ? 'selected' : ''; ?>>Kota Surabaya 1</option>
                <option value="Kota Surabaya 2" <?php echo $dapil === 'Kota Surabaya 2' ? 'selected' : ''; ?>>Kota Surabaya 2</option>
                <option value="Kota Surabaya 3" <?php echo $dapil === 'Kota Surabaya 3' ? 'selected' : ''; ?>>Kota Surabaya 3</option>
                <option value="Kota Surabaya 4" <?php echo $dapil === 'Kota Surabaya 4' ? 'selected' : ''; ?>>Kota Surabaya 4</option>
                <option value="Kota Surabaya 5" <?php echo $dapil === 'Kota Surabaya 5' ? 'selected' : ''; ?>>Kota Surabaya 5</option>
              </select>

              <select name="status_umr" onchange="this.form.submit()">
                <option value=""  <?php echo $status_umr === '' ? 'selected' : ''; ?>>Semua Status</option>
                <option value="Dibawah" <?php echo $status_umr === 'Dibawah' ? 'selected' : ''; ?>>Dibawah UMR</option>
                <option value="Diatas"  <?php echo $status_umr === 'Diatas'  ? 'selected' : ''; ?>>Diatas UMR</option>
              </select>

              <select name="kenal" onchange="this.form.submit()">
                <option value=""      <?php echo $kenal === '' ? 'selected' : ''; ?>>Sumber Kenal</option>
                <option value="Ya"    <?php echo $kenal === 'Ya' ? 'selected' : ''; ?>>Ya</option>
                <option value="Tidak" <?php echo $kenal === 'Tidak' ? 'selected' : ''; ?>>Tidak</option>
                <option value="Tidak pernah" <?php echo $kenal === 'Tidak pernah' ? 'selected' : ''; ?>>Tidak pernah</option>
              </select>

              <button type="submit" style="display:none;"></button>
            </form>
          </div>

          <div class="table-container">
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
                  <th>Aksi</th>
                </tr>
              </thead>
              <tbody>
                <?php if (mysqli_num_rows($result) > 0): ?>
                  <?php while ($row = mysqli_fetch_assoc($result)): ?>
                    <?php
                      $anggota = (int)$row['jumlah_anggota'];
                      $penghasilan = (float)$row['total_penghasilan'];
                      $per_orang = $anggota > 0 ? ($penghasilan / $anggota) : 0;
                      
                      // Kategori berdasarkan UMR per orang
                      $kategori = ($per_orang < UMR_PERSON)
                        ? "<span class='dibawah'>Dibawah UMR</span>"
                        : "<span class='diatas'>Diatas UMR</span>";
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
                      <td><?php echo e(number_format($penghasilan, 0, ',', '.')); ?></td>
                      <td><?php echo e(number_format($per_orang, 0, ',', '.')); ?></td>
                      <td><?php echo e($row['kenal']); ?></td>
                      <td><?php echo e($row['sumber']); ?></td>
                      <td><?php echo $kategori; ?></td>
                      <td><?php echo e($row['created_at']); ?></td>
                      <td><?php echo e($row['updated_at']); ?></td>
                      <td class="aksi">
                        <button class="edit" onclick="window.location.href='editdata.php?id=<?php echo $row['id']; ?>'">Edit</button>
                        <button class="verifikasi" onclick="verifikasiData(<?php echo $row['id']; ?>, '<?php echo addslashes($row['nama_lengkap']); ?>')">Verifikasi</button>
                        <button class="hapus" onclick="if(confirm('Yakin hapus data ini?')) window.location.href='hapusdata.php?id=<?php echo $row['id']; ?>'">Hapus</button>
                      </td>
                    </tr>
                  <?php endwhile; ?>
                <?php else: ?>
                  <tr>
                    <td colspan="16" style="text-align:center;">Tidak ada data ditemukan.</td>
                  </tr>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
        </div>

        <!-- FOOTER -->
        <footer>
          <img src="../assets/image/logodprd.png" alt="DPRD Logo">
          <img src="../assets/image/psiputih.png" alt="PSI Logo">
          Hak cipta © 2025 - Partai Solidaritas Indonesia
        </footer>
      </div>
    </div>
  </div>

  <!-- JAVASCRIPT UNTUK VERIFIKASI -->
  <script>
    function verifikasiData(id, nama) {
      // Pop-up konfirmasi pertama
      if (confirm('Apakah Anda yakin ingin memverifikasi data atas nama:\n\n' + nama + '?')) {
        // Redirect ke proses verifikasi
        window.location.href = 'proses_verifikasi.php?id=' + id;
      }
    }
  </script>
</body>
</html>
