<?php
// user/request_edit.php
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

// Ambil user_id
$stmtUser = $conn->prepare("SELECT id FROM login WHERE alamat_email = ?");
$stmtUser->bind_param('s', $email);
$stmtUser->execute();
$resultUser = $stmtUser->get_result();
$userData = $resultUser->fetch_assoc();
$userId = $userData ? (int)$userData['id'] : null;
$stmtUser->close();

if (!$userId) {
  header("Location: profil.php?status=failed");
  exit;
}

// ✅ PERBAIKAN: Ambil keluarga_id milik user ini
$stmtKeluarga = $conn->prepare("SELECT id FROM keluarga WHERE user_id = ? ORDER BY created_at DESC LIMIT 1");
$stmtKeluarga->bind_param('i', $userId);
$stmtKeluarga->execute();
$resultKeluarga = $stmtKeluarga->get_result();
$keluargaData = $resultKeluarga->fetch_assoc();
$keluargaId = $keluargaData ? (int)$keluargaData['id'] : null;
$stmtKeluarga->close();

// Jika tidak ada data keluarga, tidak bisa request edit
if (!$keluargaId) {
  header("Location: profil.php?status=no_data");
  exit;
}

// Cek apakah sudah ada request pending untuk keluarga_id ini
$stmtCheck = $conn->prepare("SELECT id FROM edit_requests 
                             WHERE user_id = ? 
                             AND keluarga_id = ? 
                             AND status = 'pending'");
$stmtCheck->bind_param('ii', $userId, $keluargaId);
$stmtCheck->execute();
$resultCheck = $stmtCheck->get_result();
$stmtCheck->close();

if ($resultCheck->num_rows > 0) {
  header("Location: profil.php?status=request_exists");
  exit;
}

// ✅ PERBAIKAN: Buat request baru dengan keluarga_id
$stmtInsert = $conn->prepare("INSERT INTO edit_requests 
                               (user_id, keluarga_id, status, created_at) 
                               VALUES (?, ?, 'pending', NOW())");
$stmtInsert->bind_param('ii', $userId, $keluargaId);
$stmtInsert->execute();
$stmtInsert->close();

header("Location: profil.php?status=request_sent");
exit;
?>