<?php
include 'koneksi/config.php';
session_start();
$email = isset($_SESSION['alamat_email']) ? $_SESSION['alamat_email'] : "";
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Login</title>
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
  <div class="container">
    <h2>Login</h2>
    <form method="POST" action="koneksi/proses.php">
      <input type="email" name="alamat_email" value="<?php echo $email; ?>" placeholder="Alamat Email" required>
      <input type="password" name="password" placeholder="Password" required>
      <button type="submit">Masuk</button>
    </form>
    <p>Belum punya akun? <a href="register.php">Daftar</a></p>
  </div>
</body>
</html>
