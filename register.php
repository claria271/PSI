<?php
// File: PSI/register.php
session_start();
include 'koneksi/config.php';

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
$conn->set_charset('utf8mb4');

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    try {
        // ============ AMBIL INPUT FORM ============
        $alamat_email        = trim($_POST['alamat_email'] ?? '');
        $password_plain      = (string)($_POST['password'] ?? '');

        $nama_lengkap        = trim($_POST['nama_lengkap'] ?? '');
        $alamat_ktp          = trim($_POST['alamat_ktp'] ?? '');
        $alamat_domisili     = trim($_POST['alamat_domisili'] ?? '');
        $nomor_telepon_input = trim($_POST['nomor_telepon'] ?? '');
        $nik                 = trim($_POST['nik'] ?? '');
        $jumlah_anggota      = (int)($_POST['jumlah_anggota'] ?? 0);
        $jumlah_bekerja      = (int)($_POST['jumlah_bekerja'] ?? 0);

        $total_penghasilan_raw = str_replace('.', '', ($_POST['total_penghasilan'] ?? '0'));
        $total_penghasilan   = (int)$total_penghasilan_raw;

        // ============ NORMALISASI NO WA ============
        $nomor_telepon_input = preg_replace('/\D+/', '', $nomor_telepon_input);
        if (str_starts_with($nomor_telepon_input, '0'))  $nomor_telepon_input = substr($nomor_telepon_input, 1);
        if (str_starts_with($nomor_telepon_input, '62')) $nomor_telepon_input = substr($nomor_telepon_input, 2);
        $nomor_telepon = '+62' . $nomor_telepon_input;

        // Nullable fields untuk keluarga
        $domisili_keluarga = ($alamat_domisili !== '') ? $alamat_domisili : null;
        $nik_value         = ($nik !== '') ? $nik : null;

        // ============ VALIDASI ============
        if (!filter_var($alamat_email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception('Format email tidak valid!');
        }
        if (strlen($password_plain) < 6) {
            throw new Exception('Password minimal 6 karakter!');
        }
        if ($nama_lengkap === '') {
            throw new Exception('Nama lengkap wajib diisi!');
        }
        if ($alamat_ktp === '') {
            throw new Exception('Alamat KTP wajib diisi!');
        }
        if (!preg_match('/^\+62\d{10,13}$/', $nomor_telepon)) {
            throw new Exception('Format nomor telepon tidak valid! Minimal 10 digit.');
        }
        if ($nik !== '' && !preg_match('/^\d{16}$/', $nik)) {
            throw new Exception('NIK harus 16 digit angka!');
        }
        if ($jumlah_anggota < 1) {
            throw new Exception('Jumlah anggota keluarga minimal 1!');
        }
        if ($jumlah_bekerja < 0) {
            throw new Exception('Jumlah orang yang bekerja tidak boleh negatif!');
        }
        if ($total_penghasilan <= 0) {
            throw new Exception('Total penghasilan harus lebih dari 0!');
        }

        // ============ CEK EMAIL DUPLIKAT ============
        $cek = $conn->prepare("SELECT id FROM login WHERE alamat_email = ? LIMIT 1");
        $cek->bind_param("s", $alamat_email);
        $cek->execute();
        $res = $cek->get_result();
        if ($res && $res->num_rows > 0) {
            $cek->close();
            throw new Exception('Email sudah terdaftar. Silakan login!');
        }
        $cek->close();

        // ==========================================================
        // 1) INSERT KE TABEL LOGIN (HANYA email + password + role)
        // ==========================================================
        $password_hash = password_hash($password_plain, PASSWORD_DEFAULT);

        $sql_login = "INSERT INTO login (alamat_email, password, role)
                      VALUES (?, ?, 'user')";
        $stmt_login = $conn->prepare($sql_login);
        $stmt_login->bind_param("ss", $alamat_email, $password_hash);
        $stmt_login->execute();

        $user_id = (int)$conn->insert_id;
        $stmt_login->close();

        // ==========================================================
        // 2) INSERT KE TABEL KELUARGA (data diri lengkap)
        // ==========================================================
        $sql_keluarga = "INSERT INTO keluarga
            (user_id, nama_lengkap, nik, no_wa, alamat, domisili, jumlah_anggota, jumlah_bekerja, total_penghasilan, created_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";

        $stmt_keluarga = $conn->prepare($sql_keluarga);
        $stmt_keluarga->bind_param(
            "isssssiii",
            $user_id,
            $nama_lengkap,
            $nik_value,
            $nomor_telepon,
            $alamat_ktp,
            $domisili_keluarga,
            $jumlah_anggota,
            $jumlah_bekerja,
            $total_penghasilan
        );
        $stmt_keluarga->execute();
        $stmt_keluarga->close();

        // ============ SET SESSION ============
        $_SESSION['user_id']      = $user_id;
        $_SESSION['alamat_email'] = $alamat_email;
        $_SESSION['nama_lengkap'] = $nama_lengkap;
        $_SESSION['role']         = 'user';

        header("Location: user/dashboard.php");
        exit;

    } catch (Exception $e) {
        echo "<script>alert('❌ " . addslashes($e->getMessage()) . "');</script>";
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
    * { margin: 0; padding: 0; box-sizing: border-box; }

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
      top: 0; left: 0; right: 0; bottom: 0;
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
      top: 0; left: -100%;
      width: 100%; height: 100%;
      background: linear-gradient(90deg, transparent, rgba(255,255,255,0.1), transparent);
      animation: shine 3s infinite;
    }

    @keyframes shine { to { left: 100%; } }

    .logo img { max-width: 50%; height: auto; position: relative; z-index: 1; }

    .container { padding: 50px; }

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

    label:first-of-type { margin-top: 0; }

    label .optional {
      color: #999;
      font-weight: 400;
      font-size: 12px;
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

    input.error { border-color: #d32f2f !important; animation: shake 0.3s; }
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

    .phone-input { flex: 1; margin-bottom: 0 !important; }

    .input-status {
      display: block;
      font-size: 12px;
      color: #666;
      margin-top: -10px;
      margin-bottom: 15px;
      transition: color 0.3s ease;
      font-weight: 500;
    }
    .input-status.success { color: #2e7d32; }
    .input-status.error   { color: #d32f2f; }

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

        <div class="form-grid">
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

    const nikInput = document.getElementById('nik');
    const nikStatus = document.getElementById('nik_status');

    nikInput.addEventListener('input', function() {
      let value = this.value.replace(/\D/g, "");
      this.value = value;

      if (value.length === 0) {
        nikStatus.textContent = '16 digit angka (opsional)';
        nikStatus.className = 'input-status';
      } else if (value.length === 16) {
        nikStatus.textContent = '✓ NIK valid';
        nikStatus.className = 'input-status success';
      } else {
        nikStatus.textContent = `✗ ${value.length}/16 digit`;
        nikStatus.className = 'input-status error';
      }
    });

    const phoneInput = document.getElementById('nomor_telepon');
    const phoneStatus = document.getElementById('phone_status');

    phoneInput.addEventListener('input', function() {
      let value = this.value.replace(/\D/g, "");
      if (value.startsWith('0')) value = value.substring(1);
      if (value.startsWith('62')) value = value.substring(2);
      this.value = value;

      if (value.length === 0) {
        phoneStatus.textContent = 'Masukkan nomor tanpa 0 di depan';
        phoneStatus.className = 'input-status';
      } else if (value.length >= 10) {
        phoneStatus.textContent = '✓ Nomor valid: +62' + value;
        phoneStatus.className = 'input-status success';
      } else {
        phoneStatus.textContent = `✗ Minimal 10 digit (${value.length}/10)`;
        phoneStatus.className = 'input-status error';
      }
    });

    const penghasilanInput = document.getElementById('total_penghasilan');
    const penghasilanStatus = document.getElementById('penghasilan_status');

    penghasilanInput.addEventListener('input', function() {
      let value = this.value.replace(/\D/g, "");
      if (value === "") {
        this.value = "";
        penghasilanStatus.textContent = 'Format: 1.000.000 (minimal Rp 1)';
        penghasilanStatus.className = 'input-status';
        return;
      }
      value = value.replace(/^0+/, '');
      if (value === '') value = '0';
      this.value = value.replace(/\B(?=(\d{3})+(?!\d))/g, ".");
      const numericVal = parseInt(value, 10) || 0;

      if (numericVal <= 0) {
        penghasilanStatus.textContent = '✗ Harus lebih dari Rp 0';
        penghasilanStatus.className = 'input-status error';
      } else {
        penghasilanStatus.textContent = '✓ Rp ' + this.value;
        penghasilanStatus.className = 'input-status success';
      }
    });

    const passwordInput = document.getElementById('password');
    const passwordStatus = document.getElementById('password_status');

    passwordInput.addEventListener('input', function() {
      if (this.value.length === 0) {
        passwordStatus.textContent = 'Minimal 6 karakter';
        passwordStatus.className = 'input-status';
      } else if (this.value.length >= 6) {
        passwordStatus.textContent = '✓ Password cukup kuat';
        passwordStatus.className = 'input-status success';
      } else {
        passwordStatus.textContent = `✗ ${this.value.length}/6 karakter`;
        passwordStatus.className = 'input-status error';
      }
    });

    form.addEventListener('submit', function(e) {
      let errors = [];

      const phoneValue = phoneInput.value.replace(/\D/g, "");
      if (phoneValue.length < 10) errors.push('Nomor telepon minimal 10 digit');

      const nikValue = nikInput.value;
      if (nikValue && nikValue.length !== 16) errors.push('NIK harus 16 digit atau kosongkan jika tidak ada');

      const penghasilanValue = penghasilanInput.value.replace(/\D/g, "");
      if (!penghasilanValue || parseInt(penghasilanValue) <= 0) errors.push('Total penghasilan harus diisi dan lebih dari 0');

      if (passwordInput.value.length < 6) errors.push('Password minimal 6 karakter');

      const jumlahAnggota = parseInt(document.getElementById('jumlah_anggota').value);
      if (isNaN(jumlahAnggota) || jumlahAnggota < 1) errors.push('Jumlah anggota keluarga minimal 1');

      const jumlahBekerja = parseInt(document.getElementById('jumlah_bekerja').value);
      if (isNaN(jumlahBekerja) || jumlahBekerja < 0) errors.push('Jumlah orang yang bekerja tidak boleh negatif');

      if (errors.length > 0) {
        e.preventDefault();
        alert('❌ Mohon perbaiki kesalahan berikut:\n\n' + errors.join('\n'));
      }
    });
  </script>
</body>
</html>
