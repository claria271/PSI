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
            if ($row['role'] === 'admin') {
                header("Location: http://localhost/PSI/admin/dashboardadmin.php");
            } else {
                header("Location: http://localhost/PSI/user/dashboard.php");
            }
            exit();

        } else {
            echo "<script>alert('Password salah!'); window.location.href='login.php';</script>";
        }
    } else {
        echo "<script>alert('Email tidak ditemukan!'); window.location.href='login.php';</script>";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login - PSI Surabaya</title>
  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }
    
    body {
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      background: linear-gradient(135deg, rgba(0, 0, 0, 0.85) 0%, rgba(26, 26, 26, 0.85) 50%, rgba(0, 0, 0, 0.85) 100%),
                  url('assets/image/index.jpeg') center/cover fixed no-repeat;
      background-size: cover;
      display: flex;
      justify-content: center;
      align-items: center;
      min-height: 100vh;
      padding: 30px 20px;
      position: relative;
    }
    
    body::before {
      content: '';
      position: fixed;
      top: 0;
      left: 0;
      right: 0;
      bottom: 0;
      background: radial-gradient(circle at 50% 50%, rgba(255, 255, 255, 0.05) 0%, transparent 50%);
      pointer-events: none;
      z-index: 0;
    }
    
    .wrapper {
      width: 40%;
      max-width: 600px;
      min-width: 400px;
      background: rgba(255, 255, 255, 0.98);
      border-radius: 20px;
      box-shadow: 0 20px 60px rgba(0, 0, 0, 0.5), 0 0 100px rgba(255, 255, 255, 0.1);
      overflow: hidden;
      position: relative;
      z-index: 1;
      backdrop-filter: blur(10px);
    }
    
    .logo {
      background: linear-gradient(135deg, #000000 0%, #434343 50%, #1a1a1a 100%);
      padding: 30px;
      text-align: center;
      position: relative;
      overflow: hidden;
    }
    
    .logo::before {
      content: '';
      position: absolute;
      top: 0;
      left: -100%;
      width: 100%;
      height: 100%;
      background: linear-gradient(90deg, transparent, rgba(255,255,255,0.1), transparent);
      animation: shine 3s infinite;
    }
    
    @keyframes shine {
      to { left: 100%; }
    }
    
    .logo img {
      max-width: 50%;
      height: auto;
      position: relative;
      z-index: 1;
    }
    
    .container {
      padding: 50px;
    }
    
    h2 {
      text-align: center;
      margin-bottom: 10px;
      color: #1a1a1a;
      font-size: 32px;
      font-weight: 700;
    }
    
    .subtitle {
      text-align: center;
      color: #666;
      margin-bottom: 40px;
      font-size: 15px;
    }
    
    .form-section {
      background: linear-gradient(135deg, #f8f8f8 0%, #e8e8e8 100%);
      padding: 30px;
      border-radius: 15px;
      box-shadow: 0 4px 15px rgba(0,0,0,0.1);
      border: 1px solid #ddd;
      margin-bottom: 25px;
    }
    
    label {
      display: block;
      font-size: 14px;
      margin-top: 15px;
      margin-bottom: 6px;
      font-weight: 600;
      color: #2d2d2d;
    }
    
    label:first-of-type {
      margin-top: 0;
    }
    
    input {
      width: 100%;
      padding: 12px 15px;
      margin-bottom: 15px;
      border: 2px solid #d0d0d0;
      border-radius: 10px;
      font-size: 14px;
      transition: all 0.3s ease;
      font-family: inherit;
      background: #fff;
    }
    
    input:focus {
      border-color: #333;
      box-shadow: 0 0 0 3px rgba(51, 51, 51, 0.1);
      outline: none;
    }
    
    .input-group {
      position: relative;
    }
    
    .input-group input {
      padding-right: 45px;
    }
    
    .toggle-password {
      position: absolute;
      right: 15px;
      top: 12px;
      cursor: pointer;
      font-size: 20px;
      color: #666;
      user-select: none;
      transition: color 0.3s ease;
    }
    
    .toggle-password:hover {
      color: #333;
    }
    
    .checkbox-forgot {
      display: flex;
      justify-content: space-between;
      align-items: center;
      font-size: 13px;
      margin-bottom: 20px;
      color: #555;
    }
    
    .checkbox-forgot label {
      margin: 0;
      font-weight: 400;
      cursor: pointer;
      display: flex;
      align-items: center;
      gap: 6px;
    }
    
    .checkbox-forgot input[type="checkbox"] {
      width: auto;
      margin: 0;
      cursor: pointer;
    }
    
    .checkbox-forgot a {
      color: #1a1a1a;
      text-decoration: none;
      font-weight: 600;
      border-bottom: 2px solid transparent;
      transition: all 0.3s ease;
    }
    
    .checkbox-forgot a:hover {
      border-bottom-color: #1a1a1a;
    }
    
    .btn {
      width: 100%;
      padding: 16px;
      border: none;
      border-radius: 10px;
      font-size: 17px;
      font-weight: bold;
      cursor: pointer;
      margin-top: 10px;
      transition: all 0.3s ease;
    }
    
    .btn-primary {
      background: linear-gradient(135deg, #1a1a1a 0%, #4a4a4a 50%, #2d2d2d 100%);
      color: #fff;
      box-shadow: 0 4px 15px rgba(0,0,0,0.3);
    }
    
    .btn-primary:hover {
      transform: translateY(-3px);
      box-shadow: 0 8px 25px rgba(0,0,0,0.4);
      background: linear-gradient(135deg, #000000 0%, #3a3a3a 50%, #1a1a1a 100%);
    }
    
    .btn-primary:active {
      transform: translateY(-1px);
    }
    
    .btn-secondary {
      background: linear-gradient(135deg, #e0e0e0 0%, #c0c0c0 100%);
      color: #333;
      box-shadow: 0 4px 15px rgba(0,0,0,0.1);
      text-decoration: none;
      display: block;
      text-align: center;
    }
    
    .btn-secondary:hover {
      transform: translateY(-3px);
      box-shadow: 0 8px 25px rgba(0,0,0,0.2);
      background: linear-gradient(135deg, #d0d0d0 0%, #b0b0b0 100%);
    }
    
    .btn-secondary:active {
      transform: translateY(-1px);
    }
    
    .register-link {
      text-align: center;
      margin-top: 25px;
      color: #666;
      font-size: 14px;
    }
    
    .register-link a {
      color: #1a1a1a;
      text-decoration: none;
      font-weight: 700;
      border-bottom: 2px solid #1a1a1a;
      padding-bottom: 2px;
      transition: all 0.3s ease;
    }
    
    .register-link a:hover {
      color: #4a4a4a;
      border-bottom-color: #4a4a4a;
    }
    
    @media (max-width: 1200px) {
      .wrapper {
        width: 50%;
        min-width: 350px;
      }
    }
    
    @media (max-width: 900px) {
      .wrapper {
        width: 70%;
        min-width: 300px;
      }
    }
    
    @media (max-width: 600px) {
      body {
        padding: 15px;
      }
      
      .wrapper {
        width: 100%;
        min-width: unset;
      }
      
      .container {
        padding: 30px 25px;
      }
      
      .form-section {
        padding: 20px;
      }
      
      h2 {
        font-size: 26px;
      }
    }
  </style>
</head>
<body>
  
  <div class="wrapper">
    <div class="logo">
      <img src="assets/image/logou.png" alt="Logo DPRD">
    </div>
    
    <div class="container">
      <h2>LOGIN</h2>
      <p class="subtitle">Masuk ke akun Anda untuk melanjutkan</p>
      
      <form method="POST">
        <div class="form-section">
          <label for="email">Alamat Email</label>
          <input type="email" id="email" name="alamat_email" placeholder="nama@email.com" required>
          
          <label for="password">Password</label>
          <div class="input-group">
            <input type="password" id="password" name="password" placeholder="Masukkan password Anda" required>
            <span class="toggle-password" onclick="togglePassword()">👁️</span>
          </div>

          <div class="checkbox-forgot">
            <label>
              <input type="checkbox" name="ingat_saya">
              Ingat saya
            </label>
            
          </div>

          <button type="submit" class="btn btn-primary">Masuk</button>
        </div>
        
        <div class="register-link">
          Belum punya akun? <a href="register.php">Daftar di sini</a>
        </div>
      </form>
    </div>
  </div>

  <script>
    function togglePassword() {
      const pwd = document.getElementById("password");
      const toggle = document.querySelector(".toggle-password");
      
      if (pwd.type === "password") {
        pwd.type = "text";
        toggle.textContent = "🙈";
      } else {
        pwd.type = "password";
        toggle.textContent = "👁️";
      }
    }
  </script>
</body>
</html>