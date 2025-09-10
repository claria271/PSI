<?php
$host = "localhost";
$user = "root"; // default XAMPP
$pass = "";     // default kosong
$db   = "web_psi";

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}
?>
