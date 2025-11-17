<?php
session_start();
include '../koneksi/config.php';

// Pastikan admin login
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../user/login.php");
    exit();
}

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
$conn->set_charset('utf8mb4');

// Helper aman untuk output HTML
function e($str) {
    return htmlspecialchars((string)$str, ENT_QUOTES, 'UTF-8');
}

// Ambil data admin berdasarkan session
$admin = null;

if (!empty($_SESSION['alamat_email'])) {
    $stmt = $conn->prepare("SELECT * FROM login WHERE alamat_email = ? LIMIT 1");
    $stmt->bind_param('s', $_SESSION['alamat_email']);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($res->num_rows > 0) {
        $admin = $res->fetch_assoc();
    }
} elseif (!empty($_SESSION['username'])) {
    $stmt = $conn->prepare("SELECT * FROM login WHERE username = ? LIMIT 1");
    $stmt->bind_param('s', $_SESSION['username']);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($res->num_rows > 0) {
        $admin = $res->fetch_assoc();
    }
}

if (!$admin) {
    header("Location: ../user/login.php");
    exit();
}

$adminName = !empty($admin['nama_lengkap']) ? $admin['nama_lengkap'] : (!empty($admin['username']) ? $admin['username'] : 'Admin');
$adminPhoto = !empty($admin['foto']) ? '../uploads/' . $admin['foto'] : '../assets/image/admin_photo.jpg';

?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Laporan - PSI</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body {
      font-family: 'Poppins', sans-serif;
      background: #ffffff;
      color: #333;
      height: 100vh;
      display: flex;
      flex-direction: column;
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

    .layout {
      flex: 1 1 auto;
      display: flex;
      min-height: 0;
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


    .content {
      flex:1 1 auto;
      display:flex;
      flex-direction:column;
      background:#f9f9f9;
      padding:25px 30px;
    }

    .page-header h2 { font-size:24px; font-weight:700; }

    .content-scroll { flex:1 1 auto; overflow-y:auto; }

    .card {
      background:#fff; padding:20px; border-radius:12px;
      box-shadow:0 2px 6px rgba(0,0,0,0.1);
    }

    .filters { display:flex; gap:10px; flex-wrap:wrap; }

    .filters input, .filters select {
      padding:7px 10px; border:1px solid #ccc; border-radius:6px; background:#f5f5f5;
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
  </style>
</head>
<body>
   <!-- HEADER -->
  <header>
    <div class="logo">
      <img src="../assets/image/logo.png" alt="PSI Logo">
    </div>
  </header>

   <!-- SIDEBAR -->
    <aside class="sidebar">
      <div class="admin-profile">
        <div class="admin-photo" onclick="window.location.href='profil_admin.php'">
          <img 
            src="<?php echo e($adminPhoto); ?>" 
            alt="Admin Photo"
            onerror="this.src='data:image/svg+xml,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' viewBox=\'0 0 100 100\'%3E%3Ccircle cx=\'50\' cy=\'50\' r=\'50\' fill=\'%23bbb\'/%3E%3Ctext x=\'50\' y=\'60\' font-size=\'40\' text-anchor=\'middle\' fill=\'%23666\'%3E👤%3C/text%3E%3C/svg%3E';"
          >
        </div>
        <div class="admin-name" onclick="window.location.href='profil_admin.php'">
          <?php echo e($adminName); ?>
        </div>
      </div>
      <nav>
        <a href="#" class="active">Dashboard</a>
        <a href="datakeluarga.php">Data Keluarga</a>

        <!-- 🔥 Tombol/Menu Tambah Admin -->
        <a href="tambah_admin.php">➕ Tambah Admin</a>

        <a href="#">Hasil Verifikasi</a>
        <a href="laporan.php">Laporan</a>
        <a href="logout.php">Logout</a>
      </nav>
    </aside>


         <!-- FOOTER -->
  <footer>
    <img src="../assets/image/logodprd.png" alt="DPRD Logo">
    <img src="../assets/image/psiputih.png" alt="PSI Logo">
    Hak cipta © 2025 - Partai Solidaritas Indonesia
  </footer>
      </div>
    </div>
  </div>
</body>
</html>