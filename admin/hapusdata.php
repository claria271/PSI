<?php
session_start();
include '../koneksi/config.php';

// Pastikan hanya admin yang bisa akses
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../user/login.php");
    exit();
}

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
$conn->set_charset('utf8mb4');

// Pastikan ada id di URL
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo "<script>
            alert('ID data tidak valid.');
            window.location.href = 'datakeluarga.php';
          </script>";
    exit();
}

$id = (int) $_GET['id'];

// Hapus data dengan prepared statement (lebih aman)
$stmt = $conn->prepare("DELETE FROM keluarga WHERE id = ? LIMIT 1");
$stmt->bind_param('i', $id);

try {
    if ($stmt->execute()) {
        echo "<script>
                alert('Data berhasil dihapus.');
                window.location.href = 'datakeluarga.php';
              </script>";
    } else {
        echo "<script>
                alert('Gagal menghapus data.');
                window.location.href = 'datakeluarga.php';
              </script>";
    }
} catch (Exception $e) {
    // Kalau ada error MySQL
    echo "<script>
            alert('Terjadi kesalahan: " . addslashes($e->getMessage()) . "');
            window.location.href = 'datakeluarga.php';
          </script>";
}
exit();
