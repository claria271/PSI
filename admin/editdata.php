<?php
session_start();
include '../koneksi/config.php';

// Pastikan hanya admin yang bisa akses
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
  header("Location: ../user/login.php");
  exit();
}

// Ambil data berdasarkan ID
if (isset($_GET['id'])) {
  $id = (int)$_GET['id'];

  $stmt = $conn->prepare("SELECT * FROM keluarga WHERE id = ?");
  $stmt->bind_param("i", $id);
  $stmt->execute();
  $result = $stmt->get_result();
  $data = $result->fetch_assoc();

  if (!$data) {
    echo "<script>alert('Data tidak ditemukan');window.location='datakeluarga.php';</script>";
    exit;
  }
} else {
  // Jika tidak ada id di URL, kembalikan ke daftar
  header("Location: datakeluarga.php");
  exit;
}

// Proses update data
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $id                = (int)$_POST['id'];
  $nama_lengkap      = $_POST['nama_lengkap'];
  $nik               = $_POST['nik'];
  $no_wa             = $_POST['no_wa'];
  $alamat            = $_POST['alamat'];
  $domisili          = $_POST['domisili'];
  $jumlah_anggota    = (int)$_POST['jumlah_anggota'];
  $jumlah_bekerja    = (int)$_POST['jumlah_bekerja'];
  $total_penghasilan = (int)$_POST['total_penghasilan'];

  $query = "UPDATE keluarga SET 
              nama_lengkap = ?, 
              nik = ?, 
              no_wa = ?, 
              alamat = ?, 
              domisili = ?,
              jumlah_anggota = ?, 
              jumlah_bekerja = ?, 
              total_penghasilan = ?, 
              updated_at = NOW() 
            WHERE id = ?";

  $stmt = $conn->prepare($query);
  $stmt->bind_param(
    "sssssiiii",
    $nama_lengkap,
    $nik,
    $no_wa,
    $alamat,
    $domisili,
    $jumlah_anggota,
    $jumlah_bekerja,
    $total_penghasilan,
    $id
  );

  if ($stmt->execute()) {
    header("Location: editdata.php?id=$id&status=success");
  } else {
    header("Location: editdata.php?id=$id&status=failed");
  }
  exit();
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Edit Data Keluarga</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <style>
    * {
      margin:0;
      padding:0;
      box-sizing:border-box;
      font-family:'Poppins',sans-serif;
    }
    body {
      background:#f4f4f4;
      color:#333;
    }
    header {
      background:linear-gradient(to right,#ffffff,#000000);
      padding:12px 30px;
      display:flex;
      align-items:center;
      gap:10px;
      color:white;
      font-weight:600;
    }
    header img {
      height:40px;
    }
    .container {
      width:80%;
      max-width:800px;
      background:#fff;
      margin:40px auto;
      border-radius:10px;
      box-shadow:0 2px 6px rgba(0,0,0,0.1);
      padding:25px 30px;
    }
    h2 {
      text-align:center;
      margin-bottom:20px;
      color:#333;
    }
    label {
      display:block;
      margin:8px 0 5px;
      font-weight:600;
    }
    input,select,textarea {
      width:100%;
      padding:10px;
      border:1px solid #ccc;
      border-radius:6px;
      font-size:14px;
      margin-bottom:15px;
    }
    textarea {
      resize:none;
      height:80px;
    }
    .btn {
      background:#ff4b4b;
      color:#fff;
      border:none;
      border-radius:6px;
      padding:10px 20px;
      font-weight:600;
      cursor:pointer;
      transition:0.3s;
    }
    .btn:hover {
      background:#d83c3c;
    }
    .btn-back {
      background:#a0a0a0;
      margin-right:10px;
    }
    footer {
      text-align:center;
      padding:15px;
      background:linear-gradient(to right,#ffffff,#000000);
      color:#fff;
      font-size:14px;
    }
    footer img {
      height:20px;
      vertical-align:middle;
      margin:0 5px;
      filter:brightness(0) invert(1);
    }
  </style>
</head>
<body>
  <header>
    <img src="../assets/image/logo.png" alt="Logo">
    Edit Data Keluarga
  </header>

  <div class="container">
    <h2>Form Edit Data</h2>
    <form method="POST" action="">
      <input type="hidden" name="id" value="<?= htmlspecialchars($data['id']) ?>">

      <label>Nama Lengkap</label>
      <input type="text" name="nama_lengkap" value="<?= htmlspecialchars($data['nama_lengkap']) ?>" required>

      <label>NIK</label>
      <input type="text" name="nik" value="<?= htmlspecialchars($data['nik']) ?>">

      <label>No WhatsApp</label>
      <input type="text" name="no_wa" id="no_wa" value="<?= htmlspecialchars($data['no_wa']) ?>" placeholder="Contoh: +6281234567890">

      <label>Alamat KTP</label>
      <textarea name="alamat"><?= htmlspecialchars($data['alamat']) ?></textarea>

      <label>Alamat Domisili</label>
      <textarea name="domisili"><?= htmlspecialchars($data['domisili'] ?? '') ?></textarea>

      <label>Jumlah Anggota Keluarga</label>
      <input type="number" name="jumlah_anggota" value="<?= htmlspecialchars($data['jumlah_anggota']) ?>">

      <label>Jumlah Orang Bekerja</label>
      <input type="number" name="jumlah_bekerja" value="<?= htmlspecialchars($data['jumlah_bekerja']) ?>">

      <label>Total Penghasilan Keluarga</label>
      <input type="text" name="total_penghasilan" id="total_penghasilan" value="<?= number_format($data['total_penghasilan'], 0, ',', '.') ?>" placeholder="Contoh: 5.000.000">

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

  <script>
    // Format No WhatsApp dengan +62
    const noWaInput = document.getElementById('no_wa');
    
    noWaInput.addEventListener('input', function(e) {
      let value = e.target.value.replace(/\D/g, ''); // Hapus non-digit
      
      // Jika diawali 0, ganti dengan 62
      if (value.startsWith('0')) {
        value = '62' + value.substring(1);
      }
      
      // Jika belum ada 62 di awal, tambahkan
      if (!value.startsWith('62')) {
        value = '62' + value;
      }
      
      e.target.value = '+' + value;
    });

    // Format Total Penghasilan dengan titik pemisah ribuan
    const penghasilanInput = document.getElementById('total_penghasilan');
    
    penghasilanInput.addEventListener('input', function(e) {
      let value = e.target.value.replace(/\D/g, ''); // Hapus non-digit
      
      // Format dengan titik sebagai pemisah ribuan
      if (value) {
        value = parseInt(value).toLocaleString('id-ID');
      }
      
      e.target.value = value;
    });

    // Sebelum submit, hapus titik dari penghasilan
    document.querySelector('form').addEventListener('submit', function(e) {
      const penghasilan = document.getElementById('total_penghasilan');
      penghasilan.value = penghasilan.value.replace(/\./g, '');
    });

    // SweetAlert notifikasi sukses/gagal
    const params = new URLSearchParams(window.location.search);
    if (params.get('status') === 'success') {
      Swal.fire({
        title: 'Berhasil!',
        text: 'Data keluarga berhasil diperbarui 🎉',
        icon: 'success',
        confirmButtonColor: '#ff4b4b'
      }).then(() => window.location.href = 'datakeluarga.php');
    } else if (params.get('status') === 'failed') {
      Swal.fire({
        title: 'Gagal!',
        text: 'Terjadi kesalahan saat memperbarui data 😥',
        icon: 'error',
        confirmButtonColor: '#ff4b4b'
      });
    }
  </script>
</body>
</html>