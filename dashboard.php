<?php
session_start();
require 'config.php'; 

// Proteksi halaman, pastikan user sudah login
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit;
}

// Ambil statistik data
$total_query = mysqli_query($conn, "SELECT COUNT(*) as total FROM anggota_ukm");
$total_data = mysqli_fetch_assoc($total_query);
$total_anggota = $total_data['total'];

$humas_query = mysqli_query($conn, "SELECT COUNT(*) as total FROM anggota_ukm WHERE divisi = 'Humas'");
$humas_data = mysqli_fetch_assoc($humas_query);
$total_humas = $humas_data['total'];

$psdm_query = mysqli_query($conn, "SELECT COUNT(*) as total FROM anggota_ukm WHERE divisi = 'PSDM'");
$psdm_data = mysqli_fetch_assoc($psdm_query);
$total_psdm = $psdm_data['total'];

$media_query = mysqli_query($conn, "SELECT COUNT(*) as total FROM anggota_ukm WHERE divisi = 'Media dan Informasi'");
$media_data = mysqli_fetch_assoc($media_query);
$total_media = $media_data['total'];

$event_query = mysqli_query($conn, "SELECT COUNT(*) as total FROM anggota_ukm WHERE divisi = 'Event/Acara'");
$event_data = mysqli_fetch_assoc($event_query);
$total_event = $event_data['total'];

// Ambil statistik kas (Pemasukan)
$kas_query = mysqli_query($conn, "SELECT SUM(nominal) as total FROM pembayaran_kas WHERE status_bayar = 'Sudah Bayar'");
$kas_data = mysqli_fetch_assoc($kas_query);
$total_pemasukan = $kas_data['total'] ?? 0;

// Ambil statistik pengeluaran
$pengeluaran_query = mysqli_query($conn, "SELECT SUM(jumlah_pengeluaran) as total FROM pengeluaran_kas");
$pengeluaran_data = mysqli_fetch_assoc($pengeluaran_query);
$total_pengeluaran = $pengeluaran_data['total'] ?? 0;

// Total Kas Sekarang (Net Balance)
$total_kas = $total_pemasukan - $total_pengeluaran;


$tahun_aktif = intval(date('Y'));

// Ambil data pembayaran kas untuk seluruh anggota pada tahun aktif
$payments = [];
$query_pay = mysqli_query($conn, "SELECT * FROM pembayaran_kas WHERE tahun = '$tahun_aktif'");
while ($pay = mysqli_fetch_assoc($query_pay)) {
    $payments[$pay['id_anggota']][$pay['id_bulan']] = $pay;
}

