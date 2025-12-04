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
$nama_lengkap_add    = '';
$alamat_lengkap_add  = '';
$nomor_telepon_add   = '';
$alamat_email_add    = '';

// ==== HANDLE POST (TAMBAH / EDIT / HAPUS) ====
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $aksi = $_POST['aksi'] ?? '';

    // ========== TAMBAH ADMIN ==========
    if ($aksi === 'tambah') {
        $nama_lengkap_add   = trim($_POST['nama_lengkap'] ?? '');
        $alamat_lengkap_add = trim($_POST['alamat_lengkap'] ?? '');
        $nomor_telepon_add  = trim($_POST['nomor_telepon'] ?? '');
        $alamat_email_add   = trim($_POST['alamat_email'] ?? '');
        $password           = $_POST['password'] ?? '';
        $password2          = $_POST['password_confirm'] ?? '';

        // VALIDASI
        if ($nama_lengkap_add === '') {
            $errors[] = 'Nama lengkap wajib diisi.';
        }
        if ($alamat_lengkap_add === '') {
            $errors[] = 'Alamat lengkap wajib diisi.';
        }
        if ($nomor_telepon_add === '') {
            $errors[] = 'Nomor telepon wajib diisi.';
        }
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
                INSERT INTO login (nama_lengkap, alamat_lengkap, nomor_telepon, alamat_email, password, role, foto)
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->bind_param(
                'sssssss',
                $nama_lengkap_add,
                $alamat_lengkap_add,
                $nomor_telepon_add,
                $alamat_email_add,
                $hash,
                $role,
                $nama_file_foto
            );
            $stmt->execute();

            $success = 'Admin baru berhasil ditambahkan.';

            // reset form tambah
            $nama_lengkap_add   = '';
            $alamat_lengkap_add = '';
            $nomor_telepon_add  = '';
            $alamat_email_add   = '';
        }
    }

    // ========== UPDATE ADMIN ==========
    elseif ($aksi === 'update') {
        $id_edit         = (int)($_POST['id'] ?? 0);
        $nama_lengkap    = trim($_POST['nama_lengkap'] ?? '');
        $alamat_lengkap  = trim($_POST['alamat_lengkap'] ?? '');
        $nomor_telepon   = trim($_POST['nomor_telepon'] ?? '');
        $alamat_email    = trim($_POST['alamat_email'] ?? '');
        $password        = $_POST['password'] ?? '';
        $password2       = $_POST['password_confirm'] ?? '';
        $foto_lama       = $_POST['foto_lama'] ?? '';

        if ($id_edit <= 0) {
            $errors[] = 'ID admin tidak valid.';
        }

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
                        // kalau mau, bisa hapus foto lama dari folder uploads
                        // if ($foto_lama && file_exists(__DIR__.'/../uploads/'.$foto_lama)) {
                        //     unlink(__DIR__.'/../uploads/'.$foto_lama);
                        // }
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
                    SET nama_lengkap = ?, alamat_lengkap = ?, nomor_telepon = ?, alamat_email = ?, password = ?, foto = ?
                    WHERE id = ? AND role = 'admin'
                ");
                $stmt->bind_param(
                    'ssssssi',
                    $nama_lengkap,
                    $alamat_lengkap,
                    $nomor_telepon,
                    $alamat_email,
                    $hash,
                    $nama_file_foto,
                    $id_edit
                );
            } else {
                $stmt = $conn->prepare("
                    UPDATE login
                    SET nama_lengkap = ?, alamat_lengkap = ?, nomor_telepon = ?, alamat_email = ?, foto = ?
                    WHERE id = ? AND role = 'admin'
                ");
                $stmt->bind_param(
                    'sssssi',
                    $nama_lengkap,
                    $alamat_lengkap,
                    $nomor_telepon,
                    $alamat_email,
                    $nama_file_foto,
                    $id_edit
                );
            }
            $stmt->execute();
            $success = 'Data admin berhasil diperbarui.';
        }

        // Supaya form edit tetap muncul dengan data terbaru
        if ($id_edit > 0) {
            $modeEdit = true;
            $stmt = $conn->prepare("SELECT * FROM login WHERE id = ? AND role = 'admin' LIMIT 1");
            $stmt->bind_param('i', $id_edit);
            $stmt->execute();
            $res = $stmt->get_result();
            if ($res->num_rows > 0) {
                $editData = $res->fetch_assoc();
            }
        }
    }

    // ========== HAPUS ADMIN ==========
    elseif ($aksi === 'hapus') {
        $id_hapus = (int)($_POST['id'] ?? 0);

        if ($id_hapus > 0) {
            // (opsional) cegah menghapus dirinya sendiri
            // if (!empty($_SESSION['admin_id']) && $_SESSION['admin_id'] == $id_hapus) { ... }

            $stmt = $conn->prepare("DELETE FROM login WHERE id = ? AND role = 'admin'");
            $stmt->bind_param('i', $id_hapus);
            $stmt->execute();
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
        $stmt = $conn->prepare("SELECT * FROM login WHERE id = ? AND role = 'admin' LIMIT 1");
        $stmt->bind_param('i', $id_edit);
        $stmt->execute();
        $res = $stmt->get_result();
        if ($res->num_rows > 0) {
            $modeEdit = true;
            $editData = $res->fetch_assoc();
        }
    }
}

