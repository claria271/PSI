<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Dashboard Admin - PSI</title>
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    body {
      font-family: 'Poppins', sans-serif;
      background: #ffffff;
      color: #333;
      line-height: 1.6;
    }

    /* === HEADER === */
    header {
      background: linear-gradient(to right, #ffffff, #000000);
      padding: 12px 40px;
      display: flex;
      align-items: center;
      justify-content: space-between;
      position: sticky;
      top: 0;
      z-index: 100;
      box-shadow: 0 2px 10px rgba(0,0,0,0.1);
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

    nav a:hover,
    nav a.active {
      color: #ff4b4b;
    }

    /* === MAIN LAYOUT === */
    .main {
      display: flex;
      min-height: calc(100vh - 130px);
    }

    /* === SIDEBAR === */
    .sidebar {
      width: 260px;
      padding: 30px 20px;
      background: linear-gradient(to bottom, #d9d9d9, #8c8c8c);
      border-right: 1px solid #ccc;
    }

    .admin-profile {
      text-align: center;
      margin-bottom: 30px;
      position: relative;
    }

    .admin-photo {
      width: 70px;
      height: 70px;
      background: #bbb;
      border-radius: 50%;
      margin: 0 auto 12px;
      box-shadow: 0 4px 10px rgba(0, 0, 0, 0.15);
      overflow: hidden;
      cursor: pointer;
      transition: all 0.3s;
    }

    .admin-photo:hover {
      transform: scale(1.05);
      box-shadow: 0 6px 15px rgba(255, 0, 0, 0.3);
    }

    .admin-photo img {
      width: 100%;
      height: 100%;
      object-fit: cover;
    }

    .admin-name {
      color: #000;
      font-weight: 600;
      font-size: 15px;
      padding: 10px 15px;
      background: #cfcfcf;
      border-radius: 10px;
      cursor: pointer;
      transition: all 0.3s;
    }

    .admin-name:hover {
      background: #ff4b4b;
      color: #fff;
      transform: translateY(-2px);
      box-shadow: 0 4px 12px rgba(255, 75, 75, 0.3);
    }

    .sidebar nav a {
      display: block;
      padding: 12px 16px;
      margin: 8px 0;
      text-decoration: none;
      color: #000;
      background: #b5b5b5;
      border-radius: 10px;
      transition: all 0.3s;
      font-weight: 500;
      font-size: 14px;
      text-align: center;
    }

    .sidebar nav a:hover,
    .sidebar nav a.active {
      background: #ff4b4b;
      color: #fff;
      transform: translateX(5px);
      box-shadow: 0 4px 12px rgba(255, 75, 75, 0.3);
    }

    /* === CONTENT === */
    .content {
      flex: 1;
      padding: 30px;
    }

    .page-header {
      margin-bottom: 30px;
    }

    .page-header h2 {
      color: #000;
      font-size: 28px;
      font-weight: 700;
      margin-bottom: 5px;
    }

    .page-header p {
      color: #666;
      font-size: 14px;
    }

    /* === STATS CARDS === */
    .stats-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
      gap: 20px;
      margin-bottom: 30px;
    }

    .stat-card {
      background: linear-gradient(135deg, rgba(255, 255, 255, 0.95) 0%, rgba(255, 200, 200, 0.3) 100%);
      backdrop-filter: blur(10px);
      border: 1px solid rgba(255, 0, 0, 0.15);
      border-radius: 16px;
      padding: 20px 25px;
      display: flex;
      align-items: center;
      gap: 18px;
      transition: all 0.3s;
      box-shadow: 0 2px 8px rgba(255, 0, 0, 0.1);
    }

    .stat-card:hover {
      transform: translateY(-4px);
      box-shadow: 0 6px 20px rgba(255, 0, 0, 0.2);
      background: linear-gradient(135deg, rgba(255, 255, 255, 0.98) 0%, rgba(255, 180, 180, 0.4) 100%);
    }

    .stat-icon {
      width: 55px;
      height: 55px;
      background: linear-gradient(135deg, rgba(255, 0, 0, 0.1), rgba(255, 100, 100, 0.15));
      border-radius: 12px;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 26px;
      flex-shrink: 0;
    }

    .stat-content {
      flex: 1;
    }

    .stat-label {
      color: #666;
      font-size: 12px;
      font-weight: 600;
      margin-bottom: 4px;
      text-transform: uppercase;
    }

    .stat-value {
      color: #000;
      font-size: 28px;
      font-weight: 700;
      line-height: 1;
      margin-bottom: 6px;
    }

    .stat-change {
      display: inline-flex;
      align-items: center;
      gap: 4px;
      font-size: 12px;
      font-weight: 600;
      padding: 3px 8px;
      border-radius: 6px;
    }

    .stat-change.up {
      background: rgba(52, 211, 153, 0.15);
      color: #059669;
      border: 1px solid rgba(52, 211, 153, 0.3);
    }

    .stat-change.down {
      background: rgba(239, 68, 68, 0.15);
      color: #dc2626;
      border: 1px solid rgba(239, 68, 68, 0.3);
    }

    /* === CHARTS === */
    .charts-grid {
      display: grid;
      grid-template-columns: 2fr 1fr;
      gap: 20px;
      margin-bottom: 30px;
    }

    .chart-box {
      background: #fff;
      border: 1px solid #e0e0e0;
      border-radius: 16px;
      padding: 20px;
      box-shadow: 0 2px 8px rgba(0,0,0,0.08);
      transition: all 0.3s;
    }

    .chart-box:hover {
      transform: translateY(-2px);
      box-shadow: 0 6px 20px rgba(0, 0, 0, 0.1);
    }

    .chart-box h3 {
      color: #000;
      font-size: 14px;
      font-weight: 700;
      margin-bottom: 15px;
      text-transform: uppercase;
      letter-spacing: 0.5px;
    }

    .chart-small-grid {
      display: flex;
      flex-direction: column;
      gap: 20px;
    }

    .chart-container {
      position: relative;
      height: 280px;
    }

    .chart-container-small {
      position: relative;
      height: 180px;
    }

    /* === FOOTER === */
    footer {
      margin-top: 20px;
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
      margin: 0 5px;
      filter: brightness(0) invert(1);
    }

    /* === SCROLLBAR === */
    ::-webkit-scrollbar {
      width: 8px;
    }

    ::-webkit-scrollbar-track {
      background: #f1f1f1;
    }

    ::-webkit-scrollbar-thumb {
      background: #888;
      border-radius: 4px;
    }

    ::-webkit-scrollbar-thumb:hover {
      background: #555;
    }

    /* === RESPONSIVE === */
    @media (max-width: 1024px) {
      .charts-grid {
        grid-template-columns: 1fr;
      }
    }

    @media (max-width: 768px) {
      .sidebar {
        width: 220px;
      }
      
      .stats-grid {
        grid-template-columns: 1fr;
      }
    }
  </style>
</head>
<body>
  <!-- HEADER -->
  <header>
    <div class="logo">
      <img src="../assets/image/logo.png" alt="PSI Logo">
    </div>
  </header>

  <div class="main">
    <!-- SIDEBAR -->
    <aside class="sidebar">
      <div class="admin-profile">
        <div class="admin-photo" onclick="window.location.href='profil_admin.php'">
          <img src="../assets/image/admin_photo.jpg" alt="Admin Photo" onerror="this.src='data:image/svg+xml,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' viewBox=\'0 0 100 100\'%3E%3Ccircle cx=\'50\' cy=\'50\' r=\'50\' fill=\'%23bbb\'/%3E%3Ctext x=\'50\' y=\'60\' font-size=\'40\' text-anchor=\'middle\' fill=\'%23666\'%3E👤%3C/text%3E%3C/svg%3E'">
        </div>
        <div class="admin-name" onclick="window.location.href='profil_admin.php'">Admintasya</div>
      </div>
      <nav>
        <a href="#" class="active">Dashboard</a>
        <a href="datakeluarga.php">Data Keluarga</a>
        <a href="#">Hasil Verifikasi</a>
        <a href="#">Laporan</a>
        <a href="logout.php">Logout</a>
      </nav>
    </aside>

    <!-- CONTENT -->
    <section class="content">
      <div class="page-header">
        <h2>Dashboard Admin</h2>
        <p>Selamat datang kembali! Berikut ringkasan data terkini</p>
      </div>

      <!-- STATS CARDS -->
      <div class="stats-grid">
        <div class="stat-card">
          <div class="stat-icon">👨‍👩‍👧‍👦</div>
          <div class="stat-content">
            <div class="stat-label">Total Keluarga</div>
            <div class="stat-value">3,782</div>
            <div class="stat-change up">↑ 11.01%</div>
          </div>
        </div>

        <div class="stat-card">
          <div class="stat-icon">📉</div>
          <div class="stat-content">
            <div class="stat-label">Dibawah UMR</div>
            <div class="stat-value">1,234</div>
            <div class="stat-change down">↓ 9.05%</div>
          </div>
        </div>

        <div class="stat-card">
          <div class="stat-icon">📈</div>
          <div class="stat-content">
            <div class="stat-label">Diatas UMR</div>
            <div class="stat-value">2,548</div>
            <div class="stat-change up">↑ 4.21%</div>
          </div>
        </div>
      </div>

      <!-- CHARTS -->
      <div class="charts-grid">
        <div class="chart-box">
          <h3>Jumlah Data Keluarga</h3>
          <div class="chart-container">
            <canvas id="chartLine"></canvas>
          </div>
        </div>
        <div class="chart-small-grid">
          <div class="chart-box">
            <h3>Status</h3>
            <div class="chart-container-small">
              <canvas id="chartBar"></canvas>
            </div>
          </div>
          <div class="chart-box">
            <h3>Data Daerah</h3>
            <div class="chart-container-small">
              <canvas id="chartPie"></canvas>
            </div>
          </div>
        </div>
      </div>
    </section>
  </div>

  <!-- FOOTER -->
  <footer>
    <img src="../assets/image/logodprd.png" alt="DPRD Logo">
    <img src="../assets/image/psiputih.png" alt="PSI Logo">
    Hak cipta © 2025 - Partai Solidaritas Indonesia
  </footer>

  <script>
    Chart.defaults.color = '#666';
    Chart.defaults.borderColor = '#e5e5e5';

    // Line Chart
    const ctx1 = document.getElementById('chartLine');
    new Chart(ctx1, {
      type: 'line',
      data: {
        labels: ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'],
        datasets: [{
          label: 'Jumlah Keluarga',
          data: [80, 150, 130, 160, 120, 200, 180, 170, 110, 90, 150, 210],
          borderColor: '#ff0000',
          backgroundColor: 'rgba(255, 0, 0, 0.05)',
          fill: true,
          tension: 0.4,
          borderWidth: 2,
          pointRadius: 3,
          pointHoverRadius: 6,
          pointBackgroundColor: '#ff0000',
          pointHoverBackgroundColor: '#ff0000',
          pointBorderColor: '#fff',
          pointBorderWidth: 1
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: { display: false },
          tooltip: {
            backgroundColor: 'rgba(0, 0, 0, 0.8)',
            padding: 10,
            cornerRadius: 6,
            titleFont: { size: 13 },
            bodyFont: { size: 12 }
          }
        },
        scales: {
          y: {
            beginAtZero: true,
            grid: {
              color: '#f0f0f0',
              drawBorder: false
            },
            ticks: { 
              color: '#666',
              font: { size: 11 }
            }
          },
          x: {
            grid: {
              display: false,
              drawBorder: false
            },
            ticks: { 
              color: '#666',
              font: { size: 11 }
            }
          }
        }
      }
    });

    // Bar Chart
    const ctx2 = document.getElementById('chartBar');
    new Chart(ctx2, {
      type: 'bar',
      data: {
        labels: ['Jan', 'Feb', 'Mar', 'Apr'],
        datasets: [
          {
            label: 'Dibawah UMR',
            data: [80, 100, 90, 95],
            backgroundColor: '#008000',
            borderRadius: 6,
            borderSkipped: false
          },
          {
            label: 'Diatas UMR',
            data: [120, 150, 140, 130],
            backgroundColor: '#ff0000',
            borderRadius: 6,
            borderSkipped: false
          }
        ]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: {
            position: 'bottom',
            labels: {
              color: '#666',
              padding: 10,
              usePointStyle: true,
              pointStyle: 'circle',
              font: { size: 11 }
            }
          }
        },
        scales: {
          y: {
            beginAtZero: true,
            grid: {
              color: '#f0f0f0',
              drawBorder: false
            },
            ticks: { 
              color: '#666',
              font: { size: 10 }
            }
          },
          x: {
            grid: {
              display: false,
              drawBorder: false
            },
            ticks: { 
              color: '#666',
              font: { size: 10 }
            }
          }
        }
      }
    });

    // Pie Chart
    const ctx3 = document.getElementById('chartPie');
    new Chart(ctx3, {
      type: 'doughnut',
      data: {
        labels: ['Dapil 1', 'Dapil 2', 'Dapil 3', 'Dapil 4', 'Dapil 5'],
        datasets: [{
          data: [20, 25, 15, 25, 15],
          backgroundColor: [
            '#f44336',
            '#2196f3',
            '#ff9800',
            '#4caf50',
            '#9c27b0'
          ],
          borderWidth: 2,
          borderColor: '#fff',
          hoverOffset: 6
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: {
            position: 'bottom',
            labels: {
              color: '#666',
              padding: 8,
              usePointStyle: true,
              pointStyle: 'circle',
              font: { size: 10 }
            }
          },
          tooltip: {
            backgroundColor: 'rgba(0, 0, 0, 0.8)',
            padding: 8,
            cornerRadius: 6,
            bodyFont: { size: 11 }
          }
        },
        cutout: '60%'
      }
    });
  </script>
</body>
</html>