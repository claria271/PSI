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

function e($str) {
    return htmlspecialchars((string)$str, ENT_QUOTES, 'UTF-8');
}

/**
 * ====== DETEKSI STRUKTUR TABEL pengaduan ======
 */
$cols = [];
$resCols = $conn->query("SHOW COLUMNS FROM pengaduan");
while ($c = $resCols->fetch_assoc()) {
    $cols[] = $c['Field'];
}

function hasCol(array $cols, string $name): bool {
    return in_array($name, $cols, true);
}

// Deteksi PRIMARY KEY (agar tidak tergantung nama "id")
$pkCol = null;
$resPk = $conn->query("SHOW KEYS FROM pengaduan WHERE Key_name = 'PRIMARY'");
if ($resPk && $resPk->num_rows > 0) {
    $pkCol = $resPk->fetch_assoc()['Column_name'];
}
// fallback kalau tabel tidak punya primary key (jarang)
if (!$pkCol) {
    $pkCol = $cols[0] ?? null;
}

// Deteksi kolom nomor (no_telp_wa, no_wa, no_hp, dll)
$phoneCol = null;
$phoneCandidates = ['no_telp_wa','no_wa','no_tlp','no_telp','no_hp','telepon','nomor_telepon','wa','hp'];
foreach ($phoneCandidates as $cand) {
    if (hasCol($cols, $cand)) { $phoneCol = $cand; break; }
}

// Kolom lain yang kita butuhkan
$colTanggal   = hasCol($cols, 'tanggal') ? 'tanggal' : null;
$colNama      = hasCol($cols, 'nama_pengadu') ? 'nama_pengadu' : null;
$colDomisili  = hasCol($cols, 'domisili') ? 'domisili' : null;
$colAduan     = hasCol($cols, 'aduan') ? 'aduan' : null;
$colFile      = hasCol($cols, 'file_pendukung') ? 'file_pendukung' : null;
$colCreatedAt = hasCol($cols, 'created_at') ? 'created_at' : null;

// ====== PARAM FILTER ======
$q    = trim($_GET['q'] ?? '');
$from = trim($_GET['from'] ?? '');
$to   = trim($_GET['to'] ?? '');

$where = [];
$params = [];
$types  = '';

if ($q !== '') {
    $parts = [];
    $like = "%$q%";

    if ($colNama)     { $parts[] = "$colNama LIKE ?";     $params[] = $like; $types .= 's'; }
    if ($phoneCol)    { $parts[] = "$phoneCol LIKE ?";    $params[] = $like; $types .= 's'; }
    if ($colDomisili) { $parts[] = "$colDomisili LIKE ?"; $params[] = $like; $types .= 's'; }
    if ($colAduan)    { $parts[] = "$colAduan LIKE ?";    $params[] = $like; $types .= 's'; }

    if (!empty($parts)) {
        $where[] = "(" . implode(" OR ", $parts) . ")";
    }
}

if ($from !== '' && $colTanggal) {
    $where[] = "$colTanggal >= ?";
    $params[] = $from;
    $types .= "s";
}

if ($to !== '' && $colTanggal) {
    $where[] = "$colTanggal <= ?";
    $params[] = $to;
    $types .= "s";
}

$whereSql = $where ? ("WHERE " . implode(" AND ", $where)) : "";

// ====== QUERY DATA ======
if (!$pkCol) {
    die("Tabel pengaduan tidak ditemukan / tidak punya kolom.");
}

// SELECT: pakai alias supaya tampilan konsisten meski nama kolom beda
$select = [];
$select[] = "`$pkCol` AS row_id";
$select[] = $colTanggal   ? "$colTanggal AS tanggal" : "NULL AS tanggal";
$select[] = $colNama      ? "$colNama AS nama_pengadu" : "NULL AS nama_pengadu";
$select[] = $phoneCol     ? "$phoneCol AS no_wa" : "NULL AS no_wa";
$select[] = $colDomisili  ? "$colDomisili AS domisili" : "NULL AS domisili";
$select[] = $colAduan     ? "$colAduan AS aduan" : "NULL AS aduan";
$select[] = $colFile      ? "$colFile AS file_pendukung" : "NULL AS file_pendukung";
$select[] = $colCreatedAt ? "$colCreatedAt AS created_at" : "NULL AS created_at";

$orderBy = $colCreatedAt ? "$colCreatedAt DESC" : ($colTanggal ? "$colTanggal DESC" : "`$pkCol` DESC");

