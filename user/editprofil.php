<?php
session_start();
include '../koneksi/config.php';

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
$conn->set_charset('utf8mb4');

// Pastikan user sudah login
if (!isset($_SESSION['alamat_email'])) {
    header("Location: login.php");
    exit;
}

$email = $_SESSION['alamat_email'];

/*
  Ambil data gabungan:
  - login: alamat_email, password, foto, role
  - keluarga: nama_lengkap, no_wa
*/
$stmt = $conn->prepare("
    SELECT 
        l.id AS login_id,
        l.alamat_email,
        l.password,
        l.role,
        l.foto,
        k.id AS keluarga_id,
        k.nama_lengkap,
        k.no_wa
    FROM login l
    LEFT JOIN keluarga k ON k.user_id = l.id
    WHERE l.alamat_email = ?
    LIMIT 1
");
$stmt->bind_param("s", $email);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$user) {
    die("User tidak ditemukan.");
}

// Proses jika form disubmit
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    try {
        $nama_lengkap   = trim($_POST['nama_lengkap'] ?? '');
        $nomor_telepon  = trim($_POST['nomor_telepon'] ?? '');
        $alamat_email_baru = trim($_POST['alamat_email'] ?? '');
        $password_baru  = (string)($_POST['password'] ?? '');

        if ($nama_lengkap === '') throw new Exception("Nama lengkap wajib diisi!");
        if (!filter_var($alamat_email_baru, FILTER_VALIDATE_EMAIL)) throw new Exception("Email tidak valid!");

        // Normalisasi nomor telepon -> format +62xxxxxxxxxx (disimpan ke keluarga.no_wa)
        $nomor_telepon = preg_replace('/\D+/', '', $nomor_telepon);
        if (str_starts_with($nomor_telepon, '0'))  $nomor_telepon = substr($nomor_telepon, 1);
        if (str_starts_with($nomor_telepon, '62')) $nomor_telepon = substr($nomor_telepon, 2);
        $nomor_telepon = '+62' . $nomor_telepon;

        if (!preg_match('/^\+62\d{10,13}$/', $nomor_telepon)) {
            throw new Exception("Nomor telepon tidak valid (minimal 10 digit).");
        }

        // Jika email diganti, cek duplikat
        if ($alamat_email_baru !== $email) {
            $cek = $conn->prepare("SELECT id FROM login WHERE alamat_email = ? LIMIT 1");
            $cek->bind_param("s", $alamat_email_baru);
            $cek->execute();
            if ($cek->get_result()->num_rows > 0) {
                $cek->close();
                throw new Exception("Email baru sudah dipakai akun lain!");
            }
            $cek->close();
        }

        // Password
        $password_hashed = (!empty($password_baru))
            ? password_hash($password_baru, PASSWORD_DEFAULT)
            : $user['password'];

        // Upload foto profil (disimpan di login.foto)
        $foto = $user['foto'];
        if (!empty($_FILES['foto']['name'])) {
            $target_dir = "../uploads/";
            if (!is_dir($target_dir)) mkdir($target_dir, 0777, true);

            $nama_file = time() . "_" . basename($_FILES["foto"]["name"]);
            $target_file = $target_dir . $nama_file;

            $allowed_types = ['image/jpeg', 'image/png', 'image/jpg', 'image/gif'];
            if (!in_array($_FILES["foto"]["type"], $allowed_types)) {
                throw new Exception("Format foto harus JPG/PNG/GIF");
            }

            if (!move_uploaded_file($_FILES["foto"]["tmp_name"], $target_file)) {
                throw new Exception("Upload foto gagal.");
            }

            $foto = "uploads/" . $nama_file;
        }

        // ==== UPDATE LOGIN (email, password, foto) ====
        $upLogin = $conn->prepare("
            UPDATE login
            SET alamat_email = ?, password = ?, foto = ?
            WHERE id = ?
        ");
        $login_id = (int)$user['login_id'];
        $upLogin->bind_param("sssi", $alamat_email_baru, $password_hashed, $foto, $login_id);
        $upLogin->execute();
        $upLogin->close();

        // ==== UPDATE / INSERT KELUARGA (nama_lengkap, no_wa) ====
        if (!empty($user['keluarga_id'])) {
            $upKel = $conn->prepare("
                UPDATE keluarga
                SET nama_lengkap = ?, no_wa = ?
                WHERE id = ?
            ");
            $kel_id = (int)$user['keluarga_id'];
            $upKel->bind_param("ssi", $nama_lengkap, $nomor_telepon, $kel_id);
            $upKel->execute();
            $upKel->close();
        } else {
            // kalau belum ada row keluarga, buat baru
            $insKel = $conn->prepare("
                INSERT INTO keluarga (user_id, nama_lengkap, no_wa, created_at)
                VALUES (?, ?, ?, NOW())
            ");
            $insKel->bind_param("iss", $login_id, $nama_lengkap, $nomor_telepon);
            $insKel->execute();
            $insKel->close();
        }

        // Update session email kalau berubah
        $_SESSION['alamat_email'] = $alamat_email_baru;
        $_SESSION['nama_lengkap'] = $nama_lengkap;

        echo "<script>alert('✅ Perubahan berhasil disimpan!'); window.location='profil.php';</script>";
        exit;

    } catch (Exception $e) {
        echo "<script>alert('❌ " . addslashes($e->getMessage()) . "');</script>";
    }
}

// Aman untuk tampilan (hindari null)
$namaTampil = $user['nama_lengkap'] ?? '';
$waTampil   = $user['no_wa'] ?? '';
$fotoTampil = (!empty($user['foto'])) ? '../' . htmlspecialchars($user['foto']) : '../assets/image/user.png';
$emailTampil= $user['alamat_email'] ?? '';
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Edit Profil</title>
  <style>
    body { font-family: Arial, sans-serif; background-color: #fff; margin: 0; padding: 0; }
    header {
      background: linear-gradient(90deg, #aaa, #000);
      color: white; display: flex; justify-content: space-between; align-items: center;
      padding: 10px 40px;
    }
    header img { height: 40px; }
    header nav a { color: white; margin-left: 20px; text-decoration: none; }
    header nav a:hover { text-decoration: underline; }
    .container {
      width: 50%; margin: 40px auto;
      background: linear-gradient(#e6e6e6, #a6a6a6);
      padding: 30px; border-radius: 15px;
      box-shadow: 0 4px 8px rgba(0,0,0,0.2);
    }
    h2 { text-align: center; margin-bottom: 20px; }
    .profile-info { text-align: center; margin-bottom: 20px; }
    .profile-wrapper { position: relative; display: inline-block; }
    .profile-wrapper img {
      width: 120px; height: 120px; border-radius: 50%;
      object-fit: cover; background: #bbb;
      border: 2px solid #fff; box-shadow: 0 2px 6px rgba(0,0,0,0.2);
      transition: transform 0.2s;
    }
    .profile-wrapper img:hover { transform: scale(1.05); }
    .upload-btn {
      position: absolute; right: 0; bottom: 0;
      transform: translate(25%, 25%);
      background-color: #fff; border: none; border-radius: 50%;
      width: 35px; height: 35px; display: flex; align-items: center; justify-content: center;
      cursor: pointer; font-size: 18px;
      box-shadow: 0 3px 8px rgba(0,0,0,0.3);
      transition: 0.3s;
    }
    .upload-btn:hover { background-color: #e60000; color: white; }
    label { font-weight: bold; display: block; margin-top: 10px; margin-bottom: 5px; }
    input {
      width: 100%; padding: 10px; border-radius: 10px; border: 1px solid #ccc;
      margin-bottom: 15px; box-sizing: border-box;
    }
    input:focus { border-color: #4a90e2; outline: none; }
    .btn {
      width: 100%; background: #4a90e2; color: white;
      padding: 10px; border: none; border-radius: 10px;
      cursor: pointer; font-weight: bold; transition: 0.3s;
    }
    .btn:hover { background: #357abd; }
    footer {
      margin-top: 40px; padding: 15px 5%; text-align: center;
      background: linear-gradient(to right, #ffffff, #000000);
      font-size: 14px; color: #fff; border-top: 1px solid #ccc;
    }
    footer img {
      height: 20px; vertical-align: middle; margin-left: 5px;
      filter: brightness(0) invert(1);
    }
  </style>
</head>
<body>
  <header>
    <div class="logo">
      <img src="../assets/image/logo.png" alt="PSI Logo">
    </div>
    <nav>
      <a href="tentang.php">Tentang</a>
      <a href="kontak.php">Kontak</a>
      <a href="profil.php">Profil</a>
    </nav>
  </header>

  <div class="container">
    <h2>Edit Profil</h2>
    <form method="POST" enctype="multipart/form-data">
      <div class="profile-info">
        <div class="profile-wrapper">
          <img id="previewImage" src="<?= $fotoTampil; ?>" alt="User Icon">
          <button type="button" class="upload-btn" onclick="document.getElementById('foto').click()">📷</button>
        </div>
        <input type="file" id="foto" name="foto" accept="image/*" style="display:none;">
        <p><b><?= htmlspecialchars($namaTampil); ?></b></p>
        <small><?= htmlspecialchars($emailTampil); ?></small>
      </div>

      <label for="nama_lengkap">Nama Lengkap</label>
      <input type="text" id="nama_lengkap" name="nama_lengkap" value="<?= htmlspecialchars($namaTampil); ?>" required>

      <label for="alamat_email">Email</label>
      <input type="email" id="alamat_email" name="alamat_email" value="<?= htmlspecialchars($emailTampil); ?>" required>

      <label for="password">Password</label>
      <input type="password" id="password" name="password" placeholder="Kosongkan jika tidak ingin diubah">

      <label for="nomor_telepon">No Telepon</label>
      <input type="text" id="nomor_telepon" name="nomor_telepon" value="<?= htmlspecialchars($waTampil); ?>" required>

      <button type="submit" class="btn">Simpan Perubahan</button>
    </form>
  </div>

  <footer>
    <img src="../assets/image/logodprd.png" alt="dprd Logo">
    <img src="../assets/image/psiputih.png" alt="PSI Logo">
    Hak cipta © 2025 - Partai Solidaritas Indonesia
  </footer>

  <script>
    document.getElementById('foto').addEventListener('change', function(event) {
      const file = event.target.files[0];
      if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
          document.getElementById('previewImage').src = e.target.result;
        };
        reader.readAsDataURL(file);
      }
    });
  </script>
</body>
</html>
