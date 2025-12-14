<?php
// admin/approve_edit_request.php
declare(strict_types=1);
session_start();
include '../koneksi/config.php';

// Pastikan admin login
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../user/login.php");
    exit();
}

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
$conn->set_charset('utf8mb4');

// ✅ PERBAIKAN: Ambil admin_id dari session
$adminEmail = $_SESSION['alamat_email'] ?? '';
$adminId = null;

if ($adminEmail) {
    $stmtAdmin = $conn->prepare("SELECT id FROM login WHERE alamat_email = ?");
    $stmtAdmin->bind_param('s', $adminEmail);
    $stmtAdmin->execute();
    $adminResult = $stmtAdmin->get_result();
    if ($adminRow = $adminResult->fetch_assoc()) {
        $adminId = (int)$adminRow['id'];
    }
    $stmtAdmin->close();
}

// Ambil parameter
$requestId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$action = isset($_GET['action']) ? $_GET['action'] : '';

// Validasi input
if ($requestId === 0 || !in_array($action, ['approve', 'reject'])) {
    header("Location: permintaanedit.php?status=invalid");
    exit();
}

// ✅ PERBAIKAN: Cek apakah request masih pending
$checkStmt = $conn->prepare("SELECT status, keluarga_id, user_id FROM edit_requests WHERE id = ?");
$checkStmt->bind_param('i', $requestId);
$checkStmt->execute();
$checkResult = $checkStmt->get_result();

if ($checkResult->num_rows === 0) {
    $checkStmt->close();
    header("Location: permintaanedit.php?status=not_found");
    exit();
}

$currentRequest = $checkResult->fetch_assoc();
$checkStmt->close();

// Jika sudah diproses sebelumnya, redirect
if ($currentRequest['status'] !== 'pending') {
    header("Location: permintaanedit.php?status=already_processed");
    exit();
}

// Validasi keluarga_id harus ada
if (empty($currentRequest['keluarga_id'])) {
    header("Location: permintaanedit.php?status=invalid_data");
    exit();
}

// Update status request
$newStatus = ($action === 'approve') ? 'approved' : 'rejected';

try {
    // ✅ PERBAIKAN: Simpan admin_id yang melakukan approve/reject
    $stmt = $conn->prepare("UPDATE edit_requests 
                            SET status = ?, 
                                approved_by = ?, 
                                updated_at = NOW() 
                            WHERE id = ? AND status = 'pending'");
    $stmt->bind_param('sii', $newStatus, $adminId, $requestId);
    $stmt->execute();
    $affected = $stmt->affected_rows;
    $stmt->close();
    
    if ($affected > 0) {
        // ✅ SUCCESS: Redirect dengan status sukses
        header("Location: permintaanedit.php?status=" . $action . "d");
    } else {
        // Tidak ada yang terubah (mungkin sudah diproses concurrent)
        header("Location: permintaanedit.php?status=not_found");
    }
} catch (Exception $e) {
    // Error database
    error_log("Error approve/reject request: " . $e->getMessage());
    header("Location: permintaanedit.php?status=failed");
}

exit();
?>