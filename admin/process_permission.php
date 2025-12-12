<?php
// admin/process_permission.php
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

// Ambil data POST
$action = isset($_POST['action']) ? $_POST['action'] : '';
$requestId = isset($_POST['request_id']) ? (int)$_POST['request_id'] : 0;
$adminNotes = isset($_POST['admin_notes']) ? trim($_POST['admin_notes']) : '';
$adminEmail = isset($_SESSION['admin_email']) ? $_SESSION['admin_email'] : 'admin';

// Validasi
if (!in_array($action, ['approve', 'reject']) || $requestId <= 0) {
  header("Location: manage_permissions.php?status=error");
  exit;
}

// Ambil data request
$stmtGet = $conn->prepare("
  SELECT * FROM edit_permission_requests 
  WHERE id = ? AND status = 'pending'
");
$stmtGet->bind_param('i', $requestId);
$stmtGet->execute();
$request = $stmtGet->get_result()->fetch_assoc();
$stmtGet->close();

if (!$request) {
  header("Location: manage_permissions.php?status=error");
  exit;
}

try {
  $conn->begin_transaction();
  
  if ($action === 'approve') {
    // Update status request menjadi granted
    $stmtUpdate = $conn->prepare("
      UPDATE edit_permission_requests 
      SET status = 'granted',
          processed_at = NOW(),
          processed_by = ?,
          admin_notes = ?
      WHERE id = ?
    ");
    $stmtUpdate->bind_param('ssi', $adminEmail, $adminNotes, $requestId);
    $stmtUpdate->execute();
    $stmtUpdate->close();
    
    // Commit transaction
    $conn->commit();
    
    header("Location: manage_permissions.php?status=approved");
    exit;
    
  } elseif ($action === 'reject') {
    // Validasi admin_notes wajib diisi untuk reject
    if (empty($adminNotes)) {
      $conn->rollback();
      header("Location: manage_permissions.php?status=error");
      exit;
    }
    
    // Update status request menjadi rejected
    $stmtUpdate = $conn->prepare("
      UPDATE edit_permission_requests 
      SET status = 'rejected',
          processed_at = NOW(),
          processed_by = ?,
          admin_notes = ?
      WHERE id = ?
    ");
    $stmtUpdate->bind_param('ssi', $adminEmail, $adminNotes, $requestId);
    $stmtUpdate->execute();
    $stmtUpdate->close();
    
    // Commit transaction
    $conn->commit();
    
    header("Location: manage_permissions.php?status=rejected");
    exit;
  }
  
} catch (Exception $e) {
  $conn->rollback();
  error_log("Error processing permission: " . $e->getMessage());
  header("Location: manage_permissions.php?status=error");
  exit;
}

// Fallback
header("Location: manage_permissions.php?status=error");
exit;
?>