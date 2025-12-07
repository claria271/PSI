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
    $sumber_auto       = $in['sumber_auto'] ?? null; // 🔹 Ambil nilai auto dari hidden input

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
    
    // Fix: Terima semua nilai jumlah_bekerja termasuk ">5"
    if ($jumlah_bekerja !== null && $jumlah_bekerja !== '') {
        // Jika bukan angka (misalnya ">5"), simpan sebagai string
        $jumlah_bekerja = trim($jumlah_bekerja);
    } else {
        $jumlah_bekerja = null;
    }

    // Validasi total penghasilan
    if ($total_penghasilan !== null && $total_penghasilan !== '') {
        // Hilangkan titik pemisah ribuan sebelum convert ke integer
        $total_penghasilan = str_replace('.', '', $total_penghasilan);
        $total_penghasilan = (int)$total_penghasilan;
        
        if ($total_penghasilan <= 0) {
            header('Location: tambahdata.php?status=failed&error=penghasilan_invalid');
            exit;
        }
    } else {
        $total_penghasilan = null;
    }

    // 🔹 LOGIC BARU: Kenal & Sumber
    $allowedKenal  = ['Ya','Tidak'];
    $allowedSumber = ['Kegiatan PSI Surabaya','Dari teman atau relasi','Lainnya','Tidak Kenal'];
    
    // Validasi kenal
    if ($kenal !== null && !in_array($kenal, $allowedKenal, true)) {
        $kenal = null;
    }
    
    // Logic sumber berdasarkan kenal
    if ($kenal === 'Ya') {
        // Jika pilih "Ya", ambil dari radio button sumber
        if ($sumber !== null && !in_array($sumber, $allowedSumber, true)) {
            $sumber = null;
        }
    } else if ($kenal === 'Tidak') {
        // Jika pilih "Tidak", ambil dari sumber_auto (yang otomatis set ke "Tidak Kenal")
        $sumber = $sumber_auto === 'Tidak Kenal' ? 'Tidak Kenal' : null;
    } else {
        // Jika kenal tidak dipilih, set keduanya null
        $sumber = null;
    }

    // Kosong -> null
    $nik    = ($nik === '') ? null : $nik;
    $no_wa  = ($no_wa === '') ? null : $no_wa;
    $alamat = ($alamat === '') ? null : $alamat;

    // Validasi format angka
    if ($nik !== null && !preg_match('/^\d{1,17}$/', $nik)) { 
        header('Location: tambahdata.php?status=failed&error=nik'); 
        exit; 
    }

    // Validasi nomor WA
    if ($no_wa !== null) {
        if (!preg_match('/^\+62\d{10,13}$/', $no_wa)) { 
            header('Location: tambahdata.php?status=failed&error=no_wa'); 
            exit; 
        }
    }

    // Koneksi
    if (!isset($conn) || !($conn instanceof mysqli)) {
        throw new RuntimeException('Koneksi database tidak valid.');
    }
    $conn->set_charset('utf8mb4');

    // Cek kolom yang ada
    $hasUserIdCol = $conn->query("SHOW COLUMNS FROM keluarga LIKE 'user_id'")->num_rows > 0;
    $hasEmailCol  = $conn->query("SHOW COLUMNS FROM keluarga LIKE 'alamat_email'")->num_rows > 0;

    // Insert data
    if ($hasUserIdCol) {
        $user_id = $_SESSION['user_id'] ?? null;
        $sql = "INSERT INTO keluarga (user_id, nama_lengkap, nik, no_wa, alamat, dapil, kecamatan, jumlah_anggota, jumlah_bekerja, total_penghasilan, kenal, sumber, created_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param(
            'issssssissss',
            $user_id, $nama_lengkap, $nik, $no_wa, $alamat, $dapil, $kecamatan,
            $jumlah_anggota, $jumlah_bekerja, $total_penghasilan, $kenal, $sumber
        );
    } elseif ($hasEmailCol) {
        $alamat_email = $_SESSION['alamat_email'] ?? null;
        $sql = "INSERT INTO keluarga (alamat_email, nama_lengkap, nik, no_wa, alamat, dapil, kecamatan, jumlah_anggota, jumlah_bekerja, total_penghasilan, kenal, sumber, created_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param(
            'sssssssissss',
            $alamat_email, $nama_lengkap, $nik, $no_wa, $alamat, $dapil, $kecamatan,
            $jumlah_anggota, $jumlah_bekerja, $total_penghasilan, $kenal, $sumber
        );
    } else {
        $sql = "INSERT INTO keluarga (nama_lengkap, nik, no_wa, alamat, dapil, kecamatan, jumlah_anggota, jumlah_bekerja, total_penghasilan, kenal, sumber, created_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param(
            'ssssssissss',
            $nama_lengkap, $nik, $no_wa, $alamat, $dapil, $kecamatan,
            $jumlah_anggota, $jumlah_bekerja, $total_penghasilan, $kenal, $sumber
        );
    }

    $stmt->execute();
    $stmt->close();

    // Redirect ke dashboard
    header('Location: dashboard.php?status=created');
    // Jika beranda ada di root: header('Location: ../dashboard.php?status=created');
    exit;

} catch (Throwable $e) {
    // Debug mode - uncomment untuk melihat error detail
    error_log('[PROSES_KELUARGA] ' . $e->getMessage());
    // Redirect dengan info error (untuk debugging)
    header('Location: tambahdata.php?status=failed&debug=' . urlencode($e->getMessage())); 
    exit;
}