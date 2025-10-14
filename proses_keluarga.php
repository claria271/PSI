<?php
include 'koneksi/config.php'; // pastikan kamu punya koneksi.php

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nama = $_POST['nama_lengkap'];
    $nik = $_POST['nik'];
    $no_wa = $_POST['no_wa'];
    $alamat = $_POST['alamat'];
    $dapil = $_POST['dapil'];
    $kecamatan = $_POST['kecamatan'];
    $jumlah_anggota = $_POST['jumlah_anggota'];
    $jumlah_bekerja = $_POST['jumlah_bekerja'];
    $total_penghasilan = $_POST['total_penghasilan'];
    $kenal = $_POST['kenal'] ?? '';
    $sumber = $_POST['sumber'] ?? '';

    $sql = "INSERT INTO data_keluarga 
            (nama_lengkap, nik, no_wa, alamat, dapil, kecamatan, jumlah_anggota, jumlah_bekerja, total_penghasilan, kenal, sumber)
            VALUES ('$nama', '$nik', '$no_wa', '$alamat', '$dapil', '$kecamatan', '$jumlah_anggota', '$jumlah_bekerja', '$total_penghasilan', '$kenal', '$sumber')";

    if (mysqli_query($conn, $sql)) {
        header("Location: tambahdata.php?status=success");
    } else {
        header("Location: tambahdata.php?status=failed");
    }
    exit();
}
?>
