<?php
// File: PSI/register.php
session_start();
include 'koneksi/config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Ambil dan amankan data input
    $nama_lengkap   = $conn->real_escape_string($_POST['nama_lengkap']);
    $alamat_lengkap = $conn->real_escape_string($_POST['alamat_lengkap']);
    
    // Gabungkan +62 dengan nomor telepon
    $nomor_telepon_input = $conn->real_escape_string($_POST['nomor_telepon']);
    $nomor_telepon = '+62' . $nomor_telepon_input;
    
    $alamat_email   = $conn->real_escape_string($_POST['alamat_email']);
    $password       = password_hash($_POST['password'], PASSWORD_DEFAULT);

    // Validasi format nomor telepon (10-13 digit setelah +62)
    if (!preg_match('/^\+62\d{10,13}$/', $nomor_telepon)) {
        echo "<script>alert('Format nomor telepon tidak valid! Minimal 10 digit.');</script>";
    } else {
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
    input.error {
      border-color: #ff4b4b !important;
      box-shadow: 0 0 5px rgba(255, 75, 75, 0.3);
    }
    .phone-input-wrapper {
      display: flex;
      gap: 10px;
      align-items: stretch;
      margin-bottom: 12px;
    }
    .phone-prefix {
      width: 80px;
      background: #e0e0e0;
      cursor: not-allowed;
      text-align: center;
      font-weight: bold;
      padding: 10px;
      border-radius: 10px;
      border: 1px solid #ccc;
      display: flex;
      align-items: center;
      justify-content: center;
    }
    .phone-input {
      flex: 1;
      margin-bottom: 0 !important;
    }
    .phone-status {
      display: block;
      font-size: 12px;
      color: #666;
      margin-top: -8px;
      margin-bottom: 12px;
      transition: color 0.3s ease;
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
      <form method="POST" id="registerForm">
        <label for="nama_lengkap">Nama Lengkap</label>
        <input type="text" id="nama_lengkap" name="nama_lengkap" placeholder="Nama Lengkap" required>

        <label for="alamat_lengkap">Alamat Lengkap</label>
        <input type="text" id="alamat_lengkap" name="alamat_lengkap" placeholder="Alamat Lengkap" required>

        <label for="nomor_telepon">Nomor Telepon</label>
        <div class="phone-input-wrapper">
          <div class="phone-prefix">+62</div>
          <input type="text" 
                 id="nomor_telepon" 
                 name="nomor_telepon" 
                 class="phone-input"
                 placeholder="8123456789" 
                 maxlength="13"
                 required>
        </div>
        <small class="phone-status" id="phone_status">Masukkan nomor tanpa 0 di depan</small>

        <label for="alamat_email">Alamat Email</label>
        <input type="email" id="alamat_email" name="alamat_email" placeholder="Alamat Email" required>

        <label for="password">Password</label>
        <input type="password" id="password" name="password" placeholder="Password" required>

        <button type="submit" class="btn btn-primary">Daftar</button>
      </form>
    </div>
  </div>

  <script>
    const phoneInput = document.getElementById('nomor_telepon');
    const phoneStatus = document.getElementById('phone_status');
    const registerForm = document.getElementById('registerForm');

    // Format nomor telepon otomatis
    phoneInput.addEventListener('input', function(e) {
      // Ambil hanya angka
      let value = this.value.replace(/\D/g, "");
      
      // Hilangkan leading zero jika ada
      if (value.startsWith('0')) {
        value = value.substring(1);
      }

      // Hilangkan 62 di depan jika user ketik manual
      if (value.startsWith('62')) {
        value = value.substring(2);
      }

      // Update display
      this.value = value;

      // Validasi panjang
      if (value.length === 0) {
        this.style.borderColor = '#ccc';
        phoneStatus.style.color = '#666';
        phoneStatus.textContent = 'Masukkan nomor tanpa 0 di depan';
      } else if (value.length >= 10) {
        this.style.borderColor = '#22c55e';
        phoneStatus.style.color = '#22c55e';
        phoneStatus.textContent = '✓ Nomor valid: +62' + value;
      } else {
        this.style.borderColor = '#ff4b4b';
        phoneStatus.style.color = '#ff4b4b';
        phoneStatus.textContent = 'Minimal 10 digit diperlukan';
      }
    });

    // Validasi saat blur
    phoneInput.addEventListener('blur', function() {
      const value = this.value.replace(/\D/g, "");
      
      if (value === '') {
        this.style.borderColor = '#ff4b4b';
        phoneStatus.style.color = '#ff4b4b';
        phoneStatus.textContent = '✗ Nomor telepon wajib diisi';
      } else if (value.length < 10) {
        this.style.borderColor = '#ff4b4b';
        phoneStatus.style.color = '#ff4b4b';
        phoneStatus.textContent = '✗ Nomor terlalu pendek (minimal 10 digit)';
      }
    });

    // Auto focus
    phoneInput.addEventListener('focus', function() {
      if (this.value === '') {
        phoneStatus.style.color = '#666';
        phoneStatus.textContent = 'Contoh: 812XXXXXXXX (tanpa 0)';
      }
    });

    // Validasi sebelum submit
    registerForm.addEventListener('submit', function(e) {
      const phoneValue = phoneInput.value.replace(/\D/g, "");
      
      if (phoneValue === '' || phoneValue.length < 10) {
        e.preventDefault();
        phoneInput.style.borderColor = '#ff4b4b';
        phoneInput.focus();
        
        alert('Nomor telepon tidak valid. Minimal 10 digit setelah +62');
        return false;
      }
    });
  </script>
</body>
</html>