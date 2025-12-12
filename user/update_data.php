<?php
//update_data.php
declare(strict_types=1);
session_start();
include '../koneksi/config.php';

if (!isset($_SESSION['alamat_email'])) {
  header("Location: login.php"); 
  exit;
}

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
$conn->set_charset('utf8mb4');

// --- helpers ---
function only_digits(string $s): int {
  $n = preg_replace('/\D+/', '', $s);
  return (int)($n === null ? 0 : $n);
}

$email = $_SESSION['alamat_email'];

// Ambil user_id dari tabel login
$stmtUser = $conn->prepare("SELECT id FROM login WHERE alamat_email = ?");
$stmtUser->bind_param('s', $email);
$stmtUser->execute();
$resultUser = $stmtUser->get_result();
$userData = $resultUser->fetch_assoc();
$userId = $userData ? (int)$userData['id'] : null;
$stmtUser->close();

if ($userId !== null) {
  $_SESSION['user_id'] = $userId;
}

// --- deteksi kolom ---
$hasUserIdCol  = $conn->query("SHOW COLUMNS FROM keluarga LIKE 'user_id'")->num_rows > 0;
$hasEmailCol   = $conn->query("SHOW COLUMNS FROM keluarga LIKE 'alamat_email'")->num_rows > 0;
$hasCreatedAt  = $conn->query("SHOW COLUMNS FROM keluarga LIKE 'created_at'")->num_rows > 0;
$hasUpdatedAt  = $conn->query("SHOW COLUMNS FROM keluarga LIKE 'updated_at'")->num_rows > 0;

$relMode = 'none';
if ($hasUserIdCol && $userId !== null) {
  $relMode = 'user_id';
} elseif ($hasEmailCol) {
  $relMode = 'email';
}

// --- ambil input ---
$id                 = isset($_POST['id']) ? (int)$_POST['id'] : 0;
$nama_lengkap       = trim($_POST['nama_lengkap'] ?? '');
$nik                = trim($_POST['nik'] ?? '');
$no_wa              = trim($_POST['no_wa'] ?? '');
$alamat             = trim($_POST['alamat'] ?? '');
$domisili           = trim($_POST['domisili'] ?? '');
$jumlah_anggota     = is_numeric($_POST['jumlah_anggota'] ?? '') ? (int)$_POST['jumlah_anggota'] : 0;
$jumlah_bekerja     = is_numeric($_POST['jumlah_bekerja'] ?? '') ? (int)$_POST['jumlah_bekerja'] : 0;
$total_penghasilan  = only_digits((string)($_POST['total_penghasilan'] ?? ''));

// validasi minimal
if ($nama_lengkap === '' || $nik === '' || $no_wa === '' || $alamat === '' || $domisili === '') {
  header("Location: profil.php?status=failed"); 
  exit;
}

// Validasi format nomor WA
if (!preg_match('/^\+62\d{10,13}$/', $no_wa)) {
  header("Location: profil.php?status=failed"); 
  exit;
}

// pastikan nama kolom penghasilan
$penghasilanCol = 'total_penghasilan';
if ($conn->query("SHOW COLUMNS FROM keluarga LIKE 'total_penghasilan'")->num_rows === 0) {
  if ($conn->query("SHOW COLUMNS FROM keluarga LIKE 'penghasilan'")->num_rows > 0) {
    $penghasilanCol = 'penghasilan';
  } elseif ($conn->query("SHOW COLUMNS FROM keluarga LIKE 'penghasilan_total'")->num_rows > 0) {
    $penghasilanCol = 'penghasilan_total';
  }
}

