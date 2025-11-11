<?php
session_start();
include '../koneksi/config.php';

// Pastikan admin login
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../user/login.php");
    exit();
}

// Set nilai UMR (bisa kamu pindah ke config kalau mau)
$umr = 4000000;

// Ambil filter dari GET
$search      = isset($_GET['search']) ? trim($_GET['search']) : '';
$status_umr  = isset($_GET['status_umr']) ? $_GET['status_umr'] : ''; // '', Dibawah, Diatas
$dapil       = isset($_GET['dapil']) ? $_GET['dapil'] : '';
$kenal       = isset($_GET['kenal']) ? $_GET['kenal'] : '';

// Bangun query dengan kondisi dinamis
$conditions = [];

if ($search !== '') {
    $safe = mysqli_real_escape_string($conn, $search);
    $conditions[] = "(nama_lengkap LIKE '%$safe%' 
                  OR nik LIKE '%$safe%' 
                  OR no_wa LIKE '%$safe%')";
}

if ($dapil !== '') {
    $safeDapil = mysqli_real_escape_string($conn, $dapil);
    $conditions[] = "dapil = '$safeDapil'";
}

if ($kenal === 'Ya' || $kenal === 'Tidak') {
    $safeKenal = mysqli_real_escape_string($conn, $kenal);
    $conditions[] = "kenal = '$safeKenal'";
}

// FILTER UMR
if ($status_umr === 'Dibawah') {
    $conditions[] = "total_penghasilan < $umr";
} elseif ($status_umr === 'Diatas') {
    $conditions[] = "total_penghasilan >= $umr";
}

$query = "SELECT * FROM keluarga";

if (!empty($conditions)) {
    $query .= " WHERE " . implode(" AND ", $conditions);
}

$query .= " ORDER BY created_at DESC";

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
      flex-direction: column;
      height: 100vh;
      overflow: hidden;
    }

    /* HEADER */
    header {
      background: linear-gradient(to right, #ffffff, #000000);
      padding: 10px 30px;
      display: flex;
      align-items: center;
      gap: 15px;
      height: 60px;
      position: fixed;
      top: 0;
      left: 0;
      right: 0;
      z-index: 10;
    }

    header img {
      height: 45px;
    }

    header h1 {
      color: #fff;
      font-size: 18px;
      font-weight: 600;
    }

    /* LAYOUT WRAPPER */
    .wrapper {
      display: flex;
      flex: 1;
      margin-top: 60px;
      height: calc(100vh - 60px);
      overflow: hidden;
    }

    /* SIDEBAR */
    .sidebar {
      width: 230px;
      background: linear-gradient(to bottom, #d9d9d9, #8c8c8c);
      padding: 25px 15px;
      display: flex;
      flex-direction: column;
      align-items: center;
      position: fixed;
      top: 60px;
      bottom: 0;
      left: 0;
      overflow-y: auto;
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

    /* MAIN */
    .main {
      flex: 1;
      margin-left: 230px;
      padding: 30px 40px;
      height: calc(100vh - 60px);
      overflow-y: auto;
      background: #f9f9f9;
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

    .table-container {
      width: 100%;
      overflow-x: auto;
    }

    table {
      width: 100%;
      border-collapse: collapse;
      min-width: 1200px;
      border-radius: 10px;
      overflow: hidden;
    }

    th, td {
      padding: 10px;
      border: 1px solid #ccc;
      font-size: 14px;
      text-align: left;
      white-space: nowrap;
    }

    th {
      background: #f2f2f2;
      font-weight: 600;
      position: sticky;
      top: 0;
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

    footer {
      text-align: center;
      padding: 15px 5%;
      background: linear-gradient(to right, #ffffff, #000000);
      color: #fff;
      font-size: 14px;
      border-top: 1px solid #ccc;
      margin-top: 30px;
      border-radius: 8px;
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

  <!-- HEADER -->
  <header>
    <img src="../assets/image/logo.png" alt="Logo PSI">
    <h1>Dashboard Admin</h1>
  </header>

  <div class="wrapper">
    <!-- SIDEBAR -->
    <aside class="sidebar">
      <div class="admin-photo"></div>
      <div class="admin-name"><?= htmlspecialchars($_SESSION['nama_lengkap']); ?></div>
      <nav>
        <a href="dashboardadmin.php">Dashboard</a>
        <a href="datakeluarga.php" class="active">Data Keluarga</a>
        <a href="#">Hasil Verifikasi</a>
        <a href="#">Laporan</a>
        <a href="logout.php">Logout</a>
      </nav>
    </aside>

    <!-- MAIN -->
    <main class="main">
      <h2>Sistem Entri Data Keluarga</h2>
      <h3>Data Keluarga</h3>

      <div class="card">
        <div class="card-header">
          <button class="btn-tambah" onclick="window.location.href='tambahdata.php'">+ Tambah Data</button>

          <!-- FORM FILTER -->
          <form method="GET" class="filters">
            <input
              type="text"
              name="search"
              placeholder="Cari Pengguna, NIK, No HP"
              value="<?= htmlspecialchars($search); ?>"
            >

            <!-- Contoh Dapil (opsional, sesuaikan dengan datamu) -->
            <select name="dapil" onchange="this.form.submit()">
              <option value="">Semua Dapil</option>
              <option value="Kota Surabaya 1" <?= $dapil === 'Kota Surabaya 1' ? 'selected' : '' ?>>Kota Surabaya 1</option>
              <option value="Kota Surabaya 2" <?= $dapil === 'Surabaya 2' ? 'selected' : '' ?>>Kota Surabaya 2</option>
            </select>

            <!-- FILTER STATUS UMR -->
            <select name="status_umr" onchange="this.form.submit()">
              <option value="" <?= $status_umr === '' ? 'selected' : '' ?>>Semua Status</option>
              <option value="Dibawah" <?= $status_umr === 'Dibawah' ? 'selected' : '' ?>>Dibawah UMR</option>
              <option value="Diatas" <?= $status_umr === 'Diatas' ? 'selected' : '' ?>>Diatas UMR</option>
            </select>

            <!-- FILTER KENAL -->
            <select name="kenal" onchange="this.form.submit()">
              <option value="" <?= $kenal === '' ? 'selected' : '' ?>>Kenal Semua</option>
              <option value="Ya" <?= $kenal === 'Ya' ? 'selected' : '' ?>>Ya</option>
              <option value="Tidak" <?= $kenal === 'Tidak' ? 'selected' : '' ?>>Tidak</option>
            </select>

            <button type="submit" style="display:none;"></button>
          </form>
        </div>

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
              <?php if (mysqli_num_rows($result) > 0): ?>
                <?php while ($row = mysqli_fetch_assoc($result)): ?>
                  <?php
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
                      <button class="hapus" onclick="if(confirm('Yakin hapus data ini?')) window.location.href='hapusdata.php?id=<?= $row['id'] ?>'">Hapus</button>
                    </td>
                  </tr>
                <?php endwhile; ?>
              <?php else: ?>
                <tr>
                  <td colspan="15" style="text-align:center;">Tidak ada data ditemukan.</td>
                </tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>

      <footer>
        <img src="../assets/image/logodprd.png" alt="DPRD">
        <img src="../assets/image/psiputih.png" alt="PSI">
        Hak cipta © 2025 - Partai Solidaritas Indonesia
      </footer>
    </main>
  </div>
</body>
</html>
