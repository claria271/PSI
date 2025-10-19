<?php
// proses_keluarga.php
declare(strict_types=1);

// --- SESUAIKAN PATH CONFIG ---
// Jika tambahdata.php berada di folder "user/" dan config di "koneksi/config.php" (di luar folder user),
// gunakan baris berikut:
require_once __DIR__ . '/../koneksi/config.php';

// Jika config.php ternyata sejajar (bukan di parent folder), pakai ini:
// require_once __DIR__ . '/koneksi/config.php';

// Nyalakan exception untuk mysqli agar lebih mudah ditangani
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    // Pastikan metode adalah POST
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        header('Location: tambahdata.php?status=failed');
        exit;
    }

    // Helper: trim semua input
    $input = array_map(static fn($v) => is_string($v) ? trim($v) : $v, $_POST);

    // Ambil & validasi field wajib sesuai form
    $nama_lengkap = $input['nama_lengkap'] ?? '';
    $dapil        = $input['dapil']        ?? '';
    $kecamatan    = $input['kecamatan']    ?? '';

    if ($nama_lengkap === '' || $dapil === '' || $kecamatan === '') {
        // Field required kosong
        header('Location: tambahdata.php?status=failed');
        exit;
    }

    // Field opsional
    $nik               = $input['nik']            ?? null; // opsional
    $no_wa             = $input['no_wa']          ?? null; // opsional
    $alamat            = $input['alamat']         ?? null; // opsional
    $jumlah_anggota    = $input['jumlah_anggota'] ?? null; // opsional (number)
    $jumlah_bekerja    = $input['jumlah_bekerja'] ?? null; // opsional (select 1/2/3)
    $total_penghasilan = $input['total_penghasilan'] ?? null; // opsional (rentang gaji)
    $kenal             = $input['kenal']          ?? null; // Ya / Tidak Pernah
    $sumber            = $input['sumber']         ?? null; // Kegiatan PSI Surabaya / Dari teman atau relasi / Lainnya

    // --- Whitelist nilai select agar data konsisten ---
    $allowedDapil = [
        'Kota Surabaya 1','Kota Surabaya 2','Kota Surabaya 3','Kota Surabaya 4','Kota Surabaya 5'
    ];
    if (!in_array($dapil, $allowedDapil, true)) {
        header('Location: tambahdata.php?status=failed');
        exit;
    }

    // Kumpulan kecamatan per dapil (harus sama dengan di JS halaman tambahdata.php)
    $dapilMap = [
        'Kota Surabaya 1' => ['Bubutan','Genteng','Gubeng','Krembangan','Simokerto','Tegalsari'],
        'Kota Surabaya 2' => ['Kenjeran','Pabean Cantikan','Semampir','Tambaksari'],
        'Kota Surabaya 3' => ['Bulak','Gunung Anyar','Mulyorejo','Rungkut','Sukolilo','Tenggilis Mejoyo','Wonocolo'],
        'Kota Surabaya 4' => ['Gayungan','Jambangan','Sawahan','Sukomanunggal','Wonokromo'],
        'Kota Surabaya 5' => ['Asemrowo','Benowo','Dukuhpakis','Karangpilang','Lakarsantri','Pakal','Sambikerep','Tandes','Wiyung'],
    ];
    if (!isset($dapilMap[$dapil]) || !in_array($kecamatan, $dapilMap[$dapil], true)) {
        header('Location: tambahdata.php?status=failed');
        exit;
    }

    // Normalisasi numeric
    if ($jumlah_anggota !== null && $jumlah_anggota !== '') {
        $jumlah_anggota = (int)$jumlah_anggota;
        if ($jumlah_anggota < 0) $jumlah_anggota = 0;
    } else {
        $jumlah_anggota = null;
    }

    if ($jumlah_bekerja !== null && $jumlah_bekerja !== '') {
        $jumlah_bekerja = (int)$jumlah_bekerja; // form hanya 1/2/3
        if (!in_array($jumlah_bekerja, [1,2,3], true)) {
            $jumlah_bekerja = null;
        }
    } else {
        $jumlah_bekerja = null;
    }

    // Whitelist penghasilan (harus sama persis dengan opsi di form)
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
    $allowedKenal = ['Ya', 'Tidak Pernah'];
    if ($kenal !== null && !in_array($kenal, $allowedKenal, true)) {
        $kenal = null;
    }

    $allowedSumber = ['Kegiatan PSI Surabaya','Dari teman atau relasi','Lainnya'];
    if ($kenal !== 'Ya') {
        // Jika tidak kenal, kosongkan sumber
        $sumber = null;
    } else {
        if ($sumber !== null && !in_array($sumber, $allowedSumber, true)) {
            $sumber = null;
        }
    }

    // Sanitasi ringan untuk teks panjang
    $nik    = ($nik === '') ? null : $nik;
    $no_wa  = ($no_wa === '') ? null : $no_wa;
    $alamat = ($alamat === '') ? null : $alamat;

    // --- Koneksi DB dari config.php ---
    // Harus ada variabel $conn = new mysqli(host, user, pass, db);
    if (!isset($conn) || !($conn instanceof mysqli)) {
        throw new RuntimeException('Koneksi database tidak valid. Pastikan config.php membuat $conn.');
    }
    $conn->set_charset('utf8mb4');

    // --- Query INSERT ---
    $sql = "INSERT INTO keluarga 
        (nama_lengkap, nik, no_wa, alamat, dapil, kecamatan, jumlah_anggota, jumlah_bekerja, total_penghasilan, kenal, sumber, created_at) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param(
        'ssssssiiiss',
        $nama_lengkap,
        $nik,
        $no_wa,
        $alamat,
        $dapil,
        $kecamatan,
        $jumlah_anggota,
        $jumlah_bekerja,
        $total_penghasilan,
        $kenal,
        $sumber
    );
    $stmt->execute();
    $stmt->close();

    // Sukses
    header('Location: tambahdata.php?status=success');
    exit;

} catch (Throwable $e) {
    // Kamu bisa log error ke file untuk debugging (jangan tampilkan detail ke user)
    // error_log('[PROSES_KELUARGA] ' . $e->getMessage());

    header('Location: tambahdata.php?status=failed');
    exit;
}
