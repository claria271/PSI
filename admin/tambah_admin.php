<?php
// admin/kelola_admin.php
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

$errors   = [];
$success  = '';
$modeEdit = false;
$editData = null;

// Nilai awal form tambah
$alamat_email_add = '';

// ==== HANDLE POST (TAMBAH / EDIT / HAPUS) ====
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $aksi = $_POST['aksi'] ?? '';

    // ========== TAMBAH ADMIN ==========
    if ($aksi === 'tambah') {
        $alamat_email_add = trim($_POST['alamat_email'] ?? '');
        $password         = $_POST['password'] ?? '';
        $password2        = $_POST['password_confirm'] ?? '';

        // VALIDASI
        if ($alamat_email_add === '') {
            $errors[] = 'Alamat email wajib diisi.';
        } elseif (!filter_var($alamat_email_add, FILTER_VALIDATE_EMAIL)) {
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

        // Cek email sudah dipakai atau belum
        if (!$errors) {
            $stmt = $conn->prepare("SELECT id FROM login WHERE alamat_email = ? LIMIT 1");
            $stmt->bind_param('s', $alamat_email_add);
            $stmt->execute();
            $res = $stmt->get_result();
            if ($res->num_rows > 0) {
                $errors[] = 'Email sudah digunakan oleh akun lain.';
            }
            $stmt->close();
        }

        // Upload foto (opsional)
        $nama_file_foto = '';
        if (!$errors && isset($_FILES['foto']) && $_FILES['foto']['error'] !== UPLOAD_ERR_NO_FILE) {
            if ($_FILES['foto']['error'] !== UPLOAD_ERR_OK) {
                $errors[] = 'Gagal mengupload foto.';
            } else {
                $tmp_name  = $_FILES['foto']['tmp_name'];
                $orig_name = $_FILES['foto']['name'];
                $ext       = strtolower(pathinfo($orig_name, PATHINFO_EXTENSION));
                $allowed   = ['jpg','jpeg','png'];

                if (!in_array($ext, $allowed)) {
                    $errors[] = 'Format foto harus JPG atau PNG.';
                } else {
                    $nama_file_foto = 'admin_' . time() . '_' . mt_rand(1000,9999) . '.' . $ext;
                    $tujuan_folder  = __DIR__ . '/../uploads/' . $nama_file_foto;

                    if (!move_uploaded_file($tmp_name, $tujuan_folder)) {
                        $errors[] = 'Tidak bisa menyimpan file foto di server.';
                    }
                }
            }
        }

        // Insert
        if (!$errors) {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $role = 'admin';

            $stmt = $conn->prepare("
                INSERT INTO login (alamat_email, password, role, foto)
                VALUES (?, ?, ?, ?)
            ");
            $stmt->bind_param('ssss', $alamat_email_add, $hash, $role, $nama_file_foto);
            $stmt->execute();
            $stmt->close();

            $success = 'Admin baru berhasil ditambahkan.';
            $alamat_email_add = '';
        }
    }

    // ========== UPDATE ADMIN ==========
    elseif ($aksi === 'update') {
        $id_edit      = (int)($_POST['id'] ?? 0);
        $alamat_email = trim($_POST['alamat_email'] ?? '');
        $password     = $_POST['password'] ?? '';
        $password2    = $_POST['password_confirm'] ?? '';
        $foto_lama    = $_POST['foto_lama'] ?? '';

        if ($id_edit <= 0) {
            $errors[] = 'ID admin tidak valid.';
        }

        if ($alamat_email === '') {
            $errors[] = 'Alamat email wajib diisi.';
        } elseif (!filter_var($alamat_email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Format alamat email tidak valid.';
        }

        // Jika password diisi, validasi
        $updatePassword = false;
        if ($password !== '' || $password2 !== '') {
            if ($password === '') {
                $errors[] = 'Password baru tidak boleh kosong.';
            } elseif (strlen($password) < 6) {
                $errors[] = 'Password baru minimal 6 karakter.';
            }
            if ($password !== $password2) {
                $errors[] = 'Konfirmasi password baru tidak sama.';
            }
            if (!$errors) {
                $updatePassword = true;
            }
        }

        // Cek email unik selain dirinya sendiri
        if (!$errors && $id_edit > 0) {
            $stmt = $conn->prepare("SELECT id FROM login WHERE alamat_email = ? AND id <> ? LIMIT 1");
            $stmt->bind_param('si', $alamat_email, $id_edit);
            $stmt->execute();
            $res = $stmt->get_result();
            if ($res->num_rows > 0) {
                $errors[] = 'Email sudah dipakai akun lain.';
            }
            $stmt->close();
        }

        // Upload foto baru (opsional)
        $nama_file_foto = $foto_lama;
        if (!$errors && isset($_FILES['foto']) && $_FILES['foto']['error'] !== UPLOAD_ERR_NO_FILE) {
            if ($_FILES['foto']['error'] !== UPLOAD_ERR_OK) {
                $errors[] = 'Gagal mengupload foto.';
            } else {
                $tmp_name  = $_FILES['foto']['tmp_name'];
                $orig_name = $_FILES['foto']['name'];
                $ext       = strtolower(pathinfo($orig_name, PATHINFO_EXTENSION));
                $allowed   = ['jpg','jpeg','png'];

                if (!in_array($ext, $allowed)) {
                    $errors[] = 'Format foto harus JPG atau PNG.';
                } else {
                    $nama_file_foto_baru = 'admin_' . time() . '_' . mt_rand(1000,9999) . '.' . $ext;
                    $tujuan_folder       = __DIR__ . '/../uploads/' . $nama_file_foto_baru;

                    if (!move_uploaded_file($tmp_name, $tujuan_folder)) {
                        $errors[] = 'Tidak bisa menyimpan file foto di server.';
                    } else {
                        $nama_file_foto = $nama_file_foto_baru;
                    }
                }
            }
        }

        // Update ke DB
        if (!$errors && $id_edit > 0) {
            if ($updatePassword) {
                $hash = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $conn->prepare("
                    UPDATE login
                    SET alamat_email = ?, password = ?, foto = ?
                    WHERE id = ? AND role = 'admin'
                ");
                $stmt->bind_param('sssi', $alamat_email, $hash, $nama_file_foto, $id_edit);
            } else {
                $stmt = $conn->prepare("
                    UPDATE login
                    SET alamat_email = ?, foto = ?
                    WHERE id = ? AND role = 'admin'
                ");
                $stmt->bind_param('ssi', $alamat_email, $nama_file_foto, $id_edit);
            }
            $stmt->execute();
            $stmt->close();
            $success = 'Data admin berhasil diperbarui.';
        }

        // Reload data edit
        if ($id_edit > 0) {
            $modeEdit = true;
            $stmt = $conn->prepare("
                SELECT l.*, k.nama_lengkap, k.alamat, k.no_wa 
                FROM login l
                LEFT JOIN keluarga k ON l.id = k.user_id
                WHERE l.id = ? AND l.role = 'admin' 
                LIMIT 1
            ");
            $stmt->bind_param('i', $id_edit);
            $stmt->execute();
            $res = $stmt->get_result();
            if ($res->num_rows > 0) {
                $editData = $res->fetch_assoc();
            }
            $stmt->close();
        }
    }

    // ========== HAPUS ADMIN ==========
    elseif ($aksi === 'hapus') {
        $id_hapus = (int)($_POST['id'] ?? 0);

        if ($id_hapus > 0) {
            $stmt = $conn->prepare("DELETE FROM login WHERE id = ? AND role = 'admin'");
            $stmt->bind_param('i', $id_hapus);
            $stmt->execute();
            $stmt->close();
            $success = 'Admin berhasil dihapus.';
        } else {
            $errors[] = 'ID admin tidak valid untuk dihapus.';
        }
    }
}

// ==== HANDLE MODE EDIT VIA GET ====
if (!$modeEdit && isset($_GET['edit_id'])) {
    $id_edit = (int)$_GET['edit_id'];
    if ($id_edit > 0) {
        $stmt = $conn->prepare("
            SELECT l.*, k.nama_lengkap, k.alamat, k.no_wa 
            FROM login l
            LEFT JOIN keluarga k ON l.id = k.user_id
            WHERE l.id = ? AND l.role = 'admin' 
            LIMIT 1
        ");
        $stmt->bind_param('i', $id_edit);
        $stmt->execute();
        $res = $stmt->get_result();
        if ($res->num_rows > 0) {
            $modeEdit = true;
            $editData = $res->fetch_assoc();
        }
        $stmt->close();
    }
}

// ==== AMBIL LIST DATA ADMIN dengan JOIN ke keluarga ====
$listAdmin = [];
$stmt = $conn->prepare("
    SELECT l.id, l.alamat_email, l.foto, k.nama_lengkap, k.alamat, k.no_wa 
    FROM login l
    LEFT JOIN keluarga k ON l.id = k.user_id
    WHERE l.role = 'admin' 
    ORDER BY l.id DESC
");
$stmt->execute();
$res = $stmt->get_result();
while ($row = $res->fetch_assoc()) {
    $listAdmin[] = $row;
}
$stmt->close();
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Kelola Admin - PSI</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
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
      padding: 20px 30px 30px;
    }

    .top-actions {
      margin-bottom: 15px;
    }

    .top-actions a {
      text-decoration: none;
      display: inline-block;
      padding: 8px 14px;
      border-radius: 6px;
      background: #000;
      color: #fff;
      font-size: 13px;
      font-weight: 500;
    }

    .top-actions a:hover {
      background: #333;
    }

    .layout {
      display: grid;
      grid-template-columns: 2fr 1.5fr;
      gap: 20px;
    }

    @media (max-width: 900px) {
      .layout {
        grid-template-columns: 1fr;
      }
    }

    .card {
      background: #fff;
      border-radius: 10px;
      padding: 18px 20px;
      box-shadow: 0 4px 10px rgba(0,0,0,0.08);
    }

    .card h2 {
      margin-bottom: 10px;
      font-size: 18px;
    }

    .alert {
      padding: 10px 12px;
      border-radius: 6px;
      margin-bottom: 10px;
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

    table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 10px;
      font-size: 13px;
    }

    th, td {
      border: 1px solid #ddd;
      padding: 8px 10px;
      text-align: left;
      vertical-align: middle;
    }

    th {
      background: #f7f7f7;
      font-weight: 600;
      font-size: 12px;
      text-transform: uppercase;
    }

    tr:nth-child(even) {
      background: #fafafa;
    }

    .foto-thumb {
      width: 40px;
      height: 40px;
      border-radius: 50%;
      object-fit: cover;
      background: #ddd;
    }

    .aksi-btn {
      display: flex;
      gap: 4px;
      flex-wrap: wrap;
    }

    .btn-small {
      border: none;
      border-radius: 4px;
      padding: 4px 8px;
      font-size: 12px;
      cursor: pointer;
      font-weight: 500;
      text-decoration: none;
      display: inline-block;
    }

    .btn-edit {
      background: #007bff;
      color: #fff;
    }

    .btn-edit:hover {
      background: #0062cc;
    }

    .btn-delete {
      background: #dc3545;
      color: #fff;
    }

    .btn-delete:hover {
      background: #b52a36;
    }

    label {
      display: block;
      margin-bottom: 4px;
      font-weight: 600;
      font-size: 13px;
    }

    input[type="text"],
    input[type="email"],
    input[type="password"],
    textarea {
      width: 100%;
      padding: 8px 9px;
      border: 1px solid #ccc;
      border-radius: 6px;
      margin-bottom: 10px;
      font-size: 14px;
    }

    textarea {
      resize: vertical;
      min-height: 70px;
    }

    input[type="file"] {
      margin-bottom: 10px;
      font-size: 13px;
    }

    .hint {
      font-size: 12px;
      color: #777;
      margin-top: -6px;
      margin-bottom: 10px;
    }

    button[type="submit"] {
      padding: 9px 16px;
      border: none;
      border-radius: 6px;
      background: #000;
      color: #fff;
      font-weight: 600;
      cursor: pointer;
      transition: 0.3s;
      font-size: 14px;
    }

    button[type="submit"]:hover {
      background: #333;
    }

    .current-foto {
      margin-bottom: 10px;
      font-size: 13px;
      display: flex;
      align-items: center;
      gap: 10px;
    }

    .current-foto img {
      width: 50px;
      height: 50px;
      border-radius: 50%;
      object-fit: cover;
    }

    footer {
      text-align: center;
      padding: 10px 0 15px;
      font-size: 12px;
      color: #555;
    }

    .info-box {
      background: #e3f2fd;
      border-left: 4px solid #2196f3;
      padding: 12px;
      margin-bottom: 15px;
      border-radius: 4px;
      font-size: 13px;
    }

    .admin-detail {
      font-size: 13px;
      color: #666;
    }

    .admin-detail strong {
      color: #000;
      display: block;
      margin-bottom: 2px;
    }

    .no-data {
      color: #999;
      font-style: italic;
      font-size: 12px;
    }
  </style>
  <script>
    function konfirmasiHapus() {
      return confirm('Yakin ingin menghapus admin ini?');
    }
  </script>
</head>
<body>
  <header>
    <h1>🔧 Kelola Admin PSI</h1>
  </header>

  <main>
    <div class="top-actions">
      <a href="dashboardadmin.php">← Kembali ke Dashboard</a>
    </div>

    <div class="info-box">
      ℹ️ <strong>Catatan:</strong> Data nama lengkap, alamat, dan nomor telepon diambil dari tabel keluarga yang terhubung dengan user_id admin.
    </div>

    <?php if ($errors): ?>
      <div class="alert error">
        <strong>⚠️ Error:</strong>
        <ul style="margin-left: 20px; margin-top: 5px;">
          <?php foreach ($errors as $err): ?>
            <li><?= e($err) ?></li>
          <?php endforeach; ?>
        </ul>
      </div>
    <?php endif; ?>

    <?php if ($success): ?>
      <div class="alert success">
        ✓ <?= e($success) ?>
      </div>
    <?php endif; ?>

    <div class="layout">
      <!-- LIST ADMIN -->
      <div class="card">
        <h2>📋 Daftar Admin</h2>
        <?php if (count($listAdmin) === 0): ?>
          <p>Belum ada admin terdaftar.</p>
        <?php else: ?>
          <table>
            <thead>
              <tr>
                <th>ID</th>
                <th>Foto</th>
                <th>Email</th>
                <th>Info Lengkap</th>
                <th>Aksi</th>
              </tr>
            </thead>
            <tbody>
            <?php foreach ($listAdmin as $adm): ?>
              <tr>
                <td><?= e($adm['id']) ?></td>
                <td>
                  <?php if (!empty($adm['foto'])): ?>
                    <img src="../uploads/<?= e($adm['foto']) ?>" alt="foto" class="foto-thumb">
                  <?php else: ?>
                    <div class="foto-thumb"></div>
                  <?php endif; ?>
                </td>
                <td><?= e($adm['alamat_email']) ?></td>
                <td>
                  <div class="admin-detail">
                    <?php if (!empty($adm['nama_lengkap'])): ?>
                      <strong><?= e($adm['nama_lengkap']) ?></strong>
                      <?php if (!empty($adm['no_wa'])): ?>
                        <div>📱 <?= e($adm['no_wa']) ?></div>
                      <?php endif; ?>
                      <?php if (!empty($adm['alamat'])): ?>
                        <div>📍 <?= e(substr($adm['alamat'], 0, 50)) ?><?= strlen($adm['alamat']) > 50 ? '...' : '' ?></div>
                      <?php endif; ?>
                    <?php else: ?>
                      <span class="no-data">Belum ada data di tabel keluarga</span>
                    <?php endif; ?>
                  </div>
                </td>
                <td>
                  <div class="aksi-btn">
                    <a href="?edit_id=<?= e($adm['id']) ?>" class="btn-small btn-edit">Edit</a>
                    <form method="post" onsubmit="return konfirmasiHapus();" style="display: inline;">
                      <input type="hidden" name="aksi" value="hapus">
                      <input type="hidden" name="id" value="<?= e($adm['id']) ?>">
                      <button type="submit" class="btn-small btn-delete">Hapus</button>
                    </form>
                  </div>
                </td>
              </tr>
            <?php endforeach; ?>
            </tbody>
          </table>
        <?php endif; ?>
      </div>

      <!-- FORM TAMBAH / EDIT -->
      <div class="card">
        <?php if ($modeEdit && $editData): ?>
          <h2>✏️ Edit Admin</h2>
          
          <?php if (!empty($editData['nama_lengkap'])): ?>
            <div style="background: #f0f0f0; padding: 10px; border-radius: 6px; margin-bottom: 15px; font-size: 13px;">
              <strong>📋 Data dari Tabel Keluarga:</strong><br>
              <div style="margin-top: 5px;">
                Nama: <strong><?= e($editData['nama_lengkap']) ?></strong><br>
                <?php if (!empty($editData['no_wa'])): ?>
                  No. WA: <strong><?= e($editData['no_wa']) ?></strong><br>
                <?php endif; ?>
                <?php if (!empty($editData['alamat'])): ?>
                  Alamat: <strong><?= e($editData['alamat']) ?></strong>
                <?php endif; ?>
              </div>
            </div>
          <?php else: ?>
            <div style="background: #fff3cd; padding: 10px; border-radius: 6px; margin-bottom: 15px; font-size: 13px; border-left: 4px solid #ffc107;">
              ⚠️ Admin ini belum memiliki data di tabel keluarga. Data lengkap akan muncul setelah admin mengisi profil.
            </div>
          <?php endif; ?>
          
          <form method="post" enctype="multipart/form-data">
            <input type="hidden" name="aksi" value="update">
            <input type="hidden" name="id" value="<?= e($editData['id']) ?>">
            <input type="hidden" name="foto_lama" value="<?= e($editData['foto'] ?? '') ?>">

            <label for="alamat_email_edit">Alamat Email *</label>
            <input type="email" id="alamat_email_edit" name="alamat_email"
                   value="<?= e($editData['alamat_email']) ?>" required>

            <label>Password Baru (opsional)</label>
            <input type="password" name="password" placeholder="Kosongkan jika tidak diubah">

            <label>Konfirmasi Password Baru</label>
            <input type="password" name="password_confirm" placeholder="Kosongkan jika tidak diubah">

            <div class="current-foto">
              <span>Foto saat ini:</span>
              <?php if (!empty($editData['foto'])): ?>
                <img src="../uploads/<?= e($editData['foto']) ?>" alt="foto admin">
              <?php else: ?>
                <span>Belum ada foto.</span>
              <?php endif; ?>
            </div>

            <label for="foto_edit">Foto Profil Baru (opsional)</label>
            <input type="file" id="foto_edit" name="foto" accept=".jpg,.jpeg,.png">
            <div class="hint">Format: JPG/PNG. Jika dikosongkan, tetap memakai foto lama.</div>

            <button type="submit">💾 Simpan Perubahan</button>
          </form>
          <div style="margin-top:8px;">
            <a href="kelola_admin.php" style="font-size:13px;text-decoration:none;color:#000;">← Batal edit / kembali ke mode tambah</a>
          </div>
        <?php else: ?>
          <h2>➕ Tambah Admin Baru</h2>
          <div style="background: #fff3cd; padding: 10px; border-radius: 6px; margin-bottom: 15px; font-size: 12px; border-left: 4px solid #ffc107;">
            ℹ️ Setelah admin dibuat, admin dapat login dan mengisi data lengkap (nama, alamat, telepon) melalui halaman profil user.
          </div>
          <form method="post" enctype="multipart/form-data">
            <input type="hidden" name="aksi" value="tambah">

            <label for="alamat_email_add">Alamat Email *</label>
            <input type="email" id="alamat_email_add" name="alamat_email"
                   value="<?= e($alamat_email_add) ?>" required>

            <label for="password_add">Password *</label>
            <input type="password" id="password_add" name="password" required>
            <div class="hint">Minimal 6 karakter</div>

            <label for="password_confirm_add">Konfirmasi Password *</label>
            <input type="password" id="password_confirm_add" name="password_confirm" required>

            <label for="foto_add">Foto Profil (opsional)</label>
            <input type="file" id="foto_add" name="foto" accept=".jpg,.jpeg,.png">
            <div class="hint">Format: JPG/PNG. Jika dikosongkan, akan memakai foto default.</div>

            <button type="submit">💾 Simpan Admin</button>
          </form>
        <?php endif; ?>
      </div>
    </div>
  </main>

  <footer>
    &copy; <?= date('Y') ?> PSI - Panel Admin
  </footer>
</body>
</html>