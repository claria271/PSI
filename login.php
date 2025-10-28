<?php
include 'koneksi/config.php';
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['alamat_email'];
    $password = $_POST['password'];

    // Cek apakah email ada di database
    $query = "SELECT * FROM login WHERE alamat_email = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $row = $result->fetch_assoc();

        // Verifikasi password
        if (password_verify($password, $row['password'])) {
            $_SESSION['id'] = $row['id'];
            $_SESSION['nama_lengkap'] = $row['nama_lengkap'];
            $_SESSION['alamat_email'] = $row['alamat_email'];
            $_SESSION['role'] = $row['role'];

            // Redirect sesuai role
           // Redirect sesuai role
            if ($row['role'] === 'admin') {
                header("Location: http://localhost/PSI/admin/dashboardadmin.php");
          } else {
                header("Location: http://localhost/PSI/user/dashboard.php");
    }
    exit();

        } else {
            echo "<script>alert('Password salah!'); window.location.href='../login.php';</script>";
        }
    } else {
        echo "<script>alert('Email tidak ditemukan!'); window.location.href='../login.php';</script>";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Login</title>
  <style>
    body {
      font-family: Arial, sans-serif;
      background: #fff;
      display: flex;
      justify-content: center;
      align-items: center;
      height: 100vh;
      margin: 0;
    }
    .wrapper {
      width: 50%;
      max-width: 600px;
    }
    .logo {
      text-align: center;
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
    }
    h2 {
      text-align: center;
      margin-bottom: 20px;
    }
    form {
      margin: 0 10%;
    }
    label {
      display: block;
      font-size: 14px;
      margin-top: 10px;
      margin-bottom: 4px;
      font-weight: bold;
    }
    .input-group {
      position: relative;
    }
    input[type="email"],
    input[type="password"] {
      width: 100%;
      padding: 10px 40px 10px 10px;
      margin-bottom: 10px;
      border: 1px solid #ccc;
      border-radius: 10px;
      font-size: 14px;
      box-sizing: border-box;
    }
    .toggle-password {
      position: absolute;
      right: 10px;
      top: 50%;
      transform: translateY(-50%);
      cursor: pointer;
    }
    .toggle-password img {
      width: 20px;
      height: 20px;
    }
    .checkbox-forgot {
      display: flex;
      justify-content: space-between;
      align-items: center;
      font-size: 14px;
      margin-bottom: 15px;
    }
    .checkbox-forgot a {
      color: #4a90e2;
      text-decoration: none;
    }
    .checkbox-forgot a:hover {
      text-decoration: underline;
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
      background: #ff3e3ed4;
      color: #fff;
    }
    .btn-primary:hover {
      background: #ff0000ff;
    }
    .btn-secondary {
      background: #ccc;
      color: #333;
    }
  </style>
</head>
<body>
  <div class="wrapper">
    <div class="logo">
      <img src="assets/image/psi2.jpg" alt="PSI">
    </div>
    <div class="container">
      <h2>Masuk ke Akun Anda</h2>
      <form method="POST">
        
        <label for="email">Nama Pengguna atau Email</label>
        <input type="email" id="email" name="alamat_email" placeholder="Alamat Email" required>
        
        <label for="password">Password</label>
        <div class="input-group">
          <input type="password" id="password" name="password" placeholder="Kata Sandi" required>
          <span class="toggle-password" onclick="togglePassword()"></span>
        </div>

        <div class="checkbox-forgot">
          <label><input type="checkbox" name="ingat_saya"> Ingat saya</label>
        </div>

        <button type="submit" class="btn btn-primary">Masuk</button>
        <a href="register.php"><button type="button" class="btn btn-secondary">Daftar</button></a>
      </form>
    </div>
  </div>

  <script>
    function togglePassword() {
      const pwd = document.getElementById("password");
      pwd.type = pwd.type === "password" ? "text" : "password";
    }
  </script>
</body>
</html>
