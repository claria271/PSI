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
    $_SESSION['error'] = 'ID tidak valid!';
    header("Location: verifikasi.php");
    exit();
}

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
$conn->set_charset('utf8mb4');

try {
    // Ambil data verifikasi sebelum dihapus (untuk log)
    $stmt = $conn->prepare("SELECT nama_lengkap, nik FROM verifikasi WHERE id = ?");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        $_SESSION['error'] = 'Data tidak ditemukan!';
        header("Location: verifikasi.php");
        exit();
    }
    
    $data = $result->fetch_assoc();
    
    // Hapus data dari tabel verifikasi
    $stmt_delete = $conn->prepare("DELETE FROM verifikasi WHERE id = ?");
    $stmt_delete->bind_param('i', $id);
    
    if ($stmt_delete->execute()) {
        $_SESSION['success'] = '✓ Data verifikasi atas nama "' . $data['nama_lengkap'] . '" (NIK: ' . $data['nik'] . ') berhasil dihapus dari tabel verifikasi!';
    } else {
        throw new Exception('Gagal menghapus data verifikasi');
    }
    
} catch (Exception $e) {
    $_SESSION['error'] = 'Terjadi kesalahan: ' . $e->getMessage();
}

header("Location: verifikasi.php");
exit();
?>