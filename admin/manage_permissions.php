<?php
// admin/manage_permissions.php
declare(strict_types=1);
session_start();
include '../koneksi/config.php';

// Cek apakah admin sudah login
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
  header("Location: login_admin.php");
  exit;
}

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
$conn->set_charset('utf8mb4');

function h($v) { 
  return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); 
}

// Ambil semua permission requests dengan JOIN ke tabel login dan keluarga
$query = "
  SELECT 
    epr.*,
    l.nama_lengkap as user_name,
    l.alamat_email as user_email,
    k.nama_lengkap as keluarga_name,
    k.nik,
    k.alamat as keluarga_alamat
  FROM edit_permission_requests epr
  LEFT JOIN login l ON epr.alamat_email = l.alamat_email
  LEFT JOIN keluarga k ON epr.keluarga_id = k.id
  ORDER BY 
    CASE 
      WHEN epr.status = 'pending' THEN 1
      WHEN epr.status = 'granted' THEN 2
      WHEN epr.status = 'rejected' THEN 3
    END,
    epr.requested_at DESC
";

$result = $conn->query($query);
$requests = [];
while ($row = $result->fetch_assoc()) {
  $requests[] = $row;
}

// Hitung statistik
$pendingCount = 0;
$grantedCount = 0;
$rejectedCount = 0;

