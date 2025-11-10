<?php
session_start();
include '../koneksi/config.php';

// Cek login admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
  header("Location: ../user/login.php");
  exit();
}

$id = $_GET['id'];
$result = mysqli_query($conn, "SELECT * FROM keluarga WHERE id='$id'");
$data = mysqli_fetch_assoc($result);
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Edit Data Keluarga</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <style>
    *{margin:0;padding:0;box-sizing:border-box;font-family:'Poppins',sans-serif;}
    body{background:#f4f4f4;color:#333;}
    header{background:linear-gradient(to right,#ffffff,#000000);padding:12px 30px;display:flex;align-items:center;gap:10px;color:white;font-weight:600;}
    header img{height:40px;}
    .container{width:80%;max-width:800px;background:#fff;margin:40px auto;border-radius:10px;box-shadow:0 2px 6px rgba(0,0,0,0.1);padding:25px 30px;}
    h2{text-align:center;margin-bottom:20px;color:#333;}
    label{display:block;margin:8px 0 5px;font-weight:600;}
    input,select,textarea{width:100%;padding:10px;border:1px solid #ccc;border-radius:6px;font-size:14px;margin-bottom:15px;}
    textarea{resize:none;height:80px;}
    .btn{background:#ff4b4b;color:#fff;border:none;border-radius:6px;padding:10px 20px;font-weight:600;cursor:pointer;transition:0.3s;}
    .btn:hover{background:#d83c3c;}
    .btn-back{background:#a0a0a0;margin-right:10px;}
    footer{text-align:center;padding:15px;background:linear-gradient(to right,#ffffff,#000000);color:#fff;font-size:14px;}
    footer img{height:20px;vertical-align:middle;margin:0 5px;filter:brightness(0) invert(1);}
  </style>
</head>
<body>
  <header>
    <img src="../assets/image/logo.png" alt="Logo">
    Edit Data Keluarga
  </header>

  <div class="container">
    <h2>Form Edit Data</h2>
    <form action="proses_edit.php" method="POST">
      <input type="hidden" name="id" value="<?= $data['id'] ?>">
      <label>Nama Lengkap</label>
      <input type="text" name="nama_lengkap" value="<?= htmlspecialchars($data['nama_lengkap']) ?>">

      <label>NIK</label>
      <input type="text" name="nik" value="<?= htmlspecialchars($data['nik']) ?>">

      <label>No WhatsApp</label>
      <input type="text" name="no_wa" value="<?= htmlspecialchars($data['no_wa']) ?>">

      <label>Alamat Lengkap</label>
      <textarea name="alamat"><?= htmlspecialchars($data['alamat']) ?></textarea>

      <label>Dapil</label>
      <select name="dapil" id="dapil" required>
        <option value="<?= $data['dapil'] ?>"><?= $data['dapil'] ?></option>
        <option value="Kota Surabaya 1">Kota Surabaya 1</option>
        <option value="Kota Surabaya 2">Kota Surabaya 2</option>
        <option value="Kota Surabaya 3">Kota Surabaya 3</option>
        <option value="Kota Surabaya 4">Kota Surabaya 4</option>
        <option value="Kota Surabaya 5">Kota Surabaya 5</option>
      </select>

      <label>Kecamatan</label>
      <select name="kecamatan" id="kecamatan">
        <option value="<?= $data['kecamatan'] ?>"><?= $data['kecamatan'] ?></option>
      </select>

      <label>Jumlah Anggota Keluarga</label>
      <input type="number" name="jumlah_anggota" value="<?= $data['jumlah_anggota'] ?>">

      <label>Jumlah Orang Bekerja</label>
      <input type="number" name="jumlah_bekerja" value="<?= $data['jumlah_bekerja'] ?>">

      <label>Total Penghasilan Keluarga</label>
      <input type="number" name="total_penghasilan" value="<?= $data['total_penghasilan'] ?>">

      <label>Apakah mengenal Josiah Michael?</label>
      <select name="kenal">
        <option value="<?= $data['kenal'] ?>"><?= $data['kenal'] ?></option>
        <option value="Ya">Ya</option>
        <option value="Tidak">Tidak</option>
      </select>

      <label>Sumber Mengenal</label>
      <select name="sumber">
        <option value="<?= $data['sumber'] ?>"><?= $data['sumber'] ?></option>
        <option value="Kegiatan PSI Surabaya">Kegiatan PSI Surabaya</option>
        <option value="Dari teman atau relasi">Dari teman atau relasi</option>
        <option value="Lainnya">Lainnya</option>
      </select>

      <div style="text-align:right;">
        <button type="button" class="btn btn-back" onclick="window.location.href='datakeluarga.php'">Kembali</button>
        <button type="submit" class="btn">Perbarui</button>
      </div>
    </form>
  </div>

  <footer>
    <img src="../assets/image/logodprd.png" alt="DPRD">
    <img src="../assets/image/psiputih.png" alt="PSI">
    Hak cipta © 2025 - Partai Solidaritas Indonesia
  </footer>
</body>
</html>
