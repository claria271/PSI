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
  $id = $_GET['id'];
  $stmt = $conn->prepare("SELECT * FROM keluarga WHERE id = ?");
  $stmt->bind_param("i", $id);
  $stmt->execute();
  $result = $stmt->get_result();
  $data = $result->fetch_assoc();

  if (!$data) {
    echo "<script>alert('Data tidak ditemukan');window.location='datakeluarga.php';</script>";
    exit;
  }
}

// Proses update data
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $id = $_POST['id'];
  $nama_lengkap = $_POST['nama_lengkap'];
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

  $query = "UPDATE keluarga SET 
              nama_lengkap=?, nik=?, no_wa=?, alamat=?, dapil=?, kecamatan=?, 
              jumlah_anggota=?, jumlah_bekerja=?, total_penghasilan=?, 
              kenal=?, sumber=?, updated_at=NOW() 
            WHERE id=?";
  $stmt = $conn->prepare($query);
  $stmt->bind_param("ssssssiiissi", $nama_lengkap, $nik, $no_wa, $alamat, $dapil, $kecamatan, $jumlah_anggota, $jumlah_bekerja, $total_penghasilan, $kenal, $sumber, $id);

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
    <form method="POST" action="">
      <input type="hidden" name="id" value="<?= htmlspecialchars($data['id']) ?>">

      <label>Nama Lengkap</label>
      <input type="text" name="nama_lengkap" value="<?= htmlspecialchars($data['nama_lengkap']) ?>" required>

      <label>NIK</label>
      <input type="text" name="nik" value="<?= htmlspecialchars($data['nik']) ?>">

      <label>No WhatsApp</label>
      <input type="text" name="no_wa" value="<?= htmlspecialchars($data['no_wa']) ?>">

      <label>Alamat Lengkap</label>
      <textarea name="alamat"><?= htmlspecialchars($data['alamat']) ?></textarea>

      <label>Dapil</label>
      <select name="dapil" id="dapil" required>
        <option value="<?= htmlspecialchars($data['dapil']) ?>"><?= htmlspecialchars($data['dapil']) ?></option>
        <option value="Kota Surabaya 1">Kota Surabaya 1</option>
        <option value="Kota Surabaya 2">Kota Surabaya 2</option>
        <option value="Kota Surabaya 3">Kota Surabaya 3</option>
        <option value="Kota Surabaya 4">Kota Surabaya 4</option>
        <option value="Kota Surabaya 5">Kota Surabaya 5</option>
      </select>

      <label>Kecamatan</label>
      <select name="kecamatan" id="kecamatan">
        <option value="<?= htmlspecialchars($data['kecamatan']) ?>"><?= htmlspecialchars($data['kecamatan']) ?></option>
      </select>

      <label>Jumlah Anggota Keluarga</label>
      <input type="number" name="jumlah_anggota" value="<?= htmlspecialchars($data['jumlah_anggota']) ?>">

      <label>Jumlah Orang Bekerja</label>
      <input type="number" name="jumlah_bekerja" value="<?= htmlspecialchars($data['jumlah_bekerja']) ?>">

      <label>Total Penghasilan Keluarga</label>
      <input type="number" name="total_penghasilan" value="<?= htmlspecialchars($data['total_penghasilan']) ?>">

      <label>Apakah mengenal Josiah Michael?</label>
      <select name="kenal">
        <option value="<?= htmlspecialchars($data['kenal']) ?>"><?= htmlspecialchars($data['kenal']) ?></option>
        <option value="Ya">Ya</option>
        <option value="Tidak">Tidak</option>
      </select>

      <label>Sumber Mengenal</label>
      <select name="sumber">
        <option value="<?= htmlspecialchars($data['sumber']) ?>"><?= htmlspecialchars($data['sumber']) ?></option>
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

  <script>
    // Dapil & Kecamatan dinamis (opsional seperti di tambahdata.php)
    const dapil = document.getElementById('dapil');
    const kecamatan = document.getElementById('kecamatan');
    const dataDapil = {
      "Kota Surabaya 1": ["Bubutan","Genteng","Gubeng","Krembangan","Simokerto","Tegalsari"],
      "Kota Surabaya 2": ["Kenjeran","Pabean Cantikan","Semampir","Tambaksari"],
      "Kota Surabaya 3": ["Bulak","Gunung Anyar","Mulyorejo","Rungkut","Sukolilo","Tenggilis Mejoyo","Wonocolo"],
      "Kota Surabaya 4": ["Gayungan","Jambangan","Sawahan","Sukomanunggal","Wonokromo"],
      "Kota Surabaya 5": ["Asemrowo","Benowo","Dukuhpakis","Karangpilang","Lakarsantri","Pakal","Sambikerep","Tandes","Wiyung"]
    };

    dapil.addEventListener('change', () => {
      kecamatan.innerHTML = '<option value="">-- Pilih Kecamatan --</option>';
      if (dataDapil[dapil.value]) {
        dataDapil[dapil.value].forEach(k => {
          const opt = document.createElement('option');
          opt.value = k;
          opt.textContent = k;
          kecamatan.appendChild(opt);
        });
      }
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
