<?php
// admin/profil_admin.php
session_start();
include '../koneksi/config.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../user/login.php");
    exit();
}

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
$conn->set_charset('utf8mb4');

function e($str) {
    return htmlspecialchars((string)$str, ENT_QUOTES, 'UTF-8');
}

// Ambil data admin dari tabel login
$admin = null;
$keluarga = null;

if (!empty($_SESSION['alamat_email'])) {
    $stmt = $conn->prepare("SELECT * FROM login WHERE alamat_email = ? LIMIT 1");
    $stmt->bind_param('s', $_SESSION['alamat_email']);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($res->num_rows > 0) {
        $admin = $res->fetch_assoc();
    }
    $stmt->close();
}

if (!$admin) {
    header("Location: ../user/login.php");
    exit();
}

// Ambil data keluarga admin (jika ada)
$stmt = $conn->prepare("SELECT * FROM keluarga WHERE user_id = ? LIMIT 1");
$stmt->bind_param('i', $admin['id']);
$stmt->execute();
$res = $stmt->get_result();
if ($res->num_rows > 0) {
    $keluarga = $res->fetch_assoc();
}
$stmt->close();

$success = '';
$error   = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Data untuk tabel keluarga
        $nama_lengkap = trim($_POST['nama_lengkap'] ?? '');
        $nik          = trim($_POST['nik'] ?? '');
        $no_wa        = trim($_POST['no_wa'] ?? '');
        $alamat       = trim($_POST['alamat'] ?? '');
        $domisili     = trim($_POST['domisili'] ?? '');
        
        // Data untuk tabel login
        $email_baru    = trim($_POST['alamat_email'] ?? '');
        $password_baru = $_POST['password_baru'] ?? '';
        $password_konf = $_POST['password_konfirmasi'] ?? '';

        if ($email_baru === '') {
            throw new Exception("Email wajib diisi.");
        }

        // Cek email sudah dipakai user lain atau tidak
        if ($email_baru !== $admin['alamat_email']) {
            $stmt = $conn->prepare("SELECT id FROM login WHERE alamat_email = ? AND id != ? LIMIT 1");
            $stmt->bind_param('si', $email_baru, $admin['id']);
            $stmt->execute();
            $cek = $stmt->get_result();
            if ($cek->num_rows > 0) {
                throw new Exception("Email sudah digunakan pengguna lain.");
            }
            $stmt->close();
        }

        // Siapkan nilai password (tetap lama jika tidak diubah)
        $password_final = $admin['password'];
        $updatePassword = false;
        
        if ($password_baru !== '' || $password_konf !== '') {
            if ($password_baru !== $password_konf) {
                throw new Exception("Password baru dan konfirmasi tidak sama.");
            }
            if (strlen($password_baru) < 6) {
                throw new Exception("Password minimal 6 karakter.");
            }
            $password_final = password_hash($password_baru, PASSWORD_DEFAULT);
            $updatePassword = true;
        }

        // Foto profil (opsional)
        $foto_final = $admin['foto'] ?? '';

        if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
            $tmp_name  = $_FILES['foto']['tmp_name'];
            $orig_name = basename($_FILES['foto']['name']);
            $ext = strtolower(pathinfo($orig_name, PATHINFO_EXTENSION));
            $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

            if (!in_array($ext, $allowed)) {
                throw new Exception("Format foto tidak valid. Gunakan JPG, PNG, GIF, atau WEBP.");
            }

            $new_name   = 'admin_' . $admin['id'] . '_' . time() . '.' . $ext;
            $upload_dir = __DIR__ . '/../uploads';

            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }

            $dest = $upload_dir . '/' . $new_name;

            if (!move_uploaded_file($tmp_name, $dest)) {
                throw new Exception("Gagal mengupload foto.");
            }

            // Hapus foto lama jika ada
            if (!empty($admin['foto'])) {
                $old = $upload_dir . '/' . $admin['foto'];
                if (is_file($old)) {
                    @unlink($old);
                }
            }

            $foto_final = $new_name;
        }

        // ✅ UPDATE TABEL LOGIN (hanya email, password, foto)
        if ($updatePassword) {
            $stmt = $conn->prepare("UPDATE login SET alamat_email = ?, password = ?, foto = ? WHERE id = ?");
            $stmt->bind_param('sssi', $email_baru, $password_final, $foto_final, $admin['id']);
        } else {
            $stmt = $conn->prepare("UPDATE login SET alamat_email = ?, foto = ? WHERE id = ?");
            $stmt->bind_param('ssi', $email_baru, $foto_final, $admin['id']);
        }
        $stmt->execute();
        $stmt->close();

        // ✅ UPDATE atau INSERT TABEL KELUARGA
        if ($keluarga) {
            // Update data yang ada
            $stmt = $conn->prepare("
                UPDATE keluarga 
                SET nama_lengkap = ?, nik = ?, no_wa = ?, alamat = ?, domisili = ?, updated_at = NOW()
                WHERE user_id = ?
            ");
            $stmt->bind_param('sssssi', $nama_lengkap, $nik, $no_wa, $alamat, $domisili, $admin['id']);
            $stmt->execute();
            $stmt->close();
        } else {
            // Insert data baru
            $stmt = $conn->prepare("
                INSERT INTO keluarga 
                (user_id, nama_lengkap, nik, no_wa, alamat, domisili, created_at, updated_at) 
                VALUES (?, ?, ?, ?, ?, ?, NOW(), NOW())
            ");
            $stmt->bind_param('isssss', $admin['id'], $nama_lengkap, $nik, $no_wa, $alamat, $domisili);
            $stmt->execute();
            $stmt->close();
        }

        // Update session email
        $_SESSION['alamat_email'] = $email_baru;

        // Refresh data
        $admin['alamat_email'] = $email_baru;
        $admin['password'] = $password_final;
        $admin['foto'] = $foto_final;

        // Reload data keluarga
        $stmt = $conn->prepare("SELECT * FROM keluarga WHERE user_id = ? LIMIT 1");
        $stmt->bind_param('i', $admin['id']);
        $stmt->execute();
        $res = $stmt->get_result();
        if ($res->num_rows > 0) {
            $keluarga = $res->fetch_assoc();
        }
        $stmt->close();

        $success = "Profil berhasil diperbarui.";
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

$adminName = !empty($keluarga['nama_lengkap']) ? $keluarga['nama_lengkap'] : 'Admin';
$adminPhoto = !empty($admin['foto']) ? '../uploads/' . $admin['foto'] : '../assets/image/user.png';
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Profil Admin - PSI</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body {
      font-family: 'Poppins', sans-serif;
      background: #f4f4f4;
      color: #333;
    }
    header {
      background: linear-gradient(to right, #ffffff, #000000);
      padding: 12px 40px;
      display: flex;
      align-items: center;
      justify-content: space-between;
      box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }
    header img {
      height: 40px;
    }
    .container {
      max-width: 900px;
      margin: 40px auto;
      background: #fff;
      padding: 25px 30px;
      border-radius: 16px;
      box-shadow: 0 4px 16px rgba(0,0,0,0.08);
    }
    .title {
      display: flex;
      align-items: center;
      justify-content: space-between;
      margin-bottom: 20px;
    }
    .title h2 {
      font-size: 22px;
      font-weight: 600;
      color: #000;
    }
    .back-btn {
      padding: 8px 16px;
      font-size: 13px;
      border-radius: 999px;
      border: none;
      cursor: pointer;
      background: #000;
      color: #fff;
      transition: 0.3s;
      text-decoration: none;
    }
    .back-btn:hover {
      background: #ff4b4b;
    }
    .profile-top {
      display: flex;
      align-items: center;
      gap: 18px;
      margin-bottom: 20px;
      padding: 15px;
      background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
      border-radius: 12px;
    }
    .profile-photo {
      width: 80px;
      height: 80px;
      border-radius: 50%;
      overflow: hidden;
      background: #ddd;
      box-shadow: 0 4px 12px rgba(0,0,0,0.15);
      flex-shrink: 0;
    }
    .profile-photo img {
      width: 100%;
      height: 100%;
      object-fit: cover;
    }
    .profile-info h3 {
      font-size: 18px;
      font-weight: 600;
      color: #000;
    }
    .profile-info p {
      font-size: 13px;
      color: #666;
      margin-top: 3px;
    }
    .alert {
      padding: 10px 14px;
      border-radius: 8px;
      margin-bottom: 15px;
      font-size: 13px;
    }
    .alert-success {
      background: #dcfce7;
      color: #166534;
      border: 1px solid #bbf7d0;
    }
    .alert-error {
      background: #fee2e2;
      color: #991b1b;
      border: 1px solid #fecaca;
    }
    .info-box {
      background: #e3f2fd;
      border-left: 4px solid #2196f3;
      padding: 12px;
      margin-bottom: 20px;
      border-radius: 6px;
      font-size: 13px;
    }
    form {
      margin-top: 10px;
    }
    .section-title {
      font-size: 16px;
      font-weight: 600;
      color: #000;
      margin: 20px 0 12px;
      padding-bottom: 8px;
      border-bottom: 2px solid #f0f0f0;
    }
    .form-row {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 15px;
      margin-bottom: 14px;
    }
    @media (max-width: 600px) {
      .form-row {
        grid-template-columns: 1fr;
      }
    }
    .form-group {
      margin-bottom: 14px;
    }
    label {
      display: block;
      font-size: 13px;
      font-weight: 500;
      margin-bottom: 4px;
      color: #111;
    }
    label .required {
      color: #ff4b4b;
    }
    input[type="text"],
    input[type="email"],
    input[type="password"],
    input[type="file"],
    textarea {
      width: 100%;
      padding: 9px 10px;
      border-radius: 8px;
      border: 1px solid #d4d4d4;
      font-size: 13px;
      outline: none;
      transition: 0.25s;
      font-family: inherit;
      background: #fafafa;
    }
    textarea {
      resize: vertical;
      min-height: 80px;
    }
    input:focus, textarea:focus {
      border-color: #ff4b4b;
      background: #fff;
      box-shadow: 0 0 0 2px rgba(255,75,75,0.08);
    }
    .hint {
      font-size: 11px;
      color: #777;
      margin-top: 2px;
    }
    .btn-submit {
      padding: 10px 20px;
      border: none;
      border-radius: 999px;
      background: #ff4b4b;
      color: #fff;
      font-size: 14px;
      font-weight: 500;
      cursor: pointer;
      margin-top: 8px;
      transition: 0.3s;
    }
    .btn-submit:hover {
      background: #000;
    }
  </style>
</head>
<body>
<header>
  <img src="../assets/image/logo.png" alt="PSI Logo">
</header>

<div class="container">
  <div class="title">
    <h2>✏️ Edit Profil Admin</h2>
    <a href="dashboardadmin.php" class="back-btn">← Kembali ke Dashboard</a>
  </div>

  <div class="profile-top">
    <div class="profile-photo">
      <img src="<?= e($adminPhoto) ?>" alt="Admin Photo">
    </div>
    <div class="profile-info">
      <h3><?= e($adminName) ?></h3>
      <p>📧 <?= e($admin['alamat_email']) ?></p>
      <p>👤 Role: <strong><?= e(ucfirst($admin['role'])) ?></strong></p>
    </div>
  </div>

  <div class="info-box">
    ℹ️ <strong>Informasi:</strong> Data lengkap (nama, NIK, alamat, dll) disimpan di tabel keluarga. Data login (email, password, foto) disimpan terpisah di tabel login.
  </div>

  <?php if ($success): ?>
    <div class="alert alert-success">✓ <?= e($success) ?></div>
  <?php endif; ?>

  <?php if ($error): ?>
    <div class="alert alert-error">✗ <?= e($error) ?></div>
  <?php endif; ?>

  <form method="POST" enctype="multipart/form-data">
    
    <div class="section-title">📋 Data Pribadi</div>
    
    <div class="form-row">
      <div class="form-group">
        <label>Nama Lengkap <span class="required">*</span></label>
        <input type="text" name="nama_lengkap" value="<?= e($keluarga['nama_lengkap'] ?? '') ?>" required>
      </div>

      <div class="form-group">
        <label>NIK</label>
        <input type="text" name="nik" value="<?= e($keluarga['nik'] ?? '') ?>" maxlength="20">
      </div>
    </div>

    <div class="form-group">
      <label>No. WhatsApp</label>
      <input type="text" name="no_wa" value="<?= e($keluarga['no_wa'] ?? '') ?>" placeholder="+628123456789">
      <div class="hint">Format: +628xxxxxxxxxx</div>
    </div>

    <div class="form-group">
      <label>Alamat Lengkap (sesuai KTP)</label>
      <textarea name="alamat"><?= e($keluarga['alamat'] ?? '') ?></textarea>
    </div>

    <div class="form-group">
      <label>Alamat Domisili</label>
      <textarea name="domisili"><?= e($keluarga['domisili'] ?? '') ?></textarea>
    </div>

    <div class="section-title">🔐 Data Login</div>

    <div class="form-group">
      <label>Email <span class="required">*</span></label>
      <input type="email" name="alamat_email" value="<?= e($admin['alamat_email'] ?? '') ?>" required>
    </div>

    <div class="form-row">
      <div class="form-group">
        <label>Password Baru (opsional)</label>
        <input type="password" name="password_baru" placeholder="Kosongkan jika tidak ingin mengubah">
        <div class="hint">Minimal 6 karakter</div>
      </div>

      <div class="form-group">
        <label>Konfirmasi Password Baru</label>
        <input type="password" name="password_konfirmasi" placeholder="Ulangi password baru">
      </div>
    </div>

    <div class="form-group">
      <label>Foto Profil (opsional)</label>
      <input type="file" name="foto" accept=".jpg,.jpeg,.png,.gif,.webp">
      <div class="hint">Format: JPG, PNG, GIF, WEBP. Max 2MB. Jika tidak diisi, foto lama tetap digunakan.</div>
    </div>

    <button type="submit" class="btn-submit">💾 Simpan Perubahan</button>
  </form>
</div>

</body>
</html>