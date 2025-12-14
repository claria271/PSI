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

// Ambil ID dari parameter
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id <= 0) {
    $_SESSION['error'] = 'ID tidak valid!';
    header("Location: verifikasi.php");
    exit();
}

// Ambil data verifikasi
$stmt = $conn->prepare("SELECT * FROM verifikasi WHERE id = ?");
$stmt->bind_param('i', $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $_SESSION['error'] = 'Data tidak ditemukan!';
    header("Location: verifikasi.php");
    exit();
}

$data = $result->fetch_assoc();

// Proses update data
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama_lengkap = trim($_POST['nama_lengkap']);
    $nik = trim($_POST['nik']);
    $no_wa = trim($_POST['no_wa']);
    $alamat = trim($_POST['alamat']);
    $domisili = trim($_POST['domisili']);
    $jumlah_anggota = (int)$_POST['jumlah_anggota'];
    $jumlah_bekerja = (int)$_POST['jumlah_bekerja'];
    $total_penghasilan = (int)str_replace('.', '', $_POST['total_penghasilan']); // Hapus titik
    $bantuan = trim($_POST['bantuan']);
    
    try {
        $stmt_update = $conn->prepare("
            UPDATE verifikasi SET
                nama_lengkap = ?,
                nik = ?,
                no_wa = ?,
                alamat = ?,
                domisili = ?,
                jumlah_anggota = ?,
                jumlah_bekerja = ?,
                total_penghasilan = ?,
                bantuan = ?
            WHERE id = ?
        ");
        
        $stmt_update->bind_param(
            'sssssiidsi',
            $nama_lengkap,
            $nik,
            $no_wa,
            $alamat,
            $domisili,
            $jumlah_anggota,
            $jumlah_bekerja,
            $total_penghasilan,
            $bantuan,
            $id
        );
        
        if ($stmt_update->execute()) {
            $_SESSION['success'] = '✓ Data verifikasi berhasil diupdate!';
            header("Location: verifikasi.php");
            exit();
        } else {
            throw new Exception('Gagal mengupdate data');
        }
    } catch (Exception $e) {
        $_SESSION['error'] = 'Terjadi kesalahan: ' . $e->getMessage();
    }
}

// Ambil data admin untuk sidebar
$admin = null;
$adminName = 'Admin';
$adminPhoto = '../assets/image/user.png';
$keluargaAdmin = null;

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

