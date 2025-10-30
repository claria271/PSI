<?php
// user/proses_keluarga.php
declare(strict_types=1);
session_start();

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'user') {
    header('Location: login.php'); exit;
}

require_once __DIR__ . '/../koneksi/config.php';
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        header('Location: tambahdata.php?status=failed'); exit;
    }

    // Trim input
    $in = array_map(static fn($v) => is_string($v) ? trim($v) : $v, $_POST);

    // Wajib
    $nama_lengkap = $in['nama_lengkap'] ?? '';
    $dapil        = $in['dapil'] ?? '';
    $kecamatan    = $in['kecamatan'] ?? '';
    if ($nama_lengkap === '' || $dapil === '' || $kecamatan === '') {
        header('Location: tambahdata.php?status=failed'); exit;
    }

    // Opsional
    $nik               = $in['nik'] ?? null;
    $no_wa             = $in['no_wa'] ?? null;
    $alamat            = $in['alamat'] ?? null;
    $jumlah_anggota    = $in['jumlah_anggota'] ?? null;
    $jumlah_bekerja    = $in['jumlah_bekerja'] ?? null;
    $total_penghasilan = $in['total_penghasilan'] ?? null;
    $kenal             = $in['kenal'] ?? null;
    $sumber            = $in['sumber'] ?? null;

    // Whitelist Dapil & Kecamatan
    $allowedDapil = ['Kota Surabaya 1','Kota Surabaya 2','Kota Surabaya 3','Kota Surabaya 4','Kota Surabaya 5'];
    $dapilMap = [
        'Kota Surabaya 1' => ['Bubutan','Genteng','Gubeng','Krembangan','Simokerto','Tegalsari'],
        'Kota Surabaya 2' => ['Kenjeran','Pabean Cantikan','Semampir','Tambaksari'],
        'Kota Surabaya 3' => ['Bulak','Gunung Anyar','Mulyorejo','Rungkut','Sukolilo','Tenggilis Mejoyo','Wonocolo'],
        'Kota Surabaya 4' => ['Gayungan','Jambangan','Sawahan','Sukomanunggal','Wonokromo'],
        'Kota Surabaya 5' => ['Asemrowo','Benowo','Dukuhpakis','Karangpilang','Lakarsantri','Pakal','Sambikerep','Tandes','Wiyung'],
    ];
    if (!in_array($dapil, $allowedDapil, true) || !isset($dapilMap[$dapil]) || !in_array($kecamatan, $dapilMap[$dapil], true)) {
        header('Location: tambahdata.php?status=failed'); exit;
    }

    // Normalisasi angka
    $jumlah_anggota = ($jumlah_anggota !== null && $jumlah_anggota !== '') ? max((int)$jumlah_anggota, 0) : null;
    $jumlah_bekerja = ($jumlah_bekerja !== null && $jumlah_bekerja !== '') ? (int)$jumlah_bekerja : null;
    if ($jumlah_bekerja !== null && !in_array($jumlah_bekerja, [1,2,3], true)) $jumlah_bekerja = null;

    // Whitelist penghasilan
    $allowedPenghasilan = [
        '< Rp 1.000.000',
        'Rp 1.000.000 - Rp 3.000.000',
        'Rp 3.000.000 - Rp 5.000.000',
        '> Rp 5.000.000',
    ];
    if ($total_penghasilan !== null && $total_penghasilan !== '' && !in_array($total_penghasilan, $allowedPenghasilan, true)) {
        $total_penghasilan = null;
    }

    // Kenal & sumber
    $allowedKenal  = ['Ya','Tidak Pernah'];
    $allowedSumber = ['Kegiatan PSI Surabaya','Dari teman atau relasi','Lainnya'];
    if ($kenal !== null && !in_array($kenal, $allowedKenal, true)) $kenal = null;
    if ($kenal !== 'Ya') { $sumber = null; }
    elseif ($sumber !== null && !in_array($sumber, $allowedSumber, true)) { $sumber = null; }

    // Kosong -> null
    $nik    = ($nik === '') ? null : $nik;
    $no_wa  = ($no_wa === '') ? null : $no_wa;
    $alamat = ($alamat === '') ? null : $alamat;

    // Validasi format angka
    if ($nik !== null && !preg_match('/^\d{1,17}$/', $nik))   { header('Location: tambahdata.php?status=failed&error=nik'); exit; }
    if ($no_wa !== null && !preg_match('/^\d{1,13}$/', $no_wa)) { header('Location: tambahdata.php?status=failed&error=no_wa'); exit; }

    // Koneksi
    if (!isset($conn) || !($conn instanceof mysqli)) {
        throw new RuntimeException('Koneksi database tidak valid.');
    }
    $conn->set_charset('utf8mb4');

    // (Opsional) simpan relasi user jika tabel keluarga punya user_id/alamat_email
    $hasUserIdCol = $conn->query("SHOW COLUMNS FROM keluarga LIKE 'user_id'")->num_rows > 0;
    $hasEmailCol  = $conn->query("SHOW COLUMNS FROM keluarga LIKE 'alamat_email'")->num_rows > 0;

    if ($hasUserIdCol) {
        $user_id = $_SESSION['user_id'] ?? null;
        $sql = "INSERT INTO keluarga (user_id, nama_lengkap, nik, no_wa, alamat, dapil, kecamatan, jumlah_anggota, jumlah_bekerja, total_penghasilan, kenal, sumber, created_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param(
            'issssssiiiss',
            $user_id, $nama_lengkap, $nik, $no_wa, $alamat, $dapil, $kecamatan,
            $jumlah_anggota, $jumlah_bekerja, $total_penghasilan, $kenal, $sumber
        );
    } elseif ($hasEmailCol) {
        $alamat_email = $_SESSION['alamat_email'] ?? null;
        $sql = "INSERT INTO keluarga (alamat_email, nama_lengkap, nik, no_wa, alamat, dapil, kecamatan, jumlah_anggota, jumlah_bekerja, total_penghasilan, kenal, sumber, created_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param(
            'sssssssiiiss',
            $alamat_email, $nama_lengkap, $nik, $no_wa, $alamat, $dapil, $kecamatan,
            $jumlah_anggota, $jumlah_bekerja, $total_penghasilan, $kenal, $sumber
        );
    } else {
        $sql = "INSERT INTO keluarga (nama_lengkap, nik, no_wa, alamat, dapil, kecamatan, jumlah_anggota, jumlah_bekerja, total_penghasilan, kenal, sumber, created_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param(
            'ssssssiiiss',
            $nama_lengkap, $nik, $no_wa, $alamat, $dapil, $kecamatan,
            $jumlah_anggota, $jumlah_bekerja, $total_penghasilan, $kenal, $sumber
        );
    }

    $stmt->execute();
    $stmt->close();

    // → Setelah tambah data, langsung menuju Beranda
    // Jika beranda ada di /user/dashboard.php:
    header('Location: dashboard.php?status=created');
    // Jika beranda ada di root: header('Location: ../dashboard.php?status=created');
    exit;

} catch (Throwable $e) {
    // error_log('[PROSES_KELUARGA] ' . $e->getMessage());
    header('Location: tambahdata.php?status=failed'); exit;
}
