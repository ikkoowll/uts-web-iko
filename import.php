<?php
session_start();
require 'config.php';
require 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

// Proteksi halaman, pastikan user sudah login
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit;
}

$success_count = 0;
$skipped_count = 0;
$error_msg = '';
$success_msg = '';

// Fungsi untuk membaca tabel dari file DOCX
function parseDocxTable($filePath) {
    $zip = new ZipArchive();
    if ($zip->open($filePath) === TRUE) {
        $xmlContent = $zip->getFromName('word/document.xml');
        $zip->close();
        if ($xmlContent === false) return [];
        
        $dom = new DOMDocument();
        libxml_use_internal_errors(true);
        $dom->loadXML($xmlContent);
        libxml_clear_errors();
        
        $rows = [];
        
        // Cari elemen tabel
        $tblElements = $dom->getElementsByTagName('w:tbl');
        if ($tblElements->length === 0) {
            $tblElements = $dom->getElementsByTagName('tbl');
        }
        
        if ($tblElements->length > 0) {
            $table = $tblElements->item(0);
            
            // Cari elemen baris (tr)
            $trElements = $table->getElementsByTagName('w:tr');
            if ($trElements->length === 0) {
                $trElements = $table->getElementsByTagName('tr');
            }
            
            foreach ($trElements as $tr) {
                $row = [];
                
                // Cari elemen kolom (tc)
                $tcElements = $tr->getElementsByTagName('w:tc');
                if ($tcElements->length === 0) {
                    $tcElements = $tr->getElementsByTagName('tc');
                }
                
                foreach ($tcElements as $tc) {
                    $text = '';
                    
                    // Cari elemen teks (t)
                    $tElements = $tc->getElementsByTagName('w:t');
                    if ($tElements->length === 0) {
                        $tElements = $tc->getElementsByTagName('t');
                    }
                    
                    foreach ($tElements as $t) {
                        $text .= $t->nodeValue;
                    }
                    $row[] = trim($text);
                }
                
                if (!empty($row)) {
                    $rows[] = $row;
                }
            }
        }
        return $rows;
    }
    return [];
}

