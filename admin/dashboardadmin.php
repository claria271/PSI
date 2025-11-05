<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../user/login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Dashboard Admin - PSI</title>
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
      font-family: 'Poppins', sans-serif;
    }

    body {
      background: #e6e6e6;
      color: #333;
      display: flex;
      flex-direction: column;
      min-height: 100vh;
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

    /* MAIN LAYOUT */
    .main {
      display: flex;
      flex: 1;
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

    /* CONTENT */
    .content {
      flex: 1;
      padding: 25px;
    }

    .content h2 {
      background: #fff;
      padding: 15px;
      border-radius: 10px;
      font-size: 20px;
      font-weight: 600;
      margin-bottom: 25px;
      box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    }
    /* STYLE UNTUK CARD */
.stat-card {
  background: #1e1f29;
  color: #fff;
  border-radius: 16px;
  padding: 20px;
  display: flex;
  align-items: center;
  justify-content: space-between;
  width: 250px;
  box-shadow: 0 0 10px rgba(0,0,0,0.4);
}

.stat-icon {
  font-size: 32px;
  color: #8b8d9a;
}

.stat-content {
  text-align: right;
}

.stat-label {
  color: #b3b6c7;
  font-size: 14px;
}

.stat-value {
  font-size: 28px;
  font-weight: 700;
  margin: 3px 0;
}

.stat-change {
  display: inline-block;
  font-size: 13px;
  padding: 4px 8px;
  border-radius: 8px;
  font-weight: 600;
}

.up {
  background: #173d2b;
  color: #3bd671;
}

.down {
  background: #402626;
  color: #e15b5b;
}

    /* METRIC BOXES */
    .metrics {
      display: flex;
      gap: 20px;
      align-items: center;
      margin-bottom: 25px;
      flex-wrap: wrap;
    }

    .metric {
      flex: 1;
      min-width: 160px;
      text-align: center;
      padding: 25px;
      border-radius: 10px;
      background: linear-gradient(to bottom, #d9d9d9, #8c8c8c);
      color: #fff;
      font-weight: 600;
      box-shadow: 0 3px 8px rgba(0,0,0,0.15);
    }

    .btn-input {
      background: #ff4b4b;
      border: none;
      color: #fff;
      padding: 15px 25px;
      border-radius: 10px;
      font-weight: bold;
      cursor: pointer;
      transition: 0.3s;
    }

    .btn-input:hover {
      background: #e03a3a;
    }

    /* CHARTS AREA */
    .charts {
      display: grid;
      grid-template-columns: 2fr 1fr;
      gap: 20px;
    }

    .chart-box {
      background: #fff;
      border-radius: 10px;
      padding: 20px;
      box-shadow: 0 3px 8px rgba(0,0,0,0.15);
    }

    .chart-box h3 {
      font-size: 16px;
      margin-bottom: 10px;
      font-weight: 600;
    }

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

  <div class="main">
    <aside class="sidebar">
      <div class="admin-photo"></div>
      <div class="admin-name"><?= htmlspecialchars($_SESSION['nama_lengkap']); ?></div>
      <nav>
        <a href="#" class="active">Dashboard</a>
        <a href="#">Data Keluarga</a>
        <a href="#">Hasil Verifikasi</a>
        <a href="#">Laporan</a>
        <a href="logout.php">Logout</a>
      </nav>
    </aside>

    <section class="content">
      <h2>Dashboard Admin</h2>


<!-- CONTAINER CARD -->
<div style="display:flex; gap:20px; flex-wrap:wrap;">
  <div class="stat-card">
    <div class="stat-icon">👨‍👩‍👧‍👦</div>
    <div class="stat-content">
      <div class="stat-label">Total Keluarga</div>
      <div class="stat-value">3,782</div>
      <div class="stat-change up">↑ 11.01%</div>
    </div>
  </div>

  <div class="stat-card">
    <div class="stat-icon">⬇️</div>
    <div class="stat-content">
      <div class="stat-label">Dibawah UMR</div>
      <div class="stat-value">1,234</div>
      <div class="stat-change down">↓ 9.05%</div>
    </div>
  </div>

  <div class="stat-card">
    <div class="stat-icon">⬆️</div>
    <div class="stat-content">
      <div class="stat-label">Diatas UMR</div>
      <div class="stat-value">2,548</div>
      <div class="stat-change up">↑ 4.21%</div>
    </div>
  </div>
</div>
      <!-- CHARTS -->
      <div class="charts">
        <div class="chart-box">
          <h3>JUMLAH DATA KELUARGA</h3>
          <canvas id="chartLine"></canvas>
        </div>
        <div>
          <div class="chart-box" style="margin-bottom:20px;">
            <h3>STATUS</h3>
            <canvas id="chartBar"></canvas>
          </div>
          <div class="chart-box">
            <h3>DATA DAERAH</h3>
            <canvas id="chartPie"></canvas>
          </div>
        </div>
      </div>
    </section>
  </div>

  <footer>
    <img src="../assets/image/logodprd.png" alt="DPRD">
    <img src="../assets/image/psiputih.png" alt="PSI">
    Hak cipta © 2025 - Partai Solidaritas Indonesia
  </footer>

  <script>
    // Grafik Garis
    const ctx1 = document.getElementById('chartLine');
    new Chart(ctx1, {
      type: 'line',
      data: {
        labels: ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'],
        datasets: [{
          label: 'Jumlah Keluarga',
          data: [80,150,130,160,120,200,180,170,110,90,150,210],
          borderColor: 'red',
          fill: false,
          tension: 0.3
        }]
      },
      options: { responsive: true, plugins: { legend: { display: false } } }
    });

    // Grafik Batang
    const ctx2 = document.getElementById('chartBar');
    new Chart(ctx2, {
      type: 'bar',
      data: {
        labels: ['Jan', 'Feb', 'Mar', 'Apr'],
        datasets: [
          {
            label: 'Dibawah UMR',
            data: [80, 100, 90, 95],
            backgroundColor: 'green'
          },
          {
            label: 'Diatas UMR',
            data: [120, 150, 140, 130],
            backgroundColor: 'red'
          }
        ]
      },
      options: { responsive: true, plugins: { legend: { position: 'bottom' } } }
    });

    // Grafik Pie
    const ctx3 = document.getElementById('chartPie');
    new Chart(ctx3, {
      type: 'doughnut',
      data: {
        labels: ['Dapil 1', 'Dapil 2', 'Dapil 3', 'Dapil 4', 'Dapil 5'],
        datasets: [{
          data: [20, 25, 15, 25, 15],
          backgroundColor: ['#f44336', '#2196f3', '#ff9800', '#4caf50', '#9c27b0']
        }]
      },
      options: { responsive: true, plugins: { legend: { position: 'bottom' } } }
    });
  </script>
</body>
</html>
