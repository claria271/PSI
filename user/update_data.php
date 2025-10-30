<?php
// user/update_data.php
declare(strict_types=1);
session_start();

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'user' || !isset($_SESSION['alamat_email'])) {
  header('Location: login.php'); exit;
}

require_once __DIR__ . '/../koneksi/config.php';
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
  if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: profil.php?status=failed'); exit;
  }

  $conn->set_charset('utf8mb4');

  // --- Helper ---
  $trim = static fn($v) => is_string($v) ? trim($v) : $v;
  $in = array_map($trim, $_POST);

  $alamat_email = $_SESSION['alamat_email'];
  $user_id = $_SESSION['user_id'] ?? null;

  // --- Input utama (wajib) ---
  $nama_lengkap = $in['nama_lengkap'] ?? '';
  $dapil        = $in['dapil'] ?? '';
  $kecamatan    = $in['kecamatan'] ?? '';

  if ($nama_lengkap === '' || $dapil === '' || $kecamatan === '') {
    header('Location: profil.php?status=failed&reason=required'); exit;
  }

  // --- Input opsional (boleh kosong) ---
  $nik               = $in['nik'] ?? null;
  $no_wa             = $in['no_wa'] ?? null;
  $alamat            = $in['alamat'] ?? null;
  $jumlah_anggota    = $in['jumlah_anggota'] ?? null;
  $jumlah_bekerja    = $in['jumlah_bekerja'] ?? null;           // tidak ada di form kamu, tapi disiapkan
  $total_penghasilan = $in['total_penghasilan'] ?? null;
  $kenal             = $in['kenal'] ?? null;                     // tidak ada di form kamu, tapi disiapkan
  $sumber            = $in['sumber'] ?? null;                    // tidak ada di form kamu, tapi disiapkan

  // --- Whitelist dapil & kecamatan (sama seperti proses_keluarga.php) ---
  $allowedDapil = ['Kota Surabaya 1','Kota Surabaya 2','Kota Surabaya 3','Kota Surabaya 4','Kota Surabaya 5'];
  $dapilMap = [
    'Kota Surabaya 1' => ['Bubutan','Genteng','Gubeng','Krembangan','Simokerto','Tegalsari'],
    'Kota Surabaya 2' => ['Kenjeran','Pabean Cantikan','Semampir','Tambaksari'],
    'Kota Surabaya 3' => ['Bulak','Gunung Anyar','Mulyorejo','Rungkut','Sukolilo','Tenggilis Mejoyo','Wonocolo'],
    'Kota Surabaya 4' => ['Gayungan','Jambangan','Sawahan','Sukomanunggal','Wonokromo'],
    'Kota Surabaya 5' => ['Asemrowo','Benowo','Dukuhpakis','Karangpilang','Lakarsantri','Pakal','Sambikerep','Tandes','Wiyung'],
  ];
  if (!in_array($dapil, $allowedDapil, true) || !isset($dapilMap[$dapil]) || !in_array($kecamatan, $dapilMap[$dapil], true)) {
    header('Location: profil.php?status=failed&reason=dapil'); exit;
  }

  // --- Normalisasi angka ---
  $jumlah_anggota = ($jumlah_anggota !== null && $jumlah_anggota !== '') ? max((int)$jumlah_anggota, 0) : null;
  $jumlah_bekerja = ($jumlah_bekerja !== null && $jumlah_bekerja !== '') ? (int)$jumlah_bekerja : null;
  if ($jumlah_bekerja !== null && !in_array($jumlah_bekerja, [1,2,3], true)) $jumlah_bekerja = null;

  // --- Whitelist penghasilan ---
  $allowedPenghasilan = [
    '< Rp 1.000.000',
    'Rp 1.000.000 - Rp 3.000.000',
    'Rp 3.000.000 - Rp 5.000.000',
    '> Rp 5.000.000',
  ];
  if ($total_penghasilan !== null && $total_penghasilan !== '' && !in_array($total_penghasilan, $allowedPenghasilan, true)) {
    $total_penghasilan = null;
  }

  // --- Kenal & Sumber ---
  $allowedKenal  = ['Ya','Tidak Pernah'];
  $allowedSumber = ['Kegiatan PSI Surabaya','Dari teman atau relasi','Lainnya'];
  if ($kenal !== null && !in_array($kenal, $allowedKenal, true)) $kenal = null;
  if ($kenal !== 'Ya') { $sumber = null; }
  elseif ($sumber !== null && !in_array($sumber, $allowedSumber, true)) { $sumber = null; }

  // --- Kosong -> null ---
  $nik    = ($nik === '') ? null : $nik;
  $no_wa  = ($no_wa === '') ? null : $no_wa;
  $alamat = ($alamat === '') ? null : $alamat;

  // --- Validasi format nik/no_wa ---
  if ($nik !== null && !preg_match('/^\d{1,17}$/', $nik))     { header('Location: profil.php?status=failed&error=nik'); exit; }
  if ($no_wa !== null && !preg_match('/^\d{1,13}$/', $no_wa)) { header('Location: profil.php?status=failed&error=no_wa'); exit; }

  // --- Cek skema relasi di tabel keluarga ---
  $hasUserIdCol = $conn->query("SHOW COLUMNS FROM keluarga LIKE 'user_id'")->num_rows > 0;
  $hasEmailCol  = $conn->query("SHOW COLUMNS FROM keluarga LIKE 'alamat_email'")->num_rows > 0;

  // --- Tentukan target record ---
  $targetId = isset($in['id']) && $in['id'] !== '' ? (int)$in['id'] : null;

  // Verifikasi kepemilikan bila ada id
  if ($targetId !== null) {
    if ($hasUserIdCol && $user_id !== null) {
      $stmt = $conn->prepare("SELECT id FROM keluarga WHERE id = ? AND user_id = ? LIMIT 1");
      $stmt->bind_param('ii', $targetId, $user_id);
    } elseif ($hasEmailCol) {
      $stmt = $conn->prepare("SELECT id FROM keluarga WHERE id = ? AND alamat_email = ? LIMIT 1");
      $stmt->bind_param('is', $targetId, $alamat_email);
    } else {
      $stmt = $conn->prepare("SELECT id FROM keluarga WHERE id = ? LIMIT 1");
      $stmt->bind_param('i', $targetId);
    }
    $stmt->execute();
    $ownRes = $stmt->get_result();
    $owns = (bool)$ownRes->fetch_row();
    $stmt->close();
    if (!$owns) { $targetId = null; } // jatuh ke latest
  }

  // Kalau tidak ada id valid, ambil id terbaru milik user
  if ($targetId === null) {
    if ($hasUserIdCol && $user_id !== null) {
      $stmt = $conn->prepare("SELECT id FROM keluarga WHERE user_id = ? ORDER BY created_at DESC, id DESC LIMIT 1");
      $stmt->bind_param('i', $user_id);
    } elseif ($hasEmailCol) {
      $stmt = $conn->prepare("SELECT id FROM keluarga WHERE alamat_email = ? ORDER BY created_at DESC, id DESC LIMIT 1");
      $stmt->bind_param('s', $alamat_email);
    } else {
      // tanpa relasi: tidak bisa memastikan milik user → anggap belum ada
      $stmt = $conn->prepare("SELECT id FROM keluarga ORDER BY created_at DESC, id DESC LIMIT 1");
    }
    $stmt->execute();
    $res = $stmt->get_result();
    $row = $res->fetch_assoc();
    $stmt->close();
    $targetId = $row['id'] ?? null;
  }

  // --- Jika tetap tidak ada record → INSERT baru ---
  if ($targetId === null) {
    if ($hasUserIdCol && $user_id !== null) {
      $sql = "INSERT INTO keluarga (user_id, nama_lengkap, nik, no_wa, alamat, dapil, kecamatan, jumlah_anggota, jumlah_bekerja, total_penghasilan, kenal, sumber, created_at)
              VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";
      $stmt = $conn->prepare($sql);
      $stmt->bind_param('issssssiiiss',
        $user_id, $nama_lengkap, $nik, $no_wa, $alamat, $dapil, $kecamatan,
        $jumlah_anggota, $jumlah_bekerja, $total_penghasilan, $kenal, $sumber
      );
    } elseif ($hasEmailCol) {
      $sql = "INSERT INTO keluarga (alamat_email, nama_lengkap, nik, no_wa, alamat, dapil, kecamatan, jumlah_anggota, jumlah_bekerja, total_penghasilan, kenal, sumber, created_at)
              VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";
      $stmt = $conn->prepare($sql);
      $stmt->bind_param('sssssssiiiss',
        $alamat_email, $nama_lengkap, $nik, $no_wa, $alamat, $dapil, $kecamatan,
        $jumlah_anggota, $jumlah_bekerja, $total_penghasilan, $kenal, $sumber
      );
    } else {
      $sql = "INSERT INTO keluarga (nama_lengkap, nik, no_wa, alamat, dapil, kecamatan, jumlah_anggota, jumlah_bekerja, total_penghasilan, kenal, sumber, created_at)
              VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";
      $stmt = $conn->prepare($sql);
      $stmt->bind_param('ssssssiiiss',
        $nama_lengkap, $nik, $no_wa, $alamat, $dapil, $kecamatan,
        $jumlah_anggota, $jumlah_bekerja, $total_penghasilan, $kenal, $sumber
      );
    }
    $stmt->execute();
    $stmt->close();

    header('Location: profil.php?status=created'); exit;
  }

  // --- UPDATE record yang sudah ada ---
  $sql = "UPDATE keluarga
          SET nama_lengkap = ?, nik = ?, no_wa = ?, alamat = ?, dapil = ?, kecamatan = ?,
              jumlah_anggota = ?, jumlah_bekerja = ?, total_penghasilan = ?, kenal = ?, sumber = ?, updated_at = NOW()
          WHERE id = ?";
  $stmt = $conn->prepare($sql);
  $stmt->bind_param('ssssssiiissi',
    $nama_lengkap, $nik, $no_wa, $alamat, $dapil, $kecamatan,
    $jumlah_anggota, $jumlah_bekerja, $total_penghasilan, $kenal, $sumber, $targetId
  );
  $stmt->execute();
  $stmt->close();

  header('Location: profil.php?status=updated'); exit;

} catch (Throwable $e) {
  // error_log('[UPDATE_KELUARGA] ' . $e->getMessage());
  header('Location: profil.php?status=failed'); exit;
}
