<?php
session_start();
require 'config.php';

// Proteksi halaman, pastikan user sudah login
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit;
}

// Log aktivitas
$admin_user = mysqli_real_escape_string($conn, $_SESSION['username']);
$log_aksi = mysqli_real_escape_string($conn, "Mengekspor laporan kinerja program kerja ke Word");
mysqli_query($conn, "INSERT INTO log_aktivitas (user, aksi) VALUES ('$admin_user', '$log_aksi')");

// Set Headers untuk download MS Word (.doc)
header("Content-type: application/vnd.ms-word");
header("Content-Disposition: attachment; filename=Laporan_Kinerja_Proker_HIMATIF_" . date('Ymd_His') . ".doc");
header("Pragma: no-cache");
header("Expires: 0");

// Hitung data statistik ringkasan
// 1. Total proker
$total_proker_q = mysqli_query($conn, "SELECT COUNT(*) as total FROM proker");
$total_proker_d = mysqli_fetch_assoc($total_proker_q);
$total_proker = $total_proker_d['total'] ?? 0;

// 2. Total pelaksanaan
$total_exec_q = mysqli_query($conn, "SELECT COUNT(*) as total FROM pelaksanaan_proker");
$total_exec_d = mysqli_fetch_assoc($total_exec_q);
$total_exec = $total_exec_d['total'] ?? 0;

// 3. Total peserta
$total_peserta_q = mysqli_query($conn, "SELECT SUM(jumlah_peserta) as total FROM pelaksanaan_proker");
$total_peserta_d = mysqli_fetch_assoc($total_peserta_q);
$total_peserta = $total_peserta_d['total'] ?? 0;

// 5. Total pengeluaran proker
$total_cost_q = mysqli_query($conn, "SELECT SUM(total_pengeluaran) as total FROM pelaksanaan_proker");
$total_cost_d = mysqli_fetch_assoc($total_cost_q);
$total_cost = $total_cost_d['total'] ?? 0;

// 4. Rata-rata persentase keterlaksanaan
$proker_list_q = mysqli_query($conn, "
    SELECT p.target_frekuensi, COUNT(pp.id_pelaksanaan) as total_laksana
    FROM proker p
    LEFT JOIN pelaksanaan_proker pp ON p.id_proker = pp.id_proker
    GROUP BY p.id_proker
");
$sum_percentages = 0;
while ($pr = mysqli_fetch_assoc($proker_list_q)) {
    $t = intval($pr['target_frekuensi']);
    $l = intval($pr['total_laksana']);
    $pct = $t > 0 ? ($l / $t) * 100 : 0;
    $sum_percentages += $pct;
}
$avg_percentage = $total_proker > 0 ? round($sum_percentages / $total_proker, 1) : 0;
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Kinerja Program Kerja HIMATIF</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            color: #1e293b;
            line-height: 1.6;
        }
        .header-kop {
            text-align: center;
            border-bottom: 3px double #021A54;
            padding-bottom: 12px;
            margin-bottom: 24px;
        }
        .header-kop h1 {
            font-size: 16px;
            color: #64748b;
            margin: 0;
            text-transform: uppercase;
            letter-spacing: 1px;
            font-weight: 600;
        }
        .header-kop h2 {
            font-size: 20px;
            color: #021A54;
            margin: 6px 0;
            text-transform: uppercase;
            font-weight: 800;
        }
        .header-kop p {
            font-size: 11px;
            color: #475569;
            margin: 0;
        }
        .doc-title {
            text-align: center;
            font-size: 18px;
            font-weight: bold;
            color: #021A54;
            margin-top: 15px;
            margin-bottom: 4px;
            text-transform: uppercase;
        }
        .doc-subtitle {
            text-align: center;
            font-size: 12px;
            color: #475569;
            margin-bottom: 30px;
        }
        .stats-summary {
            width: 100%;
            margin-bottom: 30px;
            border: 1px solid #cbd5e1;
            background-color: #f8fafc;
            border-collapse: collapse;
        }
        .stats-summary td {
            padding: 12px;
            text-align: center;
            border: 1px solid #cbd5e1;
            width: 25%;
        }
        .stats-label {
            font-size: 10px;
            color: #64748b;
            text-transform: uppercase;
            font-weight: bold;
            margin-bottom: 4px;
        }
        .stats-val {
            font-size: 18px;
            color: #021A54;
            font-weight: bold;
        }
        .section-title {
            font-size: 14px;
            font-weight: bold;
            color: #021A54;
            border-bottom: 2px solid #021A54;
            padding-bottom: 4px;
            margin-top: 24px;
            margin-bottom: 12px;
            text-transform: uppercase;
        }
        table.data-table {
            border-collapse: collapse;
            width: 100%;
            margin-bottom: 25px;
        }
        table.data-table th {
            background-color: #021A54;
            color: #ffffff;
            font-weight: bold;
            border: 1px solid #021A54;
            padding: 10px;
            font-size: 12px;
            text-align: left;
        }
        table.data-table td {
            border: 1px solid #cbd5e1;
            padding: 8px 10px;
            font-size: 11px;
            vertical-align: top;
        }
        table.data-table tr:nth-child(even) {
            background-color: #f8fafc;
        }
        .badge {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 4px;
            font-weight: bold;
            font-size: 10px;
        }
        .badge-tercapai {
            background-color: #d1fae5;
            color: #065f46;
            border: 1px solid #a7f3d0;
        }
        .badge-belum {
            background-color: #fef3c7;
            color: #92400e;
            border: 1px solid #fde68a;
        }
        .trend-up {
            color: #16a34a;
            font-weight: bold;
        }
        .trend-down {
            color: #dc2626;
            font-weight: bold;
        }
        .trend-stable {
            color: #d97706;
            font-weight: bold;
        }
        .trend-neutral {
            color: #475569;
        }
    </style>
