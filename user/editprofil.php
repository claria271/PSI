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
    $password_baru = $_POST['password'];

    // Password
    $password_hashed = !empty($password_baru)
        ? password_hash($password_baru, PASSWORD_DEFAULT)
        : $user['password'];

    // Upload foto profil
    $foto = $user['foto'];
    if (!empty($_FILES['foto']['name'])) {
        $target_dir = "../uploads/";
        if (!is_dir($target_dir)) mkdir($target_dir, 0777, true);

        $nama_file = time() . "_" . basename($_FILES["foto"]["name"]);
        $target_file = $target_dir . $nama_file;

        $allowed_types = ['image/jpeg', 'image/png', 'image/jpg', 'image/gif'];
        if (in_array($_FILES["foto"]["type"], $allowed_types)) {
            if (move_uploaded_file($_FILES["foto"]["tmp_name"], $target_file)) {
                $foto = "uploads/" . $nama_file;
            }
        }
    }

    // Update data
    $update = "UPDATE login 
               SET nama_lengkap='$nama_lengkap', 
                   nomor_telepon='$nomor_telepon',
                   alamat_email='$alamat_email', 
                   foto='$foto',
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
      box-shadow: 0 4px 8px rgba(0,0,0,0.2);
    }

    h2 {
      text-align: center;
      margin-bottom: 20px;
    }

    .profile-info {
      text-align: center;
      margin-bottom: 20px;
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
      background: #bbb;
      border: 2px solid #fff;
      box-shadow: 0 2px 6px rgba(0,0,0,0.2);
      transition: transform 0.2s;
    }

    .profile-wrapper img:hover {
      transform: scale(1.05);
    }

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
      transition: 0.3s;
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

    input {
      width: 100%;
      padding: 10px;
      border-radius: 10px;
      border: 1px solid #ccc;
      margin-bottom: 15px;
      box-sizing: border-box;
    }

    input:focus {
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
      transition: 0.3s;
    }

    .btn:hover {
      background: #357abd;
    }

    footer {
      margin-top: 40px;
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
    <form method="POST" enctype="multipart/form-data">
      <div class="profile-info">
        <div class="profile-wrapper">
          <img id="previewImage" 
               src="<?= !empty($user['foto']) ? '../' . htmlspecialchars($user['foto']) : '../assets/image/user.png'; ?>" 
               alt="User Icon">
          <button type="button" class="upload-btn" onclick="document.getElementById('foto').click()">📷</button>
        </div>
        <input type="file" id="foto" name="foto" accept="image/*" style="display:none;">
        <p><b><?= htmlspecialchars($user['nama_lengkap']); ?></b></p>
        <small><?= htmlspecialchars($user['alamat_email']); ?></small>
      </div>

      <label for="nama_lengkap">Nama Lengkap</label>
      <input type="text" id="nama_lengkap" name="nama_lengkap" value="<?= htmlspecialchars($user['nama_lengkap']); ?>" required>

      <label for="alamat_email">Email</label>
      <input type="email" id="alamat_email" name="alamat_email" value="<?= htmlspecialchars($user['alamat_email']); ?>" required>

      <label for="password">Password</label>
      <input type="password" id="password" name="password" placeholder="Kosongkan jika tidak ingin diubah">

      <label for="nomor_telepon">No Telepon</label>
      <input type="text" id="nomor_telepon" name="nomor_telepon" value="<?= htmlspecialchars($user['nomor_telepon']); ?>" required>

      <button type="submit" class="btn">Simpan Perubahan</button>
    </form>
  </div>

  <footer>
    <img src="../assets/image/logodprd.png" alt="dprd Logo">
    <img src="../assets/image/psiputih.png" alt="PSI Logo">
    Hak cipta © 2025 - Partai Solidaritas Indonesia
  </footer>

  <script>
    // Preview foto sebelum disimpan
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
