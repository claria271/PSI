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

// Ambil data admin
$admin = null;
$adminName = 'Admin';
$adminPhoto = '../assets/image/admin_photo.jpg';

// Cek session yang tersedia
if (isset($_SESSION['id'])) {
    $stmt = $conn->prepare("SELECT * FROM login WHERE id = ? LIMIT 1");
    $stmt->bind_param('i', $_SESSION['id']);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($res->num_rows > 0) {
        $admin = $res->fetch_assoc();
    }
} elseif (!empty($_SESSION['alamat_email'])) {
    $stmt = $conn->prepare("SELECT * FROM login WHERE alamat_email = ? LIMIT 1");
    $stmt->bind_param('s', $_SESSION['alamat_email']);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($res->num_rows > 0) {
        $admin = $res->fetch_assoc();
    }
}

// Tentukan nama & foto admin
if ($admin) {
    $adminName = !empty($admin['nama_lengkap']) ? $admin['nama_lengkap'] : 'Admin';
    $adminPhoto = !empty($admin['foto']) ? '../uploads/' . $admin['foto'] : '../assets/image/admin_photo.jpg';
}

// UMR per orang
define('UMR_PERSON', 4725479);

// Ambil filter
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$bantuan_filter = isset($_GET['bantuan']) ? $_GET['bantuan'] : '';

// Query data verifikasi
$conditions = [];
if ($search !== '') {
    $safe = mysqli_real_escape_string($conn, $search);
    $conditions[] = "(nama_lengkap LIKE '%$safe%' OR nik LIKE '%$safe%' OR no_wa LIKE '%$safe%')";
}
if ($bantuan_filter !== '') {
    $safeBantuan = mysqli_real_escape_string($conn, $bantuan_filter);
    $conditions[] = "bantuan = '$safeBantuan'";
}

$query = "SELECT * FROM verifikasi";
if (!empty($conditions)) {
    $query .= " WHERE " . implode(" AND ", $conditions);
}
$query .= " ORDER BY verified_at DESC";

$result = mysqli_query($conn, $query);

// Hitung total data terverifikasi
$total_query = "SELECT COUNT(*) as total FROM verifikasi";
$total_result = mysqli_query($conn, $total_query);
$total_data = mysqli_fetch_assoc($total_result)['total'];

// Ambil pesan dari session
$successMessage = '';
if (isset($_SESSION['success'])) {
    $successMessage = $_SESSION['success'];
    unset($_SESSION['success']);
}