</head>
<body>

    <!-- KOP SURAT KANTOR HIMATIF -->
    <div class="header-kop">
        <h1>Keluarga Besar Mahasiswa Fakultas Ilmu Komputer</h1>
        <h2>Himpunan Mahasiswa Teknik Informatika (HIMATIF)</h2>
        <p>Sekretariat: Gedung Ormawa FIK, Kampus Utama. Email: himatif@univ.ac.id | Website: himatif.univ.ac.id</p>
    </div>

    <!-- JUDUL DOKUMEN -->
    <div class="doc-title">Laporan Kinerja & Realisasi Program Kerja</div>
    <div class="doc-subtitle">Diunduh oleh: @<?php echo htmlspecialchars($admin_user); ?> | Tanggal: <?php echo date('d-m-Y H:i'); ?> WIB</div>

    <!-- RINGKASAN STATISTIK -->
    <table class="stats-summary">
        <tr>
            <td style="width: 20%;">
                <div class="stats-label">Total Program Kerja</div>
                <div class="stats-val"><?php echo $total_proker; ?></div>
            </td>
            <td style="width: 20%;">
                <div class="stats-label">Total Pelaksanaan</div>
                <div class="stats-val"><?php echo $total_exec; ?></div>
            </td>
            <td style="width: 20%;">
                <div class="stats-label">Rerata Ketercapaian</div>
                <div class="stats-val"><?php echo $avg_percentage; ?>%</div>
            </td>
            <td style="width: 20%;">
                <div class="stats-label">Total Peserta Hadir</div>
                <div class="stats-val"><?php echo number_format($total_peserta, 0, ',', '.'); ?> Orang</div>
            </td>
            <td style="width: 20%;">
                <div class="stats-label">Total Pengeluaran Proker</div>
                <div class="stats-val">Rp <?php echo number_format($total_cost, 0, ',', '.'); ?></div>
            </td>
        </tr>
    </table>

    <!-- SEKSI 1: RINGKASAN KINERJA PROGRAM KERJA -->
    <div class="section-title">1. Ringkasan Ketercapaian Program Kerja</div>
    <table class="data-table">
        <thead>
            <tr>
                <th width="5%">No</th>
                <th width="45%">Nama Program Kerja</th>
                <th width="15%" style="text-align: center;">Target Frekuensi</th>
                <th width="15%" style="text-align: center;">Realisasi</th>
                <th width="10%" style="text-align: center;">Persentase</th>
                <th width="10%" style="text-align: center;">Status</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $proker_perf = mysqli_query($conn, "
                SELECT p.*, COUNT(pp.id_pelaksanaan) AS total_laksana
                FROM proker p
                LEFT JOIN pelaksanaan_proker pp ON p.id_proker = pp.id_proker
                GROUP BY p.id_proker
                ORDER BY p.nama_proker ASC
            ");
            if (mysqli_num_rows($proker_perf) > 0) {
                $no_p = 1;
                while ($row = mysqli_fetch_assoc($proker_perf)) {
                    $target = intval($row['target_frekuensi']);
                    $terlaksana = intval($row['total_laksana']);
                    $percent = $target > 0 ? round(($terlaksana / $target) * 100, 1) : 0;
                    $is_achieved = $terlaksana >= $target;
                    $status_badge = $is_achieved ? '<span class="badge badge-tercapai">Tercapai</span>' : '<span class="badge badge-belum">Belum</span>';
                    
                    echo "<tr>";
                    echo "<td style='text-align: center;'>" . $no_p++ . "</td>";
                    echo "<td style='font-weight: bold;'>" . htmlspecialchars($row['nama_proker']) . "</td>";
                    echo "<td style='text-align: center;'>" . $target . " Kali</td>";
                    echo "<td style='text-align: center;'>" . $terlaksana . " Kali</td>";
                    echo "<td style='text-align: center; font-weight: bold;'>" . $percent . "%</td>";
                    echo "<td style='text-align: center;'>" . $status_badge . "</td>";
                    echo "</tr>";
                }
            } else {
                echo "<tr><td colspan='6' style='text-align: center;'>Belum ada data program kerja.</td></tr>";
            }
            ?>
        </tbody>
    </table>

    <!-- SEKSI 2: DETAIL PELAKSANAAN DAN DAMPAK -->
    <div class="section-title">2. Rincian Realisasi Pelaksanaan & Dampak Kegiatan</div>
    <table class="data-table">
        <thead>
            <tr>
                <th width="5%">No</th>
                <th width="20%">Nama Program Kerja</th>
                <th width="10%">Pelaksanaan</th>
                <th width="10%">Tanggal</th>
                <th width="8%">Peserta</th>
                <th width="10%">Tren Peserta</th>
                <th width="10%">Pengeluaran</th>
                <th width="12%">Tren Pengeluaran</th>
                <th width="15%">Dampak Terhadap Himpunan</th>
                <th width="15%">Evaluasi Kegiatan</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $execs = mysqli_query($conn, "
                SELECT pp.*, p.nama_proker 
                FROM pelaksanaan_proker pp 
                JOIN proker p ON pp.id_proker = p.id_proker 
                ORDER BY p.nama_proker ASC, pp.pelaksanaan_ke ASC
            ");

            if (mysqli_num_rows($execs) > 0) {
                $no_e = 1;
                while ($e_row = mysqli_fetch_assoc($execs)) {
                    $curr_ke = intval($e_row['pelaksanaan_ke']);
                    $curr_peserta = intval($e_row['jumlah_peserta']);
                    $curr_pengeluaran = intval($e_row['total_pengeluaran']);
                    $id_pr = $e_row['id_proker'];

                    // Hitung Tren Peserta dan Pengeluaran
                    $prev_q = mysqli_query($conn, "
                        SELECT jumlah_peserta, total_pengeluaran 
                        FROM pelaksanaan_proker 
                        WHERE id_proker = '$id_pr' 
                          AND pelaksanaan_ke < '$curr_ke' 
                        ORDER BY pelaksanaan_ke DESC 
                        LIMIT 1
                    ");
                    $prev_data = mysqli_fetch_assoc($prev_q);

                    if ($prev_data) {
                        // Tren Peserta
                        $prev_peserta = intval($prev_data['jumlah_peserta']);
                        if ($curr_peserta > $prev_peserta) {
                            $trend_str = '<span class="trend-up">▲ Naik</span>';
                        } elseif ($curr_peserta < $prev_peserta) {
                            $trend_str = '<span class="trend-down">▼ Turun</span>';
                        } else {
                            $trend_str = '<span class="trend-stable">▬ Stabil</span>';
                        }

                        // Tren Pengeluaran
                        $prev_pengeluaran = intval($prev_data['total_pengeluaran']);
                        if ($curr_pengeluaran > $prev_pengeluaran) {
                            $trend_pengeluaran_str = '<span class="trend-down">⚠️ Meningkat</span>';
                        } elseif ($curr_pengeluaran < $prev_pengeluaran) {
                            $trend_pengeluaran_str = '<span class="trend-up">📉 Efisiensi</span>';
                        } else {
                            $trend_pengeluaran_str = '<span class="trend-stable">▬ Stabil</span>';
                        }
                    } else {
                        $trend_str = '<span class="trend-neutral">Perdana</span>';
                        $trend_pengeluaran_str = '<span class="trend-neutral">Perdana</span>';
                    }

                    echo "<tr>";
                    echo "<td style='text-align: center;'>" . $no_e++ . "</td>";
                    echo "<td style='font-weight: bold;'>" . htmlspecialchars($e_row['nama_proker']) . "</td>";
                    echo "<td>Pelaksanaan ke-" . $e_row['pelaksanaan_ke'] . "</td>";
                    echo "<td>" . date('d-m-Y', strtotime($e_row['tanggal_pelaksanaan'])) . "</td>";
                    echo "<td>" . number_format($curr_peserta, 0, ',', '.') . " Orang</td>";
                    echo "<td>" . $trend_str . "</td>";
                    echo "<td>";
                    echo "<strong>Rp " . number_format($curr_pengeluaran, 0, ',', '.') . "</strong><br>";
                    echo "<font size='1' color='#64748b'>";
                    echo "Kas: Rp " . number_format($e_row['dana_dari_kas'], 0, ',', '.') . "<br>";
                    echo "Spon: Rp " . number_format($e_row['dana_dari_sponsor'], 0, ',', '.') . "<br>";
                    echo "Mhs: Rp " . number_format($e_row['dana_dari_kemahasiswaan'], 0, ',', '.');
                    echo "</font>";
                    echo "</td>";
                    echo "<td>" . $trend_pengeluaran_str . "</td>";
                    echo "<td>" . nl2br(htmlspecialchars($e_row['dampak_ke_himpunan'])) . "</td>";
                    echo "<td>" . nl2br(htmlspecialchars($e_row['evaluasi_kegiatan'])) . "</td>";
                    echo "</tr>";
                }
            } else {
                echo "<tr><td colspan='10' style='text-align: center;'>Belum ada catatan pelaksanaan proker.</td></tr>";
            }
            ?>
        </tbody>
    </table>

</body>
</html>
<?php
exit;
?>
