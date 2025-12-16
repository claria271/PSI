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

// Ambil data user
$email = $_SESSION['alamat_email'];
$stmt = $conn->prepare("SELECT * FROM login WHERE alamat_email = ?");
$stmt->bind_param('s', $email);
$stmt->execute();
$resUser = $stmt->get_result();
$user = $resUser->fetch_assoc() ?: [];
$stmt->close();

$tahunGabung = date('Y');
if (!empty($user['created_at'])) {
  $ts = strtotime((string)$user['created_at']);
  if ($ts !== false) {
    $tahunGabung = date('Y', $ts);
  }
}

// Ambil data keluarga
$keluarga = null;
$hasUserIdCol = $conn->query("SHOW COLUMNS FROM keluarga LIKE 'user_id'")->num_rows > 0;

if ($hasUserIdCol && !empty($user['id'])) {
  $uid = (int)$user['id'];
  $stmtK = $conn->prepare("SELECT * FROM keluarga WHERE user_id = ? ORDER BY created_at DESC, id DESC LIMIT 1");
  $stmtK->bind_param('i', $uid);
  $stmtK->execute();
  $kelRes = $stmtK->get_result();
  $keluarga = $kelRes->fetch_assoc();
  $stmtK->close();
  $_SESSION['user_id'] = $uid;
} else {
  $hasEmailColInKeluarga = $conn->query("SHOW COLUMNS FROM keluarga LIKE 'alamat_email'")->num_rows > 0;
  if ($hasEmailColInKeluarga) {
    $stmtK = $conn->prepare("SELECT * FROM keluarga WHERE alamat_email = ? ORDER BY created_at DESC, id DESC LIMIT 1");
    $stmtK->bind_param('s', $email);
    $stmtK->execute();
    $kelRes = $stmtK->get_result();
    $keluarga = $stmtK->get_result()->fetch_assoc();
    $stmtK->close();
  }
}

// Cek apakah ada pending edit request
$hasPendingEdit = false;
$pendingRequestId = null;
if (!empty($user['id'])) {
  $stmtPending = $conn->prepare("SELECT id FROM edit_requests WHERE user_id = ? AND status = 'pending' ORDER BY created_at DESC LIMIT 1");
  $stmtPending->bind_param('i', $user['id']);
  $stmtPending->execute();
  $pendingRes = $stmtPending->get_result();
  if ($pendingRes->num_rows > 0) {
    $pendingRow = $pendingRes->fetch_assoc();
    $hasPendingEdit = true;
    $pendingRequestId = $pendingRow['id'];
  }
  $stmtPending->close();
}

// Cek apakah user bisa edit (ada approved request yang belum digunakan)
$canEdit = false;
if (!empty($user['id']) && $keluarga) {
  $stmtApproved = $conn->prepare("SELECT id FROM edit_requests WHERE user_id = ? AND keluarga_id = ? AND status = 'approved' ORDER BY updated_at DESC LIMIT 1");
  $stmtApproved->bind_param('ii', $user['id'], $keluarga['id']);
  $stmtApproved->execute();
  $approvedRes = $stmtApproved->get_result();
  $canEdit = $approvedRes->num_rows > 0;
  $stmtApproved->close();
}

