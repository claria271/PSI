<?php
// user/update_data.php
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

// ================== UPDATE (dengan approval check) ==================
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
    
    // CEK APPROVAL: Apakah ada approved request?
    $stmtApproval = $conn->prepare("SELECT id FROM edit_requests WHERE user_id = ? AND keluarga_id = ? AND status = 'approved' ORDER BY updated_at DESC LIMIT 1");
    $stmtApproval->bind_param('ii', $userId, $id);
    $stmtApproval->execute();
    $approvalResult = $stmtApproval->get_result();
    
    if ($approvalResult->num_rows === 0) {
      // Tidak ada approval, tolak update
      $stmtApproval->close();
      header("Location: profil.php?status=no_permission");
      exit;
    }
    
    $approvalData = $approvalResult->fetch_assoc();
    $approvalId = (int)$approvalData['id'];
    $stmtApproval->close();
    
    // ✅ PERBAIKAN: Lakukan UPDATE dengan type yang benar
    $sql = "UPDATE keluarga SET 
              nama_lengkap=?, nik=?, no_wa=?, alamat=?, domisili=?, 
              jumlah_anggota=?, jumlah_bekerja=?, $penghasilanCol=?"
              . ($hasUpdatedAt ? ", updated_at=NOW()" : "") .
            " WHERE id=? AND user_id=?";
    $stmt = $conn->prepare($sql);
    
    // ✅ FIX: 10 parameters = 10 type specifiers (s=string, i=integer)
    // nama, nik, no_wa, alamat, domisili = string (s)
    // jumlah_anggota, jumlah_bekerja, total_penghasilan, id, user_id = integer (i)
    $stmt->bind_param('sssssiiiii', 
                      $nama_lengkap,      // s
                      $nik,               // s
                      $no_wa,             // s
                      $alamat,            // s
                      $domisili,          // s
                      $jumlah_anggota,    // i
                      $jumlah_bekerja,    // i
                      $total_penghasilan, // i
                      $id,                // i
                      $userId             // i
    );
    
    $stmt->execute();
    $ok = $stmt->affected_rows;
    $stmt->close();
    
    // ✅ PERUBAHAN UTAMA: Ubah status menjadi 'completed' dan catat waktu edit
    // JANGAN HAPUS data, hanya update status
    $stmtCompleted = $conn->prepare("UPDATE edit_requests 
                                      SET status = 'completed', 
                                          edited_at = NOW(),
                                          updated_at = NOW()
                                      WHERE id = ?");
    $stmtCompleted->bind_param('i', $approvalId);
    $stmtCompleted->execute();
    $stmtCompleted->close();
    
    if ($ok > 0) {
      header("Location: profil.php?status=updated");
      exit;
    } else {
      // Tidak ada perubahan (data sama), tetap tandai sebagai completed
      header("Location: profil.php?status=no_changes");
      exit;
    }
                                   
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
    
    // ✅ PERBAIKAN: Untuk mode email, juga update status ke completed
    // Cari approval jika ada
    $stmtApprovalEmail = $conn->query("SELECT id FROM edit_requests WHERE keluarga_id = $id AND status = 'approved' LIMIT 1");
    if ($stmtApprovalEmail && $stmtApprovalEmail->num_rows > 0) {
      $approvalEmailData = $stmtApprovalEmail->fetch_assoc();
      $approvalEmailId = (int)$approvalEmailData['id'];
      
      // Update ke completed
      $stmtCompletedEmail = $conn->prepare("UPDATE edit_requests 
                                            SET status = 'completed', 
                                                edited_at = NOW(),
                                                updated_at = NOW()
                                            WHERE id = ?");
      $stmtCompletedEmail->bind_param('i', $approvalEmailId);
      $stmtCompletedEmail->execute();
      $stmtCompletedEmail->close();
    }
    
    // ✅ PERBAIKAN: Untuk mode email
    $sql = "UPDATE keluarga SET 
              nama_lengkap=?, nik=?, no_wa=?, alamat=?, domisili=?, 
              jumlah_anggota=?, jumlah_bekerja=?, $penghasilanCol=?"
              . ($hasUpdatedAt ? ", updated_at=NOW()" : "") .
            " WHERE id=? AND alamat_email=?";
    $stmt = $conn->prepare($sql);
    
    // ✅ FIX: 10 parameters dengan type yang benar
    $stmt->bind_param('sssssiiiis', 
                      $nama_lengkap,      // s
                      $nik,               // s
                      $no_wa,             // s
                      $alamat,            // s
                      $domisili,          // s
                      $jumlah_anggota,    // i
                      $jumlah_bekerja,    // i
                      $total_penghasilan, // i
                      $id,                // i
                      $email              // s
    );
    
    $stmt->execute();
    $ok = $stmt->affected_rows;
    $stmt->close();

    header("Location: profil.php?status=" . ($ok > 0 ? "updated" : "failed"));
    exit;
  } else {
    header("Location: profil.php?status=failed");
    exit;
  }
}

// ================== INSERT (tidak perlu approval untuk data baru) ==================

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
    // 10 parameters: user_id, nama, nik, no_wa, alamat, domisili, jumlah_anggota, jumlah_bekerja, penghasilan, email
    $stmt->bind_param('isssssiiis', 
                      $userId,            // i
                      $nama_lengkap,      // s
                      $nik,               // s
                      $no_wa,             // s
                      $alamat,            // s
                      $domisili,          // s
                      $jumlah_anggota,    // i
                      $jumlah_bekerja,    // i
                      $total_penghasilan, // i
                      $email              // s
    );
  } else {
    // 9 parameters: user_id, nama, nik, no_wa, alamat, domisili, jumlah_anggota, jumlah_bekerja, penghasilan
    $stmt->bind_param('isssssiil', 
                      $userId,            // i
                      $nama_lengkap,      // s
                      $nik,               // s
                      $no_wa,             // s
                      $alamat,            // s
                      $domisili,          // s
                      $jumlah_anggota,    // i
                      $jumlah_bekerja,    // i
                      $total_penghasilan  // i
    );
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
  
  // 9 parameters: email, nama, nik, no_wa, alamat, domisili, jumlah_anggota, jumlah_bekerja, penghasilan
  $stmt->bind_param('ssssssiil', 
                    $email,             // s
                    $nama_lengkap,      // s
                    $nik,               // s
                    $no_wa,             // s
                    $alamat,            // s
                    $domisili,          // s
                    $jumlah_anggota,    // i
                    $jumlah_bekerja,    // i
                    $total_penghasilan  // i
  );
} else {
  header("Location: profil.php?status=failed");
  exit;
}

$stmt->execute();
$insertSuccess = $stmt->affected_rows > 0;
$stmt->close();

header("Location: profil.php?status=" . ($insertSuccess ? "created" : "failed"));
exit;
?>