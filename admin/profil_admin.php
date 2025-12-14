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

// Ambil data admin dari tabel login berdasarkan email di session
$admin = null;

if (!empty($_SESSION['alamat_email'])) {
    $stmt = $conn->prepare("SELECT * FROM login WHERE alamat_email = ? LIMIT 1");
    $stmt->bind_param('s', $_SESSION['alamat_email']);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($res->num_rows > 0) {
        $admin = $res->fetch_assoc();
    }
}

if (!$admin) {
    header("Location: ../user/login.php");
    exit();
}

// Ambil nama_lengkap admin dari tabel keluarga (relasi: keluarga.user_id = login.id)
$nama_keluarga = '';
$stmt = $conn->prepare("SELECT nama_lengkap FROM keluarga WHERE user_id = ? LIMIT 1");
$stmt->bind_param('i', $admin['id']);
$stmt->execute();
$resNama = $stmt->get_result();
if ($resNama->num_rows > 0) {
    $rowNama = $resNama->fetch_assoc();
    $nama_keluarga = $rowNama['nama_lengkap'] ?? '';
}

$success = '';
$error   = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $nama_lengkap  = trim($_POST['nama_lengkap'] ?? '');
        $email_baru    = trim($_POST['alamat_email'] ?? '');
        $password_baru = $_POST['password_baru'] ?? '';
        $password_konf = $_POST['password_konfirmasi'] ?? '';

        if ($nama_lengkap === '' || $email_baru === '') {
            throw new Exception("Nama lengkap dan email wajib diisi.");
        }

        // Mulai transaksi biar update 2 tabel aman
        $conn->begin_transaction();

        // 1) Cek email sudah dipakai user lain atau tidak
        if ($email_baru !== $admin['alamat_email']) {
            $stmt = $conn->prepare("SELECT id FROM login WHERE alamat_email = ? AND id != ? LIMIT 1");
            $stmt->bind_param('si', $email_baru, $admin['id']);
            $stmt->execute();
            $cek = $stmt->get_result();
            if ($cek->num_rows > 0) {
                throw new Exception("Email sudah digunakan pengguna lain.");
            }
        }

        // 2) Password (tetap lama jika tidak diubah)
        $password_final = $admin['password'];

        // Jika dua-duanya kosong -> tidak ganti password (opsional)
        if ($password_baru === '' && $password_konf === '') {
            // no-op
        } else {
            // Jika salah satu diisi -> wajib lengkap & sama
            if ($password_baru === '') {
                throw new Exception("Password baru tidak boleh kosong jika konfirmasi diisi.");
            }
            if ($password_konf === '') {
                throw new Exception("Konfirmasi password tidak boleh kosong jika password baru diisi.");
            }
            if ($password_baru !== $password_konf) {
                throw new Exception("Password baru dan konfirmasi tidak sama.");
            }
            $password_final = password_hash($password_baru, PASSWORD_BCRYPT);
        }

        // 3) Foto profil (opsional) -> simpan ke tabel login
        $foto_final = $admin['foto'] ?? null;

        if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
            $tmp_name  = $_FILES['foto']['tmp_name'];
            $orig_name = basename($_FILES['foto']['name']);

            $ext = strtolower(pathinfo($orig_name, PATHINFO_EXTENSION));
            $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

            if (!in_array($ext, $allowed, true)) {
                throw new Exception("Format foto tidak valid. Gunakan JPG, PNG, GIF, atau WEBP.");
            }

            $new_name   = 'admin_' . $admin['id'] . '_' . time() . '.' . $ext;
            $upload_dir = realpath(__DIR__ . '/../uploads');

            if (!$upload_dir) {
                $upload_dir = __DIR__ . '/../uploads';
                if (!is_dir($upload_dir)) {
                    mkdir($upload_dir, 0777, true);
                }
            }

            $dest = rtrim($upload_dir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $new_name;

            if (!move_uploaded_file($tmp_name, $dest)) {
                throw new Exception("Gagal mengupload foto.");
            }

            // Hapus foto lama jika ada
            if (!empty($admin['foto'])) {
                $old = rtrim($upload_dir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $admin['foto'];
                if (is_file($old)) {
                    @unlink($old);
                }
            }

            $foto_final = $new_name;
        }

        // 4) Update tabel login (HANYA: email, password, foto)
        $stmt = $conn->prepare("
            UPDATE login 
            SET alamat_email = ?, password = ?, foto = ?
            WHERE id = ?
        ");
        $stmt->bind_param('sssi', $email_baru, $password_final, $foto_final, $admin['id']);
        $stmt->execute();

        // 5) Update tabel keluarga (HANYA: nama_lengkap)
        // Jika data keluarga belum ada untuk admin ini, buat (INSERT) dulu.
        $stmt = $conn->prepare("SELECT user_id FROM keluarga WHERE user_id = ? LIMIT 1");
        $stmt->bind_param('i', $admin['id']);
        $stmt->execute();
        $cekK = $stmt->get_result();

        if ($cekK->num_rows > 0) {
            $stmt = $conn->prepare("UPDATE keluarga SET nama_lengkap = ? WHERE user_id = ?");
            $stmt->bind_param('si', $nama_lengkap, $admin['id']);
            $stmt->execute();
        } else {
            // INSERT minimal agar tidak error (kolom lain biarkan NULL/default)
            $stmt = $conn->prepare("INSERT INTO keluarga (user_id, nama_lengkap) VALUES (?, ?)");
            $stmt->bind_param('is', $admin['id'], $nama_lengkap);
            $stmt->execute();
        }

        // Commit transaksi
        $conn->commit();

        // Update session email supaya sinkron
        $_SESSION['alamat_email'] = $email_baru;

        // Refresh data local
        $admin['alamat_email'] = $email_baru;
        $admin['password']     = $password_final;
        $admin['foto']         = $foto_final;
        $nama_keluarga         = $nama_lengkap;

        $success = "Profil berhasil diperbarui.";
    } catch (Exception $e) {
        @ $conn->rollback();
        $error = $e->getMessage();
    }
}

