<?php
//admin/proses_verifikasi.php
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
    
    // 🔥 PERBAIKAN: Ambil nama admin dari tabel KELUARGA, bukan LOGIN
    $verified_by = 'Admin';
    
    if (isset($_SESSION['alamat_email']) && !empty($_SESSION['alamat_email'])) {
        // Ambil user_id dari login terlebih dahulu
        $stmt_login = $conn->prepare("SELECT id FROM login WHERE alamat_email = ? AND role = 'admin' LIMIT 1");
        $stmt_login->bind_param('s', $_SESSION['alamat_email']);
        $stmt_login->execute();
        $login_result = $stmt_login->get_result();
        
        if ($login_result->num_rows > 0) {
            $login_data = $login_result->fetch_assoc();
            $user_id = $login_data['id'];
            
            // Ambil nama lengkap dari tabel keluarga berdasarkan user_id
            $stmt_keluarga = $conn->prepare("SELECT nama_lengkap FROM keluarga WHERE user_id = ? LIMIT 1");
            $stmt_keluarga->bind_param('i', $user_id);
            $stmt_keluarga->execute();
            $keluarga_result = $stmt_keluarga->get_result();
            
            if ($keluarga_result->num_rows > 0) {
                $keluarga_data = $keluarga_result->fetch_assoc();
                $verified_by = !empty($keluarga_data['nama_lengkap']) ? $keluarga_data['nama_lengkap'] : 'Admin';
            }
            $stmt_keluarga->close();
        }
        $stmt_login->close();
    }
    
    // Insert data ke tabel verifikasi (TANPA dapil, kecamatan, sumber, kenal + DENGAN domisili)
    $stmt_insert = $conn->prepare("
        INSERT INTO verifikasi (
            keluarga_id,
            nama_lengkap,
            nik,
            no_wa,
            alamat,
            domisili,
            jumlah_anggota,
            jumlah_bekerja,
            total_penghasilan,
            bantuan,
            status_verifikasi,
            verified_at,
            verified_by
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Terverifikasi', NOW(), ?)
    ");
    
    // Ambil domisili dari keluarga (atau gunakan alamat jika kosong)
    $domisili = !empty($data['domisili']) ? $data['domisili'] : $data['alamat'];
    
    $stmt_insert->bind_param(
        'isssssiidss',
        $data['id'],
        $data['nama_lengkap'],
        $data['nik'],
        $data['no_wa'],
        $data['alamat'],
        $domisili,
        $data['jumlah_anggota'],
        $data['jumlah_bekerja'],
        $data['total_penghasilan'],
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