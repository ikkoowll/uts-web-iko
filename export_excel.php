<?php
session_start();
require 'config.php';
require 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

// Proteksi halaman, pastikan user sudah login
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit;
}

$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$sheet->setTitle('Anggota HIMATIF');

// Judul Sheet
$sheet->setCellValue('A1', 'DATA ANGGOTA INTERNAL HIMATIF');
$sheet->mergeCells('A1:E1');
$sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);
$sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

// Table Header
$headers = ['No', 'NIM', 'Nama Lengkap', 'Divisi', 'Angkatan'];
$col = 'A';
foreach ($headers as $header) {
    $sheet->setCellValue($col . '3', $header);
    $col++;
}

// Style Header Table
$headerStyle = [
    'font' => [
        'bold' => true,
        'color' => ['rgb' => 'FFFFFF'],
    ],
    'alignment' => [
        'horizontal' => Alignment::HORIZONTAL_CENTER,
        'vertical' => Alignment::VERTICAL_CENTER,
    ],
    'fill' => [
        'fillType' => Fill::FILL_SOLID,
        'startColor' => ['rgb' => '021A54'],
    ],
    'borders' => [
        'allBorders' => [
            'borderStyle' => Border::BORDER_THIN,
            'color' => ['rgb' => '000000'],
        ],
    ],
];
$sheet->getStyle('A3:E3')->applyFromArray($headerStyle);
$sheet->getRowDimension('3')->setRowHeight(25);

// Get Data dari Database
$query = mysqli_query($conn, "SELECT * FROM anggota_ukm ORDER BY id_user DESC");
$rowNum = 4;
$no = 1;

$bodyStyle = [
    'borders' => [
        'allBorders' => [
            'borderStyle' => Border::BORDER_THIN,
            'color' => ['rgb' => 'CCCCCC'],
        ],
    ],
    'alignment' => [
        'vertical' => Alignment::VERTICAL_CENTER,
    ],
];

while ($row = mysqli_fetch_assoc($query)) {
    $sheet->setCellValue('A' . $rowNum, $no++);
    $sheet->setCellValue('B' . $rowNum, $row['nim']);
    $sheet->setCellValue('C' . $rowNum, $row['nama']);
    $sheet->setCellValue('D' . $rowNum, $row['divisi']);
    $sheet->setCellValue('E' . $rowNum, $row['tahun_angkatan']);
    
    // Format NIM sebagai text agar tidak hilang angka nol di depan jika ada
    $sheet->getStyle('B' . $rowNum)->getNumberFormat()->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_TEXT);
    
    // Alinyemen khusus
    $sheet->getStyle('A' . $rowNum)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
    $sheet->getStyle('B' . $rowNum)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
    $sheet->getStyle('E' . $rowNum)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
    
    $sheet->getStyle('A' . $rowNum . ':E' . $rowNum)->applyFromArray($bodyStyle);
    $sheet->getRowDimension($rowNum)->setRowHeight(20);
    
    $rowNum++;
}

// Auto fit column width
foreach (range('A', 'E') as $colChar) {
    $sheet->getColumnDimension($colChar)->setAutoSize(true);
}

// Log aktivitas
$admin_user = mysqli_real_escape_string($conn, $_SESSION['username']);
$log_aksi = mysqli_real_escape_string($conn, "Mengekspor data anggota ke Excel");
mysqli_query($conn, "INSERT INTO log_aktivitas (user, aksi) VALUES ('$admin_user', '$log_aksi')");

// Send Excel to browser
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="Data_Anggota_HIMATIF_' . date('Ymd_His') . '.xlsx"');
header('Cache-Control: max-age=0');

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;
