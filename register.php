<?php
include 'koneksi/config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nama_lengkap   = $_POST['nama_lengkap'];
    $alamat_lengkap = $_POST['alamat_lengkap'];
    $nomor_telepon  = $_POST['nomor_telepon'];
    $alamat_email   = $_POST['alamat_email'];
    $password       = password_hash($_POST['password'], PASSWORD_DEFAULT);

    // Gunakan prepared statement agar lebih aman
    $sql = "INSERT INTO login (nama_lengkap, alamat_lengkap, nomor_telepon, alamat_email, password) 
            VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssss", $nama_lengkap, $alamat_lengkap, $nomor_telepon, $alamat_email, $password);

    if ($stmt->execute()) {
        // Simpan email ke session supaya otomatis terisi di login
        session_start();
        $_SESSION['alamat_email'] = $alamat_email;

        header("Location: login.php");
        exit();
    } else {
        echo "Error: " . $stmt->error;
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Daftar</title>
  <style>
    body { font-family: Arial, sans-serif; background: #f8f8f8; display: flex; justify-content: center; align-items: center; height: 100vh; }
    .container { background: #fff; padding: 20px 30px; border-radius: 10px; box-shadow: 0 0 10px rgba(0,0,0,0.1); width: 400px; }
    h2 { text-align: center; margin-bottom: 20px; }
    input { width: 100%; padding: 10px; margin: 8px 0; border: 1px solid #ccc; border-radius: 5px; }
    button { width: 100%; padding: 10px; border: none; border-radius: 5px; background: #4a90e2; color: #fff; font-size: 16px; cursor: pointer; }
    button:hover { background: #357abd; }
  </style>
</head>
<body>
  <img src="psi2.jpg" alt="">
  <div class="container">
    <h2>Daftar</h2>
    <form method="POST">
      <input type="text" name="nama_lengkap" placeholder="Nama Lengkap" required>
      <input type="text" name="alamat_lengkap" placeholder="Alamat Lengkap" required>
      <input type="text" name="nomor_telepon" placeholder="Nomor Telepon" required>
      <input type="email" name="alamat_email" placeholder="Alamat Email" required>
      <input type="password" name="password" placeholder="Password" required>
      <button type="submit">Daftar</button>
    </form>
  </div>
</body>
</html>