$noWaDisplay = '';
if (!empty($keluarga['no_wa'])) {
  $noWaDisplay = preg_replace('/^\+62/', '', $keluarga['no_wa']);
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Profil Pengguna</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />

  <!-- Google Font Poppins -->
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

  <style>
    * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Poppins', sans-serif; }
    body { background: #fff; color: #222; }
    a { text-decoration: none; color: inherit; }

    /* =======================
       NAVBAR (SAMA KAYAK KONTAK)
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
      height:64px; /* LOGO LEBIH BESAR */
      filter:drop-shadow(0 4px 10px rgba(0,0,0,.35));
    }
    .dash-menu{
      display:flex;
      gap:30px;
    }
    .dash-menu a{
      color:#000000; /* TEKS KANAN HITAM */
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
    .dash-menu a.active{ color:#dc2626; }

    @media (max-width:768px){
      .dash-navbar{padding:0 18px}
      .dash-menu{gap:18px}
      .dash-left img{height:58px;}
    }

    /* ====== PROFIL (ASLI MU, TETAP) ====== */
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
    .flash.info { border:1px solid #bfdbfe; background:#eff6ff; color:#1e40af; }

    .data-section {
      width: 70%; max-width: 800px; margin: 16px auto 40px;
      border: 1px solid #e0e0e0; border-radius: 10px; background: #fff;
      box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    }

    .section-header {
      padding: 20px 24px;
      border-bottom: 1px solid #e0e0e0;
      display: flex;
      justify-content: space-between;
      align-items: center;
      cursor: pointer;
      transition: background 0.2s;
    }
    .section-header:hover { background: #f8f8f8; }
    .section-header h3 {
      font-size: 20px;
      font-weight: 600;
      color: #333;
      display: flex;
      align-items: center;
      gap: 10px;
    }
    .section-header .meta {
      display: flex;
      align-items: center;
      gap: 15px;
      font-size: 14px;
      color: #666;
    }
    .section-header .meta span {
      display: flex;
      align-items: center;
      gap: 5px;
    }
    .chevron {
      font-size: 20px;
      color: #666;
      transition: transform 0.3s;
    }
    .chevron.open { transform: rotate(180deg); }

    .section-content {
      max-height: 0;
      overflow: hidden;
      transition: max-height 0.3s ease-out;
    }
    .section-content.open { max-height: 2000px; }
    .section-body { padding: 24px; }

    .info-box {
      background: #fff3cd;
      border: 1px solid #ffc107;
      border-radius: 8px;
      padding: 12px 16px;
      margin-bottom: 20px;
      color: #856404;
      font-size: 14px;
    }
    .info-box strong { color: #664d03; }
    .info-box.success { background: #d1fae5; border-color: #10b981; color: #065f46; }

    .pending-badge {
      display: inline-block;
      background: #fef3c7;
      border: 1px solid #fbbf24;
      color: #78350f;
      padding: 4px 12px;
      border-radius: 20px;
      font-size: 12px;
      font-weight: 600;
      margin-left: 10px;
    }

    .data-grid {
      display: grid;
      grid-template-columns: repeat(2, 1fr);
      gap: 16px 24px;
      margin-bottom: 20px;
    }
    @media (max-width: 640px) { .data-grid { grid-template-columns: 1fr; } }

    .data-item { display: flex; flex-direction: column; gap: 4px; }
    .data-label {
      font-size: 12px;
      font-weight: 600;
      color: #666;
      text-transform: uppercase;
      letter-spacing: 0.5px;
    }
    .data-value { font-size: 15px; color: #111; }

    label { font-weight: 600; display: block; margin-top: 10px; }
    input, textarea, select {
      width: 100%; padding: 10px; border-radius: 8px; border: 1px solid #ccc; margin-bottom: 15px;
      background: #fff; color: #111;
    }
    input:disabled, textarea:disabled { background: #f3f4f6; cursor: not-allowed; }
    textarea { resize: none; height: 90px; }

    .phone-input-wrapper { display: flex; gap: 10px; align-items: stretch; margin-bottom: 15px; }
    .phone-prefix {
      width: 80px;
      background: #e0e0e0;
      cursor: not-allowed;
      text-align: center;
      font-weight: bold;
      padding: 10px;
      border-radius: 8px;
      border: 1px solid #ccc;
      display: flex;
      align-items: center;
      justify-content: center;
    }
    .phone-input { flex: 1; margin-bottom: 0 !important; }
    .phone-status {
      display: block;
      font-size: 12px;
      color: #666;
      margin-top: -10px;
      margin-bottom: 15px;
      transition: color 0.3s ease;
    }

    .btn-save {
      width: 100%; padding: 12px; background: #3b82f6; color: #fff; border: none; border-radius: 8px;
      cursor: pointer; font-weight: 600; transition: 0.3s;
    }
    .btn-save:hover { background: #2563eb; }
    .btn-save:disabled { background: #9ca3af; cursor: not-allowed; }

    .btn-request {
      width: 100%; padding: 12px; background: #f59e0b; color: #fff; border: none; border-radius: 8px;
      cursor: pointer; font-weight: 600; transition: 0.3s; margin-bottom: 15px;
    }
    .btn-request:hover { background: #d97706; }

    /* Modal Styles */
    .modal {
      display: none;
      position: fixed;
      z-index: 1000;
      left: 0;
      top: 0;
      width: 100%;
      height: 100%;
      background-color: rgba(0,0,0,0.5);
      animation: fadeIn 0.3s;
    }
    @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
    .modal-content {
      background-color: #fff;
      margin: 10% auto;
      padding: 30px;
      border-radius: 12px;
      width: 90%;
      max-width: 450px;
      text-align: center;
      animation: slideDown 0.3s;
      box-shadow: 0 10px 40px rgba(0,0,0,0.3);
    }
    @keyframes slideDown { from { transform: translateY(-50px); opacity: 0; } to { transform: translateY(0); opacity: 1; } }
    .modal-icon { font-size: 64px; margin-bottom: 20px; }
    .modal-title { font-size: 24px; font-weight: 700; color: #111; margin-bottom: 12px; }
    .modal-text { font-size: 15px; color: #666; line-height: 1.6; margin-bottom: 25px; }
    .modal-btn {
      padding: 12px 32px;
      background: #3b82f6;
      color: #fff;
      border: none;
      border-radius: 8px;
      font-size: 16px;
      font-weight: 600;
      cursor: pointer;
      transition: 0.3s;
    }
    .modal-btn:hover { background: #2563eb; transform: translateY(-2px); }
    .modal-btn.warning { background: #f59e0b; }
    .modal-btn.warning:hover { background: #d97706; }
  </style>
</head>
<body>

  <!-- NAVBAR BARU (SAMA KAYAK KONTAK) -->
  <header class="dash-navbar">
    <div class="dash-left">
      <img src="../assets/image/logou.png" alt="PSI Logo">
    </div>
    <nav class="dash-menu">
      <a href="dashboard.php">Beranda</a>
      <a href="kontak.php">Kontak</a>
      <a href="profil.php" class="active">Profil</a>
    </nav>
  </header>

  <section class="profile-section">
    <div class="profile-wrapper">
      <?php
        $fotoPath = '../assets/image/user.png';
        if (!empty($user['foto'])) {
            $fotoFile = basename($user['foto']);
            $fotoFullPath = "../uploads/" . $fotoFile;
            if (file_exists($fotoFullPath)) {
                $fotoPath = $fotoFullPath;
            }
        }
      ?>
      <div class="profile-pic">
        <img src="<?= htmlspecialchars($fotoPath) ?>" alt="Foto Profil">
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

  <button class="logout-btn" onclick="window.location.href='logout.php'">Logout</button>

  <?php if (isset($_GET['status'])): ?>
    <?php if ($_GET['status'] === 'created'): ?>
      <div class="flash success">✓ Data keluarga berhasil ditambahkan!</div>
    <?php elseif ($_GET['status'] === 'pending_approval'): ?>
      <div class="flash info">⏳ Permintaan perubahan data telah dikirim dan menunggu persetujuan admin.</div>
    <?php elseif ($_GET['status'] === 'request_sent'): ?>
      <div class="flash info">⏳ Permintaan edit telah dikirim ke admin!</div>
    <?php elseif ($_GET['status'] === 'request_exists'): ?>
      <div class="flash warn">⚠️ Anda sudah memiliki permintaan edit yang sedang diproses.</div>
    <?php elseif ($_GET['status'] === 'updated'): ?>
      <div class="flash success">✓ Data berhasil diperbarui!</div>
    <?php elseif ($_GET['status'] === 'failed'): ?>
      <div class="flash fail">✗ Operasi gagal. Pastikan semua data terisi dengan benar.</div>
    <?php elseif ($_GET['status'] === 'approved'): ?>
      <div class="flash success">✓ Permintaan edit Anda telah disetujui! Silakan edit data Anda.</div>
    <?php endif; ?>
  <?php endif; ?>

  <div class="data-section">
    <div class="section-header" onclick="handleSectionClick()">
      <div>
        <h3>
          📄 Data Keluarga
          <?php if ($hasPendingEdit): ?>
            <span class="pending-badge">⏳ Menunggu Persetujuan</span>
          <?php endif; ?>
        </h3>
      </div>
      <div style="display: flex; align-items: center; gap: 15px;">
        <div class="meta">
          <span>📋 8 Artikel</span>
          <span>⏱️ 55 menit</span>
        </div>
        <span class="chevron" id="chevron">▼</span>
      </div>
    </div>

    <div class="section-content" id="sectionContent">
      <div class="section-body">
        <?php if (!$keluarga): ?>
          <div class="info-box">
            <strong>ℹ️ Informasi:</strong> Anda belum memiliki data keluarga. Silakan isi formulir di bawah untuk menambahkan data baru.
          </div>

          <form action="update_data.php" method="POST" id="profilForm">
            <label>Nama Lengkap <span style="color: red;">*</span></label>
            <input type="text" name="nama_lengkap" required>

            <label>NIK <span style="color: red;">*</span></label>
            <input type="text" name="nik" maxlength="20" required>

            <label>No WhatsApp <span style="color: red;">*</span></label>
            <div class="phone-input-wrapper">
              <div class="phone-prefix">+62</div>
              <input
                type="text"
                id="no_wa_input"
                name="no_wa_display"
                class="phone-input"
                placeholder="8123456789"
                maxlength="13"
                required
              >
            </div>
            <input type="hidden" name="no_wa" id="no_wa_hidden">
            <small class="phone-status" id="phone_status">Masukkan nomor tanpa 0 di depan</small>

            <label>Alamat Sesuai KTP <span style="color: red;">*</span></label>
            <textarea name="alamat" required></textarea>

            <label>Alamat Domisili <span style="color: red;">*</span></label>
            <textarea name="domisili" required></textarea>

            <label>Jumlah Anggota Keluarga</label>
            <input type="number" name="jumlah_anggota" min="1">

            <label>Jumlah Anggota yang Bekerja</label>
            <input type="number" name="jumlah_bekerja" min="0">

            <label>Total Penghasilan Keluarga (Rp)</label>
            <input type="text" name="total_penghasilan" placeholder="Contoh: 5000000">

            <button type="submit" class="btn-save">➕ Tambah Data Baru</button>
          </form>

        <?php else: ?>
          <div class="data-grid">
            <div class="data-item">
              <span class="data-label">Nama Lengkap</span>
              <span class="data-value"><?= h($keluarga['nama_lengkap'] ?? '-') ?></span>
            </div>
            <div class="data-item">
              <span class="data-label">NIK</span>
              <span class="data-value"><?= h($keluarga['nik'] ?? '-') ?></span>
            </div>
            <div class="data-item">
              <span class="data-label">No. WhatsApp</span>
              <span class="data-value"><?= h($keluarga['no_wa'] ?? '-') ?></span>
            </div>
            <div class="data-item">
              <span class="data-label">Jumlah Anggota</span>
              <span class="data-value"><?= h($keluarga['jumlah_anggota'] ?? '-') ?></span>
            </div>
            <div class="data-item" style="grid-column: 1 / -1;">
              <span class="data-label">Alamat KTP</span>
              <span class="data-value"><?= h($keluarga['alamat'] ?? '-') ?></span>
            </div>
            <div class="data-item" style="grid-column: 1 / -1;">
              <span class="data-label">Alamat Domisili</span>
              <span class="data-value"><?= h($keluarga['domisili'] ?? '-') ?></span>
            </div>
            <div class="data-item">
              <span class="data-label">Jumlah Bekerja</span>
              <span class="data-value"><?= h($keluarga['jumlah_bekerja'] ?? '-') ?></span>
            </div>
            <div class="data-item">
              <span class="data-label">Total Penghasilan</span>
              <span class="data-value"><?= 'Rp ' . number_format((int)($keluarga['total_penghasilan'] ?? 0), 0, ',', '.') ?></span>
            </div>
          </div>

          <hr style="margin: 24px 0; border: none; border-top: 1px solid #e0e0e0;">

          <h4 style="margin-bottom: 16px; color: #333;">✏️ Edit Data Keluarga</h4>

          <?php if ($canEdit): ?>
            <div class="info-box success">
              <strong>✓ Izin Edit Aktif:</strong> Admin telah menyetujui permintaan Anda. Silakan edit data sekarang!
            </div>
          <?php elseif ($hasPendingEdit): ?>
            <div class="info-box" style="background: #fef3c7; border-color: #fbbf24;">
              <strong>⏳ Menunggu Persetujuan:</strong> Permintaan edit Anda sedang diproses oleh admin.
            </div>
          <?php else: ?>
            <div class="info-box">
              <strong>🔒 Perlu Izin:</strong> Untuk mengubah data, Anda perlu meminta izin kepada admin terlebih dahulu.
            </div>
            <button type="button" class="btn-request" onclick="requestEdit()">
              📤 Minta Izin Edit ke Admin
            </button>
          <?php endif; ?>

          <form action="update_data.php" method="POST" id="profilFormEdit">
            <input type="hidden" name="id" value="<?= (int)$keluarga['id'] ?>">
            <input type="hidden" name="is_edit" value="1">

            <label>Nama Lengkap <span style="color: red;">*</span></label>
            <input type="text" name="nama_lengkap" value="<?= fv($keluarga, 'nama_lengkap') ?>" <?= $canEdit ? '' : 'disabled' ?> required>

            <label>NIK <span style="color: red;">*</span></label>
            <input type="text" name="nik" value="<?= fv($keluarga, 'nik') ?>" maxlength="20" <?= $canEdit ? '' : 'disabled' ?> required>

            <label>No WhatsApp <span style="color: red;">*</span></label>
            <div class="phone-input-wrapper">
              <div class="phone-prefix">+62</div>
              <input
                type="text"
                id="no_wa_input_edit"
                name="no_wa_display"
                class="phone-input"
                placeholder="8123456789"
                maxlength="13"
                value="<?= htmlspecialchars($noWaDisplay) ?>"
                <?= $canEdit ? '' : 'disabled' ?>
                required
              >
            </div>
            <input type="hidden" name="no_wa" id="no_wa_hidden_edit">
            <small class="phone-status" id="phone_status_edit">Masukkan nomor tanpa 0 di depan</small>

            <label>Alamat Sesuai KTP <span style="color: red;">*</span></label>
            <textarea name="alamat" <?= $canEdit ? '' : 'disabled' ?> required><?= fv($keluarga, 'alamat') ?></textarea>

            <label>Alamat Domisili <span style="color: red;">*</span></label>
            <textarea name="domisili" <?= $canEdit ? '' : 'disabled' ?> required><?= fv($keluarga, 'domisili') ?></textarea>

            <label>Jumlah Anggota Keluarga</label>
            <input type="number" name="jumlah_anggota" value="<?= fv($keluarga, 'jumlah_anggota') ?>" min="1" <?= $canEdit ? '' : 'disabled' ?>>

            <label>Jumlah Anggota yang Bekerja</label>
            <input type="number" name="jumlah_bekerja" value="<?= fv($keluarga, 'jumlah_bekerja') ?>" min="0" <?= $canEdit ? '' : 'disabled' ?>>

            <label>Total Penghasilan Keluarga (Rp)</label>
            <input type="text" name="total_penghasilan" value="<?= fv($keluarga, 'total_penghasilan') ?>" placeholder="Contoh: 5000000" <?= $canEdit ? '' : 'disabled' ?>>

            <button type="submit" class="btn-save" <?= $canEdit ? '' : 'disabled' ?>>
              💾 Simpan Perubahan
            </button>
          </form>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <div id="requestModal" class="modal">
    <div class="modal-content">
      <div class="modal-icon">🔒</div>
      <h2 class="modal-title">Minta Izin Edit Data</h2>
      <p class="modal-text">
        Untuk keamanan data, Anda perlu meminta izin kepada admin untuk mengubah data keluarga.<br><br>
        Setelah admin menyetujui, Anda akan dapat mengedit data Anda.
      </p>
      <button class="modal-btn warning" onclick="submitRequest()">Kirim Permintaan</button>
      <button class="modal-btn" onclick="closeRequestModal()" style="background: #6b7280; margin-top: 10px;">Batal</button>
    </div>
  </div>

  <div id="pendingModal" class="modal">
    <div class="modal-content">
      <div class="modal-icon">⏳</div>
      <h2 class="modal-title">Menunggu Persetujuan Admin</h2>
      <p class="modal-text">
        Permintaan edit data Anda telah dikirim dan sedang menunggu persetujuan dari admin.<br><br>
        Anda akan menerima notifikasi setelah admin memproses permintaan Anda.
      </p>
      <button class="modal-btn" onclick="closePendingModal()">Mengerti</button>
    </div>
  </div>

  <div id="approvedModal" class="modal">
    <div class="modal-content">
      <div class="modal-icon">✅</div>
      <h2 class="modal-title">Permintaan Disetujui!</h2>
      <p class="modal-text">
        Selamat! Admin telah menyetujui permintaan edit Anda.<br><br>
        <strong>Sekarang Anda dapat mengubah data keluarga Anda.</strong><br>
        Silakan scroll ke bawah untuk mengedit data.
      </p>
      <button class="modal-btn" onclick="closeApprovedModal()">Mulai Edit</button>
    </div>
  </div>



  <script>
    function toggleSection() {
      const content = document.getElementById('sectionContent');
      const chevron = document.getElementById('chevron');
      content.classList.toggle('open');
      chevron.classList.toggle('open');
    }

    function handleSectionClick() {
      <?php if ($keluarga && !$canEdit && !$hasPendingEdit): ?>
        document.getElementById('requestModal').style.display = 'block';
      <?php else: ?>
        toggleSection();
      <?php endif; ?>
    }

    function requestEdit() {
      document.getElementById('requestModal').style.display = 'block';
    }

    function closeRequestModal() {
      document.getElementById('requestModal').style.display = 'none';
    }

    function submitRequest() {
      window.location.href = 'request_edit.php';
    }

    function closePendingModal() {
      document.getElementById('pendingModal').style.display = 'none';
      toggleSection();
    }

    function closeApprovedModal() {
      document.getElementById('approvedModal').style.display = 'none';
      const content = document.getElementById('sectionContent');
      const chevron = document.getElementById('chevron');
      if (!content.classList.contains('open')) {
        content.classList.add('open');
        chevron.classList.add('open');
      }
    }

    <?php if (isset($_GET['status'])): ?>
      <?php if ($_GET['status'] === 'request_sent' || $_GET['status'] === 'pending_approval'): ?>
        document.getElementById('pendingModal').style.display = 'block';
      <?php elseif ($_GET['status'] === 'approved'): ?>
        document.getElementById('approvedModal').style.display = 'block';
      <?php endif; ?>
    <?php endif; ?>

    function setupPhoneInput(inputId, hiddenId, statusId) {
      const phoneInput = document.getElementById(inputId);
      const phoneHidden = document.getElementById(hiddenId);
      const phoneStatus = document.getElementById(statusId);

      if (!phoneInput) return;

      phoneInput.addEventListener('input', function() {
        let value = this.value.replace(/\D/g, "");

        if (value.startsWith('0')) value = value.substring(1);
        if (value.startsWith('62')) value = value.substring(2);

        this.value = value;

        if (value.length > 0) {
          const fullNumber = '+62' + value;
          phoneHidden.value = fullNumber;

          if (value.length >= 10) {
            this.style.borderColor = '#22c55e';
            phoneStatus.style.color = '#22c55e';
            phoneStatus.textContent = '✓ Nomor valid: ' + fullNumber;
          } else {
            this.style.borderColor = '#ff4b4b';
            phoneStatus.style.color = '#ff4b4b';
            phoneStatus.textContent = '✗ Minimal 10 digit diperlukan';
          }
        } else {
          phoneHidden.value = '';
          this.style.borderColor = '#ccc';
          phoneStatus.style.color = '#666';
          phoneStatus.textContent = 'Masukkan nomor tanpa 0 di depan';
        }
      });

      phoneInput.addEventListener('blur', function() {
        const value = this.value.replace(/\D/g, "");
        if (value !== '' && value.length < 10) {
          this.style.borderColor = '#ff4b4b';
          phoneStatus.style.color = '#ff4b4b';
          phoneStatus.textContent = '✗ Nomor terlalu pendek (minimal 10 digit)';
        }
      });

      if (phoneInput.value !== '') {
        phoneInput.dispatchEvent(new Event('input'));
      }
    }

    setupPhoneInput('no_wa_input', 'no_wa_hidden', 'phone_status');
    setupPhoneInput('no_wa_input_edit', 'no_wa_hidden_edit', 'phone_status_edit');
  </script>
</body>
</html>
