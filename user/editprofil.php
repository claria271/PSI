<?php
session_start();
include '../koneksi/config.php';

// Pastikan user sudah login
if (!isset($_SESSION['alamat_email'])) {
    header("Location: login.php");
    exit;
}

$email = $_SESSION['alamat_email'];

// Ambil data user saat ini dari database
$sql = "SELECT * FROM login WHERE alamat_email='$email'";
$result = $conn->query($sql);
$user = $result->fetch_assoc();

// Proses jika form disubmit
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nama_lengkap = $conn->real_escape_string($_POST['nama_lengkap']);
    $nomor_telepon = $conn->real_escape_string($_POST['nomor_telepon']);
    $alamat_email = $conn->real_escape_string($_POST['alamat_email']);
    $password_baru = $_POST['password'];

    // Jika password diubah, hash ulang. Kalau tidak, pakai password lama
    if (!empty($password_baru)) {
        $password_hashed = password_hash($password_baru, PASSWORD_DEFAULT);
    } else {
        $password_hashed = $user['password'];
    }

    // Update data user
    $update = "UPDATE login 
               SET nama_lengkap='$nama_lengkap', 
                   nomor_telepon='$nomor_telepon',
                   alamat_email='$alamat_email', 
                   password='$password_hashed'
               WHERE alamat_email='$email'";

    if ($conn->query($update) === TRUE) {
        $_SESSION['alamat_email'] = $alamat_email; // perbarui sesi jika email berubah
        echo "<script>
                alert('Perubahan berhasil disimpan!');
                window.location='profil.php';
              </script>";
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
    header img {
      height: 40px;
    }
    header nav a {
      color: white;
      margin-left: 20px;
      text-decoration: none;
    }
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
    }
    .profile-info img {
      width: 70px;
      height: 70px;
      margin-bottom: 10px;
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
    }
    .btn:hover {
      background: #357abd;
    }
    footer {
      background: linear-gradient(90deg, #ccc, #000);
      color: white;
      text-align: center;
      padding: 10px;
      position: fixed;
      bottom: 0;
      width: 100%;
    }
  </style>
</head>
<body>
  <header>
    <img src="assets/image/psi2.jpg" alt="Logo PSI">
    <nav>
      <a href="index.php">Beranda</a>
      <a href="kontak.php">Kontak</a>
      <a href="profil.php"><b>Profil</b></a>
    </nav>
  </header>

  <div class="container">
    <h2>Edit Profil</h2>
    <div class="profile-info">
      <img src="assets/image/user.png" alt="User Icon">
      <p><b><?= htmlspecialchars($user['nama_lengkap']); ?></b></p>
      <small><?= htmlspecialchars($user['alamat_email']); ?></small>
    </div>
    <form method="POST">
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
    Hak cipta © 2025 - Partai Solidaritas Indonesia
  </footer>
</body>
</html>
