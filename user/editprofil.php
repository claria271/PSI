<?php
session_start();
include '../koneksi/config.php';

// Pastikan user sudah login
if (!isset($_SESSION['alamat_email'])) {
    header("Location: login.php");
    exit;
}

$email = $_SESSION['alamat_email'];

// Ambil data user
$sql = "SELECT * FROM login WHERE alamat_email='$email'";
$result = $conn->query($sql);
$user = $result->fetch_assoc();

// Proses jika form disubmit
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nama_lengkap = $conn->real_escape_string($_POST['nama_lengkap']);
    $nomor_telepon = $conn->real_escape_string($_POST['nomor_telepon']);
    $alamat_email = $conn->real_escape_string($_POST['alamat_email']);
    $bio = $conn->real_escape_string($_POST['bio']);
    $password_baru = $_POST['password'];

    // Password
    $password_hashed = !empty($password_baru)
        ? password_hash($password_baru, PASSWORD_DEFAULT)
        : $user['password'];

    // Upload foto profil
    $foto_profil = $user['foto_profil'];
    if (!empty($_FILES['foto_profil']['name'])) {
        $target_dir = "../uploads/";
        if (!is_dir($target_dir)) mkdir($target_dir);
        $target_file = $target_dir . basename($_FILES["foto_profil"]["name"]);
        move_uploaded_file($_FILES["foto_profil"]["tmp_name"], $target_file);
        $foto_profil = $target_file;
    }

    // Update data
    $update = "UPDATE login 
               SET nama_lengkap='$nama_lengkap', 
                   nomor_telepon='$nomor_telepon',
                   alamat_email='$alamat_email', 
                   bio='$bio',
                   foto_profil='$foto_profil',
                   password='$password_hashed'
               WHERE alamat_email='$email'";

    if ($conn->query($update) === TRUE) {
        $_SESSION['alamat_email'] = $alamat_email;
        echo "<script>alert('Perubahan berhasil disimpan!'); window.location='profil.php';</script>";
        exit;
    } else {
        echo "Error: " . $conn->error;
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Edit Profil</title>
  <style>
    body {
      font-family: Arial, sans-serif;
      background-color: #fff;
      margin: 0;
      padding: 0;
    }

    header {
      background: linear-gradient(90deg, #aaa, #000);
      color: white;
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 10px 40px;
    }

    header img { height: 40px; }
    header nav a { color: white; margin-left: 20px; text-decoration: none; }
    header nav a:hover { text-decoration: underline; }

    .container {
      width: 50%;
      margin: 40px auto;
      background: linear-gradient(#e6e6e6, #a6a6a6);
      padding: 30px;
      border-radius: 15px;
    }

    h2 {
      text-align: center;
      margin-bottom: 20px;
    }

    .profile-info {
      text-align: center;
      margin-bottom: 20px;
      position: relative;
      display: inline-block;
      width: 100%;
    }

    .profile-wrapper {
      position: relative;
      display: inline-block;
    }

    .profile-wrapper img {
      width: 120px;
      height: 120px;
      border-radius: 50%;
      object-fit: cover;
      background: #bbb; /* warna abu */
    }

    /* Tombol kamera lingkaran di kanan bawah depan */
    .upload-btn {
      position: absolute;
      right: 0;
      bottom: 0;
      transform: translate(25%, 25%);
      background-color: #fff;
      border: none;
      border-radius: 50%;
      width: 35px;
      height: 35px;
      display: flex;
      align-items: center;
      justify-content: center;
      cursor: pointer;
      font-size: 18px;
      box-shadow: 0 3px 8px rgba(0,0,0,0.3);
    }

    .upload-btn:hover {
      background-color: #e60000;
      color: white;
    }

    label {
      font-weight: bold;
      display: block;
      margin-top: 10px;
      margin-bottom: 5px;
    }

    input, textarea {
      width: 100%;
      padding: 10px;
      border-radius: 10px;
      border: 1px solid #ccc;
      margin-bottom: 15px;
      box-sizing: border-box;
    }

    textarea {
      resize: vertical;
      min-height: 80px;
    }

    input:focus, textarea:focus {
      border-color: #4a90e2;
      outline: none;
    }

    .btn {
      width: 100%;
      background: #4a90e2;
      color: white;
      padding: 10px;
      border: none;
      border-radius: 10px;
      cursor: pointer;
      font-weight: bold;
    }

    .btn:hover {
      background: #357abd;
    }

    footer {
      margin-top: 0;
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
      margin-left: 5px;
      filter: brightness(0) invert(1);
    }
  </style>
</head>
<body>
  <!-- Header -->
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
    <div class="profile-info">
      <div class="profile-wrapper">
        <img src="<?= !empty($user['foto_profil']) ? htmlspecialchars($user['foto_profil']) : '../assets/image/user.png'; ?>" alt="User Icon">
        <button type="button" class="upload-btn" onclick="document.getElementById('foto_profil').click()">📷</button>
      </div>
      <input type="file" id="foto_profil" name="foto_profil" accept="image/*" style="display:none;">
      <p><b><?= htmlspecialchars($user['nama_lengkap']); ?></b></p>
      <small><?= htmlspecialchars($user['alamat_email']); ?></small>
    </div>

    <form method="POST" enctype="multipart/form-data">
      <label for="nama_lengkap">Nama Lengkap</label>
      <input type="text" id="nama_lengkap" name="nama_lengkap" value="<?= htmlspecialchars($user['nama_lengkap']); ?>" required>

      <label for="alamat_email">Email</label>
      <input type="email" id="alamat_email" name="alamat_email" value="<?= htmlspecialchars($user['alamat_email']); ?>" required>

      <label for="password">Password</label>
      <input type="password" id="password" name="password" placeholder="Kosongkan jika tidak ingin diubah">

      <label for="nomor_telepon">No Telepon</label>
      <input type="text" id="nomor_telepon" name="nomor_telepon" value="<?= htmlspecialchars($user['nomor_telepon']); ?>" required>

      <label for="bio">Bio</label>
      <textarea id="bio" name="bio" placeholder="Tulis sedikit tentang diri Anda..."><?= htmlspecialchars($user['bio'] ?? ''); ?></textarea>

      <button type="submit" class="btn">Simpan Perubahan</button>
    </form>
  </div>

  <!-- Footer -->
  <footer>
    <img src="../assets/image/logodprd.png" alt="dprd Logo">
    <img src="../assets/image/psiputih.png" alt="PSI Logo">
    Hak cipta © 2025 - Partai Solidaritas Indonesia
  </footer>
</body>
</html>
