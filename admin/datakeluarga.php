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

    body {
      background: #eaeaea;
      color: #333;
      display: flex;
      min-height: 100vh;
    }

    /* Sidebar */
    .sidebar {
      width: 230px;
      background: #d9d9d9;
      display: flex;
      flex-direction: column;
      align-items: center;
      padding: 20px 0;
      border-right: 2px solid #bfbfbf;
    }

    .sidebar img {
      width: 80px;
      margin-bottom: 20px;
    }

    .sidebar h3 {
      font-size: 15px;
      margin-bottom: 20px;
      font-weight: 600;
      color: #333;
      text-align: center;
    }

    .menu {
      width: 100%;
      display: flex;
      flex-direction: column;
    }

    .menu a {
      text-decoration: none;
      padding: 12px 20px;
      color: #333;
      transition: 0.3s;
    }

    .menu a.active, .menu a:hover {
      background: #bfbfbf;
      font-weight: 600;
    }

    /* Main */
    .main {
      flex: 1;
      background: #f9f9f9;
      padding: 30px 40px;
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
    table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 10px;
      border-radius: 10px;
      overflow: hidden;
    }

    th, td {
      padding: 10px;
      border: 1px solid #ccc;
      font-size: 14px;
      text-align: left;
    }

    th {
      background: #f2f2f2;
      font-weight: 600;
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

    .aksi .lihat {
      background: #4CAF50;
      color: #fff;
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
      background: #e0e0e0;
      padding: 10px;
      font-size: 13px;
      border-top: 2px solid #ccc;
      margin-top: 30px;
    }
  </style>
</head>
<body>

  <!-- Sidebar -->
  <div class="sidebar">
    <img src="../assets/logo_psi.png" alt="Logo PSI">
    <h3>[Nama Admin]</h3>
    <div class="menu">
      <a href="dashboardadmin.php">Dashboard</a>
      <a href="datakeluarga.php" class="active">Data Keluarga</a>
      <a href="hasilverifikasi.php">Hasil Verifikasi</a>
      <a href="laporan.php">Laporan</a>
      <a href="../logout.php">Logout</a>
    </div>
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

      <table>
        <thead>
          <tr>
            <th>Nama Lengkap</th>
            <th>NIK</th>
            <th>No WA</th>
            <th>Alamat Lengkap</th>
            <th>Dapil</th>
            <th>kecamatan</th>
            <th>jumlah anggota</th>
            <th>jumlah bekerja</th>
            <th>total penghasilan</th>
            <th>kenal</th>
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
                <button class="edit" onclick="window.location.href='tambahdata.php'">edit</button>
                <button class="hapus">Hapus</button>
              </td>
            </tr>
          <?php endwhile; ?>
        </tbody>
      </table>
    </div>

    <footer>
      Hak cipta © 2025 - Partai Solidaritas Indonesia
    </footer>
  </div>
</body>
</html>
