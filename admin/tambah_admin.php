<?php
// admin/tambah_admin.php
session_start();
include '../koneksi/config.php';

// Pastikan yang akses adalah admin
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

$errors  = [];
$success = '';

// nilai awal form
$nama_lengkap    = '';
$alamat_lengkap  = '';
$nomor_telepon   = '';
$alamat_email    = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama_lengkap   = trim($_POST['nama_lengkap'] ?? '');
    $alamat_lengkap = trim($_POST['alamat_lengkap'] ?? '');
    $nomor_telepon  = trim($_POST['nomor_telepon'] ?? '');
    $alamat_email   = trim($_POST['alamat_email'] ?? '');
    $password       = $_POST['password'] ?? '';
    $password2      = $_POST['password_confirm'] ?? '';

    // ==== VALIDASI DASAR ====
    if ($nama_lengkap === '') {
        $errors[] = 'Nama lengkap wajib diisi.';
    }

    if ($alamat_lengkap === '') {
        $errors[] = 'Alamat lengkap wajib diisi.';
    }

    if ($nomor_telepon === '') {
        $errors[] = 'Nomor telepon wajib diisi.';
    }

    if ($alamat_email === '') {
        $errors[] = 'Alamat email wajib diisi.';
    } elseif (!filter_var($alamat_email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Format alamat email tidak valid.';
    }

    if ($password === '') {
        $errors[] = 'Password wajib diisi.';
    } elseif (strlen($password) < 6) {
        $errors[] = 'Password minimal 6 karakter.';
    }

    if ($password !== $password2) {
        $errors[] = 'Konfirmasi password tidak sama.';
    }

    // ==== JIKA TIDAK ADA ERROR DASAR, LANJUT ====
    if (!$errors) {
        // cek email sudah dipakai atau belum
        $stmt = $conn->prepare("SELECT id FROM login WHERE alamat_email = ? LIMIT 1");
        $stmt->bind_param('s', $alamat_email);
        $stmt->execute();
        $res = $stmt->get_result();

        if ($res->num_rows > 0) {
            $errors[] = 'Email sudah digunakan oleh akun lain.';
        }
    }

    // ==== HANDLE UPLOAD FOTO (OPSIONAL) ====
    $nama_file_foto = '';
    if (!$errors && isset($_FILES['foto']) && $_FILES['foto']['error'] !== UPLOAD_ERR_NO_FILE) {
        if ($_FILES['foto']['error'] !== UPLOAD_ERR_OK) {
            $errors[] = 'Gagal mengupload foto.';
        } else {
            $tmp_name = $_FILES['foto']['tmp_name'];
            $orig_name = $_FILES['foto']['name'];

            // cek ekstensi
            $ext = strtolower(pathinfo($orig_name, PATHINFO_EXTENSION));
            $allowed = ['jpg', 'jpeg', 'png'];

            if (!in_array($ext, $allowed)) {
                $errors[] = 'Format foto harus JPG atau PNG.';
            } else {
                // buat nama file baru
                $nama_file_foto = 'admin_' . time() . '_' . mt_rand(1000, 9999) . '.' . $ext;

                $tujuan_folder = __DIR__ . '/../uploads/' . $nama_file_foto;

                if (!move_uploaded_file($tmp_name, $tujuan_folder)) {
                    $errors[] = 'Tidak bisa menyimpan file foto di server.';
                }
            }
        }
    }

    // ==== INSERT JIKA SEMUA AMAN ====
    if (!$errors) {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $role = 'admin';

        // kolom: nama_lengkap, alamat_lengkap, nomor_telepon, alamat_email, password, role, foto
        $stmt = $conn->prepare("
            INSERT INTO login (nama_lengkap, alamat_lengkap, nomor_telepon, alamat_email, password, role, foto)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->bind_param(
            'sssssss',
            $nama_lengkap,
            $alamat_lengkap,
            $nomor_telepon,
            $alamat_email,
            $hash,
            $role,
            $nama_file_foto
        );
        $stmt->execute();

        $success        = 'Admin baru berhasil ditambahkan.';
        $nama_lengkap   = '';
        $alamat_lengkap = '';
        $nomor_telepon  = '';
        $alamat_email   = '';
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Tambah Admin - PSI</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
      font-family: 'Poppins', sans-serif;
    }
    body {
      background: #f2f2f2;
      color: #000;
      min-height: 100vh;
      display: flex;
      flex-direction: column;
    }

    header {
      background: linear-gradient(to right, #ffffff, #000000);
      padding: 12px 30px;
      color: #fff;
      display: flex;
      align-items: center;
      justify-content: space-between;
    }

    header h1 {
      font-size: 20px;
      font-weight: 600;
    }

    main {
      flex: 1;
      padding: 30px;
      display: flex;
      justify-content: center;
      align-items: flex-start;
    }

    .card {
      background: #fff;
      border-radius: 10px;
      padding: 25px 30px;
      box-shadow: 0 4px 10px rgba(0,0,0,0.1);
      width: 100%;
      max-width: 550px;
    }

    .card h2 {
      margin-bottom: 15px;
      font-size: 22px;
    }

    .alert {
      padding: 10px 12px;
      border-radius: 6px;
      margin-bottom: 15px;
      font-size: 14px;
    }

    .alert.error {
      background: #ffe5e5;
      color: #b30000;
    }

    .alert.success {
      background: #e6ffe9;
      color: #006622;
    }

    label {
      display: block;
      margin-bottom: 5px;
      font-weight: 600;
      font-size: 14px;
    }

    input[type="text"],
    input[type="email"],
    input[type="password"],
    textarea {
      width: 100%;
      padding: 9px 10px;
      border: 1px solid #ccc;
      border-radius: 6px;
      margin-bottom: 15px;
      font-size: 14px;
    }

    textarea {
      resize: vertical;
      min-height: 70px;
    }

    input[type="file"] {
      margin-bottom: 15px;
      font-size: 13px;
    }

    .hint {
      font-size: 12px;
      color: #777;
      margin-top: -10px;
      margin-bottom: 15px;
    }

    button {
      padding: 10px 18px;
      border: none;
      border-radius: 6px;
      background: #000;
      color: #fff;
      font-weight: 600;
      cursor: pointer;
      transition: 0.3s;
    }

    button:hover {
      background: #333;
    }

    .back-link {
      display: inline-block;
      margin-top: 10px;
      font-size: 14px;
      text-decoration: none;
      color: #000;
    }

    .back-link:hover {
      text-decoration: underline;
    }
  </style>
</head>
<body>
  <header>
    <h1>Panel Admin PSI</h1>
  </header>

  <main>
    <div class="card">
      <h2>Tambah Admin Baru</h2>

      <?php if ($errors): ?>
        <div class="alert error">
          <ul>
            <?php foreach ($errors as $err): ?>
              <li><?= e($err) ?></li>
            <?php endforeach; ?>
          </ul>
        </div>
      <?php endif; ?>

      <?php if ($success): ?>
        <div class="alert success">
          <?= e($success) ?>
        </div>
      <?php endif; ?>

      <form method="post" enctype="multipart/form-data">
        <label for="nama_lengkap">Nama Lengkap</label>
        <input type="text" name="nama_lengkap" id="nama_lengkap"
               value="<?= e($nama_lengkap) ?>" required>

        <label for="alamat_lengkap">Alamat Lengkap</label>
        <textarea name="alamat_lengkap" id="alamat_lengkap" required><?= e($alamat_lengkap) ?></textarea>

        <label for="nomor_telepon">Nomor Telepon</label>
        <input type="text" name="nomor_telepon" id="nomor_telepon"
               value="<?= e($nomor_telepon) ?>" required>

        <label for="alamat_email">Alamat Email</label>
        <input type="email" name="alamat_email" id="alamat_email"
               value="<?= e($alamat_email) ?>" required>

        <label for="password">Password</label>
        <input type="password" name="password" id="password" required>

        <label for="password_confirm">Konfirmasi Password</label>
        <input type="password" name="password_confirm" id="password_confirm" required>

        <label for="foto">Foto Profil (opsional)</label>
        <input type="file" name="foto" id="foto" accept=".jpg,.jpeg,.png">
        <div class="hint">Format: JPG/PNG. Jika dikosongkan, akan memakai foto default.</div>

        <button type="submit">Simpan Admin</button>
      </form>

      <a href="dashboardadmin.php" class="back-link">← Kembali ke Dashboard</a>
    </div>
  </main>
</body>
</html>