if (isset($_POST['submit_import'])) {
    if (isset($_FILES['file_import']) && $_FILES['file_import']['error'] == UPLOAD_ERR_OK) {
        $fileTmp = $_FILES['file_import']['tmp_name'];
        $fileName = $_FILES['file_import']['name'];
        $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        
        $parsedRows = [];
        
        if ($fileExt === 'xlsx' || $fileExt === 'xls') {
            try {
                $spreadsheet = IOFactory::load($fileTmp);
                $worksheet = $spreadsheet->getActiveSheet();
                $parsedRows = $worksheet->toArray(null, true, true, true);
                
                // Konversi format array dari A,B,C,D ke indeks 0,1,2,3
                $tempRows = [];
                foreach ($parsedRows as $row) {
                    $tempRows[] = array_values($row);
                }
                $parsedRows = $tempRows;
            } catch (Exception $e) {
                $error_msg = "Gagal membaca file Excel: " . $e->getMessage();
            }
        } elseif ($fileExt === 'docx') {
            $parsedRows = parseDocxTable($fileTmp);
            if (empty($parsedRows)) {
                $error_msg = "Gagal membaca file Word atau tidak ada tabel ditemukan di file Word.";
            }
        } else {
            $error_msg = "Format file tidak didukung! Unggah file .xlsx, .xls, atau .docx saja.";
        }
        
        if (!empty($parsedRows) && !$error_msg) {
            // Header mapping untuk flexibilitas kolom
            $headerRow = $parsedRows[0];
            $colIndexes = [
                'nim' => -1,
                'nama' => -1,
                'divisi' => -1,
                'angkatan' => -1
            ];
            
            // Temukan index kolom berdasarkan teks header
            foreach ($headerRow as $index => $colName) {
                $colNameClean = strtolower(trim($colName));
                if (strpos($colNameClean, 'nim') !== false) {
                    $colIndexes['nim'] = $index;
                } elseif (strpos($colNameClean, 'nama') !== false || strpos($colNameClean, 'lengkap') !== false) {
                    $colIndexes['nama'] = $index;
                } elseif (strpos($colNameClean, 'divisi') !== false) {
                    $colIndexes['divisi'] = $index;
                } elseif (strpos($colNameClean, 'angkatan') !== false || strpos($colNameClean, 'tahun') !== false) {
                    $colIndexes['angkatan'] = $index;
                }
            }
            
            // Validasi apakah semua kolom wajib ditemukan
            if ($colIndexes['nim'] == -1 || $colIndexes['nama'] == -1 || $colIndexes['divisi'] == -1 || $colIndexes['angkatan'] == -1) {
                $error_msg = "Header file tidak sesuai! Pastikan terdapat kolom NIM, Nama Lengkap, Divisi, dan Angkatan.";
            } else {
                // Loop baris data (mulai dari baris ke-2 / indeks 1)
                $validDivisions = ['Humas', 'PSDM', 'Media dan Informasi', 'Event/Acara'];
                
                for ($i = 1; $i < count($parsedRows); $i++) {
                    $row = $parsedRows[$i];
                    
                    // Lewati baris kosong
                    if (empty($row) || empty($row[$colIndexes['nim']])) {
                        continue;
                    }
                    
                    $nim = mysqli_real_escape_string($conn, trim($row[$colIndexes['nim']]));
                    $nama = mysqli_real_escape_string($conn, trim($row[$colIndexes['nama']]));
                    $divisi = trim($row[$colIndexes['divisi']]);
                    $angkatan = intval(trim($row[$colIndexes['angkatan']]));
                    
                    // Normalisasi divisi agar cocok dengan DB
                    $matchedDivisi = '';
                    foreach ($validDivisions as $vd) {
                        if (strcasecmp($divisi, $vd) === 0) {
                            $matchedDivisi = $vd;
                            break;
                        }
                    }
                    
                    // Default jika divisi tidak valid
                    if (!$matchedDivisi) {
                        $matchedDivisi = 'Humas'; 
                    }
                    
                    if (empty($nim) || empty($nama) || $angkatan <= 0) {
                        $skipped_count++;
                        continue;
                    }
                    
                    // Cek duplikasi NIM
                    $checkQuery = mysqli_query($conn, "SELECT id_user FROM anggota_ukm WHERE nim = '$nim'");
                    if (mysqli_num_rows($checkQuery) > 0) {
                        $skipped_count++;
                        continue;
                    }
                    
                    // Insert ke DB
                    $insertQuery = "INSERT INTO anggota_ukm (nim, nama, divisi, tahun_angkatan) 
                                    VALUES ('$nim', '$nama', '$matchedDivisi', '$angkatan')";
                    if (mysqli_query($conn, $insertQuery)) {
                        $success_count++;
                    } else {
                        $skipped_count++;
                    }
                }
                
                if ($success_count > 0) {
                    // Log aktivitas
                    $admin_user = mysqli_real_escape_string($conn, $_SESSION['username']);
                    $log_aksi = mysqli_real_escape_string($conn, "Mengimpor data anggota sebanyak $success_count data dari file $fileName");
                    mysqli_query($conn, "INSERT INTO log_aktivitas (user, aksi) VALUES ('$admin_user', '$log_aksi')");
                    
                    $_SESSION['swal_success'] = "Impor selesai! Berhasil menambahkan $success_count anggota. (Dilewati/Gagal: $skipped_count)";
                    header("Location: dashboard.php");
                    exit;
                } else {
                    $error_msg = "Impor selesai, namun tidak ada data baru yang ditambahkan. (Dilewati/Gagal: $skipped_count)";
                }
            }
        }
    } else {
        $error_msg = "Gagal mengunggah file! Pastikan file dipilih dengan benar.";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Impor Anggota - SIM HIMATIF</title>
    <link rel="stylesheet" href="style.css?v=1.3">
</head>
<body>

    <div class="navbar">
        <div class="nav-brand">SIM HIMATIF - Impor Data</div>
        <div><a href="dashboard.php">Kembali</a></div>
    </div>

    <div class="container">
        <div class="form-card" style="max-width: 600px; margin: 30px auto;">
            <h2>Impor Data Anggota</h2>
            <p style="color: var(--text-secondary); margin-bottom: 20px; font-size: 14px; text-align: center;">
                Unggah file Microsoft Excel (.xlsx) atau Word (.docx) untuk memasukkan data anggota secara massal.
            </p>
            
            <?php if ($error_msg): ?>
                <p class="error-msg" style="margin-bottom: 20px;"><?php echo $error_msg; ?></p>
            <?php endif; ?>

            <form method="POST" action="" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="file_import">Pilih File (Excel / Word)</label>
                    <input type="file" name="file_import" id="file_import" accept=".xlsx, .xls, .docx" required style="padding: 10px; border: 1px dashed rgba(255,255,255,0.2); background: rgba(255,255,255,0.02); color: #fff; cursor: pointer; display: block; width: 100%; border-radius: 10px;">
                    <small style="color: var(--text-secondary); display: block; margin-top: 6px;">
                        *Format file wajib berupa: <strong>.xlsx</strong>, <strong>.xls</strong>, atau <strong>.docx</strong> (dengan tabel di dalamnya).
                    </small>
                </div>
                
                <button type="submit" name="submit_import" style="margin-top: 10px;">Mulai Impor Data</button>
            </form>
            
            <div style="margin-top: 30px; border-top: 1px solid rgba(255,255,255,0.08); padding-top: 20px;">
                <h4 style="color: #fff; margin-bottom: 12px; font-size: 15px;">Unduh Template Dokumen:</h4>
                <div style="display: flex; gap: 12px; flex-wrap: wrap;">
                    <a href="download_template.php?type=excel" class="btn btn-edit" style="text-decoration: none; display: inline-flex; align-items: center; justify-content: center; margin: 0; flex-grow: 1; padding: 12px;">
                        Template Excel (.xlsx)
                    </a>
                    <a href="download_template.php?type=word" class="btn btn-tambah" style="text-decoration: none; display: inline-flex; align-items: center; justify-content: center; margin: 0; flex-grow: 1; padding: 12px; background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%); border-color: transparent;">
                        Template Word (.docx)
                    </a>
                </div>
                <div style="margin-top: 16px; background: rgba(255, 255, 255, 0.02); border: 1px solid rgba(255,255,255,0.05); border-radius: 10px; padding: 12px; font-size: 13px; color: var(--text-secondary);">
                    <strong>Catatan untuk Word:</strong> Isi tabel pada template Word yang diunduh, lalu simpan (Save As) sebagai file <strong>Word Document (.docx)</strong> sebelum mengunggah.
                </div>
            </div>
        </div>
    </div>

    <?php include 'footer.php'; ?>
</body>
</html>
