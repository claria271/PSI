<?php
// Pastikan file koneksi ada di folder yang sama
include 'koneksi/config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
  // Ambil data dari form
  $nama_lengkap      = $_POST['nama_lengkap'];
  $nik               = $_POST['nik'];
  $no_wa             = $_POST['no_wa'];
  $alamat            = $_POST['alamat'];
  $dapil             = $_POST['dapil'];
  $kecamatan         = $_POST['kecamatan'];
  $jumlah_anggota    = $_POST['jumlah_anggota'];
  $jumlah_bekerja    = $_POST['jumlah_bekerja'];
  $total_penghasilan = $_POST['total_penghasilan'];
  $kenal             = $_POST['kenal'] ?? '';
  $sumber            = $_POST['sumber'] ?? '';

  // Query simpan data ke tabel
  $sql = "INSERT INTO data_keluarga 
  (nama_lengkap, nik, no_wa, alamat, dapil, kecamatan, jumlah_anggota, jumlah_bekerja, total_penghasilan, kenal, sumber)
  VALUES 
  ('$nama_lengkap', '$nik', '$no_wa', '$alamat', '$dapil', '$kecamatan', '$jumlah_anggota', '$jumlah_bekerja', '$total_penghasilan', '$kenal', '$sumber')";

  if (mysqli_query($conn, $sql)) {
    echo "<script>
      alert('✅ Data keluarga berhasil disimpan!');
      window.location.href='tambahdata.php';
    </script>";
  } else {
    echo "<script>
      alert('❌ Terjadi kesalahan: " . mysqli_error($conn) . "');
      window.history.back();
    </script>";
  }

  mysqli_close($conn);
}
?>
