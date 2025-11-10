<?php
session_start();
include '../koneksi/config.php';

// Pastikan admin login
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../user/login.php");
    exit();
}

// Ambil data keluarga
$query = "SELECT * FROM keluarga ORDER BY created_at DESC";
$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Data Keluarga - PSI</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
      font-family: 'Poppins', sans-serif;
    }
     /* HEADER */
    header {
      background: linear-gradient(to right, #ffffff, #000000);
      padding: 10px 30px;
      display: flex;
      align-items: center;
      gap: 15px;
    }

    header img {
      height: 45px;
    }

    header h1 {
      color: #fff;
      font-size: 18px;
      font-weight: 600;
    }

    body {
      background: #eaeaea;
      color: #333;
      display: flex;
      min-height: 100vh;
      overflow: hidden; /* layar tetap */
    }

     /* SIDEBAR */
    .sidebar {
      width: 230px;
      background: linear-gradient(to bottom, #d9d9d9, #8c8c8c);
      padding: 25px 15px;
      display: flex;
      flex-direction: column;
      align-items: center;
    }

    .sidebar .admin-photo {
      width: 70px;
      height: 70px;
      background: #bbb;
      border-radius: 50%;
      margin-bottom: 10px;
    }

    .sidebar .admin-name {
      background: #cfcfcf;
      padding: 8px;
      border-radius: 8px;
      width: 100%;
      text-align: center;
      font-weight: bold;
      margin-bottom: 25px;
    }

    .sidebar nav a {
      display: block;
      width: 100%;
      text-decoration: none;
      background: #b5b5b5;
      padding: 10px;
      border-radius: 6px;
      color: #000;
      margin: 5px 0;
      font-weight: 500;
      text-align: center;
      transition: 0.3s;
    }

    .sidebar nav a:hover,
    .sidebar nav a.active {
      background: #ff4b4b;
      color: #fff;
    }
     /* HEADER */
    header {
      background: linear-gradient(to right, #ffffff, #000000);
      padding: 10px 30px;
      display: flex;
      align-items: center;
      gap: 15px;
    }

    header img {
      height: 45px;
    }

    header h1 {
      color: #fff;
      font-size: 18px;
      font-weight: 600;
    }

    /* Main */
    .main {
      margin-left: 230px; /* beri ruang untuk sidebar */
      flex: 1;
      background: #f9f9f9;
      padding: 30px 40px;
      height: 100vh;
      overflow-y: auto; /* biar konten utama bisa discroll vertikal */
    }

    .main h2 {
      font-size: 22px;
      font-weight: 700;
      margin-bottom: 10px;
    }

    .main h3 {
      font-size: 18px;
      font-weight: 600;
      margin-bottom: 20px;
    }

    /* Card */
    .card {
      background: #fff;
      border-radius: 12px;
      box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
      padding: 20px;
      margin-top: 20px;
    }

    .card-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 15px;
      flex-wrap: wrap;
      gap: 10px;
    }

    .btn-tambah {
      background: #d32f2f;
      color: #fff;
      border: none;
      border-radius: 6px;
      padding: 8px 14px;
      cursor: pointer;
      font-weight: 600;
      transition: 0.3s;
    }

    .btn-tambah:hover {
      background: #b71c1c;
    }

    .filters {
      display: flex;
      align-items: center;
      gap: 10px;
      flex-wrap: wrap;
    }

    .filters input,
    .filters select {
      padding: 7px 10px;
      border: 1px solid #ccc;
      border-radius: 6px;
      font-size: 13px;
      background: #f5f5f5;
    }

    /* Table */
    .table-container {
      width: 100%;
      overflow-x: auto; /* ini bikin slider horizontal */
    }

    table {
      width: 100%;
      border-collapse: collapse;
      min-width: 1200px; /* biar bisa discroll kalau kolom banyak */
      border-radius: 10px;
      overflow: hidden;
    }

    th, td {
      padding: 10px;
      border: 1px solid #ccc;
      font-size: 14px;
      text-align: left;
      white-space: nowrap; /* biar teks nggak turun ke bawah */
    }

    th {
      background: #f2f2f2;
      font-weight: 600;
      position: sticky;
      top: 0; /* header tetap di atas */
      z-index: 2;
    }

    tr:nth-child(even) {
      background: #fafafa;
    }

    .dibawah {
      color: red;
      font-weight: 600;
    }

    .diatas {
      color: #333;
      font-weight: 600;
    }

    .aksi button {
      padding: 6px 10px;
      border: none;
      border-radius: 5px;
      cursor: pointer;
      margin-right: 4px;
      font-size: 13px;
      transition: 0.3s;
    }

    .aksi .edit {
      background: #2196F3;
      color: #fff;
    }

    .aksi .hapus {
      background: #f44336;
      color: #fff;
    }

    .aksi button:hover {
      opacity: 0.8;
    }

    /* Footer */
     footer {
      text-align: center;
      padding: 15px 5%;
      background: linear-gradient(to right, #ffffff, #000000);
      color: #fff;
      font-size: 14px;
      border-top: 1px solid #ccc;
    }

    footer img {
      height: 20px;
      vertical-align: middle;
      margin: 0 5px;
      filter: brightness(0) invert(1);
    }
  </style>
</head>
<body>
    <header>
    <img src="../assets/image/logo.png" alt="Logo PSI">
    <h1>Dashboard Admin</h1>
  </header>
  <!-- Sidebar -->
  <div class="sidebar">
    <aside class="sidebar">
      <div class="admin-photo"></div>
      <div class="admin-name"><?= htmlspecialchars($_SESSION['nama_lengkap']); ?></div>
      <nav>
        <a href="#" class="active">Dashboard</a>
        <a href="datakeluarga.php">Data Keluarga</a>
        <a href="#">Hasil Verifikasi</a>
        <a href="#">Laporan</a>
        <a href="logout.php">Logout</a>
      </nav>
    </aside>
  </div>

  <!-- Main Content -->
  <div class="main">
    <h2>Sistem Entri Data Keluarga</h2>
    <h3>Data Keluarga</h3>

    <div class="card">
      <div class="card-header">
        <button class="btn-tambah" onclick="window.location.href='tambahdata.php'">+ Tambah Data</button>
        <div class="filters">
          <input type="text" placeholder="Cari Pengguna, NIK, No HP">
          <select>
            <option>Surabaya 1</option>
            <option>Surabaya 2</option>
          </select>
          <select>
            <option>Semua Status</option>
            <option>Dibawah UMR</option>
            <option>Diatas UMR</option>
          </select>
          <select>
            <option>Semua</option>
            <option>Ya</option>
            <option>Tidak</option>
          </select>
        </div>
      </div>
        <div class="card">
          <div class="table-container">
            <table>
              <thead>
                <tr>
                  <th>Nama Lengkap</th>
                  <th>NIK</th>
                  <th>No WA</th>
                  <th>Alamat Lengkap</th>
                  <th>Dapil</th>
                  <th>Kecamatan</th>
                  <th>Jumlah Anggota</th>
                  <th>Jumlah Bekerja</th>
                  <th>Total Penghasilan</th>
                  <th>Kenal</th>
                  <th>Sumber</th>
                  <th>Kategori</th>
                  <th>Created At</th>
                  <th>Updated At</th>
                  <th>Aksi</th>
                </tr>
              </thead>
              <tbody>
                <?php while ($row = mysqli_fetch_assoc($result)): ?>
                  <?php
                    $umr = 4000000;
                    $kategori = $row['total_penghasilan'] < $umr
                      ? "<span class='dibawah'>Dibawah UMR</span>"
                      : "<span class='diatas'>Diatas UMR</span>";
                  ?>
                  <tr>
                    <td><?= htmlspecialchars($row['nama_lengkap']); ?></td>
                    <td><?= htmlspecialchars($row['nik']); ?></td>
                    <td><?= htmlspecialchars($row['no_wa']); ?></td>
                    <td><?= htmlspecialchars($row['alamat']); ?></td>
                    <td><?= htmlspecialchars($row['dapil']); ?></td>
                    <td><?= htmlspecialchars($row['kecamatan']); ?></td>
                    <td><?= htmlspecialchars($row['jumlah_anggota']); ?></td>
                    <td><?= htmlspecialchars($row['jumlah_bekerja']); ?></td>
                    <td><?= htmlspecialchars($row['total_penghasilan']); ?></td>
                    <td><?= htmlspecialchars($row['kenal']); ?></td>
                    <td><?= htmlspecialchars($row['sumber']); ?></td>
                    <td><?= $kategori ?></td>
                    <td><?= htmlspecialchars($row['created_at']); ?></td>
                    <td><?= htmlspecialchars($row['updated_at']); ?></td>
                    <td class="aksi">
                      <button class="edit" onclick="window.location.href='editdata.php?id=<?= $row['id'] ?>'">Edit</button>
                      <button class="hapus">Hapus</button>
                    </td>
                  </tr>
                <?php endwhile; ?>
              </tbody>
            </table>
          </div>
        </div>
    </div>

    <footer>
    <img src="../assets/image/logodprd.png" alt="DPRD">
    <img src="../assets/image/psiputih.png" alt="PSI">
    Hak cipta © 2025 - Partai Solidaritas Indonesia
  </footer>
  </div>

</body>
</html>
