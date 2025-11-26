<?php
require_once("../koneksi/config.php");
require "../vendor/autoload.php";

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
$conn->set_charset("utf8mb4");

define("UMR_PERSON", 4725479);

$dapil    = $_GET['dapil'] ?? '';
$kategori = $_GET['kategori'] ?? '';
$kenal    = $_GET['kenal'] ?? '';
$tahun    = $_GET['tahun'] ?? '';

function build_where($conn,$f){
    $w=[];
    if($f['dapil']!=='') $w[]="dapil='".mysqli_real_escape_string($conn,$f['dapil'])."'";
    if($f['kenal']!=='') $w[]="kenal='".mysqli_real_escape_string($conn,$f['kenal'])."'";
    if($f['kategori']!==''){
        if($f['kategori']==='dibawah'){
            $w[]="(total_penghasilan/NULLIF(jumlah_anggota,0)) < ".UMR_PERSON;
        } else {
            $w[]="(total_penghasilan/NULLIF(jumlah_anggota,0)) >= ".UMR_PERSON;
        }
    }
    if($f['tahun']!=='') $w[]="YEAR(created_at)=".intval($f['tahun']);
    return $w?"WHERE ".implode(" AND ",$w):"";
}

$where = build_where($conn,[
    'dapil'=>$dapil,'kategori'=>$kategori,'kenal'=>$kenal,'tahun'=>$tahun
]);

$q=$conn->query("SELECT * FROM keluarga $where ORDER BY created_at DESC");

$spread=new Spreadsheet();
$sheet=$spread->setActiveSheetIndex(0);

$header=[
    "Nama","NIK","NoWA","Alamat","Dapil","Kecamatan","Anggota",
    "Bekerja","TotalPenghasilan","PerOrang","Kenal","Sumber",
    "Kategori","CreatedAt","UpdatedAt"
];
$sheet->fromArray($header,NULL,"A1");

$row=2;
while($r=$q->fetch_assoc()){
    $ang=$r['jumlah_anggota'];
    $peng=$r['total_penghasilan'];
    $po=($ang>0)?($peng/$ang):0;
    $kl=($po < UMR_PERSON)?"Dibawah UMR":"Diatas UMR";

    $sheet->fromArray([
        $r['nama_lengkap'],$r['nik'],$r['no_wa'],$r['alamat'],$r['dapil'],
        $r['kecamatan'],$r['jumlah_anggota'],$r['jumlah_bekerja'],
        $peng,$po,$r['kenal'],$r['sumber'],$kl,
        $r['created_at'],$r['updated_at']
    ],NULL,"A$row");

    $row++;
}

header("Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
header("Content-Disposition: attachment; filename=laporan_tahunan.xlsx");

$writer=new Xlsx($spread);
$writer->save("php://output");
exit;
?>