foreach ($requests as $req) {
  if ($req['status'] === 'pending') $pendingCount++;
  elseif ($req['status'] === 'granted') $grantedCount++;
  elseif ($req['status'] === 'rejected') $rejectedCount++;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Kelola Permission Requests - Admin</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <style>
    * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Poppins', sans-serif; }
    body { background: #f5f5f5; color: #222; }

    header {
      background: linear-gradient(to right, #1f2937, #111827);
      padding: 16px 40px;
      display: flex;
      justify-content: space-between;
      align-items: center;
      box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    }
    header h1 { color: #fff; font-size: 24px; }
    nav a {
      margin-left: 20px;
      color: #fff;
      text-decoration: none;
      font-weight: 600;
      padding: 8px 16px;
      border-radius: 8px;
      transition: 0.3s;
    }
    nav a:hover { background: rgba(255,255,255,0.1); }
    nav a.active { background: #e60000; }

    .container {
      max-width: 1400px;
      margin: 30px auto;
      padding: 0 20px;
    }

    .stats {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
      gap: 20px;
      margin-bottom: 30px;
    }
    .stat-card {
      background: white;
      padding: 20px;
      border-radius: 10px;
      box-shadow: 0 2px 8px rgba(0,0,0,0.1);
      display: flex;
      align-items: center;
      gap: 15px;
    }
    .stat-icon {
      width: 60px;
      height: 60px;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 28px;
    }
    .stat-icon.pending { background: #fef3c7; }
    .stat-icon.granted { background: #d1fae5; }
    .stat-icon.rejected { background: #fee2e2; }
    .stat-info h3 { font-size: 32px; margin-bottom: 5px; }
    .stat-info p { color: #666; font-size: 14px; }

    .flash {
      padding: 14px 20px;
      border-radius: 10px;
      margin-bottom: 20px;
      font-size: 14px;
    }
    .flash.success { background: #d1fae5; border: 1px solid #6ee7b7; color: #065f46; }
    .flash.error { background: #fee2e2; border: 1px solid #fca5a5; color: #991b1b; }

    .filter-tabs {
      display: flex;
      gap: 10px;
      margin-bottom: 20px;
      flex-wrap: wrap;
    }
    .filter-tab {
      padding: 10px 20px;
      background: white;
      border: 2px solid #e5e7eb;
      border-radius: 8px;
      cursor: pointer;
      font-weight: 600;
      transition: 0.3s;
    }
    .filter-tab:hover { border-color: #9ca3af; }
    .filter-tab.active { background: #e60000; color: white; border-color: #e60000; }

    .requests-table {
      background: white;
      border-radius: 10px;
      box-shadow: 0 2px 8px rgba(0,0,0,0.1);
      overflow: hidden;
    }
    table {
      width: 100%;
      border-collapse: collapse;
    }
    thead {
      background: #f9fafb;
      border-bottom: 2px solid #e5e7eb;
    }
    th {
      padding: 16px;
      text-align: left;
      font-weight: 600;
      color: #374151;
      font-size: 14px;
    }
    td {
      padding: 16px;
      border-bottom: 1px solid #e5e7eb;
      font-size: 14px;
    }
    tbody tr:hover {
      background: #f9fafb;
    }

    .status-badge {
      display: inline-block;
      padding: 6px 12px;
      border-radius: 20px;
      font-size: 12px;
      font-weight: 600;
    }
    .status-badge.pending { background: #fef3c7; color: #92400e; }
    .status-badge.granted { background: #d1fae5; color: #065f46; }
    .status-badge.rejected { background: #fee2e2; color: #991b1b; }

    .action-btn {
      padding: 8px 16px;
      border: none;
      border-radius: 6px;
      cursor: pointer;
      font-weight: 600;
      font-size: 13px;
      transition: 0.3s;
      margin-right: 5px;
    }
    .btn-approve {
      background: #10b981;
      color: white;
    }
    .btn-approve:hover {
      background: #059669;
      transform: translateY(-1px);
    }
    .btn-reject {
      background: #ef4444;
      color: white;
    }
    .btn-reject:hover {
      background: #dc2626;
      transform: translateY(-1px);
    }
    .btn-view {
      background: #3b82f6;
      color: white;
    }
    .btn-view:hover {
      background: #2563eb;
      transform: translateY(-1px);
    }
    .btn-disabled {
      background: #d1d5db;
      color: #6b7280;
      cursor: not-allowed;
    }

    .empty-state {
      text-align: center;
      padding: 60px 20px;
      color: #9ca3af;
    }
    .empty-state svg {
      width: 100px;
      height: 100px;
      margin-bottom: 20px;
      opacity: 0.5;
    }

    /* Modal */
    .modal {
      display: none;
      position: fixed;
      z-index: 1000;
      left: 0;
      top: 0;
      width: 100%;
      height: 100%;
      background: rgba(0,0,0,0.6);
      animation: fadeIn 0.3s;
    }
    @keyframes fadeIn {
      from { opacity: 0; }
      to { opacity: 1; }
    }
    .modal-content {
      background: white;
      margin: 5% auto;
      padding: 0;
      border-radius: 12px;
      width: 90%;
      max-width: 600px;
      box-shadow: 0 10px 40px rgba(0,0,0,0.3);
      animation: slideDown 0.3s;
    }
    @keyframes slideDown {
      from { transform: translateY(-50px); opacity: 0; }
      to { transform: translateY(0); opacity: 1; }
    }
    .modal-header {
      padding: 20px 30px;
      border-bottom: 1px solid #e5e7eb;
      display: flex;
      justify-content: space-between;
      align-items: center;
    }
    .modal-header h3 {
      font-size: 20px;
      color: #111827;
    }
    .close-modal {
      background: none;
      border: none;
      font-size: 28px;
      cursor: pointer;
      color: #9ca3af;
      transition: 0.3s;
    }
    .close-modal:hover {
      color: #111827;
    }
    .modal-body {
      padding: 30px;
    }
    .modal-body label {
      display: block;
      margin-bottom: 8px;
      font-weight: 600;
      color: #374151;
    }
    .modal-body textarea {
      width: 100%;
      padding: 12px;
      border: 1px solid #d1d5db;
      border-radius: 8px;
      font-family: inherit;
      resize: vertical;
      min-height: 100px;
      margin-bottom: 20px;
    }
    .modal-body textarea:focus {
      outline: none;
      border-color: #3b82f6;
      box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
    }
    .modal-footer {
      display: flex;
      gap: 10px;
      justify-content: flex-end;
    }
    .user-info {
      background: #f9fafb;
      padding: 15px;
      border-radius: 8px;
      margin-bottom: 20px;
    }
    .user-info div {
      margin-bottom: 8px;
    }
    .user-info strong {
      color: #374151;
      margin-right: 8px;
    }
  </style>
</head>
<body>
  <header>
    <h1>🔐 Kelola Permission Requests</h1>
    <nav>
      <a href="dashboard_admin.php">Dashboard</a>
      <a href="manage_permissions.php" class="active">Permissions</a>
      <a href="logout_admin.php">Logout</a>
    </nav>
  </header>

  <div class="container">
    <?php if (isset($_GET['status'])): ?>
      <?php if ($_GET['status'] === 'approved'): ?>
        <div class="flash success">✓ Permission berhasil disetujui!</div>
      <?php elseif ($_GET['status'] === 'rejected'): ?>
        <div class="flash success">✓ Permission berhasil ditolak.</div>
      <?php elseif ($_GET['status'] === 'error'): ?>
        <div class="flash error">✗ Terjadi kesalahan. Coba lagi.</div>
      <?php endif; ?>
    <?php endif; ?>

    <!-- Stats -->
    <div class="stats">
      <div class="stat-card">
        <div class="stat-icon pending">⏳</div>
        <div class="stat-info">
          <h3><?= $pendingCount ?></h3>
          <p>Menunggu Persetujuan</p>
        </div>
      </div>
      <div class="stat-card">
        <div class="stat-icon granted">✅</div>
        <div class="stat-info">
          <h3><?= $grantedCount ?></h3>
          <p>Disetujui</p>
        </div>
      </div>
      <div class="stat-card">
        <div class="stat-icon rejected">❌</div>
        <div class="stat-info">
          <h3><?= $rejectedCount ?></h3>
          <p>Ditolak</p>
        </div>
      </div>
    </div>

    <!-- Filter Tabs -->
    <div class="filter-tabs">
      <button class="filter-tab active" onclick="filterRequests('all')">
        Semua (<?= count($requests) ?>)
      </button>
      <button class="filter-tab" onclick="filterRequests('pending')">
        Pending (<?= $pendingCount ?>)
      </button>
      <button class="filter-tab" onclick="filterRequests('granted')">
        Disetujui (<?= $grantedCount ?>)
      </button>
      <button class="filter-tab" onclick="filterRequests('rejected')">
        Ditolak (<?= $rejectedCount ?>)
      </button>
    </div>

    <!-- Table -->
    <div class="requests-table">
      <?php if (empty($requests)): ?>
        <div class="empty-state">
          <p style="font-size: 48px;">📭</p>
          <h3>Tidak Ada Permission Request</h3>
          <p>Belum ada request dari user untuk mengedit data.</p>
        </div>
      <?php else: ?>
        <table>
          <thead>
            <tr>
              <th>User</th>
              <th>Data Keluarga</th>
              <th>Alasan</th>
              <th>Tanggal Request</th>
              <th>Status</th>
              <th>Aksi</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($requests as $req): ?>
              <tr class="request-row" data-status="<?= h($req['status']) ?>">
                <td>
                  <strong><?= h($req['user_name'] ?? 'Unknown') ?></strong><br>
                  <small style="color: #6b7280;"><?= h($req['user_email']) ?></small>
                </td>
                <td>
                  <strong><?= h($req['keluarga_name'] ?? '-') ?></strong><br>
                  <small style="color: #6b7280;">NIK: <?= h($req['nik'] ?? '-') ?></small>
                </td>
                <td>
                  <div style="max-width: 250px; overflow: hidden; text-overflow: ellipsis;">
                    <?= h(substr($req['reason'] ?? 'Tidak ada alasan', 0, 100)) ?>
                    <?= strlen($req['reason'] ?? '') > 100 ? '...' : '' ?>
                  </div>
                </td>
                <td>
                  <?= date('d/m/Y H:i', strtotime($req['requested_at'])) ?>
                </td>
                <td>
                  <span class="status-badge <?= h($req['status']) ?>">
                    <?php
                      switch($req['status']) {
                        case 'pending': echo '⏳ Pending'; break;
                        case 'granted': echo '✅ Disetujui'; break;
                        case 'rejected': echo '❌ Ditolak'; break;
                      }
                    ?>
                  </span>
                </td>
                <td>
                  <?php if ($req['status'] === 'pending'): ?>
                    <button 
                      class="action-btn btn-approve" 
                      onclick="openApproveModal(<?= (int)$req['id'] ?>, '<?= h($req['user_name']) ?>')"
                    >
                      ✓ Setujui
                    </button>
                    <button 
                      class="action-btn btn-reject" 
                      onclick="openRejectModal(<?= (int)$req['id'] ?>, '<?= h($req['user_name']) ?>')"
                    >
                      ✗ Tolak
                    </button>
                  <?php else: ?>
                    <button 
                      class="action-btn btn-view" 
                      onclick="viewDetails(<?= (int)$req['id'] ?>)"
                    >
                      👁 Detail
                    </button>
                  <?php endif; ?>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      <?php endif; ?>
    </div>
  </div>

  <!-- Modal Approve -->
  <div id="approveModal" class="modal">
    <div class="modal-content">
      <div class="modal-header">
        <h3>✅ Setujui Permission Request</h3>
        <button class="close-modal" onclick="closeModal('approveModal')">&times;</button>
      </div>
      <form action="process_permission.php" method="POST">
        <div class="modal-body">
          <input type="hidden" name="action" value="approve">
          <input type="hidden" name="request_id" id="approve_request_id">
          
          <div class="user-info">
            <div><strong>User:</strong> <span id="approve_user_name"></span></div>
          </div>

          <label>Catatan untuk User (opsional)</label>
          <textarea 
            name="admin_notes" 
            placeholder="Contoh: Permission disetujui untuk 24 jam ke depan. Silakan edit data dengan hati-hati."
          ></textarea>
        </div>
        <div class="modal-footer">
          <button type="button" class="action-btn btn-reject" onclick="closeModal('approveModal')">
            Batal
          </button>
          <button type="submit" class="action-btn btn-approve">
            Setujui Permission
          </button>
        </div>
      </form>
    </div>
  </div>

  <!-- Modal Reject -->
  <div id="rejectModal" class="modal">
    <div class="modal-content">
      <div class="modal-header">
        <h3>❌ Tolak Permission Request</h3>
        <button class="close-modal" onclick="closeModal('rejectModal')">&times;</button>
      </div>
      <form action="process_permission.php" method="POST">
        <div class="modal-body">
          <input type="hidden" name="action" value="reject">
          <input type="hidden" name="request_id" id="reject_request_id">
          
          <div class="user-info">
            <div><strong>User:</strong> <span id="reject_user_name"></span></div>
          </div>

          <label>Alasan Penolakan <span style="color: #ef4444;">*</span></label>
          <textarea 
            name="admin_notes" 
            placeholder="Jelaskan mengapa permission ditolak..."
            required
          ></textarea>
        </div>
        <div class="modal-footer">
          <button type="button" class="action-btn btn-approve" onclick="closeModal('rejectModal')">
            Batal
          </button>
          <button type="submit" class="action-btn btn-reject">
            Tolak Permission
          </button>
        </div>
      </form>
    </div>
  </div>

  <script>
    function filterRequests(status) {
      const rows = document.querySelectorAll('.request-row');
      const tabs = document.querySelectorAll('.filter-tab');
      
      tabs.forEach(tab => tab.classList.remove('active'));
      event.target.classList.add('active');
      
      rows.forEach(row => {
        if (status === 'all') {
          row.style.display = '';
        } else {
          row.style.display = row.dataset.status === status ? '' : 'none';
        }
      });
    }

    function openApproveModal(requestId, userName) {
      document.getElementById('approve_request_id').value = requestId;
      document.getElementById('approve_user_name').textContent = userName;
      document.getElementById('approveModal').style.display = 'block';
    }

    function openRejectModal(requestId, userName) {
      document.getElementById('reject_request_id').value = requestId;
      document.getElementById('reject_user_name').textContent = userName;
      document.getElementById('rejectModal').style.display = 'block';
    }

    function closeModal(modalId) {
      document.getElementById(modalId).style.display = 'none';
    }

    function viewDetails(requestId) {
      alert('Feature detail request akan segera ditambahkan!');
    }

    window.onclick = function(event) {
      if (event.target.classList.contains('modal')) {
        event.target.style.display = 'none';
      }
    }
  </script>
</body>
</html>