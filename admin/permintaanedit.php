<?php
// admin/permintaanedit.php
declare(strict_types=1);
session_start();
include '../koneksi/config.php';

// Pastikan admin login
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../user/login.php");
    exit();
}

// Ambil admin_id dari session
$adminId = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : null;
$adminEmail = $_SESSION['alamat_email'] ?? '';

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
$conn->set_charset('utf8mb4');

// ✅ PERBAIKAN: Query disesuaikan dengan struktur tabel login yang hanya punya: id, alamat_email, password, role, foto
// Karena tidak ada nama_lengkap di tabel login, kita gunakan alamat_email saja atau ambil dari tabel keluarga
$sql = "SELECT 
          er.id,
          er.user_id,
          er.keluarga_id,
          er.status,
          er.created_at,
          er.updated_at,
          er.approved_by,
          er.notes,
          er.edited_at,
          l.alamat_email as user_email,
          k.nama_lengkap as keluarga_name,
          k.nik as keluarga_nik,
          k.no_wa as keluarga_wa,
          k.updated_at as keluarga_last_updated,
          admin.alamat_email as approved_by_email
        FROM edit_requests er
        LEFT JOIN login l ON er.user_id = l.id
        LEFT JOIN keluarga k ON er.keluarga_id = k.id
        LEFT JOIN login admin ON er.approved_by = admin.id
        ORDER BY 
          CASE er.status 
            WHEN 'pending' THEN 1
            WHEN 'approved' THEN 2
            WHEN 'completed' THEN 3
            WHEN 'rejected' THEN 4
          END,
          er.created_at DESC";

$result = $conn->query($sql);
$requests = $result->fetch_all(MYSQLI_ASSOC);

// Helper untuk output aman
function e($str) {
    return htmlspecialchars((string)$str, ENT_QUOTES, 'UTF-8');
}

