<?php
session_start();
include 'koneksi/config.php';

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $tanggal       = $_POST['tanggal'] ?? '';
    $nama_pengadu  = trim($_POST['nama_pengadu'] ?? '');
    $no_telp_wa    = trim($_POST['no_wa'] ?? ''); // input form tetap "no_wa", masuk DB ke no_telp_wa
    $domisili      = trim($_POST['domisili'] ?? '');
    $aduan         = trim($_POST['aduan'] ?? '');

    // Validasi minimal biar ga gagal insert
    if ($tanggal === '' || $nama_pengadu === '' || $no_telp_wa === '' || $domisili === '' || $aduan === '') {
        $error = 'Semua field wajib diisi (kecuali file pendukung).';
    }

    // === UPLOAD FILE ===
    $fileName = null;
    if ($error === '' && !empty($_FILES['file_pendukung']['name'])) {

        $folder = 'uploads/pengaduan/';
        if (!is_dir($folder)) {
            mkdir($folder, 0777, true);
        }

        $ext = strtolower(pathinfo($_FILES['file_pendukung']['name'], PATHINFO_EXTENSION));

        // Batasi ekstensi biar aman
        $allowed = ['jpg','jpeg','png','pdf','doc','docx'];
        if (!in_array($ext, $allowed, true)) {
            $error = 'Format file tidak didukung. (boleh: jpg, png, pdf, doc, docx)';
        } else {
            $fileName = 'aduan_' . time() . '.' . $ext;
            $target = $folder . $fileName;

            if (!move_uploaded_file($_FILES['file_pendukung']['tmp_name'], $target)) {
                $error = 'Gagal upload file pendukung';
            }
        }
    }

    // === SIMPAN KE DATABASE ===
    if ($error === '') {
        $stmt = $conn->prepare("
            INSERT INTO pengaduan
            (tanggal, nama_pengadu, no_telp_wa, domisili, aduan, file_pendukung, created_at)
            VALUES (?, ?, ?, ?, ?, ?, NOW())
        ");

        $stmt->bind_param(
            'ssssss',
            $tanggal,
            $nama_pengadu,
            $no_telp_wa,
            $domisili,
            $aduan,
            $fileName
        );

        if ($stmt->execute()) {
            // ✅ ALERT + REDIRECT KE INDEX
            echo "<script>
                alert('Pengaduan berhasil dikirim. Terima kasih atas laporannya.');
                window.location.href = 'index.php';
            </script>";
            exit;
        } else {
            $error = 'Gagal menyimpan pengaduan';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Form Aduan - PSI</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

<style>
* { box-sizing: border-box; font-family: 'Poppins', sans-serif; }

body {
  margin: 0;
  background: #f2f2f2;
}

header {
  background: linear-gradient(to right, #bfbfbf, #000);
  padding: 15px 30px;
  color: #fff;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

header img { height: 40px; }

header nav a {
  color: #fff;
  margin-left: 20px;
  text-decoration: none;
  font-weight: 500;
}

.container {
  max-width: 600px;
  margin: 40px auto;
  background: linear-gradient(to bottom, #bfbfbf, #7a7a7a);
  padding: 30px;
  border-radius: 10px;
}

.form-title {
  background: #fff;
  padding: 15px;
  border-radius: 8px;
  margin-bottom: 20px;
}

.form-title h3 {
  margin: 0;
  text-align: center;
}

.form-title p {
  font-size: 12px;
  color: #777;
  text-align: center;
}

label {
  display: block;
  font-weight: 600;
  margin-top: 15px;
  margin-bottom: 6px;
}

input, textarea {
  width: 100%;
  padding: 10px 12px;
  border-radius: 8px;
  border: none;
}

textarea {
  resize: vertical;
  min-height: 80px;
}

button {
  margin-top: 25px;
  width: 100%;
  padding: 12px;
  border-radius: 10px;
  border: 2px solid #fff;
  background: transparent;
  color: #fff;
  font-weight: 600;
  cursor: pointer;
}

button:hover {
  background: #ff4b4b;
  border-color: #ff4b4b;
}

.alert-success {
  background: #d1fae5;
  color: #065f46;
  padding: 10px;
  border-radius: 6px;
  margin-bottom: 15px;
}

.alert-error {
  background: #fee2e2;
  color: #991b1b;
  padding: 10px;
  border-radius: 6px;
  margin-bottom: 15px;
}
</style>
</head>
<body>

<header>
  <img src="assets/image/logo.png" alt="PSI">
  <nav>
    <a href="#">Beranda</a>
    <a href="#">Profil</a>
  </nav>
</header>

<div class="container">

  <div class="form-title">
    <h3>FORM ADUAN</h3>
    <p>
      Kami menghargai setiap laporan yang Anda kirimkan. Mohon isi data
      dengan benar untuk mempermudah proses penanganan.
    </p>
  </div>

  <?php if ($success): ?>
    <div class="alert-success"><?php echo $success; ?></div>
  <?php elseif ($error): ?>
    <div class="alert-error"><?php echo $error; ?></div>
  <?php endif; ?>

  <form method="POST" enctype="multipart/form-data">
    <label>Tanggal</label>
    <input type="date" name="tanggal" required>

    <label>Nama Pengadu</label>
    <input type="text" name="nama_pengadu" required>

    <label>No. Tlp / WA</label>
    <input type="text" name="no_wa" required>

    <label>Domisili</label>
    <input type="text" name="domisili" required>

    <label>Aduan</label>
    <textarea name="aduan" required></textarea>

    <label>Upload file pendukung</label>
    <input type="file" name="file_pendukung">

    <button type="submit">Kirim</button>
  </form>

</div>

</body>
</html>