// ==== AMBIL LIST DATA ADMIN ====
$listAdmin = [];
$stmt = $conn->prepare("SELECT id, nama_lengkap, alamat_email, nomor_telepon, foto FROM login WHERE role = 'admin' ORDER BY id DESC");
$stmt->execute();
$res = $stmt->get_result();
while ($row = $res->fetch_assoc()) {
    $listAdmin[] = $row;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Kelola Admin - PSI</title>
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
      font-size: 14px;
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
  </style>
  <script>
    function konfirmasiHapus() {
      return confirm('Yakin ingin menghapus admin ini?');
    }
  </script>
</head>
<body>
  <header>
    <h1>Kelola Admin PSI</h1>
  </header>

  <main>
    <div class="top-actions">
      <a href="dashboardadmin.php">← Kembali ke Dashboard</a>
    </div>

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

    <div class="layout">
      <!-- LIST ADMIN -->
      <div class="card">
        <h2>Daftar Admin</h2>
        <?php if (count($listAdmin) === 0): ?>
          <p>Belum ada admin terdaftar.</p>
        <?php else: ?>
          <table>
            <thead>
              <tr>
                <th>ID</th>
                <th>Foto</th>
                <th>Nama</th>
                <th>Email</th>
                <th>No. Telepon</th>
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
                <td><?= e($adm['nama_lengkap']) ?></td>
                <td><?= e($adm['alamat_email']) ?></td>
                <td><?= e($adm['nomor_telepon']) ?></td>
                <td>
                  <div class="aksi-btn">
                    <a href="?edit_id=<?= e($adm['id']) ?>" class="btn-small btn-edit">Edit</a>
                    <form method="post" onsubmit="return konfirmasiHapus();">
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
          <h2>Edit Admin</h2>
          <form method="post" enctype="multipart/form-data">
            <input type="hidden" name="aksi" value="update">
            <input type="hidden" name="id" value="<?= e($editData['id']) ?>">
            <input type="hidden" name="foto_lama" value="<?= e($editData['foto'] ?? '') ?>">

            <label for="nama_lengkap_edit">Nama Lengkap</label>
            <input type="text" id="nama_lengkap_edit" name="nama_lengkap"
                   value="<?= e($editData['nama_lengkap']) ?>" required>

            <label for="alamat_lengkap_edit">Alamat Lengkap</label>
            <textarea id="alamat_lengkap_edit" name="alamat_lengkap" required><?= e($editData['alamat_lengkap']) ?></textarea>

            <label for="nomor_telepon_edit">Nomor Telepon</label>
            <input type="text" id="nomor_telepon_edit" name="nomor_telepon"
                   value="<?= e($editData['nomor_telepon']) ?>" required>

            <label for="alamat_email_edit">Alamat Email</label>
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

            <button type="submit">Simpan Perubahan</button>
          </form>
          <div style="margin-top:8px;">
            <a href="tambah_admin.php" style="font-size:13px;text-decoration:none;color:#000;">← Batal edit / kembali ke mode tambah</a>
          </div>
        <?php else: ?>
          <h2>Tambah Admin Baru</h2>
          <form method="post" enctype="multipart/form-data">
            <input type="hidden" name="aksi" value="tambah">

            <label for="nama_lengkap_add">Nama Lengkap</label>
            <input type="text" id="nama_lengkap_add" name="nama_lengkap"
                   value="<?= e($nama_lengkap_add) ?>" required>

            <label for="alamat_lengkap_add">Alamat Lengkap</label>
            <textarea id="alamat_lengkap_add" name="alamat_lengkap" required><?= e($alamat_lengkap_add) ?></textarea>

            <label for="nomor_telepon_add">Nomor Telepon</label>
            <input type="text" id="nomor_telepon_add" name="nomor_telepon"
                   value="<?= e($nomor_telepon_add) ?>" required>

            <label for="alamat_email_add">Alamat Email</label>
            <input type="email" id="alamat_email_add" name="alamat_email"
                   value="<?= e($alamat_email_add) ?>" required>

            <label for="password_add">Password</label>
            <input type="password" id="password_add" name="password" required>

            <label for="password_confirm_add">Konfirmasi Password</label>
            <input type="password" id="password_confirm_add" name="password_confirm" required>

            <label for="foto_add">Foto Profil (opsional)</label>
            <input type="file" id="foto_add" name="foto" accept=".jpg,.jpeg,.png">
            <div class="hint">Format: JPG/PNG. Jika dikosongkan, akan memakai foto default.</div>

            <button type="submit">Simpan Admin</button>
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
