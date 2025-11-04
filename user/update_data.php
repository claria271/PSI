<?php
declare(strict_types=1);
session_start();
include '../koneksi/config.php';

if (!isset($_SESSION['alamat_email'])) {
  header("Location: login.php"); exit;
}

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
$conn->set_charset('utf8mb4');

// --- helpers ---
function only_digits(string $s): int {
  $n = preg_replace('/\D+/', '', $s);
  return (int)($n === null ? 0 : $n);
}

$email  = $_SESSION['alamat_email'];
$userId = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : null;

// --- deteksi kolom yang tersedia di tabel keluarga ---
$hasUserIdCol  = $conn->query("SHOW COLUMNS FROM keluarga LIKE 'user_id'")->num_rows > 0;
$hasEmailCol   = $conn->query("SHOW COLUMNS FROM keluarga LIKE 'alamat_email'")->num_rows > 0;
$hasCreatedAt  = $conn->query("SHOW COLUMNS FROM keluarga LIKE 'created_at'")->num_rows > 0;
$hasUpdatedAt  = $conn->query("SHOW COLUMNS FROM keluarga LIKE 'updated_at'")->num_rows > 0;

// pilih mode relasi
$relMode = 'none';
if ($hasUserIdCol) {
  $relMode = 'user_id';
} elseif ($hasEmailCol) {
  $relMode = 'email';
}

// --- ambil input ---
$id                 = isset($_POST['id']) ? (int)$_POST['id'] : 0;
$nama_lengkap       = $_POST['nama_lengkap']   ?? '';
$nik                = $_POST['nik']            ?? '';
$no_wa              = $_POST['no_wa']          ?? '';
$alamat             = $_POST['alamat']         ?? '';
$dapil              = $_POST['dapil']          ?? '';
$kecamatan          = $_POST['kecamatan']      ?? '';
$jumlah_anggota     = is_numeric($_POST['jumlah_anggota'] ?? '') ? (int)$_POST['jumlah_anggota'] : 0;
$total_penghasilan  = only_digits((string)($_POST['total_penghasilan'] ?? ''));

// validasi minimal
if ($nama_lengkap === '' || $nik === '') {
  header("Location: profil.php?status=failed"); exit;
}

// pastikan nama kolom penghasilan yang ada
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
    $sql = "UPDATE keluarga SET 
              nama_lengkap=?, nik=?, no_wa=?, alamat=?, dapil=?, kecamatan=?, 
              jumlah_anggota=?, $penghasilanCol=?"
              . ($hasUpdatedAt ? ", updated_at=NOW()" : "") .
            " WHERE id=? AND user_id=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('ssssssiiii', $nama_lengkap, $nik, $no_wa, $alamat, $dapil, $kecamatan,
                                   $jumlah_anggota, $total_penghasilan, $id, $userId);
  } elseif ($relMode === 'email') {
    $sql = "UPDATE keluarga SET 
              nama_lengkap=?, nik=?, no_wa=?, alamat=?, dapil=?, kecamatan=?, 
              jumlah_anggota=?, $penghasilanCol=?"
              . ($hasUpdatedAt ? ", updated_at=NOW()" : "") .
            " WHERE id=? AND alamat_email=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('ssssssiiis', $nama_lengkap, $nik, $no_wa, $alamat, $dapil, $kecamatan,
                                    $jumlah_anggota, $total_penghasilan, $id, $email);
  } else {
    // tanpa kolom relasi, hanya berdasarkan id (gunakan dengan hati-hati)
    $sql = "UPDATE keluarga SET 
              nama_lengkap=?, nik=?, no_wa=?, alamat=?, dapil=?, kecamatan=?, 
              jumlah_anggota=?, $penghasilanCol=?"
              . ($hasUpdatedAt ? ", updated_at=NOW()" : "") .
            " WHERE id=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('ssssssiii', $nama_lengkap, $nik, $no_wa, $alamat, $dapil, $kecamatan,
                                   $jumlah_anggota, $total_penghasilan, $id);
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
            (user_id, nama_lengkap, nik, no_wa, alamat, dapil, kecamatan, jumlah_anggota, $penghasilanCol"
            . ($hasEmailCol ? ", alamat_email" : "")
            . ($hasCreatedAt ? ", created_at" : "") .
          ")
          VALUES
            (?, ?, ?, ?, ?, ?, ?, ?, ?" 
            . ($hasEmailCol ? ", ?" : "")
            . ($hasCreatedAt ? ", NOW()" : "") .
          ")";
  $stmt = $conn->prepare($sql);
  if ($hasEmailCol) {
    $stmt->bind_param('issssssii s', $userId, $nama_lengkap, $nik, $no_wa, $alamat, $dapil,
                                   $kecamatan, $jumlah_anggota, $total_penghasilan, $email);
  } else {
    $stmt->bind_param('issssssis',  $userId, $nama_lengkap, $nik, $no_wa, $alamat, $dapil,
                                   $kecamatan, $jumlah_anggota, (string)$total_penghasilan);
  }
} elseif ($relMode === 'email') {
  $sql = "INSERT INTO keluarga
            (alamat_email, nama_lengkap, nik, no_wa, alamat, dapil, kecamatan, jumlah_anggota, $penghasilanCol"
            . ($hasCreatedAt ? ", created_at" : "") .
          ")
          VALUES
            (?, ?, ?, ?, ?, ?, ?, ?, ?" 
            . ($hasCreatedAt ? ", NOW()" : "") .
          ")";
  $stmt = $conn->prepare($sql);
  $stmt->bind_param('sssssssis', $email, $nama_lengkap, $nik, $no_wa, $alamat, $dapil,
                                 $kecamatan, $jumlah_anggota, (string)$total_penghasilan);
} else {
  $sql = "INSERT INTO keluarga
            (nama_lengkap, nik, no_wa, alamat, dapil, kecamatan, jumlah_anggota, $penghasilanCol"
            . ($hasCreatedAt ? ", created_at" : "") .
          ")
          VALUES
            (?, ?, ?, ?, ?, ?, ?, ?" 
            . ($hasCreatedAt ? ", NOW()" : "") .
          ")";
  $stmt = $conn->prepare($sql);
  $stmt->bind_param('ssssssis', $nama_lengkap, $nik, $no_wa, $alamat, $dapil,
                                $kecamatan, $jumlah_anggota, (string)$total_penghasilan);
}

$stmt->execute();
$stmt->close();

header("Location: profil.php?status=created");
exit;
