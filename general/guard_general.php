<?php
// general/guard_general.php
session_start();

// Belum login
if (!isset($_SESSION['role'])) {
    header("Location: ../login.php");
    exit();
}

// Bukan role general
if ($_SESSION['role'] !== 'general') {
    // optional: bisa diarahkan sesuai role
    if ($_SESSION['role'] === 'admin') {
        header("Location: ../admin/dashboardadmin.php");
    } elseif ($_SESSION['role'] === 'user') {
        header("Location: ../user/dashboard.php");
    } else {
        header("Location: ../login.php");
    }
    exit();
}
