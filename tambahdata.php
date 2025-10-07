<?php
// tambahdata.php
?>
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
      color: #333;
    }

    /* Header */
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

    /* Tombol kembali */
    .back-btn {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      margin: 25px 0 0 10%;
      text-decoration: none;
      font-size: 18px;
      font-weight: 600;
      background: #ff4b4b;
      color: white;
      width: 45px;
      height: 45px;
      border-radius: 50%;
      box-shadow: 0 3px 6px rgba(0, 0, 0, 0.2);
      transition: 0.3s;
    }

    .back-btn:hover {
      background: #e03c3c;
      transform: scale(1.05);
    }

    /* Judul halaman */
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

    /* Container umum */
    .container {
      width: 80%;
      max-width: 700px;
      background: #e9e9e9;
      margin: 0 auto 25px auto;
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

    /* Summary Box */
    .summary-box {
      background: #cfcfcf;
      border-radius: 10px;
      padding: 15px;
      margin-top: 15px;
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

    /* Radio Group */
    .radio-group {
      margin: 15px 0;
    }

    .radio-group label {
      display: flex;
      align-items: center;
      gap: 10px;
      margin-bottom: 10px;
      font-weight: 500;
      cursor: pointer;
    }

    .radio-group input[type="radio"] {
      accent-color: #ff4b4b;
      width: 18px;
      height: 18px;
      cursor: pointer;
    }

    /* Tombol */
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

  <!-- FORM 1 -->
  <div class="container">
    <div class="section-title">Data Pribadi</div>
    <form>
      <label>Nama Lengkap</label>
      <input type="text" placeholder="Mawar Lenjana" disabled>

      <label>NIK (Nomor Induk Kependudukan)</label>
      <input type="text" placeholder="10050983728200938">
      <small>*opsional</small>

      <label>No WhatsApp</label>
      <input type="text" placeholder="082264780939">

      <label>Alamat Lengkap</label>
      <textarea placeholder="Ketintang Madya No.12"></textarea>
    </form>
  </div>

  <!-- FORM 2 -->
  <div class="container">
    <div class="section-title">Pendataan Daerah Pemilihan</div>
    <form id="form-dapil">
      <label>Pilih Daerah Pemilihan</label>
      <select id="dapil">
        <option value="">-- Pilih Dapil --</option>
        <option value="Dapil I">Dapil I</option>
        <option value="Dapil II">Dapil II</option>
      </select>

      <label>Pilih Kecamatan</label>
      <select id="kecamatan">
        <option value="">-- Pilih Kecamatan --</option>
        <option value="Mulyorejo">Mulyorejo</option>
        <option value="Gubeng">Gubeng</option>
      </select>

      <div class="summary-box" id="summary">
        <div class="summary-item"><span>Daerah Pemilihan</span><span>-</span></div>
        <div class="summary-item"><span>Kecamatan</span><span>-</span></div>
      </div>
    </form>
  </div>

  <!-- FORM 3 -->
  <div class="container">
    <div class="section-title">Data Ekonomi Keluarga</div>
    <form>
      <label>Jumlah Anggota Keluarga</label>
      <input type="number" placeholder="4">
      <small>*Jumlah anggota termasuk kepala keluarga</small>

      <label>Jumlah Orang yang Bekerja</label>
      <select>
        <option>1</option><option>2</option><option>3</option>
      </select>

      <label>Total Jumlah Penghasilan Keluarga (Satu Keluarga)</label>
      <select>
        <option>< Rp 1.000.000</option>
        <option>Rp 1.000.000 - Rp 3.000.000</option>
        <option>Rp 3.000.000 - Rp 5.000.000</option>
        <option>> Rp 5.000.000</option>
      </select>
      <small>*Jumlahkan semua penghasilan dari seluruh anggota keluarga yang bekerja</small>
    </form>
  </div>

  <!-- FORM 4 -->
  <div class="container">
    <div class="section-title">Informasi</div>
    <form>
      <p><b>Apakah Anda mengenal Ketua Fraksi PSI Surabaya Josiah Michael</b></p>
      <div class="radio-group">
        <label><input type="radio" name="kenal"> Ya</label>
        <label><input type="radio" name="kenal"> Tidak Pernah</label>
      </div>

      <p><b>Jika Ya, dari mana Anda mengenal Ketua Fraksi PSI Surabaya Josiah Michael</b></p>
      <div class="radio-group">
        <label><input type="radio" name="sumber"> Kegiatan PSI Surabaya</label>
        <label><input type="radio" name="sumber"> Dari teman atau relasi</label>
        <label><input type="radio" name="sumber"> Lainnya ___________________</label>
      </div>
    </form>
  </div>

  <!-- Tombol -->
  <div class="btn-container">
    <button type="submit">Simpan Data</button>
    <button type="reset" class="btn-reset">Kosongkan Form</button>
  </div>

  <script>
    // Update summary box sesuai pilihan
    const dapil = document.getElementById('dapil');
    const kecamatan = document.getElementById('kecamatan');
    const summary = document.getElementById('summary');

    function updateSummary() {
      const items = summary.querySelectorAll('.summary-item span:last-child');
      items[0].textContent = dapil.value || '-';
      items[1].textContent = kecamatan.value || '-';
    }

    dapil.addEventListener('change', updateSummary);
    kecamatan.addEventListener('change', updateSummary);
  </script>

</body>
</html>
