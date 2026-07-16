<?php
session_start();
require 'config.php';

// Proteksi halaman, pastikan user sudah login
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit;
}

// 1. Ambil jatah dana kemahasiswaan master
$master_query = mysqli_query($conn, "SELECT anggaran FROM dana_kemahasiswaan_master LIMIT 1");
$master_data = mysqli_fetch_assoc($master_query);
$master_kemahasiswaan = $master_data['anggaran'] ?? 5500000;

// 2. Ambil total pemasukan kas dan pengeluaran kas umum untuk sisa kas utama
$pemasukan_query = mysqli_query($conn, "SELECT SUM(nominal) as total FROM pembayaran_kas WHERE status_bayar = 'Sudah Bayar'");
$pemasukan_data = mysqli_fetch_assoc($pemasukan_query);
$total_pemasukan = $pemasukan_data['total'] ?? 0;

$pengeluaran_query = mysqli_query($conn, "SELECT SUM(jumlah_pengeluaran) as total FROM pengeluaran_kas");
$pengeluaran_data = mysqli_fetch_assoc($pengeluaran_query);
$total_pengeluaran = $pengeluaran_data['total'] ?? 0;

$total_kas = $total_pemasukan - $total_pengeluaran;
$sisa_kas_utama = $total_kas;

// 3. Ambil total dana kas terpakai proker
$proker_kas_query = mysqli_query($conn, "SELECT SUM(dana_dari_kas) as total FROM pelaksanaan_proker");
$proker_kas_data = mysqli_fetch_assoc($proker_kas_query);
$total_proker_kas = $proker_kas_data['total'] ?? 0;

// 4. Ambil total dana mahasiswa terpakai proker
$proker_mhs_query = mysqli_query($conn, "SELECT SUM(dana_dari_kemahasiswaan) as total FROM pelaksanaan_proker");
$proker_mhs_data = mysqli_fetch_assoc($proker_mhs_query);
$total_proker_mhs = $proker_mhs_data['total'] ?? 0;
$sisa_kemahasiswaan = $master_kemahasiswaan - $total_proker_mhs;

// 5. Ambil data sponsor (pemasukan vs terpakai)
$sponsor_masuk_q = mysqli_query($conn, "SELECT SUM(nominal_dana) as total FROM sponsor WHERE status_pencairan = 'Cair'");
$sponsor_masuk_d = mysqli_fetch_assoc($sponsor_masuk_q);
$total_sponsor_masuk = $sponsor_masuk_d['total'] ?? 0;

$sponsor_keluar_q = mysqli_query($conn, "SELECT SUM(dana_dari_sponsor) as total FROM pelaksanaan_proker");
$sponsor_keluar_d = mysqli_fetch_assoc($sponsor_keluar_q);
$total_sponsor_keluar = $sponsor_keluar_d['total'] ?? 0;

$sisa_dana_sponsor = $total_sponsor_masuk - $total_sponsor_keluar;

// Set menu active and titles for header.php
$page_title = "Analisis Finansial & Sponsor";
$active_menu = "keuangan";
include 'header.php';
?>