$adminName = !empty($keluargaAdmin['nama_lengkap']) ? $keluargaAdmin['nama_lengkap'] : 'Admin';
$adminPhoto = !empty($admin['foto']) ? '../uploads/' . $admin['foto'] : '../assets/image/user.png';
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Edit Data Verifikasi - PSI</title>
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

    header .logo img {
      height: 40px;
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

    .content {
      flex: 1 1 auto;
      display: flex;
      flex-direction: column;
      background: #f9f9f9;
      padding: 25px 30px;
      min-width: 0;
      min-height: 0;
      overflow-y: auto;
    }

    .page-header {
      margin-bottom: 20px;
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

    .alert {
      padding: 15px 20px;
      border-radius: 8px;
      margin-bottom: 20px;
      font-size: 14px;
      font-weight: 500;
      animation: slideIn 0.3s ease-out;
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

    .card {
      background: #fff;
      border-radius: 12px;
      box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
      padding: 30px;
      max-width: 800px;
    }

    .form-group {
      margin-bottom: 20px;
    }

    .form-group label {
      display: block;
      margin-bottom: 8px;
      font-weight: 600;
      color: #000;
      font-size: 14px;
    }

    .form-group input,
    .form-group select,
    .form-group textarea {
      width: 100%;
      padding: 10px 12px;
      border: 1px solid #ccc;
      border-radius: 6px;
      font-size: 14px;
      font-family: 'Poppins', sans-serif;
      background: #f5f5f5;
      transition: 0.3s;
    }

    .form-group input:focus,
    .form-group select:focus,
    .form-group textarea:focus {
      outline: none;
      border-color: #4CAF50;
      background: #fff;
    }

    .form-group textarea {
      resize: vertical;
      min-height: 80px;
    }

    .form-row {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 20px;
    }

    .btn-group {
      display: flex;
      gap: 10px;
      margin-top: 30px;
    }

    .btn {
      flex: 1;
      padding: 12px 20px;
      border: none;
      border-radius: 6px;
      cursor: pointer;
      font-weight: 600;
      font-size: 14px;
      transition: 0.3s;
    }

    .btn-primary {
      background: #4CAF50;
      color: #fff;
    }

    .btn-primary:hover {
      background: #388E3C;
      transform: translateY(-1px);
      box-shadow: 0 4px 8px rgba(76, 175, 80, 0.3);
    }

    .btn-secondary {
      background: #999;
      color: #fff;
    }

    .btn-secondary:hover {
      background: #777;
    }

    footer {
      margin-top: 20px;
      padding: 15px 5%;
      text-align: center;
      background: linear-gradient(to right, #ffffff, #000000);
      font-size: 14px;
      color: #fff;
    }

    footer img {
      height: 20px;
      vertical-align: middle;
      margin: 0 5px;
      filter: brightness(0) invert(1);
    }

    @media (max-width: 768px) {
      .form-row {
        grid-template-columns: 1fr;
      }
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
          <img src="<?= e($adminPhoto) ?>" alt="Admin Photo">
        </div>
        <div class="admin-name" onclick="window.location.href='profil_admin.php'">
          <?= e($adminName) ?>
        </div>
      </div>
      <nav>
        <a href="dashboardadmin.php">Dashboard</a>
        <a href="datakeluarga.php">Data Keluarga</a>
        <a href="kelola_admin.php">Kelola Admin</a>
        <a href="permintaanedit.php">Permintaan Edit</a>
        <a href="verifikasi.php" class="active">Hasil Verifikasi</a>
        <a href="laporan.php">Laporan</a>
        <a href="logoutadmin.php">Logout</a>
      </nav>
    </aside>

    <div class="content">
      <div class="page-header">
        <h2>✏️ Edit Data Verifikasi</h2>
        <p>Ubah informasi data yang telah diverifikasi</p>
      </div>

      <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-error">
          <?= $_SESSION['error']; unset($_SESSION['error']); ?>
        </div>
      <?php endif; ?>

      <div class="card">
        <form method="POST">
          <div class="form-group">
            <label>Nama Lengkap *</label>
            <input type="text" name="nama_lengkap" value="<?= e($data['nama_lengkap']) ?>" required>
          </div>

          <div class="form-row">
            <div class="form-group">
              <label>NIK *</label>
              <input type="text" name="nik" value="<?= e($data['nik']) ?>" required maxlength="16">
            </div>
            <div class="form-group">
              <label>No WhatsApp *</label>
              <input type="text" name="no_wa" id="no_wa" value="<?= e($data['no_wa']) ?>" required placeholder="+6281234567890">
            </div>
          </div>

          <div class="form-group">
            <label>Alamat KTP *</label>
            <textarea name="alamat" required><?= e($data['alamat']) ?></textarea>
          </div>

          <div class="form-group">
            <label>Alamat Domisili</label>
            <textarea name="domisili"><?= e($data['domisili'] ?? '') ?></textarea>
          </div>

          <div class="form-row">
            <div class="form-group">
              <label>Jumlah Anggota Keluarga *</label>
              <input type="number" name="jumlah_anggota" value="<?= e($data['jumlah_anggota']) ?>" required min="1">
            </div>
            <div class="form-group">
              <label>Jumlah yang Bekerja *</label>
              <input type="number" name="jumlah_bekerja" value="<?= e($data['jumlah_bekerja']) ?>" required min="0">
            </div>
          </div>

          <div class="form-group">
            <label>Total Penghasilan Keluarga (Rp) *</label>
            <input type="text" name="total_penghasilan" id="total_penghasilan" value="<?= number_format($data['total_penghasilan'], 0, ',', '.') ?>" required placeholder="5.000.000">
          </div>

          <div class="form-group">
            <label>Bentuk Bantuan *</label>
            <select name="bantuan" required>
              <option value="">-- Pilih Bentuk Bantuan --</option>
              <option value="Bantuan Pendidikan" <?= $data['bantuan'] === 'Bantuan Pendidikan' ? 'selected' : '' ?>>📚 Bantuan Pendidikan</option>
              <option value="Alat Bantu Dengar" <?= $data['bantuan'] === 'Alat Bantu Dengar' ? 'selected' : '' ?>>👂 Alat Bantu Dengar</option>
              <option value="Kursi Roda" <?= $data['bantuan'] === 'Kursi Roda' ? 'selected' : '' ?>>♿ Kursi Roda</option>
              <option value="Kesehatan" <?= $data['bantuan'] === 'Kesehatan' ? 'selected' : '' ?>>🏥 Kesehatan</option>
              <option value="Sembako" <?= $data['bantuan'] === 'Sembako' ? 'selected' : '' ?>>🛒 Sembako</option>
              <option value="Uang Muka" <?= $data['bantuan'] === 'Uang Muka' ? 'selected' : '' ?>>💰 Bantuan Uang</option>
              <option value="Lainnya" <?= $data['bantuan'] === 'Lainnya' ? 'selected' : '' ?>>📦 Lainnya</option>
            </select>
          </div>

          <div class="btn-group">
            <button type="button" class="btn btn-secondary" onclick="window.location.href='verifikasi.php'">✗ Batal</button>
            <button type="submit" class="btn btn-primary">💾 Simpan Perubahan</button>
          </div>
        </form>
      </div>

      <footer>
        <img src="../assets/image/logodprd.png" alt="DPRD Logo">
        <img src="../assets/image/psiputih.png" alt="PSI Logo">
        Hak cipta © 2025 - Partai Solidaritas Indonesia
      </footer>
    </div>
  </div>

  <script>
    // Format No WhatsApp dengan +62
    const noWaInput = document.getElementById('no_wa');
    
    noWaInput.addEventListener('input', function(e) {
      let value = e.target.value.replace(/\D/g, '');
      
      if (value.startsWith('0')) {
        value = '62' + value.substring(1);
      }
      
      if (!value.startsWith('62')) {
        value = '62' + value;
      }
      
      e.target.value = '+' + value;
    });

    // Format Total Penghasilan dengan titik pemisah ribuan
    const penghasilanInput = document.getElementById('total_penghasilan');
    
    penghasilanInput.addEventListener('input', function(e) {
      let value = e.target.value.replace(/\D/g, '');
      
      if (value) {
        value = parseInt(value).toLocaleString('id-ID');
      }
      
      e.target.value = value;
    });

    // Sebelum submit, hapus titik dari penghasilan
    document.querySelector('form').addEventListener('submit', function(e) {
      const penghasilan = document.getElementById('total_penghasilan');
      penghasilan.value = penghasilan.value.replace(/\./g, '');
    });
  </script>
</body>
</html>