// Ambil data pelaksanaan untuk grafik
$chart_proker_data = [];
$chart_proker_query = mysqli_query($conn, "SELECT id_proker, nama_proker FROM proker ORDER BY nama_proker ASC");
while ($cp = mysqli_fetch_assoc($chart_proker_query)) {
    $id_cp = $cp['id_proker'];
    $cp_exec_q = mysqli_query($conn, "
        SELECT pelaksanaan_ke, jumlah_peserta, total_pengeluaran, tanggal_pelaksanaan 
        FROM pelaksanaan_proker 
        WHERE id_proker = '$id_cp' 
        ORDER BY pelaksanaan_ke ASC
    ");
    $execs = [];
    while ($cpe = mysqli_fetch_assoc($cp_exec_q)) {
        $execs[] = [
            'ke' => 'Pelaksanaan Ke-' . $cpe['pelaksanaan_ke'],
            'peserta' => intval($cpe['jumlah_peserta']),
            'pengeluaran' => intval($cpe['total_pengeluaran']),
            'tanggal' => date('d-m-Y', strtotime($cpe['tanggal_pelaksanaan']))
        ];
    }
    $chart_proker_data[$id_cp] = [
        'nama' => $cp['nama_proker'],
        'executions' => $execs
    ];
}
?>


<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard HIMATIF</title>
    <link rel="stylesheet" href="style.css?v=1.3">
</head>
<body>

    <div class="navbar">
        <div class="nav-brand">SIM HIMATIF - Welcome, <?php echo $_SESSION['username']; ?>!</div>
        <div style="display: flex; gap: 12px; align-items: center;">
            <a href="dashboard.php" class="active-nav">Dashboard</a>
            <a href="pengeluaran.php">Pengeluaran Kas</a>
            <a href="logout.php">Logout</a>
        </div>
    </div>

    <div class="container">
        <h2>Data Anggota Internal HIMATIF</h2>
        
        <!-- Statistik Grid -->
        <div class="stats-grid">
            <div class="stat-card kas">
                <span class="stat-title">Kas Terkumpul</span>
                <span class="stat-value">Rp <?php echo number_format($total_kas, 0, ',', '.'); ?></span>
            </div>
            <div class="stat-card total">
                <span class="stat-title">Total Anggota</span>
                <span class="stat-value"><?php echo $total_anggota; ?></span>
            </div>
            <div class="stat-card humas">
                <span class="stat-title">Divisi Humas</span>
                <span class="stat-value"><?php echo $total_humas; ?></span>
            </div>
            <div class="stat-card psdm">
                <span class="stat-title">Divisi PSDM</span>
                <span class="stat-value"><?php echo $total_psdm; ?></span>
            </div>
            <div class="stat-card media">
                <span class="stat-title">Media & Informasi</span>
                <span class="stat-value"><?php echo $total_media; ?></span>
            </div>
            <div class="stat-card event">
                <span class="stat-title">Event / Acara</span>
                <span class="stat-value"><?php echo $total_event; ?></span>
            </div>
        </div>
        
        <div class="search-filter-wrapper" style="display: flex; gap: 16px; margin-bottom: 20px; align-items: center; justify-content: space-between; flex-wrap: wrap; width: 100%;">
            <div style="display: flex; gap: 10px; flex-wrap: wrap; align-items: center;">
                <a href="tambah.php" class="btn btn-tambah" style="margin-bottom: 0;">+ Tambah Data Anggota</a>
                <a href="import.php" class="btn btn-import" style="margin-bottom: 0;">Impor Data</a>
                <a href="export_excel.php" class="btn btn-export-excel" style="margin-bottom: 0;">Ekspor Excel</a>
                <a href="export_word.php" class="btn btn-export-word" style="margin-bottom: 0;">Ekspor Word</a>
            </div>
            
            <div style="display: flex; gap: 12px; flex-wrap: wrap;">
                <input type="text" id="search-input" placeholder="Cari Nama / NIM Anggota..." style="padding: 10px 16px; background: rgba(255, 255, 255, 0.04); border: 1px solid rgba(255, 255, 255, 0.08); border-radius: 10px; color: #fff; font-size: 14px; outline: none; transition: all 0.3s ease; width: 240px;">
                <select id="filter-divisi" style="padding: 10px 16px; background: rgba(255, 255, 255, 0.04); border: 1px solid rgba(255, 255, 255, 0.08); border-radius: 10px; color: #fff; font-size: 14px; outline: none; transition: all 0.3s ease; width: 200px; cursor: pointer;">
                    <option value="">Semua Divisi</option>
                    <option value="Humas">Humas</option>
                    <option value="PSDM">PSDM</option>
                    <option value="Media dan Informasi">Media dan Informasi</option>
                    <option value="Event/Acara">Event / Acara</option>
                </select>
            </div>
        </div>

        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>No</th>
                        <th>NIM</th>
                        <th>Nama Lengkap</th>
                        <th>Divisi</th>
                        <th>Angkatan</th>
                        <th>Status Kas (<?php echo $tahun_aktif; ?>)</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // Menyiapkan variabel nomor urut
                    $no = 1;
                    
                    // Query untuk mengambil (Read) semua data dari tabel anggota_ukm
                    $query = mysqli_query($conn, "SELECT * FROM anggota_ukm ORDER BY id_user DESC");
                    
                    // Mengecek apakah ada data di database
                    if(mysqli_num_rows($query) > 0) {
                        // Looping data dari database
                        while($row = mysqli_fetch_assoc($query)) {
                    ?>
                            <tr class="member-row">
                                <td><?php echo $no++; ?></td>
                                <td><?php echo $row['nim']; ?></td>
                                <td><?php echo $row['nama']; ?></td>
                                <td>
                                    <?php 
                                    $badgeClass = 'badge-media';
                                    if ($row['divisi'] == 'Humas') {
                                        $badgeClass = 'badge-humas';
                                    } elseif ($row['divisi'] == 'PSDM') {
                                        $badgeClass = 'badge-psdm';
                                    } elseif ($row['divisi'] == 'Event/Acara') {
                                        $badgeClass = 'badge-event';
                                    }
                                    ?>
                                    <span class="badge <?php echo $badgeClass; ?>"><?php echo $row['divisi']; ?></span>
                                </td>
                                <td><?php echo $row['tahun_angkatan']; ?></td>
                                <td>
                                    <div class="kas-container">
                                        <?php
                                        $months = [
                                            1 => 'Jan', 2 => 'Feb', 3 => 'Mar', 4 => 'Apr',
                                            5 => 'Mei', 6 => 'Jun', 7 => 'Jul', 8 => 'Agt',
                                            9 => 'Sep', 10 => 'Okt', 11 => 'Nov', 12 => 'Des'
                                        ];
                                        foreach ($months as $m_num => $m_name) {
                                            $is_paid = isset($payments[$row['id_user']][$m_num]) && $payments[$row['id_user']][$m_num]['status_bayar'] == 'Sudah Bayar';
                                            if ($is_paid) {
                                                echo '<a href="toggle_kas.php?id_anggota=' . $row['id_user'] . '&bulan=' . $m_num . '&tahun=' . $tahun_aktif . '" class="kas-badge lunas" title="Status: Lunas (Klik untuk batalkan)">' . $m_name . ' Done</a>';
                                            } else {
                                                echo '<a href="toggle_kas.php?id_anggota=' . $row['id_user'] . '&bulan=' . $m_num . '&tahun=' . $tahun_aktif . '" class="kas-badge belum" title="Status: Belum Bayar (Klik untuk konfirmasi)">' . $m_name . '</a>';
                                            }
                                        }
                                        ?>
                                    </div>
                                </td>
                                <td>
                                    <a href="edit.php?id=<?php echo $row['id_user']; ?>" class="btn btn-edit">Edit</a>
                                    <a href="hapus.php?id=<?php echo $row['id_user']; ?>" class="btn btn-hapus" data-message="Yakin ingin menghapus data anggota ini?">Hapus</a>
                                </td>
                            </tr>
                    <?php 
                        } // Penutup while
                    ?>
                    <tr id="no-results-row" style="display: none;">
                        <td colspan="7" style="text-align: center; color: var(--text-secondary); padding: 24px;">Tidak ada anggota yang cocok dengan pencarian / filter.</td>
                    </tr>
                    <?php
                    } else {
                        // Jika data masih kosong
                        echo "<tr><td colspan='7' style='text-align: center;'>Belum ada data anggota.</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>

        <!-- SECTION: PEMANTAUAN KINERJA PROGRAM KERJA (PROKER) -->
        <div class="proker-section" style="margin-top: 50px; margin-bottom: 40px;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; flex-wrap: wrap; gap: 16px;">
                <h2 style="margin-bottom: 0;">Kinerja Program Kerja (Proker)</h2>
                <div style="display: flex; gap: 10px; flex-wrap: wrap;">
                    <a href="tambah_proker.php" class="btn btn-tambah" style="margin-bottom: 0; background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);">+ Tambah Proker Baru</a>
                    <a href="tambah_pelaksanaan.php" class="btn btn-tambah" style="margin-bottom: 0;">+ Catat Pelaksanaan Proker</a>
                    <a href="export_kinerja.php" class="btn btn-export-word" style="margin-bottom: 0; background: linear-gradient(135deg, #10b981 0%, #059669 100%); box-shadow: 0 4px 12px rgba(16, 185, 129, 0.25);">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-right: 4px;"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
                        Unduh Laporan Kinerja
                    </a>
                </div>
            </div>

            <!-- Grid Kartu Proker -->
            <div class="proker-grid">
                <?php
                $proker_perf_query = mysqli_query($conn, "
                    SELECT p.*, COUNT(pp.id_pelaksanaan) AS total_laksana
                    FROM proker p
                    LEFT JOIN pelaksanaan_proker pp ON p.id_proker = pp.id_proker
                    GROUP BY p.id_proker
                    ORDER BY p.nama_proker ASC
                ");

                if (mysqli_num_rows($proker_perf_query) > 0) {
                    while ($p_row = mysqli_fetch_assoc($proker_perf_query)) {
                        $target = intval($p_row['target_frekuensi']);
                        $terlaksana = intval($p_row['total_laksana']);
                        $percent = $target > 0 ? round(($terlaksana / $target) * 100, 1) : 0;
                        $bar_width = min($percent, 100);
                        
                        $is_achieved = $terlaksana >= $target;
                        $badge_class = $is_achieved ? 'badge-tercapai' : 'badge-belum';
                        $badge_text = $is_achieved ? 'Tercapai' : 'Belum Tercapai';
                        $bar_class = $is_achieved ? 'success' : '';
                ?>
                        <div class="proker-card clickable-card" data-id-proker="<?php echo $p_row['id_proker']; ?>">
                            <div>
                                <div class="proker-header">
                                    <span class="proker-name"><?php echo htmlspecialchars($p_row['nama_proker']); ?></span>
                                    <div style="display: flex; flex-direction: column; align-items: flex-end; gap: 6px;">
                                        <span class="proker-badge <?php echo $badge_class; ?>"><?php echo $badge_text; ?></span>
                                        <a href="hapus_proker.php?id=<?php echo $p_row['id_proker']; ?>" class="btn btn-hapus" data-message="Yakin ingin menghapus program kerja ini? Semua data pelaksanaan terkait juga akan dihapus!" style="padding: 2px 6px; font-size: 10px; border-radius: 4px; line-height: 1;" onclick="event.stopPropagation();">Hapus Proker</a>
                                    </div>
                                </div>
                                <div class="proker-stats">
                                    <span>Progress: <strong><?php echo $terlaksana; ?></strong> / <?php echo $target; ?> Kali</span>
                                    <span class="progress-text"><?php echo $percent; ?>%</span>
                                </div>
                                <div class="progress-container">
                                    <div class="progress-bar <?php echo $bar_class; ?>" style="width: <?php echo $bar_width; ?>%;"></div>
                                </div>
                            </div>
                        </div>
                <?php
                    }
                } else {
                    echo "<p style='grid-column: 1/-1; text-align: center; color: var(--text-secondary); padding: 20px;'>Belum ada Program Kerja. Tambahkan proker baru terlebih dahulu.</p>";
                }
                ?>
            </div>

            <!-- SECTION: GRAFIK TREN PESERTA (CHART.JS) -->
            <div class="chart-card">
                <div class="chart-header">
                    <h3 class="chart-title">Analisis Tren Partisipasi Peserta</h3>
                    <select id="chart-proker-selector" class="chart-select">
                        <option value="" disabled selected>-- Pilih Program Kerja --</option>
                        <?php foreach ($chart_proker_data as $id => $data): ?>
                            <option value="<?php echo $id; ?>"><?php echo htmlspecialchars($data['nama']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div id="chart-empty-state" style="text-align: center; padding: 40px; color: var(--text-secondary);">
                    <p>Klik salah satu kartu Program Kerja di atas atau pilih proker dari menu drop-down untuk melihat visualisasi tren jumlah peserta.</p>
                </div>
                
                <div id="chart-canvas-container" style="display: none; position: relative; height: 350px; width: 100%;">
                    <canvas id="participants-trend-chart"></canvas>
                </div>
            </div>

            <!-- Tabel Detail Pelaksanaan -->
            <h3 style="margin-top: 30px; margin-bottom: 16px; font-size: 18px; font-weight: 700; color: var(--title-color);">Detail Realisasi Pelaksanaan</h3>
            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Nama Program Kerja</th>
                            <th>Pelaksanaan Ke</th>
                            <th>Tanggal Pelaksanaan</th>
                            <th>Jumlah Peserta</th>
                            <th>Tren Peserta</th>
                            <th>Total Pengeluaran</th>
                            <th>Tren Pengeluaran</th>
                            <th>Dampak ke Himpunan</th>
                            <th>Evaluasi Kegiatan</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $exec_query = mysqli_query($conn, "
                            SELECT pp.*, p.nama_proker 
                            FROM pelaksanaan_proker pp 
                            JOIN proker p ON pp.id_proker = p.id_proker 
                            ORDER BY pp.tanggal_pelaksanaan DESC, pp.id_pelaksanaan DESC
                        ");

                        if (mysqli_num_rows($exec_query) > 0) {
                            $no_exec = 1;
                            while ($e_row = mysqli_fetch_assoc($exec_query)) {
                                // Hitung tren dibanding pelaksanaan_ke sebelumnya
                                $curr_ke = intval($e_row['pelaksanaan_ke']);
                                $curr_peserta = intval($e_row['jumlah_peserta']);
                                $curr_pengeluaran = intval($e_row['total_pengeluaran']);
                                $id_pr = $e_row['id_proker'];

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
                                        $trend_html = '<span class="trend-badge trend-up" title="Peserta naik dari ' . $prev_peserta . '">▲ Naik</span>';
                                    } elseif ($curr_peserta < $prev_peserta) {
                                        $trend_html = '<span class="trend-badge trend-down" title="Peserta turun dari ' . $prev_peserta . '">▼ Turun</span>';
                                    } else {
                                        $trend_html = '<span class="trend-badge trend-stable" title="Peserta stabil">▬ Stabil</span>';
                                    }

                                    // Tren Pengeluaran
                                    $prev_pengeluaran = intval($prev_data['total_pengeluaran']);
                                    if ($curr_pengeluaran > $prev_pengeluaran) {
                                        $trend_pengeluaran_html = '<span class="trend-badge trend-down" title="Pengeluaran naik dari Rp ' . number_format($prev_pengeluaran, 0, ',', '.') . '">⚠️ Meningkat</span>';
                                    } elseif ($curr_pengeluaran < $prev_pengeluaran) {
                                        $trend_pengeluaran_html = '<span class="trend-badge trend-up" title="Pengeluaran turun dari Rp ' . number_format($prev_pengeluaran, 0, ',', '.') . '">📉 Efisiensi</span>';
                                    } else {
                                        $trend_pengeluaran_html = '<span class="trend-badge trend-stable" title="Pengeluaran stabil">▬ Stabil</span>';
                                    }
                                } else {
                                    $trend_html = '<span class="trend-badge trend-neutral">Pelaksanaan Perdana</span>';
                                    $trend_pengeluaran_html = '<span class="trend-badge trend-neutral">Pelaksanaan Perdana</span>';
                                }
                        ?>
                                <tr>
                                    <td><?php echo $no_exec++; ?></td>
                                    <td style="font-weight: 600; color: var(--title-color);"><?php echo htmlspecialchars($e_row['nama_proker']); ?></td>
                                    <td>Pelaksanaan ke-<?php echo $e_row['pelaksanaan_ke']; ?></td>
                                    <td><?php echo date('d-m-Y', strtotime($e_row['tanggal_pelaksanaan'])); ?></td>
                                    <td><strong><?php echo number_format($curr_peserta, 0, ',', '.'); ?></strong> orang</td>
                                    <td><?php echo $trend_html; ?></td>
                                    <td><strong>Rp <?php echo number_format($curr_pengeluaran, 0, ',', '.'); ?></strong></td>
                                    <td><?php echo $trend_pengeluaran_html; ?></td>
                                    <td style="max-width: 200px; font-size: 14px; line-height: 1.4; color: var(--text-primary); word-wrap: break-word; white-space: normal;">
                                        <?php echo nl2br(htmlspecialchars($e_row['dampak_ke_himpunan'])); ?>
                                    </td>
                                    <td style="max-width: 200px; font-size: 14px; line-height: 1.4; color: var(--text-primary); word-wrap: break-word; white-space: normal;">
                                        <?php echo nl2br(htmlspecialchars($e_row['evaluasi_kegiatan'])); ?>
                                    </td>
                                    <td>
                                        <a href="hapus_pelaksanaan.php?id=<?php echo $e_row['id_pelaksanaan']; ?>" class="btn btn-hapus" data-message="Yakin ingin menghapus catatan pelaksanaan ini?">Hapus</a>
                                    </td>
                                </tr>
                        <?php
                            }
                        } else {
                            echo "<tr><td colspan='11' style='text-align: center; color: var(--text-secondary); padding: 24px;'>Belum ada catatan pelaksanaan proker. Silakan klik tombol 'Catat Pelaksanaan Proker' untuk menambah data.</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Aktivitas Terbaru Section -->
        <div class="activity-card" style="margin-top: 40px; background: var(--card-bg); border: 1px solid var(--card-border); border-radius: 16px; padding: 24px; box-shadow: 0 4px 20px rgba(0, 0, 0, 0.2);">
            <h3 style="margin-bottom: 20px; font-size: 18px; font-weight: 700; color: #fff; display: flex; align-items: center; gap: 10px;">
                <span style="display: inline-flex; align-items: center; justify-content: center; width: 32px; height: 32px; border-radius: 8px; background: rgba(139, 92, 246, 0.15); color: var(--primary-color);">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"></polyline></svg>
                </span>
                Aktivitas Terbaru Sistem
            </h3>
            
            <div class="activity-list" style="display: flex; flex-direction: column; gap: 16px;">
                <?php
                $activity_query = mysqli_query($conn, "SELECT * FROM log_aktivitas ORDER BY waktu DESC, id DESC LIMIT 5");
                if (mysqli_num_rows($activity_query) > 0) {
                    while ($act = mysqli_fetch_assoc($activity_query)) {
                ?>
                        <div class="activity-item" style="display: flex; align-items: flex-start; gap: 16px; padding-bottom: 16px; border-bottom: 1px solid rgba(255, 255, 255, 0.05);">
                            <div class="activity-dot" style="width: 10px; height: 10px; border-radius: 50%; background: var(--primary-gradient); margin-top: 6px; box-shadow: 0 0 8px var(--primary-color); flex-shrink: 0;"></div>
                            <div style="flex-grow: 1;">
                                <p style="font-size: 14px; color: var(--text-primary); margin-bottom: 4px;">
                                    <strong>@<?php echo htmlspecialchars($act['user']); ?></strong> <?php echo htmlspecialchars($act['aksi']); ?>
                                </p>
                                <span style="font-size: 12px; color: var(--text-secondary);"><?php echo date('d-m-Y H:i', strtotime($act['waktu'])); ?> WIB</span>
                            </div>
                        </div>
                <?php
                    }
                } else {
                    echo "<p style='text-align: center; color: var(--text-secondary); font-size: 14px; padding: 20px 0;'>Belum ada aktivitas tercatat.</p>";
                }
                ?>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.getElementById('search-input');
            const filterDivisi = document.getElementById('filter-divisi');
            const memberRows = document.querySelectorAll('.member-row');
            const noResultsRow = document.getElementById('no-results-row');

            function filterTable() {
                const searchQuery = searchInput.value.toLowerCase().trim();
                const selectedDivisi = filterDivisi.value;
                let visibleCount = 0;

                memberRows.forEach(row => {
                    const nim = row.cells[1].textContent.trim();
                    const nama = row.cells[2].textContent.toLowerCase();
                    
                    const badgeSpan = row.cells[3].querySelector('.badge');
                    const divisi = badgeSpan ? badgeSpan.textContent.trim() : '';

                    const matchesSearch = nama.includes(searchQuery) || nim.includes(searchQuery);
                    const matchesDivisi = selectedDivisi === '' || divisi === selectedDivisi;

                    if (matchesSearch && matchesDivisi) {
                        row.style.display = '';
                        visibleCount++;
                    } else {
                        row.style.display = 'none';
                    }
                });

                if (visibleCount === 0) {
                    noResultsRow.style.display = '';
                } else {
                    noResultsRow.style.display = 'none';
                }
            }

            searchInput.addEventListener('input', filterTable);
            filterDivisi.addEventListener('change', filterTable);

            // Add styles for search-input and filter-divisi focus and options dynamically
            const style = document.createElement('style');
            style.textContent = `
                #search-input:focus, #filter-divisi:focus {
                    border-color: var(--primary-color) !important;
                    background: rgba(255, 255, 255, 0.08) !important;
                    box-shadow: 0 0 0 4px rgba(139, 92, 246, 0.15) !important;
                }
                #filter-divisi option {
                    background-color: var(--select-option-bg);
                    color: var(--input-color);
                }
                .activity-item:last-child {
                    border-bottom: none !important;
                    padding-bottom: 0 !important;
                }
            `;
            document.head.appendChild(style);
        });
    </script>

    <!-- Load Chart.js Library via CDN -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const prokerData = <?php echo json_encode($chart_proker_data); ?>;
            const selector = document.getElementById('chart-proker-selector');
            const emptyState = document.getElementById('chart-empty-state');
            const canvasContainer = document.getElementById('chart-canvas-container');
            const clickableCards = document.querySelectorAll('.proker-card.clickable-card');
            
            let chartInstance = null;

            // Get theme colors dynamically based on light-mode state
            function getThemeColors() {
                const isLight = document.body.classList.contains('light-mode');
                return {
                    textColor: isLight ? '#475569' : '#9ca3af',
                    gridColor: isLight ? 'rgba(0, 0, 0, 0.06)' : 'rgba(255, 255, 255, 0.06)',
                    accentColor: isLight ? '#7c3aed' : '#a855f7',
                    pointBgColor: isLight ? '#db2777' : '#d946ef'
                };
            }

            function renderChart(prokerId) {
                if (!prokerId || !prokerData[prokerId]) return;
                
                const dataInfo = prokerData[prokerId];
                const executions = dataInfo.executions;

                if (executions.length === 0) {
                    emptyState.style.display = 'block';
                    emptyState.innerHTML = `<p style="color: var(--text-secondary); font-weight: 500; text-align: center; padding: 20px;">Belum ada catatan pelaksanaan untuk program kerja <strong>"${dataInfo.nama}"</strong>.</p>`;
                    canvasContainer.style.display = 'none';
                    return;
                }

                emptyState.style.display = 'none';
                canvasContainer.style.display = 'block';

                const labels = executions.map(e => e.ke);
                const values = executions.map(e => e.peserta);
                const expenses = executions.map(e => e.pengeluaran);
                const tooltips = executions.map(e => e.tanggal);

                const colors = getThemeColors();

                if (chartInstance) {
                    chartInstance.destroy();
                }

                const ctx = document.getElementById('participants-trend-chart').getContext('2d');
                
                // Gradient effect
                const gradientPeserta = ctx.createLinearGradient(0, 0, 0, 300);
                gradientPeserta.addColorStop(0, colors.accentColor + '30');
                gradientPeserta.addColorStop(1, colors.accentColor + '00');

                const gradientPengeluaran = ctx.createLinearGradient(0, 0, 0, 300);
                gradientPengeluaran.addColorStop(0, '#10b98130');
                gradientPengeluaran.addColorStop(1, '#10b98100');

                chartInstance = new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: labels,
                        datasets: [
                            {
                                label: 'Jumlah Peserta',
                                data: values,
                                borderColor: colors.accentColor,
                                borderWidth: 3,
                                backgroundColor: gradientPeserta,
                                fill: true,
                                tension: 0.35,
                                pointBackgroundColor: colors.pointBgColor,
                                pointBorderColor: '#ffffff',
                                pointBorderWidth: 2,
                                pointRadius: 6,
                                pointHoverRadius: 8,
                                yAxisID: 'y'
                            },
                            {
                                label: 'Total Pengeluaran (Rp)',
                                data: expenses,
                                borderColor: '#10b981',
                                borderWidth: 3,
                                backgroundColor: gradientPengeluaran,
                                fill: true,
                                tension: 0.35,
                                pointBackgroundColor: '#059669',
                                pointBorderColor: '#ffffff',
                                pointBorderWidth: 2,
                                pointRadius: 6,
                                pointHoverRadius: 8,
                                yAxisID: 'y1'
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: true,
                                position: 'top',
                                labels: {
                                    color: colors.textColor,
                                    font: {
                                        family: 'Plus Jakarta Sans',
                                        weight: '600'
                                    }
                                }
                            },
                            tooltip: {
                                backgroundColor: 'rgba(15, 23, 42, 0.9)',
                                titleColor: '#fff',
                                bodyColor: '#fff',
                                padding: 12,
                                cornerRadius: 8,
                                displayColors: true,
                                callbacks: {
                                    afterLabel: function(context) {
                                        return 'Tanggal: ' + tooltips[context.dataIndex];
                                    },
                                    label: function(context) {
                                        let label = context.dataset.label || '';
                                        if (label) {
                                            label += ': ';
                                        }
                                        if (context.parsed.y !== null) {
                                            if (context.datasetIndex === 1) {
                                                label += 'Rp ' + context.parsed.y.toLocaleString('id-ID');
                                            } else {
                                                label += context.parsed.y.toLocaleString('id-ID') + ' orang';
                                            }
                                        }
                                        return label;
                                    }
                                }
                            }
                        },
                        scales: {
                            x: {
                                grid: {
                                    color: colors.gridColor
                                },
                                ticks: {
                                    color: colors.textColor,
                                    font: {
                                        family: 'Plus Jakarta Sans',
                                        weight: '500'
                                    }
                                }
                            },
                            y: {
                                type: 'linear',
                                display: true,
                                position: 'left',
                                grid: {
                                    color: colors.gridColor
                                },
                                ticks: {
                                    color: colors.textColor,
                                    font: {
                                        family: 'Plus Jakarta Sans',
                                        weight: '500'
                                    },
                                    beginAtZero: true
                                },
                                title: {
                                    display: true,
                                    text: 'Jumlah Peserta (orang)',
                                    color: colors.textColor,
                                    font: {
                                        family: 'Plus Jakarta Sans',
                                        weight: '600'
                                    }
                                }
                            },
                            y1: {
                                type: 'linear',
                                display: true,
                                position: 'right',
                                grid: {
                                    drawOnChartArea: false
                                },
                                ticks: {
                                    color: colors.textColor,
                                    font: {
                                        family: 'Plus Jakarta Sans',
                                        weight: '500'
                                    },
                                    beginAtZero: true,
                                    callback: function(value) {
                                        return 'Rp ' + value.toLocaleString('id-ID');
                                    }
                                },
                                title: {
                                    display: true,
                                    text: 'Total Pengeluaran (Rupiah)',
                                    color: colors.textColor,
                                    font: {
                                        family: 'Plus Jakarta Sans',
                                        weight: '600'
                                    }
                                }
                            }
                        }
                    }
                });
            }

            // Dropdown change listener
            selector.addEventListener('change', function() {
                const val = this.value;
                renderChart(val);
                
                // Synchronize active card outline
                clickableCards.forEach(card => {
                    if (card.dataset.idProker === val) {
                        card.classList.add('active-card');
                    } else {
                        card.classList.remove('active-card');
                    }
                });
            });

            // Card click listener (micro-interactions)
            clickableCards.forEach(card => {
                card.addEventListener('click', function(e) {
                    if (e.target.closest('.btn-hapus')) {
                        return; // Ignore delete button click
                    }
                    
                    const prokerId = this.dataset.idProker;
                    selector.value = prokerId;
                    renderChart(prokerId);
                    
                    clickableCards.forEach(c => c.classList.remove('active-card'));
                    this.classList.add('active-card');
                    
                    document.querySelector('.chart-card').scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                });
            });

            // Re-render chart on theme switches to update grid and text colors
            window.addEventListener('themechanged', function() {
                if (chartInstance && selector.value) {
                    renderChart(selector.value);
                }
            });

            // Select first proker that has execution by default
            let initialProkerId = '';
            for (const id in prokerData) {
                if (prokerData[id].executions.length > 0) {
                    initialProkerId = id;
                    break;
                }
            }
            
            if (initialProkerId) {
                selector.value = initialProkerId;
                renderChart(initialProkerId);
                const firstActiveCard = document.querySelector(`.proker-card[data-id-proker="${initialProkerId}"]`);
                if (firstActiveCard) firstActiveCard.classList.add('active-card');
            } else {
                // fallback to first element
                const firstOption = selector.querySelector('option:not([disabled])');
                if (firstOption) {
                    selector.value = firstOption.value;
                    renderChart(firstOption.value);
                    const firstActiveCard = document.querySelector(`.proker-card[data-id-proker="${firstOption.value}"]`);
                    if (firstActiveCard) firstActiveCard.classList.add('active-card');
                }
            }
        });
    </script>
    <?php include 'footer.php'; ?>
    <?php include 'alerts.php'; ?>
</body>
</html>