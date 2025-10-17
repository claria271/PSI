<?php
// tambahdata.php
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Entri Data Keluarga</title>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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
      background: linear-gradient(to right, #ffffff, #000000);
      border-bottom: 1px solid #ddd;
      padding: 12px 40px;
      display: flex;
      align-items: center;
      justify-content: space-between;
      position: sticky;
      top: 0;
      z-index: 100;
    }

    header .logo {
      display: flex;
      align-items: center;
      gap: 10px;
    }

    header img {
      height: 40px;
    }

    /* Navigasi */
    nav a {
      margin: 0 15px;
      text-decoration: none;
      font-weight: bold;
      color: #fff;
      transition: 0.3s;
    }

    nav a:hover {
      color: #ff4b4b;
    }

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
      background: linear-gradient(to bottom, #c0c0c0ff, #6d6d6dff);
      margin: 0 auto 25px auto;
      border-radius: 10px;
      padding: 30px;
      color: #fff;
      box-shadow: 0 3px 8px rgba(0, 0, 0, 0.2);
    }

    .section-title {
      text-align: center;
      font-weight: bold;
      margin-bottom: 20px;
      font-size: 18px;
      color: #fff;
    }

    form label {
      display: block;
      font-weight: 600;
      margin-bottom: 5px;
      color: #fff;
    }

    form input, 
    form textarea,
    form select {
      width: 100%;
      padding: 12px;
      border-radius: 8px;
      border: 1px solid #ccc;
      margin-bottom: 20px;
      background-color: rgba(255, 255, 255, 0.9);
      color: #000;
      font-weight: 500;
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

    footer img:first-child {
      height: 20px;
    }
  </style>
</head>
<body>

  <header>
    <div class="logo">
      <img src="../assets/image/logo.png" alt="PSI">
    </div>
    <nav>
      <a href="tambahdata.php">Tambah Data</a>
      <a href="logout.php">Logout</a>
    </nav>
  </header>

  <h1>Entri Data Keluarga</h1>
  <p class="subtitle">Masukkan Data Keluarga Dengan Akurat</p>

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
      <select name="dapil" id="dapil" required>
        <option value="">-- Pilih Dapil --</option>
        <option value="Kota Surabaya 1">Kota Surabaya 1</option>
        <option value="Kota Surabaya 2">Kota Surabaya 2</option>
        <option value="Kota Surabaya 3">Kota Surabaya 3</option>
        <option value="Kota Surabaya 4">Kota Surabaya 4</option>
        <option value="Kota Surabaya 5">Kota Surabaya 5</option>
      </select>

      <label>Pilih Kecamatan</label>
      <select name="kecamatan" id="kecamatan" required>
        <option value="">-- Pilih Kecamatan --</option>
      </select>

      <div class="summary-box" id="summary">
        <div class="summary-item"><span>Daerah Pemilihan</span><span>-</span></div>
        <div class="summary-item"><span>Kecamatan</span><span>-</span></div>
      </div>
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
      <p><b>Apakah Anda mengenal Ketua Fraksi PSI Surabaya Josiah Michael?</b></p>
      <div class="radio-group">
        <label><input type="radio" name="kenal" value="Ya"> Ya</label>
        <label><input type="radio" name="kenal" value="Tidak Pernah"> Tidak Pernah</label>
      </div>

      <p><b>Jika Ya, dari mana Anda mengenal Ketua Fraksi PSI Surabaya Josiah Michael?</b></p>
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

  <script>
    const dapil = document.getElementById('dapil');
    const kecamatan = document.getElementById('kecamatan');
    const summary = document.getElementById('summary');

    // Pembagian 5 Dapil Kota Surabaya
    const dataDapil = {
      "Kota Surabaya 1": ["Bubutan", "Genteng", "Gubeng", "Krembangan", "Simokerto", "Tegalsari"],
      "Kota Surabaya 2": ["Kenjeran", "Pabean Cantikan", "Semampir", "Tambaksari"],
      "Kota Surabaya 3": ["Bulak", "Gunung Anyar", "Mulyorejo", "Rungkut", "Sukolilo", "Tenggilis Mejoyo", "Wonocolo"],
      "Kota Surabaya 4": ["Gayungan", "Jambangan", "Sawahan", "Sukomanunggal", "Wonokromo"],
      "Kota Surabaya 5": ["Asemrowo", "Benowo", "Dukuhpakis", "Karangpilang", "Lakarsantri", "Pakal", "Sambikerep", "Tandes", "Wiyung"]
    };

    dapil.addEventListener('change', () => {
      const selectedDapil = dapil.value;
      kecamatan.innerHTML = '<option value="">-- Pilih Kecamatan --</option>';

      if (dataDapil[selectedDapil]) {
        dataDapil[selectedDapil].forEach(kec => {
          const option = document.createElement('option');
          option.value = kec;
          option.textContent = kec;
          kecamatan.appendChild(option);
        });
      }
      updateSummary();
    });

    function updateSummary() {
      const items = summary.querySelectorAll('.summary-item span:last-child');
      items[0].textContent = dapil.value || '-';
      items[1].textContent = kecamatan.value || '-';
    }

    kecamatan.addEventListener('change', updateSummary);

    // SweetAlert2 setelah submit
    const urlParams = new URLSearchParams(window.location.search);
    const status = urlParams.get('status');

    if (status === 'success') {
      Swal.fire({
        title: 'Berhasil!',
        text: 'Data keluarga berhasil disimpan 🎉',
        icon: 'success',
        showConfirmButton: false,
        timer: 2000
      }).then(() => {
        window.location.href = 'dashboard.php';
      });
    } else if (status === 'failed') {
      Swal.fire({
        title: 'Gagal!',
        text: 'Terjadi kesalahan saat menyimpan data 😥',
        icon: 'error',
        confirmButtonText: 'Coba Lagi'
      });
    }
  </script>

  <footer>
    <img src="../assets/image/logodprd.png" alt="dprd Logo">
    <img src="../assets/image/psiputih.png" alt="PSI Logo">
    Hak cipta © 2025 - Partai Solidaritas Indonesia
  </footer>
</body>
</html>