$warningMessage = '';
if (isset($_SESSION['warning'])) {
    $warningMessage = $_SESSION['warning'];
    unset($_SESSION['warning']);
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Data Verifikasi - PSI</title>
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

    .layout {
      flex: 1 1 auto;
      display: flex;
      min-height: 0;
    }

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

    .stats-badge {
      display: inline-block;
      background: #4CAF50;
      color: white;
      padding: 5px 15px;
      border-radius: 20px;
      font-size: 13px;
      font-weight: 600;
      margin-top: 5px;
    }

    /* 🔥 ALERT MESSAGES */
    .alert {
      padding: 15px 20px;
      border-radius: 8px;
      margin-bottom: 20px;
      font-size: 14px;
      font-weight: 500;
      animation: slideIn 0.3s ease-out;
    }

    .alert-success {
      background: #d4edda;
      color: #155724;
      border: 1px solid #c3e6cb;
    }

    .alert-warning {
      background: #fff3cd;
      color: #856404;
      border: 1px solid #ffeeba;
    }

    @keyframes slideIn {
      from {
        opacity: 0;
        transform: translateY(-10px);
      }
      to {
        opacity: 1;
        transform: translateY(0);
      }
    }

    .content-scroll {
      flex: 1 1 auto;
      overflow-y: auto;
      padding-right: 5px;
      min-height: 0;
    }

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
      min-width: 1200px;
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
      background: #4CAF50;
      color: white;
      font-weight: 600;
      position: sticky;
      top: 0;
      z-index: 1;
    }

    tr:nth-child(even) {
      background: #f0f8f0;
    }

    .badge-verified {
      background: #4CAF50;
      color: white;
      padding: 4px 10px;
      border-radius: 12px;
      font-size: 12px;
      font-weight: 600;
    }

    /* 🔥 BADGE BANTUAN */
    .badge-bantuan {
      display: inline-block;
      padding: 5px 12px;
      border-radius: 15px;
      font-size: 12px;
      font-weight: 600;
      background: #2196F3;
      color: white;
    }

    /* 🔥 STYLING UNTUK TOMBOL AKSI */
    .aksi {
      white-space: nowrap;
    }

    .aksi button {
      padding: 6px 12px;
      border: none;
      border-radius: 5px;
      cursor: pointer;
      margin-right: 4px;
      font-size: 12px;
      font-weight: 600;
      transition: 0.3s;
    }

    .aksi .edit {
      background: #2196F3;
      color: #fff;
    }

    .aksi .edit:hover {
      background: #1976D2;
      transform: translateY(-1px);
      box-shadow: 0 2px 5px rgba(33, 150, 243, 0.3);
    }

    .aksi .hapus {
      background: #f44336;
      color: #fff;
    }

    .aksi .hapus:hover {
      background: #d32f2f;
      transform: translateY(-1px);
      box-shadow: 0 2px 5px rgba(244, 67, 54, 0.3);
    }

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
  </style>
