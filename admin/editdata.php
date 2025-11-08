<?php
session_start();
include '../koneksi/config.php';

// Cek login admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
  header("Location: ../user/login.php");
  exit();
}

$id = $_GET['id'] ?? null;
if (!$id) {
  header("Location: datakeluarga.php");
  exit();
}

// Ambil data berdasarkan ID
$query = "SELECT * FROM keluarga WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$data = $result->fetch_assoc();

if (!$data) {
  echo "<script>alert('Data tidak ditemukan!'); window.location='datakeluarga.php';</script>";
  exit();
}

// Update data
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $nama = $_POST['nama_lengkap'];
  $nik = $_POST['nik'];
  $no_wa = $_POST['no_wa'];
  $alamat = $_POST['alamat'];
  $dapil = $_POST['dapil'];
  $kecamatan = $_POST['kecamatan'];
  $jumlah_anggota = $_POST['jumlah_anggota'];
  $jumlah_bekerja = $_POST['jumlah_bekerja'];
  $total_penghasilan = $_POST['total_penghasilan'];
  $kenal = $_POST['kenal'];
  $sumber = $_POST['sumber'];

  $update = "UPDATE keluarga SET 
    nama_lengkap=?, nik=?, no_wa=?, alamat=?, dapil=?, kecamatan=?, jumlah_anggota=?, jumlah_bekerja=?, total_penghasilan=?, kenal=?, sumber=?, updated_at=NOW()
    WHERE id=?";
  $stmt2 = $conn->prepare($update);
  $stmt2->bind_param("ssssssiisssi", $nama, $nik, $no_wa, $alamat, $dapil, $kecamatan, $jumlah_anggota, $jumlah_bekerja, $total_penghasilan, $kenal, $sumber, $id);
  
  if ($stmt2->execute()) {
    echo "<script>alert('Data berhasil diperbarui!'); window.location='datakeluarga.php';</script>";
  } else {
    echo "<script>alert('Gagal memperbarui data!');</script>";
  }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Edit Data Keluarga</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
  <style>
    body {
      font-family: 'Poppins', sans-serif;
      background: #f2f2f2;
      margin: 0;
      padding: 0;
    }
    .container {
      width: 70%;
      margin: 40px auto;
      background: #fff;
      border-radius: 12px;
      box-shadow: 0 4px 10px rgba(0,0,0,0.1);
      padding: 30px;
    }
    h2 {
      text-align: center;
      margin-bottom: 25px;
      color: #333;
    }
    form {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 20px;
    }
    label {
      font-weight: 600;
      margin-bottom: 5px;
      display: block;
    }
    input, select, textarea {
      width: 100%;
      padding: 8px 10px;
      border-radius: 6px;
      border: 1px solid #ccc;
      font-size: 14px;
    }
    textarea { resize: none; height: 60px; }
    .full { grid-column: span 2; }
    .btn {
      display: flex;
      justify-content: center;
      gap: 10px;
      margin-top: 25px;
      grid-column: span 2;
    }
    button {
      background: #1976d2;
      border: none;
      color: #fff;
      padding: 10px 20px;
      font-size: 15px;
      border-radius: 6px;
      cursor: pointer;
      transition: 0.3s;
    }
    button:hover { background: #0d47a1; }
    .back { background: #999; }
    .back:hover { background: #777; }
  </style>
</head>
<body>
  <div class="container">
    <h2>Edit Data Keluarga</h2>
    <form method="POST" action="">
      <div>
        <label>Nama Lengkap</label>
        <input type="text" name="nama_lengkap" value="<?= $data['nama_lengkap'] ?>" required>
      </div>
      <div>
        <label>NIK</label>
        <input type="text" name="nik" value="<?= $data['nik'] ?>" required>
      </div>
      <div>
        <label>No WhatsApp</label>
        <input type="text" name="no_wa" value="<?= $data['no_wa'] ?>" required>
      </div>
      <div class="full">
        <label>Alamat</label>
        <textarea name="alamat" required><?= $data['alamat'] ?></textarea>
      </div>
      <div>
        <label>Dapil</label>
        <select name="dapil" required>
          <option value="<?= $data['dapil'] ?>"><?= $data['dapil'] ?></option>
          <option value="Surabaya 1">Surabaya 1</option>
          <option value="Surabaya 2">Surabaya 2</option>
          <option value="Surabaya 3">Surabaya 3</option>
        </select>
      </div>
      <div>
        <label>Kecamatan</label>
        <input type="text" name="kecamatan" value="<?= $data['kecamatan'] ?>" required>
      </div>
      <div>
        <label>Jumlah Anggota</label>
        <input type="number" name="jumlah_anggota" value="<?= $data['jumlah_anggota'] ?>" required>
      </div>
      <div>
        <label>Jumlah Bekerja</label>
        <input type="number" name="jumlah_bekerja" value="<?= $data['jumlah_bekerja'] ?>" required>
      </div>
      <div>
        <label>Total Penghasilan (Rp)</label>
        <input type="number" name="total_penghasilan" value="<?= $data['total_penghasilan'] ?>" required>
      </div>
      <div>
        <label>Kenal PSI?</label>
        <select name="kenal" required>
          <option value="<?= $data['kenal'] ?>"><?= $data['kenal'] ?></option>
          <option value="Ya">Ya</option>
          <option value="Tidak">Tidak</option>
        </select>
      </div>
      <div class="full">
        <label>Sumber</label>
        <input type="text" name="sumber" value="<?= $data['sumber'] ?>" required>
      </div>
      <div class="btn">
        <button type="submit">Update</button>
        <button type="button" class="back" onclick="window.location='datakeluarga.php'">Kembali</button>
      </div>
    </form>
  </div>
</body>
</html>
