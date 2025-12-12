<?php
// File: PSI/register.php
session_start();
include 'koneksi/config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $conn->begin_transaction();
    
    try {
        // ============ DATA UNTUK TABEL LOGIN ============
        $nama_lengkap      = $conn->real_escape_string(trim($_POST['nama_lengkap']));
        $alamat_ktp        = $conn->real_escape_string(trim($_POST['alamat_ktp']));
        $alamat_domisili   = $conn->real_escape_string(trim($_POST['alamat_domisili']));
        $nomor_telepon_input = $conn->real_escape_string(trim($_POST['nomor_telepon']));
        $nomor_telepon     = '+62' . $nomor_telepon_input;
        $alamat_email      = $conn->real_escape_string(trim($_POST['alamat_email']));
        $password          = password_hash($_POST['password'], PASSWORD_DEFAULT);
        
        // ============ DATA UNTUK TABEL KELUARGA ============
        $nik               = trim($_POST['nik']);
        $jumlah_anggota    = (int)$_POST['jumlah_anggota'];
        $jumlah_bekerja    = (int)$_POST['jumlah_bekerja'];
        $total_penghasilan_raw = str_replace('.', '', $_POST['total_penghasilan']);
        $total_penghasilan = (int)$total_penghasilan_raw;

        // ============ VALIDASI ============
        if (!preg_match('/^\+62\d{10,13}$/', $nomor_telepon)) {
            throw new Exception('Format nomor telepon tidak valid! Minimal 10 digit.');
        }

        if (!empty($nik) && !preg_match('/^\d{16}$/', $nik)) {
            throw new Exception('NIK harus 16 digit angka!');
        }

        if ($total_penghasilan <= 0) {
            throw new Exception('Total penghasilan harus lebih dari 0!');
        }

        if ($jumlah_anggota < 1) {
            throw new Exception('Jumlah anggota keluarga minimal 1!');
        }
        
        if ($jumlah_bekerja < 0) {
            throw new Exception('Jumlah orang yang bekerja tidak boleh negatif!');
        }

        // ============ INSERT KE TABEL LOGIN ============
        $sql_login = "INSERT INTO login (nama_lengkap, alamat_lengkap, nomor_telepon, alamat_email, password) 
                      VALUES (?, ?, ?, ?, ?)";
        
        $stmt_login = $conn->prepare($sql_login);
        $stmt_login->bind_param('sssss', $nama_lengkap, $alamat_ktp, $nomor_telepon, $alamat_email, $password);
        
        if (!$stmt_login->execute()) {
            throw new Exception('Gagal menyimpan data login: ' . $stmt_login->error);
        }
        
        $user_id = $conn->insert_id;
        $stmt_login->close();

        // ============ INSERT KE TABEL KELUARGA ============
        $alamat_keluarga = $alamat_ktp;
        $domisili_keluarga = !empty($alamat_domisili) ? $alamat_domisili : null;
        $nik_value = !empty($nik) ? $nik : null;
        
        $sql_keluarga = "INSERT INTO keluarga 
                        (user_id, nama_lengkap, nik, no_wa, alamat, domisili, jumlah_anggota, jumlah_bekerja, total_penghasilan, created_at) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";
        
        $stmt_keluarga = $conn->prepare($sql_keluarga);
        $stmt_keluarga->bind_param('isssssiis', 
            $user_id, $nama_lengkap, $nik_value, $nomor_telepon, $alamat_keluarga, $domisili_keluarga,
            $jumlah_anggota, $jumlah_bekerja, $total_penghasilan
        );
        
        if (!$stmt_keluarga->execute()) {
            throw new Exception('Gagal menyimpan data keluarga: ' . $stmt_keluarga->error);
        }
        
        $stmt_keluarga->close();
        $conn->commit();

        // Set session
        $_SESSION['user_id'] = $user_id;
        $_SESSION['alamat_email'] = $alamat_email;
        $_SESSION['nama_lengkap'] = $nama_lengkap;
        $_SESSION['role'] = 'user';

        header("Location: user/dashboard.php");
        exit;

    } catch (Exception $e) {
        $conn->rollback();
        $error_message = $e->getMessage();
        echo "<script>alert('❌ " . addslashes($error_message) . "');</script>";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Daftar Akun - PSI Surabaya</title>
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
      width: 100%;
      max-width: 1200px;
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
    
    .form-grid {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 40px;
      margin-bottom: 40px;
    }
    
    .form-section {
      background: linear-gradient(135deg, #f8f8f8 0%, #e8e8e8 100%);
      padding: 30px;
      border-radius: 15px;
      box-shadow: 0 4px 15px rgba(0,0,0,0.1);
      border: 1px solid #ddd;
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
    
    label .optional {
      color: #999;
      font-weight: 400;
      font-size: 12px;
    }
    
    input, select {
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
    
    input:focus, select:focus {
      border-color: #333;
      box-shadow: 0 0 0 3px rgba(51, 51, 51, 0.1);
      outline: none;
    }
    
    input.error {
      border-color: #d32f2f !important;
      animation: shake 0.3s;
    }
    
    @keyframes shake {
      0%, 100% { transform: translateX(0); }
      25% { transform: translateX(-5px); }
      75% { transform: translateX(5px); }
    }
    
    .phone-input-wrapper {
      display: flex;
      gap: 10px;
      align-items: stretch;
      margin-bottom: 15px;
    }
    
    .phone-prefix {
      width: 80px;
      background: linear-gradient(135deg, #e0e0e0 0%, #c0c0c0 100%);
      cursor: not-allowed;
      text-align: center;
      font-weight: bold;
      padding: 12px 15px;
      border-radius: 10px;
      border: 2px solid #d0d0d0;
      display: flex;
      align-items: center;
      justify-content: center;
      color: #555;
    }
    
    .phone-input {
      flex: 1;
      margin-bottom: 0 !important;
    }
    
    .input-status {
      display: block;
      font-size: 12px;
      color: #666;
      margin-top: -10px;
      margin-bottom: 15px;
      transition: color 0.3s ease;
      font-weight: 500;
    }
    
    .input-status.success {
      color: #2e7d32;
    }
    
    .input-status.error {
      color: #d32f2f;
    }
    
    .btn {
      width: 100%;
      padding: 16px;
      border: none;
      border-radius: 10px;
      font-size: 17px;
      font-weight: bold;
      cursor: pointer;
      margin-top: 25px;
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
    
    .login-link {
      text-align: center;
      margin-top: 25px;
      color: #666;
      font-size: 14px;
    }
    
    .login-link a {
      color: #1a1a1a;
      text-decoration: none;
      font-weight: 700;
      border-bottom: 2px solid #1a1a1a;
      padding-bottom: 2px;
      transition: all 0.3s ease;
    }
    
    .login-link a:hover {
      color: #4a4a4a;
      border-bottom-color: #4a4a4a;
    }
    
    @media (max-width: 900px) {
      .form-grid {
        grid-template-columns: 1fr;
        gap: 30px;
      }
      
      .container {
        padding: 30px 25px;
      }
      
      h2 {
        font-size: 26px;
      }
    }
    
    @media (max-width: 600px) {
      body {
        padding: 15px;
      }
      
      .container {
        padding: 25px 20px;
      }
      
      .form-section {
        padding: 20px;
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
      <h2>REGISTRASI</h2>
      <p class="subtitle">Lengkapi data diri Anda untuk membuat akun baru</p>
      
      <form method="POST" id="registerForm">
        
        <!-- GRID 2 KOLOM -->
        <div class="form-grid">
          
          <!-- KOLOM KIRI -->
          <div class="form-section">
            <label for="alamat_email">Alamat Email</label>
            <input type="email" id="alamat_email" name="alamat_email" placeholder="nama@email.com" required>

            <label for="password">Password</label>
            <input type="password" id="password" name="password" placeholder="Minimal 6 karakter" required>
            <small class="input-status" id="password_status">Minimal 6 karakter</small>

            <label for="nama_lengkap">Nama Lengkap</label>
            <input type="text" id="nama_lengkap" name="nama_lengkap" placeholder="Mawar Lenjana" required>

            <label for="alamat_ktp">Alamat KTP</label>
            <input type="text" id="alamat_ktp" name="alamat_ktp" placeholder="Jl. Ketintang Madya No.12, Surabaya" required>

            <label for="alamat_domisili">Alamat Domisili <span class="optional">(opsional)</span></label>
            <input type="text" id="alamat_domisili" name="alamat_domisili" placeholder="Jl. Raya Darmo No.45, Surabaya">
            <small class="input-status">Kosongkan jika sama dengan KTP</small>
          </div>

          <!-- KOLOM KANAN -->
          <div class="form-section">
            <label for="nomor_telepon">Nomor Telepon / WhatsApp</label>
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
            <small class="input-status" id="phone_status">Masukkan nomor tanpa 0 di depan</small>

            <label for="nik">NIK <span class="optional">(opsional)</span></label>
            <input type="text" id="nik" name="nik" placeholder="3578XXXXXXXXXXXX" maxlength="16">
            <small class="input-status" id="nik_status">16 digit angka</small>

            <label for="jumlah_anggota">Jumlah Anggota Keluarga</label>
            <input type="number" id="jumlah_anggota" name="jumlah_anggota" placeholder="4" min="1" required>
            <small class="input-status">Termasuk kepala keluarga</small>

            <label for="jumlah_bekerja">Jumlah Orang yang Bekerja</label>
            <input type="number" id="jumlah_bekerja" name="jumlah_bekerja" placeholder="2" min="0" required>

            <label for="total_penghasilan">Total Penghasilan Keluarga (per bulan)</label>
            <input type="text" id="total_penghasilan" name="total_penghasilan" placeholder="5.000.000" required>
            <small class="input-status" id="penghasilan_status">Format: 1.000.000 (minimal Rp 1)</small>
          </div>
        </div>

        <button type="submit" class="btn btn-primary">Daftar Sekarang</button>
        
        <div class="login-link">
          Sudah punya akun? <a href="login.php">Login di sini</a>
        </div>
      </form>
    </div>
  </div>

  <script>
    const form = document.getElementById('registerForm');
    
    // ============ VALIDASI NIK ============
    const nikInput = document.getElementById('nik');
    const nikStatus = document.getElementById('nik_status');
    
    nikInput.addEventListener('input', function() {
      let value = this.value.replace(/\D/g, "");
      this.value = value;
      
      if (value.length === 0) {
        nikStatus.textContent = '16 digit angka (opsional)';
        nikStatus.className = 'input-status';
        this.style.borderColor = '#d0d0d0';
      } else if (value.length === 16) {
        nikStatus.textContent = '✓ NIK valid';
        nikStatus.className = 'input-status success';
        this.style.borderColor = '#2e7d32';
      } else {
        nikStatus.textContent = `✗ ${value.length}/16 digit`;
        nikStatus.className = 'input-status error';
        this.style.borderColor = '#d32f2f';
      }
    });

    // ============ VALIDASI NOMOR TELEPON ============
    const phoneInput = document.getElementById('nomor_telepon');
    const phoneStatus = document.getElementById('phone_status');

    phoneInput.addEventListener('input', function() {
      let value = this.value.replace(/\D/g, "");
      
      if (value.startsWith('0')) {
        value = value.substring(1);
      }
      if (value.startsWith('62')) {
        value = value.substring(2);
      }

      this.value = value;

      if (value.length === 0) {
        phoneStatus.textContent = 'Masukkan nomor tanpa 0 di depan';
        phoneStatus.className = 'input-status';
        this.style.borderColor = '#d0d0d0';
      } else if (value.length >= 10) {
        phoneStatus.textContent = '✓ Nomor valid: +62' + value;
        phoneStatus.className = 'input-status success';
        this.style.borderColor = '#2e7d32';
      } else {
        phoneStatus.textContent = `✗ Minimal 10 digit (${value.length}/10)`;
        phoneStatus.className = 'input-status error';
        this.style.borderColor = '#d32f2f';
      }
    });

    // ============ FORMAT PENGHASILAN ============
    const penghasilanInput = document.getElementById('total_penghasilan');
    const penghasilanStatus = document.getElementById('penghasilan_status');

    penghasilanInput.addEventListener('input', function() {
      let value = this.value.replace(/\D/g, "");
      
      if (value === "") {
        this.value = "";
        this.style.borderColor = '#d0d0d0';
        penghasilanStatus.textContent = 'Format: 1.000.000 (minimal Rp 1)';
        penghasilanStatus.className = 'input-status';
        return;
      }

      value = value.replace(/^0+/, '');
      if (value === '') value = '0';

      this.value = value.replace(/\B(?=(\d{3})+(?!\d))/g, ".");

      const numericVal = parseInt(value, 10) || 0;
      if (numericVal <= 0) {
        this.style.borderColor = '#d32f2f';
        penghasilanStatus.textContent = '✗ Harus lebih dari Rp 0';
        penghasilanStatus.className = 'input-status error';
      } else {
        this.style.borderColor = '#2e7d32';
        penghasilanStatus.textContent = '✓ Rp ' + this.value;
        penghasilanStatus.className = 'input-status success';
      }
    });

    // ============ VALIDASI PASSWORD ============
    const passwordInput = document.getElementById('password');
    const passwordStatus = document.getElementById('password_status');

    passwordInput.addEventListener('input', function() {
      if (this.value.length === 0) {
        passwordStatus.textContent = 'Minimal 6 karakter';
        passwordStatus.className = 'input-status';
        this.style.borderColor = '#d0d0d0';
      } else if (this.value.length >= 6) {
        passwordStatus.textContent = '✓ Password cukup kuat';
        passwordStatus.className = 'input-status success';
        this.style.borderColor = '#2e7d32';
      } else {
        passwordStatus.textContent = `✗ ${this.value.length}/6 karakter`;
        passwordStatus.className = 'input-status error';
        this.style.borderColor = '#d32f2f';
      }
    });

    // ============ VALIDASI FORM SEBELUM SUBMIT ============
    form.addEventListener('submit', function(e) {
      let errors = [];

      const phoneValue = phoneInput.value.replace(/\D/g, "");
      if (phoneValue.length < 10) {
        errors.push('Nomor telepon minimal 10 digit');
        phoneInput.classList.add('error');
      }

      const nikValue = nikInput.value;
      if (nikValue && nikValue.length !== 16) {
        errors.push('NIK harus 16 digit atau kosongkan jika tidak ada');
        nikInput.classList.add('error');
      }

      const penghasilanValue = penghasilanInput.value.replace(/\D/g, "");
      if (!penghasilanValue || parseInt(penghasilanValue) <= 0) {
        errors.push('Total penghasilan harus diisi dan lebih dari 0');
        penghasilanInput.classList.add('error');
      }

      if (passwordInput.value.length < 6) {
        errors.push('Password minimal 6 karakter');
        passwordInput.classList.add('error');
      }

      const jumlahAnggota = parseInt(document.getElementById('jumlah_anggota').value);
      if (isNaN(jumlahAnota) || jumlahAnggota < 1) {
        errors.push('Jumlah anggota keluarga minimal 1');
        document.getElementById('jumlah_anggota').classList.add('error');
      }

      const jumlahBekerja = parseInt(document.getElementById('jumlah_bekerja').value);
      if (isNaN(jumlahBekerja) || jumlahBekerja < 0) {
        errors.push('Jumlah orang yang bekerja tidak boleh negatif');
        document.getElementById('jumlah_bekerja').classList.add('error');
      }

      if (errors.length > 0) {
        e.preventDefault();
        alert('❌ Mohon perbaiki kesalahan berikut:\n\n' + errors.join('\n'));
        return false;
      }
    });

    document.querySelectorAll('input, select').forEach(input => {
      input.addEventListener('input', function() {
        this.classList.remove('error');
      });
    });
  </script>
</body>
</html>