// Hitung statistik
$stats = [
    'pending' => count(array_filter($requests, fn($r) => $r['status'] === 'pending')),
    'approved' => count(array_filter($requests, fn($r) => $r['status'] === 'approved')),
    'completed' => count(array_filter($requests, fn($r) => $r['status'] === 'completed')),
    'rejected' => count(array_filter($requests, fn($r) => $r['status'] === 'rejected'))
];
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Kelola Permintaan Edit Data - Admin PSI</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <style>
    * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Poppins', sans-serif; }
    body { background: #f5f5f5; color: #222; }
    
    header {
      background: linear-gradient(to right, #ffffff, #000000);
      padding: 15px 40px;
      display: flex;
      justify-content: space-between;
      align-items: center;
      box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    }
    header .logo img { height: 40px; }
    header .admin-info {
      color: #fff;
      font-size: 14px;
    }
    header .admin-info a {
      color: #fff;
      text-decoration: none;
      margin-left: 15px;
      padding: 6px 15px;
      background: rgba(255,255,255,0.1);
      border-radius: 5px;
      transition: 0.3s;
    }
    header .admin-info a:hover {
      background: rgba(255,255,255,0.2);
    }
    
    .container {
      max-width: 1400px;
      margin: 30px auto;
      padding: 0 20px;
    }
    
    .back-button-container {
      margin-bottom: 20px;
    }
    
    .btn-back {
      display: inline-flex;
      align-items: center;
      gap: 8px;
      padding: 10px 20px;
      background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
      color: #fff;
      text-decoration: none;
      border-radius: 10px;
      font-size: 14px;
      font-weight: 600;
      box-shadow: 0 2px 8px rgba(59, 130, 246, 0.3);
      transition: all 0.3s;
    }
    
    .btn-back:hover {
      transform: translateY(-2px);
      box-shadow: 0 4px 12px rgba(59, 130, 246, 0.4);
    }
    
    .page-title {
      margin-bottom: 30px;
    }
    .page-title h1 {
      font-size: 32px;
      color: #000;
      margin-bottom: 8px;
      display: flex;
      align-items: center;
      gap: 12px;
    }
    .page-title p {
      color: #666;
      font-size: 14px;
    }
    
    .flash {
      padding: 14px 20px;
      border-radius: 10px;
      margin-bottom: 25px;
      font-size: 14px;
      display: flex;
      align-items: center;
      gap: 10px;
      animation: slideDown 0.3s ease;
    }
    @keyframes slideDown {
      from { transform: translateY(-20px); opacity: 0; }
      to { transform: translateY(0); opacity: 1; }
    }
    .flash.success { 
      background: linear-gradient(135deg, #d1fae5 0%, #a7f3d0 100%);
      border-left: 4px solid #10b981;
      color: #065f46; 
    }
    .flash.fail { 
      background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%);
      border-left: 4px solid #ef4444;
      color: #991b1b; 
    }
    .flash.warning { 
      background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
      border-left: 4px solid #f59e0b;
      color: #78350f; 
    }
    
    .stats {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
      gap: 20px;
      margin-bottom: 30px;
    }
    .stat-card {
      background: linear-gradient(135deg, #fff 0%, #f9fafb 100%);
      padding: 25px;
      border-radius: 16px;
      box-shadow: 0 2px 10px rgba(0,0,0,0.08);
      border: 1px solid #e5e7eb;
      transition: all 0.3s;
      text-align: center;
    }
    .stat-card:hover {
      transform: translateY(-4px);
      box-shadow: 0 8px 24px rgba(0,0,0,0.12);
    }
    .stat-icon {
      font-size: 40px;
      margin-bottom: 12px;
    }
    .stat-number {
      font-size: 42px;
      font-weight: 700;
      margin: 10px 0 5px;
    }
    .stat-label {
      color: #666;
      font-size: 13px;
      font-weight: 600;
      text-transform: uppercase;
      letter-spacing: 0.5px;
    }
    .pending { color: #f59e0b; }
    .approved { color: #3b82f6; }
    .completed { color: #10b981; }
    .rejected { color: #ef4444; }
    
    .table-container {
      background: #fff;
      border-radius: 16px;
      box-shadow: 0 2px 10px rgba(0,0,0,0.08);
      overflow: hidden;
    }
    
    .table-header {
      padding: 20px 25px;
      background: linear-gradient(135deg, #f9fafb 0%, #f3f4f6 100%);
      border-bottom: 2px solid #e5e7eb;
    }
    .table-header h2 {
      font-size: 18px;
      color: #111;
      font-weight: 600;
    }
    
    table {
      width: 100%;
      border-collapse: collapse;
    }
    th, td {
      padding: 16px 20px;
      text-align: left;
    }
    th {
      background: #f9fafb;
      font-weight: 600;
      color: #374151;
      font-size: 13px;
      text-transform: uppercase;
      letter-spacing: 0.5px;
      border-bottom: 2px solid #e5e7eb;
    }
    td {
      border-bottom: 1px solid #f3f4f6;
      font-size: 14px;
    }
    tbody tr {
      transition: all 0.2s;
    }
    tbody tr:hover {
      background: #f9fafb;
    }
    tbody tr:last-child td {
      border-bottom: none;
    }
    
    .status-badge {
      display: inline-flex;
      align-items: center;
      gap: 5px;
      padding: 6px 14px;
      border-radius: 20px;
      font-size: 12px;
      font-weight: 600;
    }
    .status-pending {
      background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
      color: #78350f;
      border: 1px solid #fbbf24;
    }
    .status-approved {
      background: linear-gradient(135deg, #dbeafe 0%, #bfdbfe 100%);
      color: #1e40af;
      border: 1px solid #3b82f6;
    }
    .status-completed {
      background: linear-gradient(135deg, #d1fae5 0%, #a7f3d0 100%);
      color: #065f46;
      border: 1px solid #10b981;
    }
    .status-rejected {
      background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%);
      color: #991b1b;
      border: 1px solid #ef4444;
    }
    
    .action-buttons {
      display: flex;
      gap: 8px;
      flex-wrap: wrap;
    }
    .btn {
      padding: 8px 18px;
      border: none;
      border-radius: 8px;
      font-size: 13px;
      font-weight: 600;
      cursor: pointer;
      transition: all 0.3s;
      text-decoration: none;
      display: inline-flex;
      align-items: center;
      gap: 6px;
    }
    .btn-approve {
      background: linear-gradient(135deg, #10b981 0%, #059669 100%);
      color: #fff;
      box-shadow: 0 2px 8px rgba(16, 185, 129, 0.3);
    }
    .btn-approve:hover {
      transform: translateY(-2px);
      box-shadow: 0 4px 12px rgba(16, 185, 129, 0.4);
    }
    .btn-reject {
      background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
      color: #fff;
      box-shadow: 0 2px 8px rgba(239, 68, 68, 0.3);
    }
    .btn-reject:hover {
      transform: translateY(-2px);
      box-shadow: 0 4px 12px rgba(239, 68, 68, 0.4);
    }
    .btn-view {
      background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
      color: #fff;
      box-shadow: 0 2px 8px rgba(59, 130, 246, 0.3);
    }
    .btn-view:hover {
      transform: translateY(-2px);
      box-shadow: 0 4px 12px rgba(59, 130, 246, 0.4);
    }
    
    .empty-state {
      text-align: center;
      padding: 80px 20px;
      color: #9ca3af;
    }
    .empty-state .icon {
      font-size: 80px;
      margin-bottom: 20px;
      opacity: 0.3;
    }
    .empty-state h3 {
      font-size: 24px;
      margin: 20px 0 10px;
      color: #374151;
    }
    .empty-state p {
      font-size: 14px;
      color: #6b7280;
    }
    
    .user-info strong {
      color: #111;
      font-size: 14px;
    }
    .user-info small {
      color: #6b7280;
      font-size: 12px;
      display: block;
      margin-top: 3px;
    }
    
    .time-display {
      font-size: 12px;
      color: #6b7280;
    }
    
    .admin-badge {
      display: inline-block;
      background: #e0e7ff;
      color: #4338ca;
      padding: 3px 10px;
      border-radius: 12px;
      font-size: 11px;
      font-weight: 600;
      margin-top: 4px;
    }
    
    .edit-timestamp {
      display: inline-block;
      background: #dcfce7;
      color: #166534;
      padding: 3px 10px;
      border-radius: 12px;
      font-size: 11px;
      font-weight: 600;
      margin-top: 4px;
    }
    
    footer {
      margin-top: 40px;
      padding: 20px;
      text-align: center;
      background: linear-gradient(to right, #ffffff, #000000);
      color: #fff;
      font-size: 13px;
    }
    footer img {
      height: 18px;
      vertical-align: middle;
      margin: 0 5px;
      filter: brightness(0) invert(1);
    }
  </style>
</head>
<body>
  <header>
    <div class="logo">
      <img src="../assets/image/logo.png" alt="PSI Logo">
    </div>
    <div class="admin-info">
      👤 Admin: <?= e($adminEmail) ?>
      <a href="dashboardadmin.php">← Kembali</a>
    </div>
  </header>
  
  <div class="container">
    <div class="back-button-container">
      <a href="dashboardadmin.php" class="btn-back">
        ← Kembali ke Dashboard
      </a>
    </div>
    
    <div class="page-title">
      <h1>📋 Kelola Permintaan Edit Data</h1>
      <p>Kelola permintaan perubahan data dari user yang perlu persetujuan</p>
    </div>
    
    <?php if (isset($_GET['status'])): ?>
      <?php if ($_GET['status'] === 'approved'): ?>
        <div class="flash success">✓ Permintaan berhasil disetujui! User dapat mengedit datanya sekarang.</div>
      <?php elseif ($_GET['status'] === 'rejected'): ?>
        <div class="flash success">✓ Permintaan berhasil ditolak.</div>
      <?php elseif ($_GET['status'] === 'failed'): ?>
        <div class="flash fail">✗ Operasi gagal! Silakan coba lagi.</div>
      <?php elseif ($_GET['status'] === 'invalid'): ?>
        <div class="flash warning">⚠ Parameter tidak valid!</div>
      <?php elseif ($_GET['status'] === 'not_found'): ?>
        <div class="flash warning">⚠ Permintaan tidak ditemukan!</div>
      <?php elseif ($_GET['status'] === 'already_processed'): ?>
        <div class="flash warning">⚠ Permintaan sudah diproses sebelumnya!</div>
      <?php elseif ($_GET['status'] === 'invalid_data'): ?>
        <div class="flash fail">✗ Data keluarga tidak valid!</div>
      <?php endif; ?>
    <?php endif; ?>
    
    <div class="stats">
      <div class="stat-card">
        <div class="stat-icon">⏳</div>
        <div class="stat-number pending"><?= $stats['pending'] ?></div>
        <div class="stat-label">Menunggu Persetujuan</div>
      </div>
      <div class="stat-card">
        <div class="stat-icon">📝</div>
        <div class="stat-number approved"><?= $stats['approved'] ?></div>
        <div class="stat-label">Disetujui</div>
      </div>
      <div class="stat-card">
        <div class="stat-icon">✅</div>
        <div class="stat-number completed"><?= $stats['completed'] ?></div>
        <div class="stat-label">Telah Diubah</div>
      </div>
      <div class="stat-card">
        <div class="stat-icon">❌</div>
        <div class="stat-number rejected"><?= $stats['rejected'] ?></div>
        <div class="stat-label">Ditolak</div>
      </div>
    </div>
    
    <div class="table-container">
      <div class="table-header">
        <h2>Daftar Permintaan Edit Data (<?= count($requests) ?> Total)</h2>
      </div>
      
      <?php if (empty($requests)): ?>
        <div class="empty-state">
          <div class="icon">📭</div>
          <h3>Tidak Ada Permintaan</h3>
          <p>Belum ada permintaan edit data dari user</p>
        </div>
      <?php else: ?>
        <table>
          <thead>
            <tr>
              <th style="width: 60px;">ID</th>
              <th>User Email</th>
              <th>Data Keluarga</th>
              <th style="width: 150px;">Status</th>
              <th style="width: 140px;">Tanggal</th>
              <th style="width: 200px;">Aksi</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($requests as $req): ?>
              <tr>
                <td><strong>#<?= $req['id'] ?></strong></td>
                <td>
                  <div class="user-info">
                    <strong><?= e($req['user_email'] ?? 'N/A') ?></strong>
                    <small>User ID: <?= $req['user_id'] ?></small>
                  </div>
                </td>
                <td>
                  <?php if ($req['keluarga_id']): ?>
                    <div class="user-info">
                      <strong><?= e($req['keluarga_name'] ?? '-') ?></strong>
                      <?php if (!empty($req['keluarga_nik'])): ?>
                        <small>NIK: <?= e($req['keluarga_nik']) ?></small>
                      <?php endif; ?>
                    </div>
                  <?php else: ?>
                    <span style="color: #ef4444; font-size: 12px;">⚠ Data tidak ditemukan</span>
                  <?php endif; ?>
                </td>
                <td>
                  <?php
                    $statusClass = 'status-' . $req['status'];
                    $statusText = [
                      'pending' => '⏳ Menunggu',
                      'approved' => '📝 Disetujui',
                      'completed' => '✅ Telah Diubah',
                      'rejected' => '❌ Ditolak'
                    ][$req['status']] ?? $req['status'];
                  ?>
                  <span class="status-badge <?= $statusClass ?>">
                    <?= $statusText ?>
                  </span>
                  <?php if (in_array($req['status'], ['approved', 'completed', 'rejected']) && $req['approved_by_email']): ?>
                    <div class="admin-badge">
                      oleh: <?= e($req['approved_by_email']) ?>
                    </div>
                  <?php endif; ?>
                  <?php if ($req['status'] === 'completed' && $req['edited_at']): ?>
                    <div class="edit-timestamp">
                      Diubah: <?= date('d M Y H:i', strtotime($req['edited_at'])) ?>
                    </div>
                  <?php endif; ?>
                </td>
                <td>
                  <div class="time-display">
                    <?= date('d M Y', strtotime($req['created_at'])) ?><br>
                    <small><?= date('H:i', strtotime($req['created_at'])) ?> WIB</small>
                  </div>
                </td>
                <td>
                  <div class="action-buttons">
                    <?php if ($req['status'] === 'pending'): ?>
                      <?php if ($req['keluarga_id']): ?>
                        <a href="approve_edit_request.php?id=<?= $req['id'] ?>&action=approve" 
                           class="btn btn-approve"
                           onclick="return confirm('✅ Setujui permintaan edit dari <?= e($req['user_email']) ?>?')">
                          ✓ Setujui
                        </a>
                        <a href="approve_edit_request.php?id=<?= $req['id'] ?>&action=reject" 
                           class="btn btn-reject"
                           onclick="return confirm('❌ Tolak permintaan edit dari <?= e($req['user_email']) ?>?')">
                          ✗ Tolak
                        </a>
                      <?php else: ?>
                        <span style="color: #ef4444; font-size: 12px;">Data keluarga tidak valid</span>
                      <?php endif; ?>
                    <?php elseif ($req['status'] === 'approved'): ?>
                      <span style="color: #3b82f6; font-size: 13px; font-weight: 500;">
                        📝 Menunggu user mengedit
                      </span>
                    <?php elseif ($req['status'] === 'completed'): ?>
                      <span style="color: #10b981; font-size: 13px; font-weight: 500;">
                        ✓ Perubahan selesai
                      </span>
                    <?php else: ?>
                      <span style="color: #9ca3af; font-size: 13px; font-weight: 500;">
                        ✗ Sudah ditolak
                      </span>
                    <?php endif; ?>
                  </div>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      <?php endif; ?>
    </div>
  </div>
  
  <footer>
    <img src="../assets/image/logodprd.png" alt="DPRD Logo">
    <img src="../assets/image/psiputih.png" alt="PSI Logo">
    Hak cipta © 2025 - Partai Solidaritas Indonesia
  </footer>
</body>
</html>