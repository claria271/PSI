<?php
// user/request_permission.php
declare(strict_types=1);
session_start();
include '../koneksi/config.php';

if (!isset($_SESSION['alamat_email'])) {
  header("Location: login.php");
  exit;
}

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
$conn->set_charset('utf8mb4');

$email = $_SESSION['alamat_email'];
$userId = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;

// Ambil data dari POST
$keluargaId = isset($_POST['keluarga_id']) ? (int)$_POST['keluarga_id'] : 0;
$reason = isset($_POST['reason']) ? trim($_POST['reason']) : '';

// Validasi
if ($keluargaId <= 0) {
  header("Location: profil.php?status=failed");
  exit;
}

if (empty($reason)) {
  $reason = 'Tidak ada alasan diberikan';
}

// Cek apakah sudah ada pending request untuk keluarga ini
$stmtCheck = $conn->prepare("
  SELECT id FROM edit_permission_requests 
  WHERE keluarga_id = ? AND alamat_email = ? AND status = 'pending'
  LIMIT 1
");
$stmtCheck->bind_param('is', $keluargaId, $email);
$stmtCheck->execute();
$resultCheck = $stmtCheck->get_result();

if ($resultCheck->num_rows > 0) {
  // Sudah ada pending request
  $stmtCheck->close();
  header("Location: profil.php?status=already_requested");
  exit;
}
$stmtCheck->close();

// Insert request permission
$stmtInsert = $conn->prepare("
  INSERT INTO edit_permission_requests 
  (user_id, alamat_email, keluarga_id, reason, status, requested_at)
  VALUES (?, ?, ?, ?, 'pending', NOW())
");

// Fix: user_id bisa null, jadi gunakan 'isis' atau handle null
if ($userId !== null) {
  $stmtInsert->bind_param('isis', $userId, $email, $keluargaId, $reason);
} else {
  // Jika user_id tidak ada di session, set null
  $nullUserId = null;
  $stmtInsert->bind_param('isis', $nullUserId, $email, $keluargaId, $reason);
}

$success = $stmtInsert->execute();
$stmtInsert->close();

// Redirect dengan status
if ($success) {
  header("Location: profil.php?status=permission_requested");
} else {
  header("Location: profil.php?status=failed");
}
exit;
?>