// Nama tampil dari keluarga (kalau kosong fallback "Admin")
$adminName = !empty($nama_keluarga) ? $nama_keluarga : 'Admin';

$adminPhoto = !empty($admin['foto'])
    ? '../uploads/' . $admin['foto']
    : '../assets/image/admin_photo.jpg';
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
    body { font-family: 'Poppins', sans-serif; background: #f4f4f4; color: #333; }
    header {
      background: linear-gradient(to right, #ffffff, #000000);
      padding: 12px 40px;
      display: flex;
      align-items: center;
      justify-content: space-between;
      box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }
    header img { height: 40px; }
    .container {
      max-width: 900px;
      margin: 40px auto;
      background: #fff;
      padding: 25px 30px;
      border-radius: 16px;
      box-shadow: 0 4px 16px rgba(0,0,0,0.08);
    }
    .title { display:flex; align-items:center; justify-content:space-between; margin-bottom:20px; }
    .title h2 { font-size: 22px; font-weight: 600; color: #000; }
    .back-btn {
      padding: 8px 16px; font-size: 13px; border-radius: 999px; border: none;
      cursor: pointer; background: #000; color: #fff; transition: 0.3s; text-decoration: none;
    }
    .back-btn:hover { background: #ff4b4b; }
    .profile-top { display:flex; align-items:center; gap:18px; margin-bottom:20px; }
    .profile-photo {
      width: 80px; height: 80px; border-radius: 50%; overflow: hidden;
      background: #ddd; box-shadow: 0 4px 12px rgba(0,0,0,0.15); flex-shrink: 0;
    }
    .profile-photo img { width:100%; height:100%; object-fit:cover; }
    .profile-info h3 { font-size:18px; font-weight:600; color:#000; }
    .profile-info p { font-size:13px; color:#666; }

    .alert { padding: 10px 14px; border-radius: 8px; margin-bottom: 15px; font-size: 13px; }
    .alert-success { background: #dcfce7; color: #166534; border: 1px solid #bbf7d0; }
    .alert-error { background: #fee2e2; color: #991b1b; border: 1px solid #fecaca; }

    form { margin-top: 10px; }
    .form-group { margin-bottom: 14px; }
    label { display:block; font-size:13px; font-weight:500; margin-bottom:4px; color:#111; }
    input[type="text"], input[type="email"], input[type="password"], input[type="file"] {
      width: 100%; padding: 9px 10px; border-radius: 8px; border: 1px solid #d4d4d4;
      font-size: 13px; outline: none; transition: 0.25s; font-family: inherit; background: #fafafa;
    }
    input:focus {
      border-color: #ff4b4b; background: #fff; box-shadow: 0 0 0 2px rgba(255,75,75,0.08);
    }
    .hint { font-size: 11px; color: #777; margin-top: 2px; }
    .btn-submit {
      padding: 10px 20px; border: none; border-radius: 999px;
      background: #ff4b4b; color: #fff; font-size: 14px; font-weight: 500;
      cursor: pointer; margin-top: 8px; transition: 0.3s;
    }
    .btn-submit:hover { background: #000; }
  </style>
</head>
<body>
<header>
  <img src="../assets/image/logo.png" alt="PSI Logo">
</header>

<div class="container">
  <div class="title">
    <h2>Edit Profil Admin</h2>
    <a href="dashboardadmin.php" class="back-btn">← Kembali ke Dashboard</a>
  </div>

  <div class="profile-top">
    <div class="profile-photo">
      <img src="<?php echo e($adminPhoto); ?>" alt="Admin Photo"
           onerror="this.src='data:image/svg+xml,%3Csvg xmlns=\\'http://www.w3.org/2000/svg\\' viewBox=\\'0 0 100 100\\'%3E%3Ccircle cx=\\'50\\' cy=\\'50\\' r=\\'50\\' fill=\\'%23bbb\\'/%3E%3Ctext x=\\'50\\' y=\\'60\\' font-size=\\'40\\' text-anchor=\\'middle\\' fill=\\'%23666\\'%3E👤%3C/text%3E%3C/svg%3E';">
    </div>
    <div class="profile-info">
      <h3><?php echo e($adminName); ?></h3>
      <p><?php echo e($admin['alamat_email']); ?> · Role: <?php echo e($admin['role']); ?></p>
    </div>
  </div>

  <?php if ($success): ?>
    <div class="alert alert-success"><?php echo e($success); ?></div>
  <?php endif; ?>

  <?php if ($error): ?>
    <div class="alert alert-error"><?php echo e($error); ?></div>
  <?php endif; ?>

  <form method="POST" enctype="multipart/form-data">
    <div class="form-group">
      <label>Nama Lengkap</label>
      <input type="text" name="nama_lengkap" value="<?php echo e($nama_keluarga); ?>" required>
      <div class="hint">Disimpan ke tabel <b>keluarga</b>.</div>
    </div>

    <div class="form-group">
      <label>Email</label>
      <input type="email" name="alamat_email" value="<?php echo e($admin['alamat_email'] ?? ''); ?>" required>
      <div class="hint">Disimpan ke tabel <b>login</b>.</div>
    </div>

    <div class="form-group">
      <label>Password Baru (opsional)</label>
      <input type="password" name="password_baru" placeholder="Kosongkan jika tidak ingin mengubah password">
      <div class="hint">Kosongkan juga konfirmasi kalau tidak ingin ganti password.</div>
    </div>

    <div class="form-group">
      <label>Konfirmasi Password Baru (opsional)</label>
      <input type="password" name="password_konfirmasi" placeholder="Ulangi password baru (jika mengganti)">
    </div>

    <div class="form-group">
      <label>Foto Profil (opsional)</label>
      <input type="file" name="foto" accept=".jpg,.jpeg,.png,.gif,.webp">
      <div class="hint">Jika tidak diisi, foto lama tetap digunakan.</div>
    </div>

    <button type="submit" class="btn-submit">Simpan Perubahan</button>
  </form>
</div>

</body>
</html>
