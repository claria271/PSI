<?php
//admin/datakeluarga.php
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
$adminName = 'Admin';
$adminPhoto = '../assets/image/user.png';
$keluargaAdmin = null;

// Ambil data login admin
if (isset($_SESSION['alamat_email']) && !empty($_SESSION['alamat_email'])) {
    $stmt = $conn->prepare("SELECT * FROM login WHERE alamat_email = ? AND role = 'admin' LIMIT 1");
    $stmt->bind_param('s', $_SESSION['alamat_email']);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($res->num_rows > 0) {
        $admin = $res->fetch_assoc();
        
        // Ambil data keluarga admin untuk nama lengkap
        $stmtK = $conn->prepare("SELECT * FROM keluarga WHERE user_id = ? LIMIT 1");
        $stmtK->bind_param('i', $admin['id']);
        $stmtK->execute();
        $resK = $stmtK->get_result();
        if ($resK->num_rows > 0) {
            $keluargaAdmin = $resK->fetch_assoc();
        }
        $stmtK->close();
    }
    $stmt->close();
}

// Jika admin tidak ditemukan, redirect ke login
if (!$admin) {
    session_destroy();
    header("Location: ../user/login.php");
    exit();
}

// Tentukan nama & foto admin
$adminName = !empty($keluargaAdmin['nama_lengkap']) ? $keluargaAdmin['nama_lengkap'] : 'Admin';
$adminPhoto = !empty($admin['foto']) ? '../uploads/' . $admin['foto'] : '../assets/image/user.png';

// ================== LOGIKA DATA KELUARGA ================== //

// UMR per orang (Surabaya 2025)
define('UMR_PERSON', 4725479);

// Ambil filter dari GET
$search      = isset($_GET['search']) ? trim($_GET['search']) : '';
$status_umr  = isset($_GET['status_umr']) ? $_GET['status_umr'] : '';

// Bangun query dengan kondisi dinamis
$conditions = [];

if ($search !== '') {
    $safe = mysqli_real_escape_string($conn, $search);
    $conditions[] = "(k.nama_lengkap LIKE '%$safe%' 
                  OR k.nik LIKE '%$safe%' 
                  OR k.no_wa LIKE '%$safe%'
                  OR l.alamat_email LIKE '%$safe%')";
}

// Filter UMR berdasarkan UMR PER ORANG
if ($status_umr === 'Dibawah') {
    $umr = UMR_PERSON;
    $conditions[] = "( (k.total_penghasilan / NULLIF(k.jumlah_anggota,0)) < $umr )";
} elseif ($status_umr === 'Diatas') {
    $umr = UMR_PERSON;
    $conditions[] = "( (k.total_penghasilan / NULLIF(k.jumlah_anggota,0)) >= $umr )";
}

// Join dengan tabel verifikasi + login
$query = "SELECT k.*, 
          v.id as verification_id,
          v.verified_by,
          v.verified_at,
          l.alamat_email AS email_pengisi
          FROM keluarga k
          LEFT JOIN verifikasi v ON k.id = v.keluarga_id
          LEFT JOIN login l ON k.user_id = l.id";

if (!empty($conditions)) {
    $query .= " WHERE " . implode(" AND ", $conditions);
}

$query .= " ORDER BY k.created_at DESC";

$result = mysqli_query($conn, $query);

// Hitung total data terverifikasi
$total_query = "SELECT COUNT(*) as total FROM verifikasi";
$total_result = mysqli_query($conn, $total_query);
$total_data = mysqli_fetch_assoc($total_result)['total'];

// Tampilkan pesan dari session
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