$sql = "
    SELECT " . implode(", ", $select) . "
    FROM pengaduan
    $whereSql
    ORDER BY $orderBy
";

$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();
$total = $result->num_rows;
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Admin - Pengaduan</title>

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

  <style>
    *{margin:0;padding:0;box-sizing:border-box}
    body{font-family:'Poppins',sans-serif;background:#f3f3f3;color:#111827}

    /* === HEADER === */
    header {
      width: 100%;
      background: linear-gradient(to right, #000000 0%, #ffffff 100%);
      padding: 6px 48px;
      display: flex;
      justify-content: space-between;
      align-items: center;
      position: sticky;
      top: 0;
      z-index: 20;
      box-shadow: 0 4px 18px rgba(15,23,42,0.20);
      overflow: visible;
    }

    .nav-left {
      display: flex;
      align-items: center;
      gap: 12px;
    }

    .nav-logo-image img {
      height: 5vh;
      min-height: 50px;
      max-height: 80px;
      width: auto;
      display: block;
      object-fit: contain;
      transform: translateY(-6px);
    }

    /* BACK BUTTON */
    .back-btn {
      display: inline-flex;
      align-items: center;
      gap: 8px;
      padding: 10px 18px;
      background: #000000;
      color: #ffffff;
      border: 2px solid #dc2626;
      border-radius: 12px;
      font-weight: 700;
      font-size: 14px;
      text-decoration: none;
      transition: all 0.25s;
      box-shadow: 0 4px 12px rgba(220, 38, 38, 0.15);
    }

    .back-btn:hover {
      background: #dc2626;
      transform: translateY(-2px);
      box-shadow: 0 6px 18px rgba(220, 38, 38, 0.25);
    }

    .wrap{max-width:1150px;margin:28px auto;padding:0 18px}
    
    .page-title{
      display:flex;
      justify-content:space-between;
      align-items:flex-end;
      gap:15px;
      margin-bottom:16px;
      margin-top: 20px;
    }
    
    .page-title h2{font-size:26px;font-weight:800}
    .muted{color:#6b7280;font-size:12px}
    
    .badge{
      display:inline-flex;align-items:center;gap:8px;
      padding:8px 12px;border-radius:999px;background:#fff;border:1px solid #e5e7eb;
      font-size:13px;font-weight:700;color:#111827;
      box-shadow:0 2px 10px rgba(0,0,0,.05);
    }
    .badge span{color:#ff0000}

    .card{
      background:#fff;border:1px solid #e5e7eb;border-radius:16px;
      box-shadow:0 2px 12px rgba(0,0,0,.06);
      overflow:hidden;
    }
    .toolbar{
      padding:16px;display:flex;flex-wrap:wrap;gap:10px;
      align-items:center;justify-content:space-between;
      border-bottom:1px solid #eee;
      background:linear-gradient(135deg, rgba(255,255,255,.98), rgba(255,220,220,.18));
    }
    .toolbar form{display:flex;flex-wrap:wrap;gap:10px;align-items:center}
    .toolbar input{
      border:1px solid #e5e7eb;border-radius:12px;
      padding:10px 12px;font-size:13px;outline:none;background:#fff;
    }
    .toolbar input:focus{border-color:#ff4b4b;box-shadow:0 0 0 3px rgba(255,75,75,.15)}
    .btn{border:none;border-radius:12px;padding:10px 14px;font-weight:700;font-size:13px;cursor:pointer;transition:.2s;}
    .btn-primary{background:#ff0000;color:#fff}
    .btn-primary:hover{background:#e60000}
    .btn-ghost{background:#fff;border:1px solid #e5e7eb;text-decoration:none;color:#111827;display:inline-flex;align-items:center}
    .btn-ghost:hover{border-color:#ff4b4b;color:#ff4b4b}

    table{width:100%;border-collapse:separate;border-spacing:0}
    thead th{
      text-align:left;font-size:12px;letter-spacing:.3px;text-transform:uppercase;color:#6b7280;
      padding:12px 14px;border-bottom:1px solid #eee;background:#fafafa;
    }
    tbody td{padding:12px 14px;border-bottom:1px solid #f1f1f1;vertical-align:top;font-size:13px;color:#111827;}
    tbody tr:hover{background:#fff6f6}
    .pill{
      display:inline-block;padding:4px 10px;border-radius:999px;
      background:rgba(255,0,0,.1);color:#c30000;font-weight:700;font-size:12px;
      border:1px solid rgba(255,0,0,.18);
    }
    .actions{display:flex;gap:8px;flex-wrap:wrap}
    .btn-sm{padding:8px 10px;border-radius:10px;font-size:12px;font-weight:700;border:none;cursor:pointer}
    .btn-detail{background:#111827;color:#fff}
    .btn-detail:hover{background:#000}
    .btn-link{background:#fff;border:1px solid #e5e7eb;text-decoration:none;color:#111827}
    .btn-link:hover{border-color:#ff4b4b;color:#ff4b4b}

    .modal-backdrop{
      position:fixed;inset:0;background:rgba(0,0,0,.5);
      display:none;align-items:center;justify-content:center;padding:18px;z-index:50;
    }
    .modal{
      width:min(720px, 100%);
      background:#fff;border-radius:16px;overflow:hidden;
      box-shadow:0 20px 60px rgba(0,0,0,.35);
      border:1px solid rgba(255,255,255,.3);
    }
    .modal-header{
      padding:14px 16px;display:flex;justify-content:space-between;align-items:center;
      background:linear-gradient(to right,#ffffff,#000000);color:#fff;
    }
    .modal-header h3{font-size:14px;font-weight:800}
    .modal-close{
      border:none;background:rgba(255,255,255,.18);
      color:#fff;border-radius:10px;padding:6px 10px;cursor:pointer;font-weight:800;
    }
    .modal-close:hover{background:#ff4b4b}
    .modal-body{padding:16px}
    .grid{display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:12px;}
    .field{border:1px solid #eee;border-radius:12px;padding:10px 12px;background:#fafafa;}
    .field .k{font-size:11px;color:#6b7280;text-transform:uppercase;font-weight:700}
    .field .v{margin-top:4px;font-size:13px;font-weight:600;color:#111827}
    .aduan-box{border:1px solid #eee;border-radius:12px;padding:12px;background:#fff;line-height:1.6;white-space:pre-wrap;}

    @media (max-width: 800px){
      .grid{grid-template-columns:1fr}
      header{padding:6px 18px}
      .nav-logo-image img{ transform: translateY(-5px); }
    }
  </style>
</head>
<body>

<header>
  <div class="nav-left">
    <div class="nav-logo-image">
      <img src="../assets/image/logou.png" alt="Logo">
    </div>
  </div>
  
  <a href="dashboardadmin.php" class="back-btn">
    <i class="fa-solid fa-arrow-left"></i> Kembali ke Dashboard
  </a>
</header>

<div class="wrap">
  <div class="page-title">
    <div>
      <h2>Data Pengaduan</h2>
      <div class="muted">Menampilkan daftar aduan yang masuk dari form pengaduan.</div>
    </div>
    <div class="badge">Total: <span><?php echo number_format($total, 0, ',', '.'); ?></span></div>
  </div>

  <div class="card">
    <div class="toolbar">
      <form method="GET">
        <input type="text" name="q" value="<?php echo e($q); ?>" placeholder="Cari nama/no WA/domisi/aduan...">
        <input type="date" name="from" value="<?php echo e($from); ?>">
        <input type="date" name="to" value="<?php echo e($to); ?>">
        <button class="btn btn-primary" type="submit">Filter</button>
        <a class="btn btn-ghost" href="pengaduan_admin.php">Reset</a>
      </form>
    </div>

    <div style="overflow:auto;">
      <table>
        <thead>
          <tr>
            <th>#</th>
            <th>Tanggal</th>
            <th>Pengadu</th>
            <th>No. WA</th>
            <th>Domisili</th>
            <th>Aduan</th>
            <th>File</th>
            <th>Dibuat</th>
            <th>Aksi</th>
          </tr>
        </thead>
        <tbody>
          <?php if ($total > 0): ?>
            <?php $no=1; while($row = $result->fetch_assoc()): ?>
              <?php
                $shortAduan = mb_substr($row['aduan'] ?? '', 0, 60);
                if (mb_strlen($row['aduan'] ?? '') > 60) $shortAduan .= '...';

                $file = $row['file_pendukung'] ?? '';
                $fileUrl = $file ? ("../uploads/pengaduan/" . $file) : '';
              ?>
              <tr>
                <td><span class="pill"><?php echo $no++; ?></span></td>
                <td><?php echo e($row['tanggal']); ?></td>
                <td>
                  <div style="font-weight:700"><?php echo e($row['nama_pengadu']); ?></div>
                  <div class="muted">Key: <?php echo e($row['row_id']); ?></div>
                </td>
                <td><?php echo e($row['no_wa']); ?></td>
                <td><?php echo e($row['domisili']); ?></td>
                <td><?php echo e($shortAduan); ?></td>
                <td>
                  <?php if (!empty($file)): ?>
                    <a class="btn-sm btn-link" href="<?php echo e($fileUrl); ?>" target="_blank">Lihat</a>
                  <?php else: ?>
                    <span class="muted">-</span>
                  <?php endif; ?>
                </td>
                <td class="muted"><?php echo e($row['created_at']); ?></td>
                <td>
                  <div class="actions">
                    <button
                      class="btn-sm btn-detail"
                      type="button"
                      onclick="openDetail(
                        '<?php echo e($row['row_id']); ?>',
                        '<?php echo e($row['tanggal']); ?>',
                        '<?php echo e($row['nama_pengadu']); ?>',
                        '<?php echo e($row['no_wa']); ?>',
                        '<?php echo e($row['domisili']); ?>',
                        `<?php echo e($row['aduan']); ?>`,
                        '<?php echo e($fileUrl); ?>'
                      )"
                    >
                      Detail
                    </button>
                    <?php if (!empty($file)): ?>
                      <a class="btn-sm btn-link" href="<?php echo e($fileUrl); ?>" target="_blank">File</a>
                    <?php endif; ?>
                  </div>
                </td>
              </tr>
            <?php endwhile; ?>
          <?php else: ?>
            <tr>
              <td colspan="9" style="text-align:center;padding:28px;color:#6b7280">
                Belum ada pengaduan yang masuk.
              </td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<!-- MODAL -->
<div class="modal-backdrop" id="modalBackdrop" onclick="closeModal(event)">
  <div class="modal" onclick="event.stopPropagation()">
    <div class="modal-header">
      <h3 id="mTitle">Detail Pengaduan</h3>
      <button class="modal-close" type="button" onclick="closeModal()">Tutup</button>
    </div>
    <div class="modal-body">
      <div class="grid">
        <div class="field">
          <div class="k">KEY</div>
          <div class="v" id="mId">-</div>
        </div>
        <div class="field">
          <div class="k">Tanggal</div>
          <div class="v" id="mTanggal">-</div>
        </div>
        <div class="field">
          <div class="k">Nama Pengadu</div>
          <div class="v" id="mNama">-</div>
        </div>
        <div class="field">
          <div class="k">No. WA</div>
          <div class="v" id="mWa">-</div>
        </div>
        <div class="field" style="grid-column:1/-1">
          <div class="k">Domisili</div>
          <div class="v" id="mDomisili">-</div>
        </div>
      </div>

      <div style="margin-bottom:10px;font-weight:800">Isi Aduan</div>
      <div class="aduan-box" id="mAduan">-</div>

      <div style="margin-top:12px;display:flex;gap:10px;flex-wrap:wrap;align-items:center">
        <div class="muted" style="font-weight:700">File pendukung:</div>
        <a id="mFile" class="btn btn-ghost" href="#" target="_blank" style="display:none">Buka File</a>
        <span id="mNoFile" class="muted">Tidak ada file</span>
      </div>
    </div>
  </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/js/all.min.js"></script>

<script>
  const backdrop = document.getElementById('modalBackdrop');

  function openDetail(id, tanggal, nama, wa, domisili, aduan, fileUrl) {
    document.getElementById('mId').textContent = id || '-';
    document.getElementById('mTanggal').textContent = tanggal || '-';
    document.getElementById('mNama').textContent = nama || '-';
    document.getElementById('mWa').textContent = wa || '-';
    document.getElementById('mDomisili').textContent = domisili || '-';
    document.getElementById('mAduan').textContent = aduan || '-';

    const fileBtn = document.getElementById('mFile');
    const noFile  = document.getElementById('mNoFile');

    if (fileUrl && fileUrl !== '../uploads/pengaduan/') {
      fileBtn.href = fileUrl;
      fileBtn.style.display = 'inline-flex';
      noFile.style.display = 'none';
    } else {
      fileBtn.style.display = 'none';
      noFile.style.display = 'inline';
    }

    backdrop.style.display = 'flex';
  }

  function closeModal(e) {
    backdrop.style.display = 'none';
  }
</script>

</body>
</html>