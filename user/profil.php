<?php
// user/profil.php
declare(strict_types=1);
session_start();
include '../koneksi/config.php';

if (!isset($_SESSION['alamat_email'])) {
  header("Location: login.php");
  exit;
}

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
$conn->set_charset('utf8mb4');

// Helper
function h($v) { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
function fv($arr, $key) { return isset($arr[$key]) && $arr[$key] !== null ? h($arr[$key]) : ''; }

// Ambil data user (prepared)
$email = $_SESSION['alamat_email'];
$stmt = $conn->prepare("SELECT * FROM login WHERE alamat_email = ?");
$stmt->bind_param('s', $email);
$stmt->execute();
$resUser = $stmt->get_result();
$user = $resUser->fetch_assoc() ?: [];
$stmt->close();

/**
 * TAHUN GABUNG (PERUBAHAN DI SINI)
 * - Jika kolom created_at ada & berisi tanggal valid -> ambil tahunnya
 * - Jika kosong / tidak ada -> pakai tahun sekarang
 */
$tahunGabung = date('Y'); // default: tahun berjalan
if (!empty($user['created_at'])) {
  $ts = strtotime((string)$user['created_at']);
  if ($ts !== false) {
    $tahunGabung = date('Y', $ts);
  }
}

// Ambil data keluarga terbaru milik user
$keluarga = null;
$hasUserIdCol = $conn->query("SHOW COLUMNS FROM keluarga LIKE 'user_id'")->num_rows > 0;
$hasEmailCol  = $conn->query("SHOW COLUMNS FROM keluarga LIKE 'alamat_email'")->num_rows > 0;

if ($hasUserIdCol && isset($_SESSION['user_id'])) {
  $uid = (int)$_SESSION['user_id'];
  $stmtK = $conn->prepare("SELECT * FROM keluarga WHERE user_id = ? ORDER BY created_at DESC, id DESC LIMIT 1");
  $stmtK->bind_param('i', $uid);
  $stmtK->execute();
  $kelRes = $stmtK->get_result();
  $keluarga = $kelRes->fetch_assoc();
  $stmtK->close();
} elseif ($hasEmailCol) {
  $stmtK = $conn->prepare("SELECT * FROM keluarga WHERE alamat_email = ? ORDER BY created_at DESC, id DESC LIMIT 1");
  $stmtK->bind_param('s', $email);
  $stmtK->execute();
  $kelRes = $stmtK->get_result();
  $keluarga = $kelRes->fetch_assoc();
  $stmtK->close();
} else {
  // Fallback (tanpa relasi): ambil entry terbaru global
  $keluarga = $conn->query("SELECT * FROM keluarga ORDER BY created_at DESC, id DESC LIMIT 1")->fetch_assoc();
}

// Dapil options untuk select
$dapilOptions = ['Kota Surabaya 1','Kota Surabaya 2','Kota Surabaya 3','Kota Surabaya 4','Kota Surabaya 5'];
$dapilNow = $keluarga['dapil'] ?? '';
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Profil Pengguna</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <style>
    * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Poppins', sans-serif; }
    body { background: #fff; color: #222; }

       /* === HEADER === */
    header {
      background: linear-gradient(to right, #ffffff, #000000);
      padding: 12px 40px;
      display: flex;
      justify-content: space-between;
      align-items: center;
    }

    header img {
      height: 40px;
    }

    nav a {
      margin-left: 20px;
      color: #fff;
      text-decoration: none;
      font-weight: 600;
      transition: 0.3s;
    }

    nav a:hover,
    nav a.active {
      color: #ff4b4b;
    }


    .profile-section {
      background-color: #111;
      background-image:
        linear-gradient(45deg, rgba(255,255,255,0.05) 25%, transparent 25%),
        linear-gradient(-45deg, rgba(255,255,255,0.05) 25%, transparent 25%),
        linear-gradient(45deg, transparent 75%, rgba(255,255,255,0.05) 75%),
        linear-gradient(-45deg, transparent 75%, rgba(255,255,255,0.05) 75%);
      background-size: 40px 40px;
      color: #fff;
      padding: 60px 10%;
      display: flex;
      align-items: center;
      gap: 40px;
      position: relative;
    }
    .profile-wrapper { position: relative; display: inline-block; }
    .profile-pic { width: 180px; height: 180px; border-radius: 50%; background: #bbb; overflow: hidden; position: relative; }
    .profile-pic img { width: 100%; height: 100%; object-fit: cover; display: block; }
    .edit-profile-btn {
      position: absolute; bottom: 10px; right: -25px; width: 40px; height: 40px;
      background-color: #fff; color: #111; border: none; border-radius: 50%;
      display: flex; align-items: center; justify-content: center; cursor: pointer; font-size: 18px;
      box-shadow: 0 4px 10px rgba(0,0,0,0.3); transition: 0.3s;
    }
    .edit-profile-btn:hover { background-color: #e60000; color: #fff; transform: scale(1.1); }
    .profile-info h1 { font-size: 32px; margin-bottom: 5px; }
    .profile-info p.email { font-size: 18px; color: #ccc; }
    .detail { margin-top: 8px; color: #ddd; }

    .logout-btn {
      display: block; width: 90%; margin: 20px auto; padding: 12px 0;
      background: #e60000; color: #fff; border: none; border-radius: 10px;
      font-size: 16px; font-weight: 600; cursor: pointer; transition: 0.3s;
    }
    .logout-btn:hover { background: #b80000; transform: translateY(-2px); }

    .flash {
      width: 70%; max-width: 800px; margin: 10px auto;
      padding: 10px 12px; border-radius: 10px; font-size: 14px;
    }
    .flash.success { border:1px solid #a7f3d0; background:#e6ffed; color:#065f46; }
    .flash.warn { border:1px solid #fed7aa; background:#fff7ed; color:#9a3412; }
    .flash.fail { border:1px solid #fecaca; background:#fee2e2; color:#991b1b; }

    .summary {
      width: 70%; max-width: 800px; margin: 16px auto 0;
      padding: 16px 20px; border: 1px solid #eee; border-radius: 10px; background: #fafafa; color: #111;
    }
    .summary h3 { margin-bottom: 10px; }
    .summary .grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 8px 16px; }
    @media (max-width: 640px) { .summary .grid { grid-template-columns: 1fr; } }
    .muted { color: #666; }

    .form-container {
      width: 70%; max-width: 800px; margin: 20px auto 40px;
      background: linear-gradient(to bottom, #e0e0e0, #b3b3b3);
      padding: 30px 40px; border-radius: 10px; box-shadow: 0 3px 8px rgba(0,0,0,0.2); color: #000;
    }
    label { font-weight: 600; display: block; margin-top: 10px; }
    input, textarea, select {
      width: 100%; padding: 10px; border-radius: 8px; border: 1px solid #ccc; margin-bottom: 15px;
      background: rgba(255,255,255,0.9); color: #111;
    }
    textarea { resize: none; height: 70px; }
    .btn-save {
      width: 100%; padding: 12px; background: #e60000; color: #fff; border: none; border-radius: 8px;
      cursor: pointer; font-weight: 600; transition: 0.3s;
    }
    .btn-save:hover { background: #b80000; }
  </style>
</head>
<body>

  <!-- Header -->
  <header>
    <div class="logo">
      <img src="../assets/image/logo.png" alt="PSI Logo">
    </div>
    <nav>
      <a href="dashboard.php">Beranda</a>
      <a href="kontak.php">Kontak</a>
      <a href="profil.php"class="active">Profil</a>
    </nav>
  </header>

  <!-- Profil -->
  <section class="profile-section">
  <div class="profile-wrapper">
    <?php
      // Tentukan foto profil default
      $fotoPath = '../assets/image/user.png';

      if (!empty($user['foto'])) {
          $fotoFile = basename($user['foto']); // hindari path traversal
          $fotoFullPath = "../uploads/" . $fotoFile;

          if (file_exists($fotoFullPath)) {
              $fotoPath = $fotoFullPath;
          }
      }

      // Tahun bergabung (ambil dari kolom created_at jika ada, atau fallback)
      $tahunGabung = !empty($user['created_at'])
          ? date('Y', strtotime($user['created_at']))
          : '2025';
    ?>
    <div class="profile-pic">
      <img src="<?= htmlspecialchars($fotoPath) ?>" alt="Foto Profil" id="fotoPreview">
    </div>

    <button class="edit-profile-btn" onclick="window.location.href='editprofil.php'">📷</button>
  </div>

  <div class="profile-info">
    <h1><?= htmlspecialchars($user['nama_lengkap'] ?? '') ?></h1>
    <p class="email"><?= htmlspecialchars($user['alamat_email'] ?? $email) ?></p>
    <div class="detail">🕓 Bergabung sejak <?= htmlspecialchars($tahunGabung) ?></div>
    <div class="detail">📍 <?= htmlspecialchars($user['kota'] ?? 'Kota Surabaya') ?></div>
  </div>
</section>


  <!-- Logout -->
  <button class="logout-btn" onclick="window.location.href='logout.php'">Logout</button>

  <!-- Flash message -->
  <?php if (isset($_GET['status'])): ?>
    <?php if ($_GET['status'] === 'created'): ?>
      <div class="flash success">Data keluarga berhasil ditambahkan. Ditampilkan pada form di bawah.</div>
    <?php elseif ($_GET['status'] === 'updated'): ?>
      <div class="flash success">Perubahan disimpan. Form di bawah menampilkan data terbaru.</div>
    <?php elseif ($_GET['status'] === 'failed'): ?>
      <div class="flash fail">Operasi gagal. Pastikan data wajib terisi dan format benar.</div>
    <?php endif; ?>
  <?php endif; ?>

  <!-- Ringkasan Data Keluarga -->
  <div class="summary">
    <h3>Data Keluarga Terbaru</h3>
    <?php if (!$keluarga): ?>
      <div class="muted">Belum ada data keluarga. Silakan isi form di bawah, lalu simpan.</div>
    <?php else: ?>
      <div class="grid">
        <div><strong>Nama Lengkap:</strong> <?= h($keluarga['nama_lengkap'] ?? '-') ?></div>
        <div><strong>NIK:</strong> <?= h($keluarga['nik'] ?? '-') ?></div>
        <div><strong>No. WA:</strong> <?= h($keluarga['no_wa'] ?? '-') ?></div>
        <div><strong>Alamat:</strong> <?= h($keluarga['alamat'] ?? '-') ?></div>
        <div><strong>Dapil:</strong> <?= h($keluarga['dapil'] ?? '-') ?></div>
        <div><strong>Kecamatan:</strong> <?= h($keluarga['kecamatan'] ?? '-') ?></div>
        <div><strong>Jumlah Anggota:</strong> <?= h($keluarga['jumlah_anggota'] ?? '-') ?></div>
        <div><strong>Total Penghasilan:</strong> <?= 'Rp ' . number_format((int)($keluarga['total_penghasilan'] ?? 0), 0, ',', '.') ?></div>
        <div><strong>Dibuat:</strong> <?= h($keluarga['created_at'] ?? '-') ?></div>
        <div><strong>Diperbarui:</strong> <?= h($keluarga['updated_at'] ?? '-') ?></div>
      </div>
    <?php endif; ?>
  </div>

  <!-- Form Edit / Tambah (selalu tampil dengan prefill) -->
  <div class="form-container" id="formEdit">
    <form action="update_data.php" method="POST">
      <?php if (!empty($keluarga['id'])): ?>
        <input type="hidden" name="id" value="<?= (int)$keluarga['id'] ?>">
      <?php endif; ?>

      <label>Nama Lengkap</label>
      <input type="text" name="nama_lengkap" value="<?= fv($keluarga, 'nama_lengkap') ?>">

      <label>NIK</label>
      <input type="text" name="nik" value="<?= fv($keluarga, 'nik') ?>">

      <label>No WhatsApp</label>
      <input type="text" name="no_wa" value="<?= fv($keluarga, 'no_wa') ?>">

      <label>Alamat Lengkap</label>
      <textarea name="alamat"><?= fv($keluarga, 'alamat') ?></textarea>

      <label>Daerah Pemilihan</label>
      <select name="dapil">
        <option value="">-- Pilih Dapil --</option>
        <?php foreach ($dapilOptions as $opt): ?>
          <option value="<?= h($opt) ?>" <?= ($opt === ($dapilNow ?? '')) ? 'selected' : '' ?>><?= h($opt) ?></option>
        <?php endforeach; ?>
      </select>

      <label>Kecamatan</label>
      <input type="text" name="kecamatan" value="<?= fv($keluarga, 'kecamatan') ?>">

      <label>Jumlah Anggota Keluarga</label>
      <input type="number" name="jumlah_anggota" value="<?= fv($keluarga, 'jumlah_anggota') ?>">

      <label>Total Penghasilan</label>
      <input type="text" name="total_penghasilan" value="<?= fv($keluarga, 'total_penghasilan') ?>">

      <button type="submit" class="btn-save">💾 Simpan Perubahan</button>
    </form>
  </div>

  <footer style="margin-top:0; padding:15px 5%; text-align:center; background:linear-gradient(to right, #ffffff, #000000); font-size:14px; color:#fff; border-top:1px solid #ccc;">
    <img src="../assets/image/logodprd.png" alt="dprd Logo" style="height:20px; vertical-align:middle; margin-left:5px; filter:brightness(0) invert(1);">
    <img src="../assets/image/psiputih.png" alt="PSI Logo" style="height:20px; vertical-align:middle; margin-left:5px; filter:brightness(0) invert(1);">
    Hak cipta © 2025 - Partai Solidaritas Indonesia
  </footer>
</body>
</html>
