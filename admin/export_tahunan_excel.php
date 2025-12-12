<?php
session_start();
require_once '../koneksi/config.php';

// Cek login admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../user/login.php");
    exit();
}

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
$conn->set_charset("utf8mb4");

// ==== KONSTANTA UMR PER ORANG ====
define('UMR_PERSON', 4725479);

// helper aman untuk text (hindari warning null)
function h($v) {
    return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
}

// ==== AMBIL FILTER GET (SAMA DENGAN LINK DI laporan.php) ====
$dapil    = isset($_GET['dapil'])    ? trim($_GET['dapil'])    : '';
$kategori = isset($_GET['kategori']) ? trim($_GET['kategori']) : '';
$kenal    = isset($_GET['kenal'])    ? trim($_GET['kenal'])    : '';
$tahun    = isset($_GET['tahun'])    ? trim($_GET['tahun'])    : '';


// =======================
//   BUILD WHERE CLAUSE
// =======================
$where = [];

if ($dapil !== '') {
    $safe = $conn->real_escape_string($dapil);
    $where[] = "dapil = '$safe'";
}

if ($kenal !== '') {
    $safe = $conn->real_escape_string($kenal);
    $where[] = "kenal = '$safe'";
}

if ($kategori === 'dibawah') {
    $where[] = "( (total_penghasilan / NULLIF(jumlah_anggota,0)) < " . UMR_PERSON . " )";
} elseif ($kategori === 'diatas') {
    $where[] = "( (total_penghasilan / NULLIF(jumlah_anggota,0)) >= " . UMR_PERSON . " )";
}

if ($tahun !== '') {
    $where[] = "YEAR(created_at) = " . intval($tahun);
}

$whereSQL = '';
if (!empty($where)) {
    $whereSQL = 'WHERE ' . implode(' AND ', $where);
}

// =======================
//     QUERY DATA
// =======================
$sql = "SELECT * FROM keluarga $whereSQL ORDER BY created_at DESC";
$res = $conn->query($sql);

// =======================
//  HEADER UNTUK EXCEL
// =======================
header("Content-Type: application/vnd.ms-excel; charset=utf-8");
header("Content-Disposition: attachment; filename=laporan_tahunan_" . date('Ymd') . ".xls");
header("Pragma: no-cache");
header("Expires: 0");

// =======================
//  OUTPUT TABEL
// =======================
echo "<table border='1'>";

// JUDUL & INFO
echo "<tr><th colspan='11' style='font-size:16px; font-weight:bold; text-align:center; background-color:#e0e0e0; padding:10px;'>LAPORAN DATA KELUARGA - TAHUNAN</th></tr>";
echo "<tr><td colspan='11' style='text-align:center; background-color:#f5f5f5; padding:5px;'>Tanggal Cetak: " . date('d-m-Y H:i') . " WIB</td></tr>";
echo "<tr><td colspan='11'></td></tr>"; // baris kosong

// HEADER TABEL
echo "<tr style='background-color:#d9d9d9; font-weight:bold;'>
        <th>No</th>
        <th>Nama Lengkap</th>
        <th>NIK</th>
        <th>No WhatsApp</th>
        <th>Alamat Lengkap</th>
        <th>Jumlah Anggota</th>
        <th>Jumlah Bekerja</th>
        <th>Total Penghasilan</th>
        <th>Kategori</th>
        <th>Created At</th>
        <th>Updated At</th>
      </tr>";

if ($res && $res->num_rows > 0) {
    $no = 1;
    
    while ($row = $res->fetch_assoc()) {
        $anggota     = (int)$row['jumlah_anggota'];
        $penghasilan = (float)$row['total_penghasilan'];
        $perOrang    = $anggota > 0 ? ($penghasilan / $anggota) : 0;

        $kategoriLabel = ($perOrang < UMR_PERSON)
            ? 'Dibawah UMR'
            : 'Diatas UMR';

        echo "<tr>";
        echo "<td>".$no."</td>";
        echo "<td>".h($row['nama_lengkap'])."</td>";
        echo "<td>'".h($row['nik'])."</td>";
        echo "<td>'".h($row['no_wa'])."</td>";
        echo "<td>".h($row['alamat'])."</td>";
        echo "<td>".$row['jumlah_anggota']."</td>";
        echo "<td>".$row['jumlah_bekerja']."</td>";
        echo "<td>".number_format($penghasilan,0,',','.')."</td>";
        echo "<td>".$kategoriLabel."</td>";
        echo "<td>".$row['created_at']."</td>";
        echo "<td>".$row['updated_at']."</td>";
        echo "</tr>";
        
        $no++;
    }
} else {
    echo "<tr><td colspan='11' align='center'>Tidak ada data.</td></tr>";
}

echo "</table>";
exit;