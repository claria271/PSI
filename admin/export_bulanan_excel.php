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

// ==== AMBIL FILTER GET (SAMA DENGAN LINK DI laporan.php - BULANAN) ====
// range = 1 / 3 / 7 / 14 / 30 (hari terakhir)
$range    = isset($_GET['range'])    ? trim($_GET['range'])    : '';
$dapil    = isset($_GET['dapil'])    ? trim($_GET['dapil'])    : '';
$kategori = isset($_GET['kategori']) ? trim($_GET['kategori']) : '';


// =======================
//   BUILD WHERE CLAUSE
// =======================
$where = [];

// RANGE HARI TERAKHIR (berdasarkan created_at)
if ($range !== '') {
    $allowed = ['1', '3', '7', '14', '30'];
    if (in_array($range, $allowed, true)) {
        $days = (int)$range;
        // DATE(created_at) supaya ignore jam
        $where[] = "DATE(created_at) >= (CURDATE() - INTERVAL $days DAY)";
    }
}

// FILTER DAPIL
if ($dapil !== '') {
    $safe = $conn->real_escape_string($dapil);
    $where[] = "dapil = '$safe'";
}

// FILTER KATEGORI (UMR per orang)
if ($kategori === 'dibawah') {
    $where[] = "( (total_penghasilan / NULLIF(jumlah_anggota,0)) < " . UMR_PERSON . " )";
} elseif ($kategori === 'diatas') {
    $where[] = "( (total_penghasilan / NULLIF(jumlah_anggota,0)) >= " . UMR_PERSON . " )";
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
header("Content-Disposition: attachment; filename=laporan_bulanan.xls");
header("Pragma: no-cache");
header("Expires: 0");

// =======================
//  OUTPUT TABEL
// =======================
echo "<table border='1'>";
echo "<tr>
        <th>Nama Lengkap</th>
        <th>NIK</th>
        <th>No WA</th>
        <th>Alamat Lengkap</th>
        <th>Dapil</th>
        <th>Kecamatan</th>
        <th>Jumlah Anggota</th>
        <th>Jumlah Bekerja</th>
        <th>Total Penghasilan</th>
        <th>Rata-rata/Orang</th>
        <th>Kenal</th>
        <th>Sumber</th>
        <th>Kategori</th>
        <th>Created At</th>
        <th>Updated At</th>
      </tr>";

if ($res && $res->num_rows > 0) {
    while ($row = $res->fetch_assoc()) {
        $anggota     = (int)$row['jumlah_anggota'];
        $penghasilan = (float)$row['total_penghasilan'];
        $perOrang    = $anggota > 0 ? ($penghasilan / $anggota) : 0;

        $kategoriLabel = ($perOrang < UMR_PERSON)
            ? 'Dibawah UMR'
            : 'Diatas UMR';

        echo "<tr>";
        echo "<td>".h($row['nama_lengkap'])."</td>";
        // pakai ' di depan supaya Excel tidak ubah ke scientific notation
        echo "<td>'".h($row['nik'])."</td>";
        echo "<td>'".h($row['no_wa'])."</td>";
        echo "<td>".h($row['alamat'])."</td>";
        echo "<td>".h($row['dapil'])."</td>";
        echo "<td>".h($row['kecamatan'])."</td>";
        echo "<td>".$row['jumlah_anggota']."</td>";
        echo "<td>".$row['jumlah_bekerja']."</td>";
        echo "<td>".number_format($penghasilan,0,',','.')."</td>";
        echo "<td>".number_format($perOrang,0,',','.')."</td>";
        echo "<td>".h($row['kenal'])."</td>";
        echo "<td>".h($row['sumber'])."</td>";
        echo "<td>".$kategoriLabel."</td>";
        echo "<td>".$row['created_at']."</td>";
        echo "<td>".$row['updated_at']."</td>";
        echo "</tr>";
    }
} else {
    echo "<tr><td colspan='15' align='center'>Tidak ada data.</td></tr>";
}

echo "</table>";
exit;
