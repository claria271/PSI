<?php
session_start();
include '../koneksi/config.php';

// Pastikan admin login
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../user/login.php");
    exit();
}

// Ambil ID dan bentuk bantuan dari parameter
$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
$bentuk_bantuan = isset($_POST['bentuk_bantuan']) ? trim($_POST['bentuk_bantuan']) : '';

if ($id <= 0) {
    $_SESSION['error'] = 'ID tidak valid!';
    header("Location: datakeluarga.php");
    exit();
}

if (empty($bentuk_bantuan)) {
    $_SESSION['error'] = 'Bentuk bantuan harus dipilih!';
    header("Location: datakeluarga.php");
    exit();
}

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
$conn->set_charset('utf8mb4');

try {
    // Ambil data dari tabel keluarga
    $stmt = $conn->prepare("SELECT * FROM keluarga WHERE id = ?");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        $_SESSION['error'] = 'Data tidak ditemukan!';
        header("Location: datakeluarga.php");
        exit();
    }
    
    $data = $result->fetch_assoc();
    
    // Cek apakah data sudah ada di tabel verifikasi
    $stmt_check = $conn->prepare("SELECT id FROM verifikasi WHERE keluarga_id = ?");
    $stmt_check->bind_param('i', $id);
    $stmt_check->execute();
    $check_result = $stmt_check->get_result();
    
    if ($check_result->num_rows > 0) {
        $_SESSION['warning'] = 'Data atas nama "' . $data['nama_lengkap'] . '" sudah pernah diverifikasi sebelumnya!';
        header("Location: datakeluarga.php");
        exit();
    }
    
    // Tentukan siapa yang memverifikasi (ambil nama lengkap admin)
// Tentukan siapa yang memverifikasi
$verified_by = 'Admin';

// Prioritas: ID > Email
if (isset($_SESSION['id']) && is_numeric($_SESSION['id'])) {
    $stmt_admin = $conn->prepare("SELECT nama_lengkap FROM login WHERE id = ? AND role = 'admin' LIMIT 1");
    $stmt_admin->bind_param('i', $_SESSION['id']);
    $stmt_admin->execute();
    $admin_result = $stmt_admin->get_result();
    
    if ($admin_result->num_rows > 0) {
        $admin_data = $admin_result->fetch_assoc();
        $verified_by = !empty($admin_data['nama_lengkap']) ? $admin_data['nama_lengkap'] : 'Admin';
    }
} elseif (isset($_SESSION['alamat_email']) && !empty($_SESSION['alamat_email'])) {
    $stmt_admin = $conn->prepare("SELECT nama_lengkap FROM login WHERE alamat_email = ? AND role = 'admin' LIMIT 1");
    $stmt_admin->bind_param('s', $_SESSION['alamat_email']);
    $stmt_admin->execute();
    $admin_result = $stmt_admin->get_result();
    
    if ($admin_result->num_rows > 0) {
        $admin_data = $admin_result->fetch_assoc();
        $verified_by = !empty($admin_data['nama_lengkap']) ? $admin_data['nama_lengkap'] : 'Admin';
    }
}
    
    // Insert data ke tabel verifikasi (DENGAN KOLOM BANTUAN)
    $stmt_insert = $conn->prepare("
        INSERT INTO verifikasi (
            keluarga_id,
            nama_lengkap,
            nik,
            no_wa,
            alamat,
            dapil,
            kecamatan,
            jumlah_anggota,
            jumlah_bekerja,
            total_penghasilan,
            kenal,
            sumber,
            bantuan,
            status_verifikasi,
            verified_at,
            verified_by
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Terverifikasi', NOW(), ?)
    ");
    
    $stmt_insert->bind_param(
        'issssssiisssss',
        $data['id'],
        $data['nama_lengkap'],
        $data['nik'],
        $data['no_wa'],
        $data['alamat'],
        $data['dapil'],
        $data['kecamatan'],
        $data['jumlah_anggota'],
        $data['jumlah_bekerja'],
        $data['total_penghasilan'],
        $data['kenal'],
        $data['sumber'],
        $bentuk_bantuan,
        $verified_by
    );
    
    if ($stmt_insert->execute()) {
        // Update timestamp di tabel keluarga
        $stmt_update = $conn->prepare("UPDATE keluarga SET updated_at = NOW() WHERE id = ?");
        $stmt_update->bind_param('i', $id);
        $stmt_update->execute();
        
        // Set success message dengan informasi detail + bantuan
        $_SESSION['success'] = '✓ Data berhasil diverifikasi oleh ' . $verified_by . '! Data atas nama "' . $data['nama_lengkap'] . '" (NIK: ' . $data['nik'] . ') telah disimpan dengan bantuan: ' . $bentuk_bantuan;
        
        // Redirect ke halaman data keluarga untuk melihat highlight hijau
        header("Location: datakeluarga.php");
        exit();
    } else {
        throw new Exception('Gagal menyimpan data verifikasi ke database');
    }
    
} catch (Exception $e) {
    $_SESSION['error'] = 'Terjadi kesalahan: ' . $e->getMessage();
    header("Location: datakeluarga.php");
    exit();
}
?>