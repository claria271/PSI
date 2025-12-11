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

// ==== AMBIL FILTER GET (SAMA DENGAN laporan.php - CARD BULANAN) ====
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
//  TEMPLATE HTML PDF
// =======================
$html = "
<style>
body { 
    font-family: DejaVu Sans, sans-serif; 
    font-size: 10px;
    margin: 20px;
}
table { 
    width: 100%; 
    border-collapse: collapse; 
    margin-top: 15px; 
}
th, td { 
    border: 1px solid #666; 
    padding: 8px 6px; 
    font-size: 9px;
    vertical-align: middle;
}
th { 
    background: #e8e8e8; 
    font-weight: bold;
    text-align: center;
    color: #333;
    border: 1px solid #555;
}
tbody tr:nth-child(even) {
    background: #f9f9f9;
}
tbody tr:hover {
    background: #f0f0f0;
}
td.center { text-align: center; }
td.right { text-align: right; }
h2 { 
    text-align: center; 
    margin: 0 0 20px 0;
    font-size: 18px;
    color: #222;
    font-weight: bold;
    text-transform: uppercase;
    letter-spacing: 1px;
}
</style>

<h2>LAPORAN DATA KELUARGA - BULANAN</h2>

<table>
<thead>
<tr>
    <th style='width: 3%;'>No</th>
    <th style='width: 16%;'>Nama Lengkap</th>
    <th style='width: 11%;'>NIK</th>
    <th style='width: 10%;'>No WhatsApp</th>
    <th style='width: 19%;'>Alamat</th>
    <th style='width: 5%;'>Anggota</th>
    <th style='width: 5%;'>Bekerja</th>
    <th style='width: 11%;'>Total Penghasilan</th>
    <th style='width: 8%;'>Kategori</th>
    <th style='width: 11%;'>Created At</th>
    <th style='width: 11%;'>Updated At</th>
</tr>
</thead>
<tbody>
";

if ($res && $res->num_rows > 0) {
    $no = 1;
    
    while ($row = $res->fetch_assoc()) {
        $anggota     = (int)$row['jumlah_anggota'];
        $penghasilan = (float)$row['total_penghasilan'];
        $perOrang    = $anggota > 0 ? ($penghasilan / $anggota) : 0;

        $kategoriLabel = ($perOrang < UMR_PERSON)
            ? 'Dibawah UMR'
            : 'Diatas UMR';

        $nama       = htmlspecialchars($row['nama_lengkap'] ?? '', ENT_QUOTES, 'UTF-8');
        $nik        = htmlspecialchars($row['nik'] ?? '', ENT_QUOTES, 'UTF-8');
        $nowa       = htmlspecialchars($row['no_wa'] ?? '', ENT_QUOTES, 'UTF-8');
        $alamat     = htmlspecialchars($row['alamat'] ?? '', ENT_QUOTES, 'UTF-8');
        $createdAt  = htmlspecialchars($row['created_at'] ?? '', ENT_QUOTES, 'UTF-8');
        $updatedAt  = htmlspecialchars($row['updated_at'] ?? '', ENT_QUOTES, 'UTF-8');

        $html .= "
        <tr>
            <td class='center' style='font-weight:bold;'>{$no}</td>
            <td style='padding-left:8px;'>{$nama}</td>
            <td class='center' style='font-size:8.5px;'>{$nik}</td>
            <td class='center'>{$nowa}</td>
            <td style='padding-left:8px;'>{$alamat}</td>
            <td class='center' style='font-weight:bold;'>{$anggota}</td>
            <td class='center' style='font-weight:bold;'>{$row['jumlah_bekerja']}</td>
            <td class='right' style='padding-right:8px; font-weight:bold;'>Rp " . number_format($penghasilan, 0, ',', '.') . "</td>
            <td class='center' style='font-size:8.5px;'>{$kategoriLabel}</td>
            <td class='center' style='font-size:8px;'>{$createdAt}</td>
            <td class='center' style='font-size:8px;'>{$updatedAt}</td>
        </tr>
        ";
        
        $no++;
    }

} else {
    $html .= "<tr><td colspan='11' class='center' style='padding: 15px;'>Tidak ada data.</td></tr>";
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
$dompdf->setPaper('A4', 'landscape');
$dompdf->render();

// STREAM KE BROWSER
$dompdf->stream("laporan_bulanan_" . date('Ymd') . ".pdf", ["Attachment" => true]);
exit;