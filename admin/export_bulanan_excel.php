<?php
// export_tahunan_excel.php
session_start();
require_once '../koneksi/config.php';

// Cek admin login
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    die('Unauthorized');
}

require_once '../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;

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

// Buat Spreadsheet
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$sheet->setTitle('Laporan Tahunan');

// Header
$sheet->setCellValue('A1', 'LAPORAN TAHUNAN DATA KELUARGA');
$sheet->mergeCells('A1:O1');
$sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);
$sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

$sheet->setCellValue('A2', 'Partai Solidaritas Indonesia');
$sheet->mergeCells('A2:O2');
$sheet->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

// Info filter
$filterInfo = 'Filter: ';
if ($dapil) $filterInfo .= "Dapil: $dapil | ";
if ($kategori) $filterInfo .= "Kategori: " . ucfirst($kategori) . " UMR | ";
if ($kenal) $filterInfo .= "Kenal: $kenal | ";
if ($tahun) $filterInfo .= "Tahun: $tahun | ";

$sheet->setCellValue('A3', rtrim($filterInfo, ' | '));
$sheet->mergeCells('A3:O3');

// Header tabel
$row = 5;
$headers = [
    'Nama Lengkap', 'NIK', 'No WA', 'Alamat Lengkap', 'Dapil', 
    'Kecamatan', 'Jumlah Anggota', 'Jumlah Bekerja', 'Total Penghasilan',
    'Rata-rata/Orang', 'Kenal', 'Sumber', 'Kategori', 'Created At', 'Updated At'
];

$col = 'A';
foreach ($headers as $header) {
    $sheet->setCellValue($col . $row, $header);
    $sheet->getStyle($col . $row)->getFont()->setBold(true);
    $sheet->getStyle($col . $row)->getFill()
        ->setFillType(Fill::FILL_SOLID)
        ->getStartColor()->setARGB('FFE0E0E0');
    $col++;
}

// Data
$row++;
if ($result && $result->num_rows > 0) {
    while ($data = $result->fetch_assoc()) {
        $anggota = (int)$data['jumlah_anggota'];
        $penghasilan = (float)$data['total_penghasilan'];
        $per_orang = $anggota > 0 ? ($penghasilan / $anggota) : 0;
        $kategori_label = ($per_orang < UMR_PERSON) ? "Dibawah UMR" : "Diatas UMR";
        
        $sheet->setCellValue('A' . $row, $data['nama_lengkap']);
        $sheet->setCellValue('B' . $row, $data['nik']);
        $sheet->setCellValue('C' . $row, $data['no_wa']);
        $sheet->setCellValue('D' . $row, $data['alamat']);
        $sheet->setCellValue('E' . $row, $data['dapil']);
        $sheet->setCellValue('F' . $row, $data['kecamatan']);
        $sheet->setCellValue('G' . $row, $data['jumlah_anggota']);
        $sheet->setCellValue('H' . $row, $data['jumlah_bekerja']);
        $sheet->setCellValue('I' . $row, $penghasilan);
        $sheet->setCellValue('J' . $row, $per_orang);
        $sheet->setCellValue('K' . $row, $data['kenal']);
        $sheet->setCellValue('L' . $row, $data['sumber']);
        $sheet->setCellValue('M' . $row, $kategori_label);
        $sheet->setCellValue('N' . $row, $data['created_at']);
        $sheet->setCellValue('O' . $row, $data['updated_at']);
        
        $row++;
    }
}

// Auto size columns
foreach (range('A', 'O') as $col) {
    $sheet->getColumnDimension($col)->setAutoSize(true);
}

// Border untuk tabel
$sheet->getStyle('A5:O' . ($row - 1))
    ->getBorders()
    ->getAllBorders()
    ->setBorderStyle(Border::BORDER_THIN);

// Output
$filename = 'Laporan_Tahunan_' . date('Y-m-d_His') . '.xlsx';

header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="' . $filename . '"');
header('Cache-Control: max-age=0');

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;
?>