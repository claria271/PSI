
<?php
$host = 'localhost'; // atau IP server database
$username = 'u433620554_web_psi'; // username database
$password = '1Kgkentang'; // ISI PASSWORD DATABASE DI SINI
$database = 'u433620554_nama_database'; // nama database

$conn = new mysqli($host, $username, $password, $database);

if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}
?>
