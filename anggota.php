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

$tahun_aktif = intval(date('Y'));

// Ambil data pembayaran kas untuk seluruh anggota pada tahun aktif
$payments = [];
$kas_detail_q = mysqli_query($conn, "SELECT * FROM pembayaran_kas WHERE tahun = '$tahun_aktif'");
while ($kp = mysqli_fetch_assoc($kas_detail_q)) {
    $payments[$kp['id_anggota']][$kp['id_bulan']] = $kp;
}

// Set menu active and titles for header.php
$page_title = "Data Anggota Internal HIMATIF";
$active_menu = "anggota";
include 'header.php';
?>

<div class="container">
    <h2>Data Anggota Internal HIMATIF</h2>
    
    <!-- Statistik Grid -->
    <div class="stats-grid" style="margin-bottom: 30px;">
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
                $no = 1;
                $query = mysqli_query($conn, "SELECT * FROM anggota_ukm ORDER BY id_user DESC");
                
                if(mysqli_num_rows($query) > 0) {
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
                                            echo '<a href="toggle_kas.php?id_anggota=' . $row['id_user'] . '&bulan=' . $m_num . '&tahun=' . $tahun_aktif . '" class="kas-badge belum" title="Status: Belum Lunas (Klik untuk lunasi)">' . $m_name . '</a>';
                                        }
                                    }
                                    ?>
                                </div>
                            </td>
                            <td>
                                <a href="edit.php?id=<?php echo $row['id_user']; ?>" class="btn btn-edit">Edit</a>
                                <a href="hapus.php?id=<?php echo $row['id_user']; ?>" class="btn btn-hapus" data-message="Yakin ingin menghapus data anggota <?php echo $row['nama']; ?>?">Hapus</a>
                            </td>
                        </tr>
                <?php
                    }
                } else {
                    echo "<tr><td colspan='7' style='text-align: center; color: var(--text-secondary); padding: 24px;'>Belum ada data anggota. Silakan tambah data baru.</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const searchInput = document.getElementById('search-input');
        const filterDivisi = document.getElementById('filter-divisi');
        const rows = document.querySelectorAll('.member-row');

        function filterTable() {
            const searchValue = searchInput.value.toLowerCase().trim();
            const filterValue = filterDivisi.value;

            rows.forEach(row => {
                const nameCell = row.cells[2].textContent.toLowerCase();
                const nimCell = row.cells[1].textContent.toLowerCase();
                const divisiCell = row.cells[3].textContent.trim();

                const matchesSearch = nameCell.includes(searchValue) || nimCell.includes(searchValue);
                const matchesFilter = filterValue === '' || divisiCell === filterValue;

                if (matchesSearch && matchesFilter) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        }

        searchInput.addEventListener('input', filterTable);
        filterDivisi.addEventListener('change', filterTable);
    });
</script>

<?php 
// Close main content wrapper from header.php
echo '</div>'; 
include 'footer.php'; 
include 'alerts.php';
?>
