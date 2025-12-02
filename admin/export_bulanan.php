<?php
session_start();
require_once '../koneksi/config.php';

// autoload composer (dompdf)
require_once __DIR__ . '/../vendor/autoload.php';

use Dompdf\Dompdf;
use Dompdf\Options;

// Cek login admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../user/login.php");
    exit();
}

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
$conn->set_charset("utf8mb4");

// ==== KONSTANTA UMR PER ORANG ====
define('UMR_PERSON', 4725479);

// ==== AMBIL FILTER GET (SAMA DENGAN laporan.php) ====
$dapil    = isset($_GET['dapil'])    ? trim($_GET['dapil'])    : '';
$kategori = isset($_GET['kategori']) ? trim($_GET['kategori']) : '';
$kenal    = isset($_GET['kenal'])    ? trim($_GET['kenal'])    : '';
$bulan    = isset($_GET['bulan'])    ? trim($_GET['bulan'])    : '';
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

if ($bulan !== '') {
    $where[] = "MONTH(created_at) = " . intval($bulan);
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
//  TEMPLATE HTML PDF
// =======================
$html = "
<style>
body { font-family: DejaVu Sans, sans-serif; font-size: 12px; }
table { width: 100%; border-collapse: collapse; margin-top:10px; }
th, td { border: 1px solid #555; padding: 6px; font-size: 11px; }
th { background: #eee; }
h2 { text-align:center; margin-bottom: 4px; }
.small { font-size: 11px; }
</style>

<h2>LAPORAN DATA KELUARGA - BULANAN</h2>
<div class='small'>Dibuat pada: " . date('d-m-Y H:i') . "</div>
<br>

<table>
<thead>
<tr>
    <th>Nama</th>
    <th>NIK</th>
    <th>No WA</th>
    <th>Alamat</th>
    <th>Dapil</th>
    <th>Kecamatan</th>
    <th>Anggota</th>
    <th>Bekerja</th>
    <th>Total Penghasilan</th>
    <th>Rata-rata</th>
    <th>Kenal</th>
    <th>Sumber</th>
    <th>Kategori</th>
    <th>Created</th>
</tr>
</thead>
<tbody>
";

if ($res && $res->num_rows > 0) {

    while ($row = $res->fetch_assoc()) {
        $anggota     = (int)$row['jumlah_anggota'];
        $penghasilan = (float)$row['total_penghasilan'];
        $perOrang    = $anggota > 0 ? ($penghasilan / $anggota) : 0;

        $kategoriLabel = ($perOrang < UMR_PERSON)
            ? 'Dibawah UMR'
            : 'Diatas UMR';

        $html .= "
        <tr>
            <td>{$row['nama_lengkap']}</td>
            <td>{$row['nik']}</td>
            <td>{$row['no_wa']}</td>
            <td>{$row['alamat']}</td>
            <td>{$row['dapil']}</td>
            <td>{$row['kecamatan']}</td>
            <td>{$row['jumlah_anggota']}</td>
            <td>{$row['jumlah_bekerja']}</td>
            <td>" . number_format($penghasilan, 0, ',', '.') . "</td>
            <td>" . number_format($perOrang, 0, ',', '.') . "</td>
            <td>{$row['kenal']}</td>
            <td>{$row['sumber']}</td>
            <td>{$kategoriLabel}</td>
            <td>{$row['created_at']}</td>
        </tr>
        ";
    }

} else {
    $html .= "<tr><td colspan='14' align='center'>Tidak ada data.</td></tr>";
}

$html .= "</tbody></table>";


// =======================
//  GENERATE PDF
// =======================
$options = new Options();
$options->set('isHtml5ParserEnabled', true);
$options->set('isRemoteEnabled', true);

$dompdf = new Dompdf($options);
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'landscape'); // tabel lebar jadi landscape
$dompdf->render();

// STREAM KE BROWSER
$dompdf->stream("laporan_bulanan.pdf", ["Attachment" => true]);
exit;
