<?php
// export_tahunan_pdf.php
session_start();
require_once '../koneksi/config.php';

// Cek admin login
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    die('Unauthorized');
}

require_once('../vendor/tecnickcom/tcpdf/tcpdf.php');

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
$conn->set_charset('utf8mb4');

define('UMR_PERSON', 4725479);

// Ambil filter dari GET
$dapil = isset($_GET['dapil']) ? trim($_GET['dapil']) : '';
$kategori = isset($_GET['kategori']) ? trim($_GET['kategori']) : '';
$kenal = isset($_GET['kenal']) ? trim($_GET['kenal']) : '';
$tahun = isset($_GET['tahun']) ? trim($_GET['tahun']) : '';

// Build WHERE clause
function build_where($conn, $filters) {
    $conds = [];
    if (!empty($filters['dapil'])) {
        $safe = mysqli_real_escape_string($conn, $filters['dapil']);
        $conds[] = "dapil = '$safe'";
    }
    if ($filters['kenal'] !== '' && $filters['kenal'] !== null) {
        $safe = mysqli_real_escape_string($conn, $filters['kenal']);
        $conds[] = "kenal = '$safe'";
    }
    if (!empty($filters['kategori'])) {
        $umr = intval($filters['umr']);
        if ($filters['kategori'] === 'dibawah') {
            $conds[] = "( (total_penghasilan / NULLIF(jumlah_anggota,0)) < $umr )";
        } elseif ($filters['kategori'] === 'diatas') {
            $conds[] = "( (total_penghasilan / NULLIF(jumlah_anggota,0)) >= $umr )";
        }
    }
    if (!empty($filters['tahun'])) {
        $yt = intval($filters['tahun']);
        $conds[] = "YEAR(created_at) = $yt";
    }
    return count($conds) > 0 ? "WHERE " . implode(" AND ", $conds) : "";
}

$filters = [
    'dapil' => $dapil,
    'kategori' => $kategori,
    'kenal' => $kenal,
    'tahun' => $tahun,
    'umr' => UMR_PERSON
];

$where = build_where($conn, $filters);
$sql = "SELECT * FROM keluarga $where ORDER BY created_at DESC";
$result = $conn->query($sql);

// Buat PDF
$pdf = new TCPDF('L', 'mm', 'A4', true, 'UTF-8', false);

$pdf->SetCreator('PSI Admin');
$pdf->SetAuthor('PSI');
$pdf->SetTitle('Laporan Tahunan');
$pdf->SetSubject('Laporan Data Keluarga Tahunan');

$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);

$pdf->SetMargins(10, 10, 10);
$pdf->SetAutoPageBreak(TRUE, 10);

$pdf->AddPage();

$pdf->SetFont('helvetica', 'B', 16);
$pdf->Cell(0, 10, 'LAPORAN TAHUNAN DATA KELUARGA', 0, 1, 'C');

$pdf->SetFont('helvetica', '', 10);
$pdf->Cell(0, 5, 'Partai Solidaritas Indonesia', 0, 1, 'C');
$pdf->Ln(3);

// Info filter
$pdf->SetFont('helvetica', '', 9);
$filterInfo = 'Filter: ';
if ($dapil) $filterInfo .= "Dapil: $dapil | ";
if ($kategori) $filterInfo .= "Kategori: " . ucfirst($kategori) . " UMR | ";
if ($kenal) $filterInfo .= "Kenal: $kenal | ";
if ($tahun) $filterInfo .= "Tahun: $tahun | ";
$pdf->Cell(0, 5, rtrim($filterInfo, ' | '), 0, 1, 'L');
$pdf->Ln(2);

// Header tabel
$pdf->SetFont('helvetica', 'B', 7);
$pdf->SetFillColor(240, 240, 240);

$html = '<table border="1" cellpadding="3" style="font-size:7px;">
<thead>
  <tr style="background-color:#f0f0f0;">
    <th width="60"><b>Nama</b></th>
    <th width="45"><b>NIK</b></th>
    <th width="40"><b>No WA</b></th>
    <th width="50"><b>Alamat</b></th>
    <th width="35"><b>Dapil</b></th>
    <th width="35"><b>Kecamatan</b></th>
    <th width="25"><b>Anggota</b></th>
    <th width="25"><b>Bekerja</b></th>
    <th width="35"><b>Penghasilan</b></th>
    <th width="35"><b>Rata/Orang</b></th>
    <th width="30"><b>Kenal</b></th>
    <th width="35"><b>Kategori</b></th>
  </tr>
</thead>
<tbody>';

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $anggota = (int)$row['jumlah_anggota'];
        $penghasilan = (float)$row['total_penghasilan'];
        $per_orang = $anggota > 0 ? ($penghasilan / $anggota) : 0;
        $kategori_label = ($per_orang < UMR_PERSON) ? "Dibawah UMR" : "Diatas UMR";
        
        $html .= '<tr>
            <td>' . htmlspecialchars($row['nama_lengkap']) . '</td>
            <td>' . htmlspecialchars($row['nik']) . '</td>
            <td>' . htmlspecialchars($row['no_wa']) . '</td>
            <td>' . htmlspecialchars(substr($row['alamat'], 0, 30)) . '</td>
            <td>' . htmlspecialchars($row['dapil']) . '</td>
            <td>' . htmlspecialchars($row['kecamatan']) . '</td>
            <td>' . $row['jumlah_anggota'] . '</td>
            <td>' . $row['jumlah_bekerja'] . '</td>
            <td>' . number_format($penghasilan, 0, ',', '.') . '</td>
            <td>' . number_format($per_orang, 0, ',', '.') . '</td>
            <td>' . htmlspecialchars($row['kenal']) . '</td>
            <td>' . $kategori_label . '</td>
        </tr>';
    }
} else {
    $html .= '<tr><td colspan="12" align="center">Tidak ada data</td></tr>';
}

$html .= '</tbody></table>';

$pdf->writeHTML($html, true, false, true, false, '');

$filename = 'Laporan_Tahunan_' . date('Y-m-d_His') . '.pdf';
$pdf->Output($filename, 'D');
?>