<?php
session_start();
include '../koneksi/config.php';

// Pastikan user sudah login
if (!isset($_SESSION['alamat_email'])) {
  header("Location: login.php");
  exit;
}

// Ambil data user dari database
$email = $_SESSION['alamat_email'];
$sql = "SELECT * FROM login WHERE alamat_email='$email'";
$result = $conn->query($sql);
$user = $result->fetch_assoc();

// Data keluarga sementara dikosongkan
$dataKeluarga = [];
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Profil Pengguna</title>
  <style>
    * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Poppins', sans-serif; }
    body { background: #fff; color: #222; }

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

    /* === PROFILE SECTION DENGAN TEKSTUR KOTAK === */
    .profile-section {
      background-color: #111;
      background-image:
        linear-gradient(45deg, rgba(255,255,255,0.05) 25%, transparent 25%),
        linear-gradient(-45deg, rgba(255,255,255,0.05) 25%, transparent 25%),
        linear-gradient(45deg, transparent 75%, rgba(255,255,255,0.05) 75%),
        linear-gradient(-45deg, transparent 75%, rgba(255,255,255,0.05) 75%);
      background-size: 40px 40px;
      color: #fff;
      padding: 60px 10%;
      display: flex;
      align-items: center;
      gap: 40px;
      position: relative;
    }

    .profile-wrapper { position: relative; display: inline-block; }

    .profile-pic {
      width: 180px;
      height: 180px;
      border-radius: 50%;
      background: #bbb;
      overflow: hidden;
      position: relative;
    }

    .profile-pic img {
      width: 100%;
      height: 100%;
      object-fit: cover;
      display: block;
    }

    .edit-profile-btn {
      position: absolute;
      bottom: 10px;
      right: -25px;
      width: 40px;
      height: 40px;
      background-color: #fff;
      color: #111;
      border: none;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      cursor: pointer;
      font-size: 18px;
      box-shadow: 0 4px 10px rgba(0,0,0,0.3);
      transition: 0.3s;
    }

    .edit-profile-btn:hover {
      background-color: #e60000;
      color: #fff;
      transform: scale(1.1);
    }

    .profile-info h1 { font-size: 32px; margin-bottom: 5px; }
    .profile-info p.email { font-size: 18px; color: #ccc; }

    .detail {
      margin-top: 8px;
      color: #ddd;
    }

    .section-tabs {
      display: flex;
      justify-content: center;
      background: #fff;
      border-bottom: 1px solid #ccc;
    }

    .tab {
      padding: 15px 30px;
      font-weight: 500;
      color: #333;
      cursor: pointer;
      transition: 0.3s;
    }

    .tab:hover, .tab.active {
      border-bottom: 3px solid #e60000;
      color: #e60000;
    }

    .logout-btn {
      display: block;
      width: 90%;
      margin: 20px auto;
      padding: 12px 0;
      background: #e60000;
      color: #fff;
      border: none;
      border-radius: 10px;
      font-size: 16px;
      font-weight: 600;
      cursor: pointer;
      transition: 0.3s;
    }

    .logout-btn:hover {
      background: #b80000;
      transform: translateY(-2px);
    }

    .form-container {
      display: none;
      width: 70%;
      max-width: 800px;
      margin: 30px auto;
      background: linear-gradient(to bottom, #e0e0e0, #b3b3b3);
      padding: 30px 40px;
      border-radius: 10px;
      box-shadow: 0 3px 8px rgba(0,0,0,0.2);
      color: #000;
    }

    .form-container.active {
      display: block;
      animation: fadeIn 0.6s ease-in-out;
    }

    @keyframes fadeIn {
      from { opacity: 0; transform: translateY(20px); }
      to { opacity: 1; transform: translateY(0); }
    }

    label { font-weight: 600; display: block; margin-top: 10px; }
    input, textarea, select {
      width: 100%;
      padding: 10px;
      border-radius: 8px;
      border: 1px solid #ccc;
      margin-bottom: 15px;
      background: rgba(255,255,255,0.9);
      color: #111;
    }

    textarea { resize: none; height: 70px; }

    .btn-save {
      width: 100%;
      padding: 12px;
      background: #e60000;
      color: #fff;
      border: none;
      border-radius: 8px;
      cursor: pointer;
      font-weight: 600;
      transition: 0.3s;
    }

    .btn-save:hover { background: #b80000; }

    footer {
      margin-top: 0;
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

  <!-- Profil -->
  <section class="profile-section">
    <div class="profile-wrapper">
      <div class="profile-pic">
        <img src="../assets/image/user.png" alt="Foto Profil">
      </div>
      <button class="edit-profile-btn" onclick="window.location.href='editprofil.php'">📷</button>
    </div>

    <div class="profile-info">
      <h1><?= htmlspecialchars($user['nama_lengkap']); ?></h1>
      <p class="email"><?= htmlspecialchars($user['alamat_email']); ?></p>
      <div class="detail">🕓 Bergabung sejak 2023</div>
      <div class="detail">📍 Kota Surabaya</div>
    </div>
  </section>

  <!-- Tombol Logout -->
  <button class="logout-btn" onclick="window.location.href='logout.php'">Logout</button>

  <!-- Tabs -->
  <div class="section-tabs">
    <div class="tab active" id="tabEdit">Edit Data</div>
  </div>

  <!-- Form Edit Data -->
  <div class="form-container" id="formEdit">
    <form action="update_data.php" method="POST">
      <label>Nama Lengkap</label>
      <input type="text" name="nama_lengkap" value="">
      <label>NIK</label>
      <input type="text" name="nik" value="">
      <label>No WhatsApp</label>
      <input type="text" name="no_wa" value="">
      <label>Alamat Lengkap</label>
      <textarea name="alamat"></textarea>
      <label>Daerah Pemilihan</label>
      <select name="dapil">
        <option value="">-- Pilih Dapil --</option>
        <option value="Kota Surabaya 1">Kota Surabaya 1</option>
        <option value="Kota Surabaya 2">Kota Surabaya 2</option>
        <option value="Kota Surabaya 3">Kota Surabaya 3</option>
        <option value="Kota Surabaya 4">Kota Surabaya 4</option>
        <option value="Kota Surabaya 5">Kota Surabaya 5</option>
      </select>
      <label>Kecamatan</label>
      <input type="text" name="kecamatan" value="">
      <label>Jumlah Anggota Keluarga</label>
      <input type="number" name="jumlah_anggota" value="">
      <label>Total Penghasilan</label>
      <input type="text" name="total_penghasilan" value="">
      <button type="submit" class="btn-save">💾 Simpan Perubahan</button>
    </form>
  </div>

  <!-- Footer -->
  <footer>
    <img src="../assets/image/logodprd.png" alt="dprd Logo">
    <img src="../assets/image/psiputih.png" alt="PSI Logo">
    Hak cipta © 2025 - Partai Solidaritas Indonesia
  </footer>

  <script>
    // Tombol tab Edit Data
    document.getElementById('tabEdit').addEventListener('click', function() {
      document.getElementById('formEdit').classList.toggle('active');
    });
  </script>
</body>
</html>
