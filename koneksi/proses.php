<?php
session_start();
include 'config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $alamat_email = $_POST['alamat_email'];
    $password     = $_POST['password'];

    // Ambil data user berdasarkan email
    $sql = "SELECT * FROM login WHERE alamat_email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $alamat_email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();

        // Verifikasi password
        if (password_verify($password, $user['password'])) {
            // Simpan session
            $_SESSION['user_id'] = $user['id']; 
            $_SESSION['nama_lengkap'] = $user['nama_lengkap'];
            $_SESSION['alamat_email'] = $user['alamat_email'];

            // Arahkan ke index
            header("Location:../user/dashboard.php");
            exit();
        } else {
            echo "<script>alert('Password salah!'); window.location.href='login.php';</script>";
        }
    } else {
        echo "<script>alert('Email tidak ditemukan!'); window.location.href='login.php';</script>";
    }
}
?>
