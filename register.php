<?php
// File: PSI/register.php
session_start();
include 'koneksi/config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Ambil dan amankan data input
    $nama_lengkap   = $conn->real_escape_string($_POST['nama_lengkap']);
    $alamat_lengkap = $conn->real_escape_string($_POST['alamat_lengkap']);
    $nomor_telepon  = $conn->real_escape_string($_POST['nomor_telepon']);
    $alamat_email   = $conn->real_escape_string($_POST['alamat_email']);
    $password       = password_hash($_POST['password'], PASSWORD_DEFAULT);

    // Simpan data ke database
    $sql = "INSERT INTO login (nama_lengkap, alamat_lengkap, nomor_telepon, alamat_email, password) 
            VALUES ('$nama_lengkap', '$alamat_lengkap', '$nomor_telepon', '$alamat_email', '$password')";

    if ($conn->query($sql) === TRUE) {
        // Simpan data ke session agar halaman user mengenali
        $_SESSION['alamat_email'] = $alamat_email;
        $_SESSION['nama_lengkap'] = $nama_lengkap;
        $_SESSION['role'] = 'user';

        // ✅ Arahkan ke halaman tambah data (karena file ini di root)
        header("Location: user/tambahdata.php");
        exit;
    } else {
        echo "<script>alert('Terjadi kesalahan saat pendaftaran: " . $conn->error . "');</script>";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Daftar Akun</title>
  <style>
    body {
      font-family: Arial, sans-serif;
      background: #fff;
      display: flex;
      justify-content: center;
      align-items: center;
      min-height: 100vh;
      margin: 0;
    }
    .wrapper {
      width: 50%;
      max-width: 600px;
    }
    .logo {
      text-align: center;
      margin-top: 30px;
      margin-bottom: 15px;
    }
    .logo img {
      width: 100%;
      height: auto;
      border-radius: 20px 20px 0 0;
      opacity: 0.5;
    }
    .container {
      background: #fff;
      padding: 30px;
      border-radius: 10px 10px 20px 20px;
      box-shadow: 0 0 15px rgba(0,0,0,0.1);
      width: 100%;
      box-sizing: border-box;
      margin-bottom: 40px;
    }
    h2 {
      text-align: center;
      margin-bottom: 20px;
    }
    form {
      margin: 0 7%;
    }
    label {
      display: block;
      font-size: 14px;
      margin-top: 10px;
      margin-bottom: 4px;
      font-weight: bold;
    }
    input {
      width: 100%;
      padding: 10px;
      margin-bottom: 12px;
      border: 1px solid #ccc;
      border-radius: 10px;
      font-size: 14px;
      box-sizing: border-box;
      transition: 0.3s;
    }
    input:focus {
      border-color: #00aeff;
      box-shadow: 0 0 6px rgba(74,144,226,0.4);
      outline: none;
    }
    .btn {
      width: 100%;
      padding: 10px;
      border: none;
      border-radius: 10px;
      font-size: 16px;
      cursor: pointer;
      margin-top: 8px;
      box-sizing: border-box;
    }
    .btn-primary {
      background: #e24a4a;
      color: #fff;
    }
    .btn-primary:hover {
      background: #ff0000;
    }
  </style>
</head>
<body>
  <div class="wrapper">
    <div class="logo">
      <img src="assets/image/psi2.jpg" alt="Logo PSI">
    </div>
    <div class="container">
      <h2>Form Pendaftaran</h2>
      <form method="POST">
        <label for="nama_lengkap">Nama Lengkap</label>
        <input type="text" id="nama_lengkap" name="nama_lengkap" placeholder="Nama Lengkap" required>

        <label for="alamat_lengkap">Alamat Lengkap</label>
        <input type="text" id="alamat_lengkap" name="alamat_lengkap" placeholder="Alamat Lengkap" required>

        <label for="nomor_telepon">Nomor Telepon</label>
        <input type="text" id="nomor_telepon" name="nomor_telepon" placeholder="Nomor Telepon" required>

        <label for="alamat_email">Alamat Email</label>
        <input type="email" id="alamat_email" name="alamat_email" placeholder="Alamat Email" required>

        <label for="password">Password</label>
        <input type="password" id="password" name="password" placeholder="Password" required>

        <button type="submit" class="btn btn-primary">Daftar</button>
      </form>
    </div>
  </div>
</body>
</html>
