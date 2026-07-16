<?php
session_start();
require 'config.php';

// Proteksi halaman, pastikan user sudah login
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit;
}

// Logika menyimpan pengeluaran baru
if (isset($_POST['simpan'])) {
    $judul = mysqli_real_escape_string($conn, $_POST['judul_pengeluaran']);
    $jumlah = intval($_POST['jumlah_pengeluaran']);
    $tanggal = mysqli_real_escape_string($conn, $_POST['tanggal']);
    $keterangan = mysqli_real_escape_string($conn, $_POST['keterangan']);

    if (empty($judul) || $jumlah <= 0 || empty($tanggal)) {
        $error = "Mohon isi semua field wajib dengan benar!";
    } else {
        $query = "INSERT INTO pengeluaran_kas (judul_pengeluaran, jumlah_pengeluaran, tanggal, keterangan) 
                  VALUES ('$judul', '$jumlah', '$tanggal', '$keterangan')";
        
        if (mysqli_query($conn, $query)) {
            // Catat aktivitas ke log
            $admin_user = mysqli_real_escape_string($conn, $_SESSION['username']);
            $log_aksi = mysqli_real_escape_string($conn, "Mencatat pengeluaran kas: " . $judul . " sebesar Rp " . number_format($jumlah, 0, ',', '.'));
            mysqli_query($conn, "INSERT INTO log_aktivitas (user, aksi) VALUES ('$admin_user', '$log_aksi')");

            $_SESSION['swal_success'] = 'Pengeluaran kas berhasil dicatat!';
            header("Location: pengeluaran.php");
            exit;
        } else {
            $error = "Gagal mencatat pengeluaran: " . mysqli_error($conn);
        }
    }
}

// Hitung total pengeluaran
$total_query = mysqli_query($conn, "SELECT SUM(jumlah_pengeluaran) as total FROM pengeluaran_kas");
$total_data = mysqli_fetch_assoc($total_query);
$total_pengeluaran = $total_data['total'] ?? 0;

// Ambil riwayat pengeluaran (terbaru di atas)
$riwayat_query = mysqli_query($conn, "SELECT * FROM pengeluaran_kas ORDER BY tanggal DESC, id DESC");

$page_title = "Pengeluaran Kas";
$active_menu = "pengeluaran";
include 'header.php';
?>

    <div class="container">
        <h2>Pencatatan & Riwayat Pengeluaran Kas</h2>
        
        <div class="content-layout" style="display: flex; gap: 30px; flex-wrap: wrap; margin-top: 20px;">
            
            <!-- Sisi Kiri: Form Input -->
            <div style="flex: 1; min-width: 320px;">
                <div class="form-card" style="margin: 0; max-width: 100%; width: 100%;">
                    <h2>Input Pengeluaran Kas</h2>
                    
                    <?php if (isset($error)) echo "<p class='error-msg'>$error</p>"; ?>
                    
                    <form method="POST" action="">
                        <div class="form-group">
                            <label for="judul_pengeluaran">Judul Pengeluaran / Kebutuhan</label>
                            <input type="text" name="judul_pengeluaran" id="judul_pengeluaran" placeholder="Contoh: Beli Kertas Print" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="jumlah_pengeluaran">Jumlah Pengeluaran (Rp)</label>
                            <input type="number" name="jumlah_pengeluaran" id="jumlah_pengeluaran" placeholder="Contoh: 50000" min="1" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="tanggal">Tanggal Pengeluaran</label>
                            <input type="date" name="tanggal" id="tanggal" value="<?php echo date('Y-m-d'); ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="keterangan">Keterangan Tambahan</label>
                            <textarea name="keterangan" id="keterangan" rows="4" style="width: 100%; padding: 12px 16px; background: rgba(255, 255, 255, 0.04); border: 1px solid rgba(255, 255, 255, 0.08); border-radius: 10px; color: #fff; font-family: inherit; font-size: 15px; resize: vertical; outline: none; transition: all 0.3s ease;" placeholder="Contoh: Pembelian alat tulis dan kertas A4 untuk kegiatan rapat pengurus"></textarea>
                        </div>
                        
                        <button type="submit" name="simpan">Catat Pengeluaran</button>
                    </form>
                </div>
            </div>

            <!-- Sisi Kanan: Riwayat & Statistik -->
            <div style="flex: 2; min-width: 480px; display: flex; flex-direction: column;">
                
                <!-- Card Total Pengeluaran -->
                <div class="stats-grid" style="grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); margin-bottom: 20px; width: 100%;">
                    <div class="stat-card belum-bayar" style="padding: 20px; width: 100%; box-shadow: 0 4px 15px rgba(239, 68, 68, 0.1);">
                        <span class="stat-title">Total Pengeluaran Kas</span>
                        <span class="stat-value">Rp <?php echo number_format($total_pengeluaran, 0, ',', '.'); ?></span>
                    </div>
                </div>
                
                <!-- Tabel Riwayat -->
                <div class="table-wrapper" style="margin-top: 0; width: 100%;">
                    <table style="width: 100%;">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Kebutuhan</th>
                                <th>Jumlah</th>
                                <th>Tanggal</th>
                                <th>Keterangan</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $no = 1;
                            if (mysqli_num_rows($riwayat_query) > 0) {
                                while ($row = mysqli_fetch_assoc($riwayat_query)) {
                            ?>
                                    <tr>
                                        <td><?php echo $no++; ?></td>
                                        <td style="font-weight: 600; color: #fff;"><?php echo htmlspecialchars($row['judul_pengeluaran']); ?></td>
                                        <td style="color: #f87171; font-weight: 600;">Rp <?php echo number_format($row['jumlah_pengeluaran'], 0, ',', '.'); ?></td>
                                        <td><?php echo date('d-m-Y', strtotime($row['tanggal'])); ?></td>
                                        <td style="max-width: 200px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; color: var(--text-secondary);" title="<?php echo htmlspecialchars($row['keterangan']); ?>">
                                            <?php echo htmlspecialchars($row['keterangan']) ?: '-'; ?>
                                        </td>
                                        <td>
                                            <a href="hapus_pengeluaran.php?id=<?php echo $row['id']; ?>" class="btn btn-hapus" data-message="Apakah Anda yakin ingin menghapus data pengeluaran ini?" style="padding: 6px 12px; font-size: 13px;">Hapus</a>
                                        </td>
                                    </tr>
                            <?php
                                }
                            } else {
                                echo "<tr><td colspan='6' style='text-align: center; color: var(--text-secondary);'>Belum ada catatan pengeluaran kas.</td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>

    <!-- Menambahkan focus styling untuk textarea secara manual karena tidak terjangkau styling standar form-group input/select -->
    <script>
        const textarea = document.getElementById('keterangan');
        textarea.addEventListener('focus', () => {
            textarea.style.borderColor = 'var(--primary-color)';
            textarea.style.background = 'rgba(255, 255, 255, 0.08)';
            textarea.style.boxShadow = '0 0 0 4px rgba(139, 92, 246, 0.15)';
        });
        textarea.addEventListener('blur', () => {
            textarea.style.borderColor = 'rgba(255, 255, 255, 0.08)';
            textarea.style.background = 'rgba(255, 255, 255, 0.04)';
            textarea.style.boxShadow = 'none';
        });
    </div>

    <?php 
    echo '</div>'; // Close main content wrapper from header.php
    include 'footer.php'; 
    ?>
    <?php include 'alerts.php'; ?>
</body>
</html>