</head>
<body>
  <header>
    <div class="logo">
      <img src="../assets/image/logo.png" alt="PSI Logo">
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
        <a href="dashboardadmin.php">Dashboard</a>
        <a href="datakeluarga.php">Data Keluarga</a>
        <a href="tambah_admin.php">➕ Tambah Admin</a>
        <a href="verifikasi.php" class="active">Hasil Verifikasi</a>
        <a href="laporan.php">Laporan</a>
        <a href="../user/logout.php">Logout</a>
      </nav>
    </aside>

    <div class="content">
      <div class="page-header">
        <h2>Data Terverifikasi</h2>
        <p>Daftar data keluarga yang telah diverifikasi oleh admin</p>
        <span class="stats-badge">Total: <?php echo $total_data; ?> Data</span>
      </div>

      <div class="content-scroll">
        <!-- 🔥 TAMPILKAN ALERT MESSAGES -->
        <?php if ($successMessage): ?>
          <div class="alert alert-success">
            <?php echo $successMessage; ?>
          </div>
        <?php endif; ?>

        <?php if ($warningMessage): ?>
          <div class="alert alert-warning">
            <?php echo $warningMessage; ?>
          </div>
        <?php endif; ?>

        <div class="card">
          <div class="card-header">
            <h3 style="color: #4CAF50; font-size: 18px;">📋 Data Verifikasi</h3>
            
            <form method="GET" class="filters">
              <input
                type="text"
                name="search"
                placeholder="Cari Nama, NIK, No HP"
                value="<?php echo e($search); ?>"
              >

              <select name="bantuan" onchange="this.form.submit()">
                <option value="">Semua Bantuan</option>
                <option value="Bantuan Pendidikan" <?php echo $bantuan_filter === 'Bantuan Pendidikan' ? 'selected' : ''; ?>>Bantuan Pendidikan</option>
                <option value="Alat Bantu Dengar" <?php echo $bantuan_filter === 'Alat Bantu Dengar' ? 'selected' : ''; ?>>Alat Bantu Dengar</option>
                <option value="Kursi Roda" <?php echo $bantuan_filter === 'Kursi Roda' ? 'selected' : ''; ?>>Kursi Roda</option>
                <option value="Kesehatan" <?php echo $bantuan_filter === 'Kesehatan' ? 'selected' : ''; ?>>Kesehatan</option>
                <option value="Sembako" <?php echo $bantuan_filter === 'Sembako' ? 'selected' : ''; ?>>Sembako</option>
                <option value="Uang Muka" <?php echo $bantuan_filter === 'Uang Muka' ? 'selected' : ''; ?>>Bantuan Uang</option>
                <option value="Lainnya" <?php echo $bantuan_filter === 'Lainnya' ? 'selected' : ''; ?>>Lainnya</option>
              </select>

              <button type="submit" style="display:none;"></button>
            </form>
          </div>

          <div class="table-container">
            <table>
              <thead>
                <tr>
                  <th>No</th>
                  <th>Nama Lengkap</th>
                  <th>NIK</th>
                  <th>No WA</th>
                  <th>Alamat</th>
                  <th>Jumlah Anggota</th>
                  <th>Total Penghasilan</th>
                  <th>Bantuan</th>
                  <th>Status</th>
                  <th>Diverifikasi Oleh</th>
                  <th>Tanggal Verifikasi</th>
                  <th>Aksi</th>
                </tr>
              </thead>
              <tbody>
                <?php if (mysqli_num_rows($result) > 0): ?>
                  <?php $no = 1; ?>
                  <?php while ($row = mysqli_fetch_assoc($result)): ?>
                    <tr>
                      <td><?php echo $no++; ?></td>
                      <td><?php echo e($row['nama_lengkap']); ?></td>
                      <td><?php echo e($row['nik']); ?></td>
                      <td><?php echo e($row['no_wa']); ?></td>
                      <td><?php echo e($row['alamat']); ?></td>
                      <td><?php echo e($row['jumlah_anggota']); ?></td>
                      <td><?php echo e(number_format($row['total_penghasilan'], 0, ',', '.')); ?></td>
                      <td><span class="badge-bantuan"><?php echo e($row['bantuan']); ?></span></td>
                      <td><span class="badge-verified"><?php echo e($row['status_verifikasi']); ?></span></td>
                      <td><?php echo e($row['verified_by']); ?></td>
                      <td><?php echo e(date('d/m/Y H:i', strtotime($row['verified_at']))); ?></td>
                      <td class="aksi">
                        <button class="edit" onclick="window.location.href='edit_verifikasi.php?id=<?php echo $row['id']; ?>'">Edit</button>
                        <button class="hapus" onclick="hapusVerifikasi(<?php echo $row['id']; ?>, '<?php echo addslashes($row['nama_lengkap']); ?>')">Hapus</button>
                      </td>
                    </tr>
                  <?php endwhile; ?>
                <?php else: ?>
                  <tr>
                    <td colspan="12" style="text-align:center; padding: 30px;">
                      Belum ada data yang diverifikasi.
                    </td>
                  </tr>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
        </div>

        <footer>
          <img src="../assets/image/logodprd.png" alt="DPRD Logo">
          <img src="../assets/image/psiputih.png" alt="PSI Logo">
          Hak cipta © 2025 - Partai Solidaritas Indonesia
        </footer>
      </div>
    </div>
  </div>

  <script>
    // 🔥 FUNGSI HAPUS VERIFIKASI
    function hapusVerifikasi(id, nama) {
      if (confirm('Apakah Anda yakin ingin menghapus data verifikasi atas nama:\n\n' + nama + '?\n\n⚠️ Data akan dihapus dari tabel verifikasi, namun tetap ada di tabel keluarga.')) {
        window.location.href = 'hapus_verifikasi.php?id=' + id;
      }
    }

    // 🔥 AUTO-HIDE ALERT SETELAH 5 DETIK
    setTimeout(function() {
      const alerts = document.querySelectorAll('.alert');
      alerts.forEach(function(alert) {
        alert.style.transition = 'opacity 0.5s';
        alert.style.opacity = '0';
        setTimeout(function() {
          alert.remove();
        }, 500);
      });
    }, 5000);
  </script>
</body>
</html>