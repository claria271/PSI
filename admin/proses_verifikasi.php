<?php
session_start();
include '../koneksi/config.php';

// Pastikan admin login
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../user/login.php");
    exit();
}

// Ambil ID dari parameter
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id <= 0) {
    echo "<script>alert('ID tidak valid!'); window.location.href='datakeluarga.php';</script>";
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
        echo "<script>alert('Data tidak ditemukan!'); window.location.href='datakeluarga.php';</script>";
        exit();
    }
    
    $data = $result->fetch_assoc();
    
    // Cek apakah data sudah ada di tabel verifikasi
    $stmt_check = $conn->prepare("SELECT id FROM verifikasi WHERE keluarga_id = ?");
    $stmt_check->bind_param('i', $id);
    $stmt_check->execute();
    $check_result = $stmt_check->get_result();
    
    if ($check_result->num_rows > 0) {
        echo "<script>
            alert('Data atas nama " . addslashes($data['nama_lengkap']) . " sudah pernah diverifikasi sebelumnya!');
            window.location.href='verifikasi.php';
        </script>";
        exit();
    }
    
    // Tentukan siapa yang memverifikasi
    $verified_by = '';
    if (!empty($_SESSION['alamat_email'])) {
        $verified_by = $_SESSION['alamat_email'];
    } elseif (!empty($_SESSION['username'])) {
        $verified_by = $_SESSION['username'];
    } else {
        $verified_by = 'Admin';
    }
    
    // Insert data ke tabel verifikasi
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
            status_verifikasi,
            verified_at,
            verified_by
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Terverifikasi', NOW(), ?)
    ");
    
    $stmt_insert->bind_param(
        'issssssiiisss',
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
        $verified_by
    );
    
    if ($stmt_insert->execute()) {
        // Update status di tabel keluarga (tambahkan kolom status_verifikasi jika belum ada)
        $stmt_update = $conn->prepare("UPDATE keluarga SET updated_at = NOW() WHERE id = ?");
        $stmt_update->bind_param('i', $id);
        $stmt_update->execute();
        
        // Pop-up sukses dan redirect ke halaman verifikasi
        echo "<script>
            alert('✓ Data telah berhasil disimpan di halaman verifikasi!\\n\\nNama: " . addslashes($data['nama_lengkap']) . "\\nNIK: " . addslashes($data['nik']) . "');
            window.location.href='verifikasi.php';
        </script>";
    } else {
        throw new Exception('Gagal menyimpan data verifikasi');
    }
    
} catch (Exception $e) {
    echo "<script>
        alert('Terjadi kesalahan: " . addslashes($e->getMessage()) . "');
        window.location.href='datakeluarga.php';
    </script>";
    exit();
}
?>