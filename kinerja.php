<?php
session_start();
require 'config.php';

// Proteksi halaman, pastikan user sudah login
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit;
}

// Ambil tahun aktif
$tahun_aktif = intval(date('Y'));

// Query data chart proker (tren peserta dan pengeluaran)
$chart_proker_data = [];
$cp_query = mysqli_query($conn, "SELECT id_proker, nama_proker FROM proker ORDER BY nama_proker ASC");
while ($c_row = mysqli_fetch_assoc($cp_query)) {
    $id_cp = $c_row['id_proker'];
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
        'nama' => $c_row['nama_proker'],
        'executions' => $execs
    ];
}

// Set menu active and titles for header.php
$page_title = "Kinerja Program Kerja (Proker)";
$active_menu = "kinerja";
$load_chartjs = true; // Load Chart.js library in header.php
include 'header.php';
?>

<div class="container">
    <div class="proker-section" style="margin-bottom: 40px;">
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
        <div class="proker-grid" style="margin-bottom: 30px;">
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
                    
                    $bar_class = 'bar-belum';
                    if ($percent >= 100) {
                        $bar_class = 'bar-lunas';
                    } elseif ($percent >= 50) {
                        $bar_class = 'bar-setengah';
                    }
            ?>
                    <div class="proker-card clickable-card" data-id-proker="<?php echo $p_row['id_proker']; ?>">
                        <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 12px;">
                            <h4 class="proker-card-title"><?php echo htmlspecialchars($p_row['nama_proker']); ?></h4>
                            <span class="proker-badge <?php echo $badge_class; ?>"><?php echo $badge_text; ?></span>
                        </div>
                        <p class="proker-card-desc">Target pelaksanaan program kerja: <?php echo $target; ?> kali selama periode kepengurusan.</p>
                        
                        <div style="margin-top: 15px;">
                            <div class="proker-progress-meta">
                                <span>Progres Frekuensi</span>
                                <strong><?php echo $terlaksana; ?> / <?php echo $target; ?> Kali (<?php echo $percent; ?>%)</strong>
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
        <div class="chart-card" style="margin-bottom: 40px;">
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
                            $curr_peserta = intval($e_row['jumlah_peserta']);
                            $curr_pengeluaran = intval($e_row['total_pengeluaran']);
                            $curr_ke = intval($e_row['pelaksanaan_ke']);
                            $id_p = $e_row['id_proker'];

                            // Hitung Tren Peserta
                            $prev_q = mysqli_query($conn, "
                                SELECT jumlah_peserta, total_pengeluaran 
                                FROM pelaksanaan_proker 
                                WHERE id_proker = '$id_p' 
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
                                <td>
                                    <strong>Rp <?php echo number_format($curr_pengeluaran, 0, ',', '.'); ?></strong>
                                    <div style="font-size: 11px; color: var(--text-secondary); margin-top: 4px; line-height: 1.3;">
                                        Kas: Rp <?php echo number_format($e_row['dana_dari_kas'], 0, ',', '.'); ?><br>
                                        Spon: Rp <?php echo number_format($e_row['dana_dari_sponsor'], 0, ',', '.'); ?><br>
                                        Mhs: Rp <?php echo number_format($e_row['dana_dari_kemahasiswaan'], 0, ',', '.'); ?>
                                    </div>
                                </td>
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
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const prokerData = <?php echo json_encode($chart_proker_data); ?>;
        
        let chartInstance = null;
        const selector = document.getElementById('chart-proker-selector');
        const emptyState = document.getElementById('chart-empty-state');
        const canvasContainer = document.getElementById('chart-canvas-container');
        const clickableCards = document.querySelectorAll('.proker-card.clickable-card');

        function getThemeColors() {
            const isLightMode = document.body.classList.contains('light-mode');
            return {
                gridColor: isLightMode ? 'rgba(0,0,0,0.05)' : 'rgba(255, 255, 255, 0.05)',
                textColor: isLightMode ? '#475569' : '#9ca3af',
                tooltipBg: isLightMode ? '#ffffff' : '#1e1b4b',
                tooltipBorder: isLightMode ? '#cbd5e1' : 'rgba(255, 255, 255, 0.1)',
                tooltipText: isLightMode ? '#0f172a' : '#ffffff'
            };
        }

        function renderChart(prokerId) {
            if (!prokerId || !prokerData[prokerId]) return;
            
            const proker = prokerData[prokerId];
            const executions = proker.executions;

            if (executions.length === 0) {
                emptyState.style.display = 'block';
                emptyState.innerHTML = '<p style="color: var(--text-secondary);">Belum ada catatan pelaksanaan untuk Program Kerja "' + proker.nama + '".</p>';
                canvasContainer.style.display = 'none';
                if (chartInstance) {
                    chartInstance.destroy();
                    chartInstance = null;
                }
                return;
            }

            emptyState.style.display = 'none';
            canvasContainer.style.display = 'block';

            const labels = executions.map(e => e.ke);
            const values = executions.map(e => e.peserta);
            const costValues = executions.map(e => e.pengeluaran);
            const tooltips = executions.map(e => e.tanggal);

            const colors = getThemeColors();

            if (chartInstance) {
                chartInstance.destroy();
            }

            const ctx = document.getElementById('participants-trend-chart').getContext('2d');
            chartInstance = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [
                        {
                            label: 'Jumlah Peserta',
                            data: values,
                            borderColor: '#8b5cf6',
                            backgroundColor: 'rgba(139, 92, 246, 0.1)',
                            borderWidth: 3,
                            pointBackgroundColor: '#8b5cf6',
                            pointBorderColor: '#fff',
                            pointHoverBackgroundColor: '#fff',
                            pointHoverBorderColor: '#8b5cf6',
                            pointRadius: 6,
                            pointHoverRadius: 8,
                            tension: 0.3,
                            fill: true,
                            yAxisID: 'y'
                        },
                        {
                            label: 'Total Pengeluaran (Rp)',
                            data: costValues,
                            borderColor: '#10b981',
                            backgroundColor: 'rgba(16, 185, 129, 0.05)',
                            borderWidth: 2,
                            borderDash: [5, 5],
                            pointBackgroundColor: '#10b981',
                            pointBorderColor: '#fff',
                            pointHoverRadius: 6,
                            tension: 0.3,
                            fill: false,
                            yAxisID: 'y1'
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
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
                            backgroundColor: colors.tooltipBg,
                            titleColor: colors.tooltipText,
                            bodyColor: colors.tooltipText,
                            borderColor: colors.tooltipBorder,
                            borderWidth: 1,
                            padding: 12,
                            boxPadding: 6,
                            usePointStyle: true,
                            callbacks: {
                                title: function(context) {
                                    const index = context[0].dataIndex;
                                    return proker.nama + ' (' + tooltips[index] + ')';
                                },
                                label: function(context) {
                                    const label = context.dataset.label || '';
                                    const val = context.raw;
                                    if (context.datasetIndex === 1) {
                                        return label + ': Rp ' + val.toLocaleString('id-ID');
                                    }
                                    return label + ': ' + val + ' orang';
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

        // Card click listener
        clickableCards.forEach(card => {
            card.addEventListener('click', function(e) {
                if (e.target.closest('.btn-hapus')) {
                    return;
                }
                
                const prokerId = this.dataset.idProker;
                selector.value = prokerId;
                renderChart(prokerId);
                
                clickableCards.forEach(c => c.classList.remove('active-card'));
                this.classList.add('active-card');
                
                document.querySelector('.chart-card').scrollIntoView({ behavior: 'smooth', block: 'nearest' });
            });
        });

        // Re-render chart on theme switches
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

<?php 
// Close main content wrapper from header.php
echo '</div>'; 
include 'footer.php'; 
include 'alerts.php';
?>