$errorMessage = '';
if (isset($_SESSION['error'])) {
    $errorMessage = $_SESSION['error'];
    unset($_SESSION['error']);
}
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
      width: 100%;
      background: linear-gradient(to right, #000000 0%, #ffffff 100%);
      padding: 6px 48px;            /* ✅ BARIS NAVBAR DIKECILIN */
      display: flex;
      justify-content: space-between;
      align-items: center;
      position: sticky;
      top: 0;
      z-index: 20;
      box-shadow: 0 4px 18px rgba(15,23,42,0.20);
      overflow: visible;            /* ✅ Biar logo boleh “keluar” dari bar */
    }
   /* ✅ LOGO TETAP BESAR (TIDAK DIUBAH) */
    .nav-logo-image img {
      height: 62px;                 /* tetap 62px */
      width: auto;
      display: block;
      object-fit: contain;
      transform: translateY(-6px);  /* ✅ Naik dikit biar bar tetap tipis */
    }
   nav a {
      color: #000000;
      position: relative;
      font-weight: 600;
      cursor: pointer;
      font-size: 14px;              /* ✅ teks agak diperkecil biar pas di bar kecil */
      line-height: 1;
    }

    nav a:hover::after,
    nav a.active::after {
      width: 100%;
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

    /* ALERT */
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

    .alert-error {
      background: #f8d7da;
      color: #721c24;
      border: 1px solid #f5c6cb;
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

    /* CARD, FILTER, TABEL */
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
      min-width: 1200px;
      border-radius: 10px;
      overflow: hidden;
    }

    th, td {
      padding: 10px;
      border: 1px solid #ccc;
      font-size: 13px;
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

    /* BARIS TERVERIFIKASI */
    tr.verified {
      background: #e8f5e9 !important;
      border-left: 4px solid #4CAF50 !important;
    }

    tr.verified:hover {
      background: #c8e6c9 !important;
    }

    tr.verified td:first-child {
      border-left: 4px solid #4CAF50;
    }

    .verified-badge {
      display: inline-block;
      background: #4CAF50;
      color: white;
      padding: 4px 10px;
      border-radius: 12px;
      font-size: 11px;
      font-weight: 600;
      margin-left: 8px;
      box-shadow: 0 2px 4px rgba(76, 175, 80, 0.3);
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
      margin-bottom: 4px;
      font-size: 12px;
      transition: 0.3s;
      white-space: nowrap;
    }

    .aksi .edit {
      background: #2196F3;
      color: #fff;
    }

    .aksi .verifikasi {
      background: #4CAF50;
      color: #fff;
    }

    .aksi .verifikasi:disabled {
      background: #9E9E9E;
      cursor: not-allowed;
      opacity: 0.6;
    }

    .aksi .hapus {
      background: #f44336;
      color: #fff;
    }

    .aksi button:hover:not(:disabled) {
      opacity: 0.8;
      transform: translateY(-1px);
      box-shadow: 0 2px 5px rgba(0,0,0,0.2);
    }

    /* FOOTER */
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

    /* MODAL */
    .modal {
      display: none;
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: rgba(0,0,0,0.6);
      z-index: 9999;
      justify-content: center;
      align-items: center;
    }

    .modal-content {
      background: #fff;
      padding: 30px;
      border-radius: 12px;
      max-width: 500px;
      width: 90%;
      box-shadow: 0 10px 40px rgba(0,0,0,0.3);
    }

    .modal-content h3 {
      margin-bottom: 20px;
      color: #000;
      font-size: 20px;
      text-align: center;
    }

    .modal-content p {
      margin-bottom: 20px;
      color: #666;
      text-align: center;
      font-size: 14px;
    }

    .modal-content label {
      display: block;
      margin-bottom: 8px;
      font-weight: 600;
      color: #000;
    }

    .modal-content input,
    .modal-content select,
    .modal-content textarea {
      width: 100%;
      padding: 10px;
      border: 1px solid #ccc;
      border-radius: 6px;
      font-size: 14px;
      margin-bottom: 15px;
      background: #f5f5f5;
      font-family: inherit;
    }

    .modal-content textarea {
      resize: vertical;
      min-height: 80px;
    }

    .modal-content select {
      cursor: pointer;
    }

    .modal-buttons {
      display: flex;
      gap: 10px;
      justify-content: center;
    }

    .modal-buttons button {
      padding: 10px 24px;
      border: none;
      border-radius: 6px;
      font-size: 14px;
      font-weight: 600;
      cursor: pointer;
      transition: 0.3s;
    }

    .btn-submit {
      background: #4CAF50;
      color: #fff;
    }

    .btn-submit:hover {
      background: #388E3C;
    }

    .btn-cancel {
      background: #9E9E9E;
      color: #fff;
    }

    .btn-cancel:hover {
      background: #757575;
    }

    .required {
      color: red;
      font-weight: bold;
    }
  </style>
</head>
<body>
  <!-- HEADER -->
 <header>
  <div class="nav-left">
    <div class="nav-logo-image">
      <img src="../assets/image/logou.png" alt="Logo">
    </div>
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
        <a href="permintaanedit.php">📝 Kelola Edit User</a>
        <a href="datakeluarga.php"class="active">Data Keluarga</a>
        <a href="tambah_admin.php">➕ Tambah Admin</a>
        <a href="verifikasi.php">Hasil Verifikasi</a>
        <a href="laporan.php">Laporan</a>
        <a href="pengaduan_admin.php">Pengaduan</a>
        <a href="logoutadmin.php">Logout</a>
      </nav>
    </aside>

    <!-- CONTENT -->
    <div class="content">
      <div class="page-header">
        <h2>📋 Data Keluarga</h2>
        <p>Kelola data keluarga yang telah terdaftar dalam sistem. UMR per orang: Rp <?= number_format(UMR_PERSON, 0, ',', '.') ?></p>
      </div>

      <div class="content-scroll">
        <!-- ALERT -->
        <?php if ($successMessage): ?>
          <div class="alert alert-success">
            ✓ <?= $successMessage ?>
          </div>
        <?php endif; ?>

        <?php if ($warningMessage): ?>
          <div class="alert alert-warning">
            ⚠ <?= $warningMessage ?>
          </div>
        <?php endif; ?>

        <?php if ($errorMessage): ?>
          <div class="alert alert-error">
            ✗ <?= $errorMessage ?>
          </div>
        <?php endif; ?>

        <div class="card">
          <div class="card-header">
            <button class="btn-tambah" onclick="window.location.href='tambahdata.php'">+ Tambah Data</button>

            <form method="GET" class="filters">
              <input
                type="text"
                name="search"
                placeholder="Cari Nama, NIK, No HP, Email"
                value="<?= e($search) ?>"
              >

              <select name="status_umr" onchange="this.form.submit()">
                <option value=""  <?= $status_umr === '' ? 'selected' : '' ?>>Semua Status</option>
                <option value="Dibawah" <?= $status_umr === 'Dibawah' ? 'selected' : '' ?>>Dibawah UMR</option>
                <option value="Diatas"  <?= $status_umr === 'Diatas'  ? 'selected' : '' ?>>Diatas UMR</option>
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
                  <th>Email Pengisi</th>
                  <th>Alamat KTP</th>
                  <th>Alamat Domisili</th>
                  <th>Jumlah Anggota</th>
                  <th>Jumlah Bekerja</th>
                  <th>Total Penghasilan</th>
                  <th>Rata-rata/Orang</th>
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
                      $anggota     = (int)$row['jumlah_anggota'];
                      $penghasilan = (float)$row['total_penghasilan'];
                      $per_orang   = $anggota > 0 ? ($penghasilan / $anggota) : 0;

                      $kategori = ($per_orang < UMR_PERSON)
                        ? "<span class='dibawah'>Dibawah UMR</span>"
                        : "<span class='diatas'>Diatas UMR</span>";

                      $isVerified = !empty($row['verification_id']);
                    ?>
                    <tr class="<?= $isVerified ? 'verified' : '' ?>">
                      <td>
                        <?= e($row['nama_lengkap']) ?>
                        <?php if ($isVerified): ?>
                          <span class="verified-badge">✓ Terverifikasi</span>
                        <?php endif; ?>
                      </td>
                      <td><?= e($row['nik']) ?></td>
                      <td><?= e($row['no_wa']) ?></td>
                      <td><?= e($row['email_pengisi']) ?></td>
                      <td><?= e($row['alamat']) ?></td>
                      <td><?= e($row['domisili'] ?? '-') ?></td>
                      <td><?= e($row['jumlah_anggota']) ?></td>
                      <td><?= e($row['jumlah_bekerja']) ?></td>
                      <td>Rp <?= number_format($penghasilan, 0, ',', '.') ?></td>
                      <td>Rp <?= number_format($per_orang, 0, ',', '.') ?></td>
                      <td><?= $kategori ?></td>
                      <td><?= e($row['created_at']) ?></td>
                      <td><?= e($row['updated_at']) ?></td>
                      <td class="aksi">
                        <button class="edit" onclick="window.location.href='editdata.php?id=<?= $row['id'] ?>'">✏️ Edit</button>
                        <button 
                          class="verifikasi" 
                          onclick="openModalVerifikasi(<?= $row['id'] ?>, '<?= addslashes($row['nama_lengkap']) ?>')"
                          <?= $isVerified ? 'disabled title="Data sudah diverifikasi"' : '' ?>
                        >
                          <?= $isVerified ? '✓ Verified' : '✓ Verifikasi' ?>
                        </button>
                        <button class="hapus" onclick="if(confirm('Yakin hapus data <?= addslashes($row['nama_lengkap']) ?>?')) window.location.href='hapusdata.php?id=<?= $row['id'] ?>'">🗑️ Hapus</button>
                      </td>
                    </tr>
                  <?php endwhile; ?>
                <?php else: ?>
                  <tr>
                    <td colspan="14" style="text-align:center; padding: 30px;">Tidak ada data ditemukan.</td>
                  </tr>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
        </div>

        <!-- FOOTER -->
        <!--<footer>
          <img src="../assets/image/logodprd.png" alt="DPRD Logo">
          <img src="../assets/image/psiputih.png" alt="PSI Logo">
          Hak cipta © 2025 - Partai Solidaritas Indonesia
        </footer>-->
      </div>
    </div>
  </div>

  <!-- MODAL VERIFIKASI -->
  <div id="modalVerifikasi" class="modal">
    <div class="modal-content">
      <h3>✅ Verifikasi Data Keluarga</h3>
      <p>Verifikasi data untuk: <br><strong id="namaKeluarga"></strong></p>
      
      <form id="formVerifikasi" method="POST" action="proses_verifikasi.php">
        <input type="hidden" name="id" id="keluargaId">
        
        <label>Bentuk Bantuan: <span class="required">*</span></label>
        <select name="bentuk_bantuan" id="bentukBantuan" required>
          <option value="">-- Pilih Bentuk Bantuan --</option>
          <option value="Bantuan Pendidikan">Bantuan Pendidikan</option>
          <option value="Alat Bantu Dengar">Alat Bantu Dengar</option>
          <option value="Kursi Roda">Kursi Roda</option>
          <option value="Kesehatan">Kesehatan</option>
          <option value="Sembako">Sembako</option>
          <option value="Uang Muka">Bantuan Uang</option>
          <option value="Lainnya">Lainnya</option>
        </select>

        <div class="modal-buttons">
          <button type="submit" class="btn-submit">✓ Verifikasi Sekarang</button>
          <button type="button" class="btn-cancel" onclick="closeModal()">✗ Batal</button>
        </div>
      </form>
    </div>
  </div>

  <script>
    function openModalVerifikasi(id, nama) {
      document.getElementById('keluargaId').value = id;
      document.getElementById('namaKeluarga').textContent = nama;
      document.getElementById('bentukBantuan').value = '';
      document.getElementById('modalVerifikasi').style.display = 'flex';
    }

    function closeModal() {
      document.getElementById('modalVerifikasi').style.display = 'none';
    }

    // Close modal when clicking outside
    window.onclick = function(event) {
      const modal = document.getElementById('modalVerifikasi');
      if (event.target == modal) {
        closeModal();
      }
    }

    // Auto-hide alerts
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

    // Validate form before submit
    document.getElementById('formVerifikasi').addEventListener('submit', function(e) {
      const bantuan = document.getElementById('bentukBantuan').value;
      if (!bantuan) {
        e.preventDefault();
        alert('⚠️ Pilih bentuk bantuan terlebih dahulu!');
        return false;
      }
    });
  </script>
</body>
</html>