<div class="container">
    <h2>Analisis Finansial & Sponsor</h2>
    <p style="color: var(--text-secondary); margin-bottom: 30px;">Statistik detail mengenai alokasi anggaran program kerja dari multi-sumber dana (Dana Kampus, Kas Himpunan, dan Sponsor).</p>

    <!-- Statistik Grid Finansial -->
    <div class="stats-grid" style="grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); margin-bottom: 30px; gap: 24px;">
        
        <!-- Sisa Dana Kemahasiswaan Card -->
        <div class="stat-card" style="background: linear-gradient(135deg, rgba(59, 130, 246, 0.1) 0%, rgba(37, 99, 235, 0.1) 100%); border-color: rgba(59, 130, 246, 0.2); padding: 24px;">
            <span class="stat-title" style="color: #60a5fa; font-size: 14px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px;">Sisa Dana Kemahasiswaan Kampus</span>
            <span class="stat-value" style="color: #fff; font-size: 28px; font-weight: 800; display: block; margin: 10px 0;">Rp <?php echo number_format($sisa_kemahasiswaan, 0, ',', '.'); ?></span>
            <div style="margin-top: 15px;">
                <div style="display: flex; justify-content: space-between; font-size: 12px; color: var(--text-secondary); margin-bottom: 6px;">
                    <span>Terpakai: Rp <?php echo number_format($total_proker_mhs, 0, ',', '.'); ?></span>
                    <span>Jatah: Rp <?php echo number_format($master_kemahasiswaan, 0, ',', '.'); ?></span>
                </div>
                <div class="progress-container" style="background: rgba(255, 255, 255, 0.05); height: 8px;">
                    <div class="progress-bar" style="width: <?php echo min(100, ($master_kemahasiswaan > 0 ? ($total_proker_mhs / $master_kemahasiswaan) * 100 : 0)); ?>%; background: linear-gradient(90deg, #3b82f6, #60a5fa);"></div>
                </div>
            </div>
        </div>

        <!-- Alokasi Kas Himpunan Card -->
        <div class="stat-card" style="background: linear-gradient(135deg, rgba(16, 185, 129, 0.1) 0%, rgba(5, 150, 105, 0.1) 100%); border-color: rgba(16, 185, 129, 0.2); padding: 24px;">
            <span class="stat-title" style="color: #34d399; font-size: 14px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px;">Kas Himpunan (Terpakai vs Sisa)</span>
            <span class="stat-value" style="color: #fff; font-size: 28px; font-weight: 800; display: block; margin: 10px 0;">Rp <?php echo number_format($sisa_kas_utama, 0, ',', '.'); ?> <span style="font-size: 14px; font-weight: normal; color: var(--text-secondary);">sisa</span></span>
            <div style="margin-top: 15px;">
                <div style="display: flex; justify-content: space-between; font-size: 12px; color: var(--text-secondary); margin-bottom: 6px;">
                    <span>Terpakai Proker: Rp <?php echo number_format($total_proker_kas, 0, ',', '.'); ?></span>
                    <span>Total Kas Sekarang: Rp <?php echo number_format($sisa_kas_utama, 0, ',', '.'); ?></span>
                </div>
                <?php 
                $total_kas_himpunan = $sisa_kas_utama + $total_proker_kas;
                $persen_kas_terpakai = $total_kas_himpunan > 0 ? ($total_proker_kas / $total_kas_himpunan) * 100 : 0;
                ?>
                <div class="progress-container" style="background: rgba(255, 255, 255, 0.05); height: 8px;">
                    <div class="progress-bar" style="width: <?php echo min(100, $persen_kas_terpakai); ?>%; background: linear-gradient(90deg, #10b981, #34d399);"></div>
                </div>
            </div>
        </div>

        <!-- Sisa Dana Sponsor Card -->
        <div class="stat-card" style="background: linear-gradient(135deg, rgba(245, 158, 11, 0.1) 0%, rgba(217, 119, 6, 0.1) 100%); border-color: rgba(245, 158, 11, 0.2); padding: 24px;">
            <span class="stat-title" style="color: #fbbf24; font-size: 14px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px;">Sisa Dana Sponsor</span>
            <span class="stat-value" style="color: #fff; font-size: 28px; font-weight: 800; display: block; margin: 10px 0;">Rp <?php echo number_format($sisa_dana_sponsor, 0, ',', '.'); ?></span>
            <div style="margin-top: 15px;">
                <div style="display: flex; justify-content: space-between; font-size: 12px; color: var(--text-secondary); margin-bottom: 6px;">
                    <span>Terpakai: Rp <?php echo number_format($total_sponsor_keluar, 0, ',', '.'); ?></span>
                    <span>Total Masuk: Rp <?php echo number_format($total_sponsor_masuk, 0, ',', '.'); ?></span>
                </div>
                <div class="progress-container" style="background: rgba(255, 255, 255, 0.05); height: 8px;">
                    <div class="progress-bar" style="width: <?php echo min(100, ($total_sponsor_masuk > 0 ? ($total_sponsor_keluar / $total_sponsor_masuk) * 100 : 0)); ?>%; background: linear-gradient(90deg, #fbbf24, #f59e0b);"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Info Box mengenai Multi-Sumber Dana -->
    <div style="background: var(--card-bg); border: 1px solid var(--card-border); padding: 24px; border-radius: 16px; margin-top: 40px; box-shadow: 0 4px 20px var(--card-shadow);">
        <h4 style="margin-top: 0; margin-bottom: 12px; color: var(--title-color); font-size: 16px; font-weight: 700; display: flex; align-items: center; gap: 8px;">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color: var(--primary-color);"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="16" x2="12" y2="12"></line><line x1="12" y1="8" x2="12.01" y2="8"></line></svg>
            Informasi Kebijakan Finansial Proker
        </h4>
        <ul style="color: var(--text-secondary); font-size: 14px; padding-left: 20px; line-height: 1.6;">
            <li style="margin-bottom: 8px;">**Dana Kemahasiswaan Kampus:** Bersumber dari pagu anggaran Universitas sebesar Rp 5.500.000 per periode aktif, digunakan khusus untuk program kerja resmi.</li>
            <li style="margin-bottom: 8px;">**Kas Himpunan:** Diperoleh secara rutin dari iuran bulanan anggota internal (Rp 10.000/bulan). Pelaksanaan proker yang menggunakan Kas Himpunan akan mengurangi saldo Kas Terkumpul Utama secara otomatis.</li>
            <li style="margin-bottom: 8px;">**Dana Sponsor:** Diperoleh melalui pengajuan proposal sponsorship eksternal. Hanya sponsor berstatus **"Cair"** yang dapat dialokasikan pada pencatatan pelaksanaan proker.</li>
        </ul>
    </div>
</div>

<?php 
// Close main content wrapper from header.php
echo '</div>'; 
include 'footer.php'; 
include 'alerts.php';
?>
