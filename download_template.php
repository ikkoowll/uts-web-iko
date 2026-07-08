<?php
session_start();
require 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

// Proteksi halaman, pastikan user sudah login
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit;
}

$type = $_GET['type'] ?? 'excel';

if ($type === 'excel') {
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setTitle('Template Impor');

    // Header
    $sheet->setCellValue('A1', 'NIM');
    $sheet->setCellValue('B1', 'Nama Lengkap');
    $sheet->setCellValue('C1', 'Divisi');
    $sheet->setCellValue('D1', 'Angkatan');

    // Sample Data
    $sheet->setCellValue('A2', '2024001');
    $sheet->setCellValue('B2', 'Ahmad Hidayat');
    $sheet->setCellValue('C2', 'Humas');
    $sheet->setCellValue('D2', '2024');

    $sheet->setCellValue('A3', '2024002');
    $sheet->setCellValue('B3', 'Laras Swastika');
    $sheet->setCellValue('C3', 'Media dan Informasi');
    $sheet->setCellValue('D3', '2024');

    foreach (range('A', 'D') as $colChar) {
        $sheet->getColumnDimension($colChar)->setAutoSize(true);
    }

    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="Template_Impor_Anggota.xlsx"');
    header('Cache-Control: max-age=0');

    $writer = new Xlsx($spreadsheet);
    $writer->save('php://output');
    exit;
} elseif ($type === 'word') {
    // Stream a clean .doc template. MS Word opens it and can save it as .docx
    header("Content-type: application/vnd.ms-word");
    header("Content-Disposition: attachment; filename=Template_Impor_Anggota.doc");
    header("Pragma: no-cache");
    header("Expires: 0");
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="utf-8">
        <style>
            body { font-family: Arial, sans-serif; }
            table { border-collapse: collapse; width: 100%; margin-top: 10px; }
            th, td { border: 1px solid #000000; padding: 8px; text-align: left; }
            th { background-color: #f2f2f2; font-weight: bold; }
        </style>
    </head>
    <body>
        <h3>TEMPLATE IMPOR ANGGOTA SIM HIMATIF</h3>
        <p><strong>PETUNJUK PENGGUNAAN:</strong></p>
        <ol>
            <li>Isi tabel di bawah ini dengan data anggota yang akan diimpor.</li>
            <li>Pilihan Divisi yang valid: <strong>Humas</strong>, <strong>PSDM</strong>, <strong>Media dan Informasi</strong>, atau <strong>Event/Acara</strong>.</li>
            <li>Setelah selesai mengisi, simpan file ini dengan format <strong>Word Document (.docx)</strong> sebelum diunggah di sistem.</li>
        </ol>
        
        <table>
            <thead>
                <tr>
                    <th>NIM</th>
                    <th>Nama Lengkap</th>
                    <th>Divisi</th>
                    <th>Angkatan</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>2024001</td>
                    <td>Ahmad Hidayat</td>
                    <td>Humas</td>
                    <td>2024</td>
                </tr>
                <tr>
                    <td>2024002</td>
                    <td>Laras Swastika</td>
                    <td>Media dan Informasi</td>
                    <td>2024</td>
                </tr>
            </tbody>
        </table>
    </body>
    </html>
    <?php
    exit;
}
