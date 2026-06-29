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
?>


<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard HIMATIF</title>
    <link rel="stylesheet" href="style.css?v=1.1">
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
            <a href="tambah.php" class="btn btn-tambah" style="margin-bottom: 0;">+ Tambah Data Anggota</a>
            
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
                                    <a href="hapus.php?id=<?php echo $row['id_user']; ?>" class="btn btn-hapus" onclick="return confirm('Yakin ingin menghapus data ini?');">Hapus</a>
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
                    background-color: #120c26;
                    color: #fff;
                }
                .activity-item:last-child {
                    border-bottom: none !important;
                    padding-bottom: 0 !important;
                }
            `;
            document.head.appendChild(style);
        });
    </script>

</body>
</html>