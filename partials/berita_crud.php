<?php
// partials/berita_crud.php
// Wajib sudah ada: $conn (dari config.php)
// Optional: $BACK_URL (kalau mau set manual). Kalau tidak ada, akan pakai halaman aktif.

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
$conn->set_charset('utf8mb4');

function e($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }

// kalau tidak diset dari halaman pemanggil, otomatis balik ke halaman sekarang
$BACK_URL = $BACK_URL ?? basename($_SERVER['PHP_SELF']);

$uploadDirFs   = __DIR__ . '/../uploads/berita/';   // filesystem (tetap)
$uploadDirUrl  = '../uploads/berita/';             // URL tampil (general & user sama-sama ../uploads/berita/)
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

  echo "<script>alert('Berita berhasil dihapus!'); window.location.href='".e($BACK_URL)."';</script>";
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

      echo "<script>alert('Berita berhasil ditambahkan!'); window.location.href='".e($BACK_URL)."';</script>";
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

      echo "<script>alert('Berita berhasil diperbarui!'); window.location.href='".e($BACK_URL)."';</script>";
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
    echo "<script>alert('Data tidak ditemukan!'); window.location.href='".e($BACK_URL)."';</script>";
    exit();
  }
}

// ===== LIST DATA =====
$res = $conn->query("SELECT * FROM berita ORDER BY tanggal DESC, created_at DESC");
$total = $res->num_rows;
?>

<!-- ====== MULAI TAMPILAN CRUD ====== -->
<div class="wrap">
  <div class="title">
    <div>
      <h2>Kelola Berita</h2>
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
              <a class="btn btn-ghost" href="<?php echo e($BACK_URL); ?>">Batal Edit</a>
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
                      <a class="btn-sm btn-edit" href="<?php echo e($BACK_URL); ?>?action=edit&id=<?php echo (int)$b['id']; ?>">Edit</a>
                      <a class="btn-sm btn-del" href="<?php echo e($BACK_URL); ?>?action=delete&id=<?php echo (int)$b['id']; ?>"
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
