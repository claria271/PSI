<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Entri Data Keluarga</title>
  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
      font-family: 'Poppins', sans-serif;
    }

    body {
      background: #f5f5f5;
    }

    header {
      background: #d9d9d9;
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding: 10px 20px;
    }

    header img {
      height: 40px;
    }

    header .user {
      font-weight: 500;
    }

    .back-btn {
      display: inline-block;
      margin: 20px 0 0 10%;
      font-size: 36px;
      font-weight: 900;
      color: #000;
      text-decoration: none;
      transition: 0.3s;
    }

    .back-btn:hover {
      color: #ff4b4b;
    }

    h1 {
      text-align: center;
      font-size: 26px;
      margin-top: 15px;
      margin-bottom: 5px;
    }

    .subtitle {
      text-align: center;
      font-size: 13px;
      color: #777;
      margin-bottom: 25px;
    }

    .container {
      width: 80%;
      max-width: 700px;
      background: #e9e9e9;
      margin: 0 auto 20px auto;
      border-radius: 10px;
      padding: 30px;
    }

    .section-title {
      text-align: center;
      font-weight: bold;
      margin-bottom: 20px;
      font-size: 18px;
    }

    form label {
      display: block;
      font-weight: 600;
      margin-bottom: 5px;
    }

    form input, 
    form textarea,
    form select {
      width: 100%;
      padding: 12px;
      border-radius: 8px;
      border: 1px solid #ccc;
      margin-bottom: 20px;
      background-color: #f9f9f9;
    }

    form textarea {
      height: 80px;
      resize: none;
    }

    small {
      color: red;
      display: block;
      margin-top: -15px;
      margin-bottom: 10px;
      font-size: 12px;
    }

    .summary-box {
      background: #cfcfcf;
      border-radius: 10px;
      padding: 15px;
    }

    .summary-item {
      display: flex;
      justify-content: space-between;
      background: #bfbfbf;
      padding: 8px 10px;
      border-radius: 8px;
      margin-bottom: 8px;
      font-size: 14px;
    }

    .radio-group {
      margin-bottom: 20px;
    }

    .radio-group label {
      display: flex;
      align-items: center;
      margin-bottom: 10px;
      font-weight: 500;
    }

    .radio-group input[type="radio"] {
      width: 20px;
      height: 20px;
      margin-right: 10px;
    }

    .btn-container {
      width: 80%;
      max-width: 700px;
      margin: 10px auto 40px auto;
      display: flex;
      justify-content: space-between;
      gap: 20px;
    }

    button {
      flex: 1;
      padding: 12px;
      background-color: #ff4b4b;
      color: white;
      border: none;
      border-radius: 8px;
      font-size: 16px;
      font-weight: bold;
      cursor: pointer;
      transition: 0.3s;
    }

    button:hover {
      background-color: #e03c3c;
    }

    .btn-reset {
      background-color: #a3a3a3;
    }

    .btn-reset:hover {
      background-color: #8b8b8b;
    }
  </style>
</head>
<body>

  <header>
    <img src="assets/image/logo.png" alt="Logo PSI">
    <div class="user">Sugeng ⚙️</div>
  </header>

  <a class="back-btn" href="javascript:history.back()">←</a>

  <h1>Entri Data Keluarga</h1>
  <p class="subtitle">Masukkan Data Keluarga Dengan Akurat</p>

  <!-- FORM UTAMA -->
  <form action="proses_keluarga.php" method="POST">
    <!-- FORM 1 -->
    <div class="container">
      <div class="section-title">Data Pribadi</div>
      <label>Nama Lengkap</label>
      <input type="text" name="nama_lengkap" placeholder="Mawar Lenjana" required>

      <label>NIK (Nomor Induk Kependudukan)</label>
      <input type="text" name="nik" placeholder="10050983728200938">
      <small>*opsional</small>

      <label>No WhatsApp</label>
      <input type="text" name="no_wa" placeholder="082264780939">

      <label>Alamat Lengkap</label>
      <textarea name="alamat" placeholder="Ketintang Madya No.12"></textarea>
    </div>

    <!-- FORM 2 -->
    <div class="container">
      <div class="section-title">Pendataan Daerah Pemilihan</div>
      <label>Pilih Daerah Pemilihan</label>
      <select name="dapil">
        <option>Dapil I</option>
        <option>Dapil II</option>
      </select>

      <label>Pilih Kecamatan</label>
      <select name="kecamatan">
        <option>Mulyorejo</option>
        <option>Gubeng</option>
      </select>
    </div>

    <!-- FORM 3 -->
    <div class="container">
      <div class="section-title">Data Ekonomi Keluarga</div>
      <label>Jumlah Anggota Keluarga</label>
      <input type="number" name="jumlah_anggota" placeholder="4">
      <small>*Jumlah anggota termasuk kepala keluarga</small>

      <label>Jumlah Orang yang Bekerja</label>
      <select name="jumlah_bekerja">
        <option>1</option><option>2</option><option>3</option>
      </select>

      <label>Total Jumlah Penghasilan Keluarga (Satu Keluarga)</label>
      <select name="total_penghasilan">
        <option>< Rp 1.000.000</option>
        <option>Rp 1.000.000 - Rp 3.000.000</option>
        <option>Rp 3.000.000 - Rp 5.000.000</option>
        <option>> Rp 5.000.000</option>
      </select>
    </div>

    <!-- FORM 4 -->
    <div class="container">
      <div class="section-title">Informasi</div>
      <p><b>Apakah Anda mengenal Ketua Fraksi PSI Surabaya Josiah Michael</b></p>
      <div class="radio-group">
        <label><input type="radio" name="kenal" value="Ya"> Ya</label>
        <label><input type="radio" name="kenal" value="Tidak Pernah"> Tidak Pernah</label>
      </div>

      <p><b>Jika Ya, dari mana Anda mengenal Ketua Fraksi PSI Surabaya Josiah Michael</b></p>
      <div class="radio-group">
        <label><input type="radio" name="sumber" value="Kegiatan PSI Surabaya"> Kegiatan PSI Surabaya</label>
        <label><input type="radio" name="sumber" value="Dari teman atau relasi"> Dari teman atau relasi</label>
        <label><input type="radio" name="sumber" value="Lainnya"> Lainnya</label>
      </div>
    </div>

    <div class="btn-container">
      <button type="submit">Simpan Data</button>
      <button type="reset" class="btn-reset">Kosongkan Form</button>
    </div>
  </form>

</body>
</html>
