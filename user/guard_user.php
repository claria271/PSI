<?php
// user/guard_user.php
session_start();

/*
  Sesuaikan dengan struktur session kamu.
  Dari kode-kode kamu sebelumnya, kamu pakai:
  - $_SESSION['role']
  - $_SESSION['alamat_email']
*/

if (!isset($_SESSION['alamat_email'])) {
  header("Location: ../user/login.php");
  exit();
}

// role yang boleh masuk dashboard user:
$allowed = ['user']; // kalau kamu mau admin juga bisa lihat dashboard user
$role = $_SESSION['role'] ?? '';

if (!in_array($role, $allowed, true)) {
  header("Location: ../user/login.php");
  exit();
}
