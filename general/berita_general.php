<?php
require_once __DIR__ . '/guard_general.php';
include '../koneksi/config.php';

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
$conn->set_charset('utf8mb4');

// ====== TAMBAHAN (biar bisa dipakai di halaman lain juga) ======
$BACK_URL = basename($_SERVER['PHP_SELF']); // di general => berita_general.php
function backUrl($extra = '') {
  global $BACK_URL;
  return $BACK_URL . $extra;
}
// ===============================================================

function e($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }

$uploadDirFs   = __DIR__ . '/../uploads/berita/';   // filesystem
$uploadDirUrl  = '../uploads/berita/';             // untuk tampil di halaman general
$allowedExt    = ['jpg','jpeg','png','webp'];

if (!is_dir($uploadDirFs)) {
  mkdir($uploadDirFs, 0777, true);
}

function handleUpload($file, $allowedExt, $uploadDirFs) {
  if (empty($file['name'])) return [null, null];
  if ($file['error'] !== UPLOAD_ERR_OK) return [null, 'Gagal upload gambar (error code: '.$file['error'].')'];

  $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
  if (!in_array($ext, $allowedExt, true)) return [null, 'Format gambar harus: ' . implode(', ', $allowedExt)];

  $safeName = 'berita_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
  $target = $uploadDirFs . $safeName;

  if (!move_uploaded_file($file['tmp_name'], $target)) return [null, 'Gagal memindahkan file upload.'];

  return [$safeName, null];
}

$action = $_GET['action'] ?? '';
$id     = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$error = '';

// ===== DELETE =====
if ($action === 'delete' && $id > 0) {
  $stmt = $conn->prepare("SELECT gambar FROM berita WHERE id = ? LIMIT 1");
  $stmt->bind_param("i", $id);
  $stmt->execute();
  $row = $stmt->get_result()->fetch_assoc();

  $stmt = $conn->prepare("DELETE FROM berita WHERE id = ?");
  $stmt->bind_param("i", $id);
  $stmt->execute();

  if (!empty($row['gambar'])) {
    $path = $uploadDirFs . $row['gambar'];
    if (is_file($path)) @unlink($path);
  }

  echo "<script>alert('Berita berhasil dihapus!'); window.location.href='".e(backUrl())."';</script>";
  exit();
}

// ===== ADD / EDIT SUBMIT =====
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $mode   = $_POST['mode'] ?? 'add';
  $editId = (int)($_POST['id'] ?? 0);

  $judul     = trim($_POST['judul'] ?? '');
  $ringkasan = trim($_POST['ringkasan'] ?? '');
  $link      = trim($_POST['link_berita'] ?? '');
  $tanggal   = $_POST['tanggal'] ?? '';
  $status    = $_POST['status'] ?? 'publish';

  if ($judul === '' || $ringkasan === '' || $link === '' || $tanggal === '') {
    $error = "Judul, ringkasan, link berita, dan tanggal wajib diisi.";
  } elseif (!filter_var($link, FILTER_VALIDATE_URL)) {
    $error = "Link berita tidak valid. Harus format URL (contoh: https://...).";
  } elseif (!in_array($status, ['publish','draft'], true)) {
    $error = "Status tidak valid.";
  }

  $newImage = null;
  if ($error === '') {
    [$imgName, $errUpload] = handleUpload($_FILES['gambar'] ?? [], $allowedExt, $uploadDirFs);
    if ($errUpload) $error = $errUpload;
    $newImage = $imgName;
  }

  if ($error === '') {
    if ($mode === 'add') {
      $stmt = $conn->prepare("INSERT INTO berita (judul, ringkasan, link_berita, gambar, tanggal, status) VALUES (?,?,?,?,?,?)");
      $stmt->bind_param("ssssss", $judul, $ringkasan, $link, $newImage, $tanggal, $status);
      $stmt->execute();

      echo "<script>alert('Berita berhasil ditambahkan!'); window.location.href='".e(backUrl())."';</script>";
      exit();
    } else {
      $stmt = $conn->prepare("SELECT gambar FROM berita WHERE id = ? LIMIT 1");
      $stmt->bind_param("i", $editId);
      $stmt->execute();
      $old = $stmt->get_result()->fetch_assoc();
      $oldImage = $old['gambar'] ?? null;

      $finalImage = $newImage ?: $oldImage;

      $stmt = $conn->prepare("UPDATE berita SET judul=?, ringkasan=?, link_berita=?, gambar=?, tanggal=?, status=? WHERE id=?");
      $stmt->bind_param("ssssssi", $judul, $ringkasan, $link, $finalImage, $tanggal, $status, $editId);
      $stmt->execute();

      if ($newImage && $oldImage && $oldImage !== $newImage) {
        $path = $uploadDirFs . $oldImage;
        if (is_file($path)) @unlink($path);
      }

      echo "<script>alert('Berita berhasil diperbarui!'); window.location.href='".e(backUrl())."';</script>";
      exit();
    }
  }
}