// ================== UPDATE ==================
if ($id > 0) {
  
  if ($relMode === 'user_id' && $userId !== null) {
    // Cek kepemilikan
    $checkStmt = $conn->prepare("SELECT id FROM keluarga WHERE id = ? AND user_id = ?");
    $checkStmt->bind_param('ii', $id, $userId);
    $checkStmt->execute();
    $checkResult = $checkStmt->get_result();
    
    if ($checkResult->num_rows === 0) {
      $checkStmt->close();
      header("Location: profil.php?status=no_access");
      exit;
    }
    $checkStmt->close();
    
    $sql = "UPDATE keluarga SET 
              nama_lengkap=?, nik=?, no_wa=?, alamat=?, domisili=?, 
              jumlah_anggota=?, jumlah_bekerja=?, $penghasilanCol=?"
              . ($hasUpdatedAt ? ", updated_at=NOW()" : "") .
            " WHERE id=? AND user_id=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('sssssiiii', $nama_lengkap, $nik, $no_wa, $alamat, $domisili,
                                   $jumlah_anggota, $jumlah_bekerja, $total_penghasilan,
                                   $id, $userId);
                                   
  } elseif ($relMode === 'email') {
    // Cek kepemilikan
    $checkStmt = $conn->prepare("SELECT id FROM keluarga WHERE id = ? AND alamat_email = ?");
    $checkStmt->bind_param('is', $id, $email);
    $checkStmt->execute();
    $checkResult = $checkStmt->get_result();
    
    if ($checkResult->num_rows === 0) {
      $checkStmt->close();
      header("Location: profil.php?status=no_access");
      exit;
    }
    $checkStmt->close();
    
    $sql = "UPDATE keluarga SET 
              nama_lengkap=?, nik=?, no_wa=?, alamat=?, domisili=?, 
              jumlah_anggota=?, jumlah_bekerja=?, $penghasilanCol=?"
              . ($hasUpdatedAt ? ", updated_at=NOW()" : "") .
            " WHERE id=? AND alamat_email=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('sssssiiiis', $nama_lengkap, $nik, $no_wa, $alamat, $domisili,
                                    $jumlah_anggota, $jumlah_bekerja, $total_penghasilan,
                                    $id, $email);
  } else {
    header("Location: profil.php?status=failed");
    exit;
  }

  $stmt->execute();
  $ok = $stmt->affected_rows;
  $stmt->close();

  header("Location: profil.php?status=" . ($ok > 0 ? "updated" : "failed"));
  exit;
}

// ================== INSERT ==================

if ($relMode === 'user_id' && $userId !== null) {
  $sql = "INSERT INTO keluarga
            (user_id, nama_lengkap, nik, no_wa, alamat, domisili, jumlah_anggota, jumlah_bekerja, $penghasilanCol"
            . ($hasEmailCol ? ", alamat_email" : "")
            . ($hasCreatedAt ? ", created_at" : "")
            . ($hasUpdatedAt ? ", updated_at" : "") .
          ")
          VALUES
            (?, ?, ?, ?, ?, ?, ?, ?, ?" 
            . ($hasEmailCol ? ", ?" : "")
            . ($hasCreatedAt ? ", NOW()" : "")
            . ($hasUpdatedAt ? ", NOW()" : "") .
          ")";
  $stmt = $conn->prepare($sql);
  
  if ($hasEmailCol) {
    $stmt->bind_param('isssssiiss', $userId, $nama_lengkap, $nik, $no_wa, $alamat, $domisili,
                                   $jumlah_anggota, $jumlah_bekerja, $total_penghasilan, 
                                   $email);
  } else {
    $stmt->bind_param('isssssiii', $userId, $nama_lengkap, $nik, $no_wa, $alamat, $domisili,
                                   $jumlah_anggota, $jumlah_bekerja, $total_penghasilan);
  }
  
} elseif ($relMode === 'email') {
  $sql = "INSERT INTO keluarga
            (alamat_email, nama_lengkap, nik, no_wa, alamat, domisili, jumlah_anggota, jumlah_bekerja, $penghasilanCol"
            . ($hasCreatedAt ? ", created_at" : "")
            . ($hasUpdatedAt ? ", updated_at" : "") .
          ")
          VALUES
            (?, ?, ?, ?, ?, ?, ?, ?, ?" 
            . ($hasCreatedAt ? ", NOW()" : "")
            . ($hasUpdatedAt ? ", NOW()" : "") .
          ")";
  $stmt = $conn->prepare($sql);
  $stmt->bind_param('ssssssiis', $email, $nama_lengkap, $nik, $no_wa, $alamat, $domisili,
                                 $jumlah_anggota, $jumlah_bekerja, $total_penghasilan);
} else {
  header("Location: profil.php?status=failed");
  exit;
}

$stmt->execute();
$insertSuccess = $stmt->affected_rows > 0;
$stmt->close();

header("Location: profil.php?status=" . ($insertSuccess ? "created" : "failed"));
exit;