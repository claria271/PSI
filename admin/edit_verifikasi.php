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
    $dapil = trim($_POST['dapil']);
    $kecamatan = trim($_POST['kecamatan']);
    $jumlah_anggota = (int)$_POST['jumlah_anggota'];
    $jumlah_bekerja = (int)$_POST['jumlah_bekerja'];
    $total_penghasilan = (float)$_POST['total_penghasilan'];
    $kenal = trim($_POST['kenal']);
    $sumber = trim($_POST['sumber']);
    $bantuan = trim($_POST['bantuan']);
    
    try {
        $stmt_update = $conn->prepare("
            UPDATE verifikasi SET
                nama_lengkap = ?,
                nik = ?,
                no_wa = ?,
                alamat = ?,
                dapil = ?,
                kecamatan = ?,
                jumlah_anggota = ?,
                jumlah_bekerja = ?,
                total_penghasilan = ?,
                kenal = ?,
                sumber = ?,
                bantuan = ?
            WHERE id = ?
        ");
        
        // ✅ DIPERBAIKI: Tambahkan 'i' untuk $id (13 tipe untuk 13 variabel)
        $stmt_update->bind_param(
            'ssssssiidsssi',  // 13 karakter: s,s,s,s,s,s,i,i,d,s,s,s,i
            $nama_lengkap,
            $nik,
            $no_wa,
            $alamat,
            $dapil,
            $kecamatan,
            $jumlah_anggota,
            $jumlah_bekerja,
            $total_penghasilan,
            $kenal,
            $sumber,
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
$adminPhoto = '../assets/image/admin_photo.jpg';

if (isset($_SESSION['id'])) {
    $stmt = $conn->prepare("SELECT * FROM login WHERE id = ? AND role = 'admin' LIMIT 1");
    $stmt->bind_param('i', $_SESSION['id']);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($res->num_rows > 0) {
        $admin = $res->fetch_assoc();
    }
} elseif (isset($_SESSION['alamat_email'])) {
    $stmt = $conn->prepare("SELECT * FROM login WHERE alamat_email = ? AND role = 'admin' LIMIT 1");
    $stmt->bind_param('s', $_SESSION['alamat_email']);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($res->num_rows > 0) {
        $admin = $res->fetch_assoc();
    }
}

if ($admin) {
    $adminName = !empty($admin['nama_lengkap']) ? $admin['nama_lengkap'] : 'Admin';
    $adminPhoto = !empty($admin['foto']) ? '../uploads/' . $admin['foto'] : '../assets/image/admin_photo.jpg';
}
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
    }

    .alert-error {
      background: #f8d7da;
      color: #721c24;
      border: 1px solid #f5c6cb;
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
        <h2>Edit Data Verifikasi</h2>
        <p>Ubah informasi data yang telah diverifikasi</p>
      </div>

      <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-error">
          <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
        </div>
      <?php endif; ?>

      <div class="card">
        <form method="POST">
          <div class="form-group">
            <label>Nama Lengkap *</label>
            <input type="text" name="nama_lengkap" value="<?php echo e($data['nama_lengkap']); ?>" required>
          </div>

          <div class="form-row">
            <div class="form-group">
              <label>NIK *</label>
              <input type="text" name="nik" value="<?php echo e($data['nik']); ?>" required maxlength="16">
            </div>
            <div class="form-group">
              <label>No WhatsApp *</label>
              <input type="text" name="no_wa" value="<?php echo e($data['no_wa']); ?>" required maxlength="15">
            </div>
          </div>

          <div class="form-group">
            <label>Alamat Lengkap *</label>
            <textarea name="alamat" required><?php echo e($data['alamat']); ?></textarea>
          </div>

          <div class="form-row">
            <div class="form-group">
              <label>Dapil *</label>
              <select name="dapil" required>
                <option value="">-- Pilih Dapil --</option>
                <option value="Kota Surabaya 1" <?php echo $data['dapil'] === 'Kota Surabaya 1' ? 'selected' : ''; ?>>Kota Surabaya 1</option>
                <option value="Kota Surabaya 2" <?php echo $data['dapil'] === 'Kota Surabaya 2' ? 'selected' : ''; ?>>Kota Surabaya 2</option>
                <option value="Kota Surabaya 3" <?php echo $data['dapil'] === 'Kota Surabaya 3' ? 'selected' : ''; ?>>Kota Surabaya 3</option>
                <option value="Kota Surabaya 4" <?php echo $data['dapil'] === 'Kota Surabaya 4' ? 'selected' : ''; ?>>Kota Surabaya 4</option>
                <option value="Kota Surabaya 5" <?php echo $data['dapil'] === 'Kota Surabaya 5' ? 'selected' : ''; ?>>Kota Surabaya 5</option>
              </select>
            </div>
            <div class="form-group">
              <label>Kecamatan *</label>
              <input type="text" name="kecamatan" value="<?php echo e($data['kecamatan']); ?>" required>
            </div>
          </div>

          <div class="form-row">
            <div class="form-group">
              <label>Jumlah Anggota Keluarga *</label>
              <input type="number" name="jumlah_anggota" value="<?php echo e($data['jumlah_anggota']); ?>" required min="1">
            </div>
            <div class="form-group">
              <label>Jumlah yang Bekerja *</label>
              <input type="number" name="jumlah_bekerja" value="<?php echo e($data['jumlah_bekerja']); ?>" required min="0">
            </div>
          </div>

          <div class="form-group">
            <label>Total Penghasilan Keluarga (Rp) *</label>
            <input type="number" name="total_penghasilan" value="<?php echo e($data['total_penghasilan']); ?>" required min="0">
          </div>

          <div class="form-row">
            <div class="form-group">
              <label>Kenal PSI? *</label>
              <select name="kenal" required>
                <option value="">-- Pilih --</option>
                <option value="Ya" <?php echo $data['kenal'] === 'Ya' ? 'selected' : ''; ?>>Ya</option>
                <option value="Tidak" <?php echo $data['kenal'] === 'Tidak' ? 'selected' : ''; ?>>Tidak</option>
                <option value="Tidak pernah" <?php echo $data['kenal'] === 'Tidak pernah' ? 'selected' : ''; ?>>Tidak pernah</option>
              </select>
            </div>
            <div class="form-group">
              <label>Sumber *</label>
              <input type="text" name="sumber" value="<?php echo e($data['sumber']); ?>" required>
            </div>
          </div>

          <div class="form-group">
            <label>Bentuk Bantuan *</label>
            <select name="bantuan" required>
              <option value="">-- Pilih Bentuk Bantuan --</option>
              <option value="Bantuan Pendidikan" <?php echo $data['bantuan'] === 'Bantuan Pendidikan' ? 'selected' : ''; ?>>📚 Bantuan Pendidikan</option>
              <option value="Alat Bantu Dengar" <?php echo $data['bantuan'] === 'Alat Bantu Dengar' ? 'selected' : ''; ?>>👂 Alat Bantu Dengar</option>
              <option value="Kursi Roda" <?php echo $data['bantuan'] === 'Kursi Roda' ? 'selected' : ''; ?>>♿ Kursi Roda</option>
              <option value="Kesehatan" <?php echo $data['bantuan'] === 'Kesehatan' ? 'selected' : ''; ?>>🏥 Kesehatan</option>
              <option value="Sembako" <?php echo $data['bantuan'] === 'Sembako' ? 'selected' : ''; ?>>🛒 Sembako</option>
              <option value="Uang Muka" <?php echo $data['bantuan'] === 'Uang Muka' ? 'selected' : ''; ?>>💰 Uang Muka</option>
              <option value="Lainnya" <?php echo $data['bantuan'] === 'Lainnya' ? 'selected' : ''; ?>>📦 Lainnya</option>
            </select>
          </div>

          <div class="btn-group">
            <button type="button" class="btn btn-secondary" onclick="window.location.href='verifikasi.php'">Batal</button>
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
</body>
</html>