// ===== LOAD EDIT DATA =====
$editData = null;
if ($action === 'edit' && $id > 0) {
  $stmt = $conn->prepare("SELECT * FROM berita WHERE id = ? LIMIT 1");
  $stmt->bind_param("i", $id);
  $stmt->execute();
  $editData = $stmt->get_result()->fetch_assoc();
  if (!$editData) {
    echo "<script>alert('Data tidak ditemukan!'); window.location.href='".e(backUrl())."';</script>";
    exit();
  }
}

// ===== LIST DATA =====
$res = $conn->query("SELECT * FROM berita ORDER BY tanggal DESC, created_at DESC");
$total = $res->num_rows;
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>General - Kelola Berita</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
  <style>
    *{margin:0;padding:0;box-sizing:border-box}
    body{font-family:'Poppins',sans-serif;background:#f3f3f3;color:#111827}
    a{text-decoration:none;color:inherit}
    header{
      background:linear-gradient(to right,#ffffff,#000000);
      padding:12px 40px;display:flex;align-items:center;justify-content:space-between;
      position:sticky;top:0;z-index:20;box-shadow:0 2px 10px rgba(0,0,0,.1);
    }
    header img{height:40px}
    header .right a{
      color:#fff;font-weight:800;background:rgba(255,255,255,.12);
      padding:8px 12px;border-radius:10px;transition:.2s
    }
    header .right a:hover{background:#ff4b4b}
    .wrap{max-width:1180px;margin:24px auto;padding:0 18px}
    .title{display:flex;justify-content:space-between;align-items:flex-end;gap:16px;margin-bottom:14px}
    .title h2{font-size:26px;font-weight:800}
    .muted{color:#6b7280;font-size:13px}
    .badge{padding:8px 12px;border-radius:999px;background:#fff;border:1px solid #e5e7eb;font-size:13px;font-weight:800;box-shadow:0 2px 12px rgba(0,0,0,.06)}
    .badge span{color:#ff0000}
    .grid{display:grid;grid-template-columns:1.15fr 1.85fr;gap:18px;align-items:start}
    .card{background:#fff;border:1px solid #e5e7eb;border-radius:16px;box-shadow:0 2px 12px rgba(0,0,0,.06);overflow:hidden}
    .card-head{padding:14px 16px;background:linear-gradient(135deg, rgba(255,255,255,.98), rgba(255,220,220,.18));border-bottom:1px solid #eee;font-weight:900}
    .card-body{padding:16px}
    label{display:block;font-size:12px;font-weight:900;color:#374151;margin:10px 0 6px}
    input, textarea, select{width:100%;border:1px solid #e5e7eb;border-radius:12px;padding:10px 12px;font-size:13px;outline:none;background:#fff}
    textarea{min-height:110px;resize:vertical;line-height:1.6}
    input:focus, textarea:focus, select:focus{border-color:#ff4b4b;box-shadow:0 0 0 3px rgba(255,75,75,.15)}
    .btn{border:none;border-radius:12px;padding:10px 14px;font-weight:900;font-size:13px;cursor:pointer;transition:.2s}
    .btn-primary{background:#ff0000;color:#fff}
    .btn-primary:hover{background:#e60000}
    .btn-ghost{background:#fff;border:1px solid #e5e7eb}
    .btn-ghost:hover{border-color:#ff4b4b;color:#ff4b4b}
    .row-btn{display:flex;gap:10px;flex-wrap:wrap;margin-top:12px}
    table{width:100%;border-collapse:separate;border-spacing:0}
    thead th{text-align:left;font-size:12px;letter-spacing:.3px;text-transform:uppercase;color:#6b7280;padding:12px 14px;border-bottom:1px solid #eee;background:#fafafa}
    tbody td{padding:12px 14px;border-bottom:1px solid #f1f1f1;vertical-align:top;font-size:13px}
    tbody tr:hover{background:#fff6f6}
    .pill{display:inline-block;padding:4px 10px;border-radius:999px;font-weight:900;font-size:12px;border:1px solid rgba(0,0,0,.08)}
    .pill.pub{background:rgba(34,197,94,.12);color:#166534;border-color:rgba(34,197,94,.25)}
    .pill.draft{background:rgba(245,158,11,.14);color:#92400e;border-color:rgba(245,158,11,.28)}
    .thumb{width:64px;height:44px;border-radius:10px;object-fit:cover;border:1px solid #eee;background:#f3f4f6}
    .actions{display:flex;gap:8px;flex-wrap:wrap}
    .btn-sm{padding:8px 10px;border-radius:10px;font-size:12px;font-weight:900;border:none;cursor:pointer}
    .btn-edit{background:#111827;color:#fff}
    .btn-edit:hover{background:#000}
    .btn-del{background:#dc2626;color:#fff}
    .btn-del:hover{background:#b91c1c}
    .alert{padding:10px 12px;border-radius:12px;margin-bottom:12px;font-size:13px;font-weight:900}
    .alert.err{background:#fee2e2;color:#991b1b;border:1px solid rgba(220,38,38,.25)}
    @media (max-width: 980px){header{padding:12px 18px}.grid{grid-template-columns:1fr}}
  </style>
</head>
<body>

<header>
  <div class="left">
    <img src="../assets/image/logo.png" alt="PSI Logo">
  </div>
  <div class="right">
    <a href="../logout_general.php">Logout</a>
  </div>
</header>

<div class="wrap">
  <div class="title">
    <div>
      <h2>Kelola Berita (General)</h2>
      <div class="muted">Isi berita + link, lalu tombol “Baca” di index akan menuju link tersebut.</div>
    </div>
    <div class="badge">Total: <span><?php echo number_format($total,0,',','.'); ?></span></div>
  </div>

  <div class="grid">
    <!-- FORM -->
    <div class="card">
      <div class="card-head"><?php echo $editData ? 'Edit Berita' : 'Tambah Berita'; ?></div>
      <div class="card-body">

        <?php if ($error): ?>
          <div class="alert err"><?php echo e($error); ?></div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data">
          <input type="hidden" name="mode" value="<?php echo $editData ? 'edit' : 'add'; ?>">
          <input type="hidden" name="id" value="<?php echo $editData ? (int)$editData['id'] : 0; ?>">

          <label>Judul</label>
          <input type="text" name="judul" required value="<?php echo $editData ? e($editData['judul']) : ''; ?>">

          <label>Ringkasan (muncul di card index)</label>
          <textarea name="ringkasan" required><?php echo $editData ? e($editData['ringkasan']) : ''; ?></textarea>

          <label>Link Berita (URL)</label>
          <input type="url" name="link_berita" placeholder="https://contoh.com/berita/..." required
                 value="<?php echo $editData ? e($editData['link_berita']) : ''; ?>">

          <label>Tanggal</label>
          <input type="date" name="tanggal" required value="<?php echo $editData ? e($editData['tanggal']) : date('Y-m-d'); ?>">

          <label>Status</label>
          <select name="status">
            <option value="publish" <?php echo ($editData && $editData['status']==='publish') ? 'selected' : ''; ?>>Publish (tampil di index)</option>
            <option value="draft" <?php echo ($editData && $editData['status']==='draft') ? 'selected' : ''; ?>>Draft (tidak tampil)</option>
          </select>

          <label>Gambar (opsional)</label>
          <input type="file" name="gambar" accept=".jpg,.jpeg,.png,.webp">

          <?php if ($editData && !empty($editData['gambar'])): ?>
            <div style="margin-top:10px" class="muted">
              Gambar saat ini:
              <div style="margin-top:8px">
                <img class="thumb" src="<?php echo $uploadDirUrl . e($editData['gambar']); ?>" alt="">
              </div>
            </div>
          <?php endif; ?>

          <div class="row-btn">
            <button class="btn btn-primary" type="submit"><?php echo $editData ? 'Simpan Perubahan' : 'Tambah Berita'; ?></button>
            <?php if ($editData): ?>
              <a class="btn btn-ghost" href="<?php echo e(backUrl()); ?>">Batal Edit</a>
            <?php else: ?>
              <button class="btn btn-ghost" type="reset">Reset</button>
            <?php endif; ?>
          </div>
        </form>

      </div>
    </div>

    <!-- LIST -->
    <div class="card">
      <div class="card-head">Daftar Berita</div>
      <div style="overflow:auto;">
        <table>
          <thead>
            <tr>
              <th>#</th>
              <th>Gambar</th>
              <th>Judul</th>
              <th>Tanggal</th>
              <th>Link</th>
              <th>Status</th>
              <th>Aksi</th>
            </tr>
          </thead>
          <tbody>
            <?php if ($total > 0): ?>
              <?php $no=1; while($b = $res->fetch_assoc()): ?>
                <tr>
                  <td><?php echo $no++; ?></td>
                  <td>
                    <?php if (!empty($b['gambar'])): ?>
                      <img class="thumb" src="<?php echo $uploadDirUrl . e($b['gambar']); ?>" alt="">
                    <?php else: ?>
                      <div class="muted">-</div>
                    <?php endif; ?>
                  </td>
                  <td>
                    <div style="font-weight:900"><?php echo e($b['judul']); ?></div>
                    <div class="muted"><?php echo e(mb_substr($b['ringkasan'],0,70)) . (mb_strlen($b['ringkasan'])>70?'...':''); ?></div>
                  </td>
                  <td><?php echo e($b['tanggal']); ?></td>
                  <td>
                    <a class="btn-sm btn-edit" style="background:#0ea5e9" href="<?php echo e($b['link_berita']); ?>" target="_blank">Buka</a>
                  </td>
                  <td>
                    <?php if (($b['status'] ?? '') === 'publish'): ?>
                      <span class="pill pub">publish</span>
                    <?php else: ?>
                      <span class="pill draft">draft</span>
                    <?php endif; ?>
                  </td>
                  <td>
                    <div class="actions">
                      <a class="btn-sm btn-edit" href="<?php echo e(backUrl('?action=edit&id='.(int)$b['id'])); ?>">Edit</a>
                      <a class="btn-sm btn-del" href="<?php echo e(backUrl('?action=delete&id='.(int)$b['id'])); ?>"
                         onclick="return confirm('Yakin hapus berita ini?');">Hapus</a>
                    </div>
                  </td>
                </tr>
              <?php endwhile; ?>
            <?php else: ?>
              <tr>
                <td colspan="7" style="text-align:center;padding:24px;color:#6b7280">
                  Belum ada berita. Tambahkan berita lewat form di kiri.
                </td>
              </tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>

  </div>
</div>

</body>
</html>
