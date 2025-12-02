<?php
session_start();

/*
 * Kalau user baru saja selesai register, biasanya sudah ada alamat_email di session,
 * tapi role belum diset. Kita set default role=‘user’ supaya guard tidak melempar ke login.
 */
if (!isset($_SESSION['role']) && isset($_SESSION['alamat_email'])) {
    $_SESSION['role'] = 'user';
}

/* Guard akses: pastikan role user */
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'user') {
    // Jika file login kamu ada di folder /PSI/user/login.php (sesuai screenshot), biarkan seperti ini:
    //header("Location: login.php");

    // Kalau ternyata login ada di root (/PSI/login.php), pakai:
     header("Location: ../login.php");
    exit();
}
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
      overflow-x: hidden;
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

    h1 {
      text-align: center;
      font-size: 26px;
      margin-top: 15px;
      margin-bottom: 5px;
    }
    p {
      text-align: center;
      font-size: 16px;
      margin-top: 5px;
      margin-bottom: 5px;
    }

    .container {
      width: 80%;
      max-width: 700px;
      background: linear-gradient(to bottom, #c6c6c6ff, #757575ff);
      margin: 0 auto 25px auto;
      border-radius: 10px;
      padding: 30px;
      color: #fff;
      box-shadow: 0 3px 8px rgba(0, 0, 0, 0.2);
      flex-shrink: 0;
      transition: transform 0.6s ease, opacity 0.4s ease;
    }


    .container1 {
      width: 80%;
      max-width: 700px;
      background-color: rgba(255, 255, 255, 0.9);
      margin: 0 auto 25px auto;
      border-radius: 10px;
      padding: 30px;
      color: #000000ff;
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

     /* === Footer === */
    footer {
      margin-top: 60px;
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

    /* 🎞️ Efek Slide Seperti PowerPoint */
    .slide {
      display: none;
      opacity: 0;
      transform: translateX(100%);
      transition: all 0.6s ease-in-out;
    }

    .slide.active {
      display: block;
      opacity: 1;
      transform: translateX(0);
    }

    .slide.exit-left {
      transform: translateX(-100%);
      opacity: 0;
    }

    .slide.exit-right {
      transform: translateX(100%);
      opacity: 0;
    }

    .nav-slide {
      display: flex;
      justify-content: center;
      gap: 15px;
      margin: 20px;
    }

    .nav-slide button {
      padding: 10px 20px;
      background: #ff4b4b;
      color: #fff;
      border: none;
      border-radius: 8px;
      cursor: pointer;
      font-weight: 600;
      transition: 0.3s;
    }

    .nav-slide button:hover {
      background: #e03c3c;
      transform: scale(1.05);
    }

  </style>
</head>
<body>

  <header>
    <div class="logo">
      <img src="../assets/image/logo.png" alt="PSI">
    </div>
    <nav>
      <a href="logout.php">Logout</a>
    </nav>
  </header>

  <h1>Silahkan lengkapi data keluarga Anda untuk lanjut ke halaman utama</h1>
  
  <div class="container1">
    <h1>ENTRI DATA KELUARGA</h1>
    <p>Silakan lengkapi data keluarga Anda sesuai dengan form di bawah ini.</p>
  </div>

  <form id="multiForm" action="proses_keluarga.php" method="POST">
    <!-- Slide 1 -->
    <div class="container slide active">
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

    <!-- Slide 2 -->
    <div class="container slide">
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

    <!-- Slide 3 -->
    <div class="container slide">
      <div class="section-title">Data Ekonomi Keluarga</div>
      <label>Jumlah Anggota Keluarga</label>
      <input type="number" name="jumlah_anggota" placeholder="4">
      <small>*Jumlah anggota termasuk kepala keluarga</small>
      <label>Jumlah Orang yang Bekerja</label>
      <select name="jumlah_bekerja">
        <option>1</option><option>2</option><option>3</option><option>4</option><option>5</option><option>>5</option>
      </select>
      <label>Total Jumlah Penghasilan Keluarga (Satu Keluarga)</label>
      <input 
        type="number" 
        name="total_penghasilan" 
        placeholder="1000000" 
        min="1"
        required
        oninvalid="this.setCustomValidity('Penghasilan harus lebih dari 0')"
        oninput="this.setCustomValidity('')"
      >
      <small>*Minimal Rp 1 (wajib diisi)</small>
    </div>

    <!-- Slide 4 -->
    <div class="container slide">
        <div class="section-title">Informasi</div>
          <p><b>Apakah Anda mengenal Ketua Fraksi PSI Surabaya Josiah Michael?</b></p>
          <div class="radio-group">
            <label><input type="radio" name="kenal" value="Ya"> Ya</label>
            <label><input type="radio" name="kenal" value="Tidak"> Tidak</label>
          </div>
          <p><b>Jika Ya, dari mana Anda mengenal Ketua Fraksi PSI Surabaya Josiah Michael?</b></p>
          <div class="radio-group">
            <label><input type="radio" name="sumber" value="Kegiatan PSI Surabaya"> Kegiatan PSI Surabaya</label>
            <label><input type="radio" name="sumber" value="Dari teman atau relasi"> Dari teman atau relasi</label>
            <label><input type="radio" name="sumber" value="Lainnya"> Lainnya</label>
          </div>

          <div class="btn-container">
            <button type="submit">Simpan Data</button>
            <button type="reset" class="btn-reset">Kosongkan Form</button>
          </div>
        </div>

    <!-- Tombol Navigasi -->
    <div class="nav-slide">
      <button type="button" id="prevBtn">← Sebelumnya</button>
      <button type="button" id="nextBtn">Selanjutnya →</button>
    </div>
  </form>

  <script>
    // === Dropdown dinamis ===
    const dapil = document.getElementById('dapil');
    const kecamatan = document.getElementById('kecamatan');
    const summary = document.getElementById('summary');

    const dataDapil = {
      "Kota Surabaya 1": ["Bubutan","Genteng","Gubeng","Krembangan","Simokerto","Tegalsari"],
      "Kota Surabaya 2": ["Kenjeran","Pabean Cantikan","Semampir","Tambaksari"],
      "Kota Surabaya 3": ["Bulak","Gunung Anyar","Mulyorejo","Rungkut","Sukolilo","Tenggilis Mejoyo","Wonocolo"],
      "Kota Surabaya 4": ["Gayungan","Jambangan","Sawahan","Sukomanunggal","Wonokromo"],
      "Kota Surabaya 5": ["Asemrowo","Benowo","Dukuhpakis","Karangpilang","Lakarsantri","Pakal","Sambikerep","Tandes","Wiyung"]
    };

    dapil.addEventListener('change', () => {
      const selected = dapil.value;
      kecamatan.innerHTML = '<option value="">-- Pilih Kecamatan --</option>';
      if (dataDapil[selected]) {
        dataDapil[selected].forEach(k => {
          const opt = document.createElement('option');
          opt.value = k;
          opt.textContent = k;
          kecamatan.appendChild(opt);
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

    // === Slide PPT Logic ===
    const slides = document.querySelectorAll('.slide');
    const nextBtn = document.getElementById('nextBtn');
    const prevBtn = document.getElementById('prevBtn');
    let index = 0;

    function showSlide(newIndex, direction = 'right') {
      slides[index].classList.remove('active');
      slides[index].classList.add(direction === 'right' ? 'exit-left' : 'exit-right');
      setTimeout(() => slides[index].classList.remove('exit-left', 'exit-right'), 600);
      index = newIndex;
      slides[index].classList.add('active');
      prevBtn.style.display = index === 0 ? 'none' : 'inline-block';
      nextBtn.textContent = index === slides.length - 1 ? 'Selesai' : 'Selanjutnya →';
    }

    nextBtn.addEventListener('click', () => {
      if (index < slides.length - 1) {
        showSlide(index + 1, 'right');
      } else {
        document.getElementById('multiForm').submit();
      }
    });

    prevBtn.addEventListener('click', () => {
      if (index > 0) {
        showSlide(index - 1, 'left');
      }
    });

    prevBtn.style.display = 'none';

    // Tambahkan di bagian SweetAlert handling (sekitar baris 273)

// === SweetAlert dengan Error Spesifik ===
const params = new URLSearchParams(window.location.search);
if (params.get('status') === 'success') {
  Swal.fire({
    title: 'Berhasil!',
    text: 'Data keluarga berhasil disimpan 🎉',
    icon: 'success',
    timer: 2000,
    showConfirmButton: false
  }).then(() => window.location.href = 'dashboard.php');
} 
else if (params.get('status') === 'failed') {
  const errorType = params.get('error');
  let errorMsg = 'Terjadi kesalahan saat menyimpan data 😥';
  
  // Pesan error spesifik
  if (errorType === 'penghasilan_invalid') {
    errorMsg = 'Total penghasilan harus lebih dari 0!';
  } else if (errorType === 'penghasilan_required') {
    errorMsg = 'Total penghasilan wajib diisi!';
  } else if (errorType === 'nik') {
    errorMsg = 'Format NIK tidak valid!';
  } else if (errorType === 'no_wa') {
    errorMsg = 'Format nomor WhatsApp tidak valid!';
  }
  
  Swal.fire({
    title: 'Gagal!',
    text: errorMsg,
    icon: 'error',
    confirmButtonColor: '#ff4b4b'
  });
}
  
  // Cek jika penghasilan 0 atau negatif
  //if (penghasilanValue <= 0 || isNaN(penghasilanValue)) {
   // e.preventDefault(); // Batalkan submit
    
  //  Swal.fire({
  //    title: 'Perhatian!',
  //    text: 'Total penghasilan harus lebih dari 0',
  //    icon: 'warning',
  //    confirmButtonColor: '#ff4b4b'
  //  });
    
    // Fokus ke input penghasilan
  //  penghasilanInput.focus();
    
    // Scroll ke slide penghasilan (slide ke-3, index 2)
   // showSlide(2, 'left');
    
  //  return false;
 // }
//});

// Tambahkan juga validasi real-time saat user mengetik
document.querySelector('input[name="total_penghasilan"]').addEventListener('input', function() {
  const value = parseFloat(this.value);
  
  if (value < 0) {
    this.value = 0;
  }
  
  // Tampilkan peringatan jika 0
  if (value === 0) {
    this.style.borderColor = '#ff4b4b';
  } else {
    this.style.borderColor = '#ccc';
  }
});
  </script>

  <footer>
    <img src="../assets/image/logodprd.png" alt="dprd Logo">
    <img src="../assets/image/psiputih.png" alt="PSI Logo">
    Hak cipta © 2025 - Partai Solidaritas Indonesia
  </footer>
</body>
</html>
