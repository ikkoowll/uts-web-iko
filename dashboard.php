<?php
session_start();
require 'config.php'; 

// Proteksi halaman, pastikan user sudah login
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit;
}

// Set menu active and titles for header.php
$page_title = "Dashboard Utama";
$active_menu = "dashboard";
include 'header.php';
?>

<div class="container">
    <div style="margin-bottom: 30px;">
        <h2 style="font-size: 28px; font-weight: 800; color: var(--title-color); margin-bottom: 8px;">
            SIM HIMATIF - Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>!
        </h2>
        <p style="color: var(--text-secondary); font-size: 15px;">Selamat datang di Sistem Informasi Manajemen Himpunan Mahasiswa Informatika (HIMATIF). Gunakan menu sidebar untuk mengelola anggota, kinerja proker, keuangan, dan kas.</p>
    </div>

    <!-- Aktivitas Terbaru Section -->
    <div class="activity-card" style="background: var(--card-bg); border: 1px solid var(--card-border); border-radius: 16px; padding: 24px; box-shadow: 0 4px 20px rgba(0, 0, 0, 0.2); margin-top: 20px;">
        <h3 style="margin-bottom: 20px; font-size: 18px; font-weight: 700; color: #fff; display: flex; align-items: center; gap: 10px;">
            <span style="display: inline-flex; align-items: center; justify-content: center; width: 32px; height: 32px; border-radius: 8px; background: rgba(139, 92, 246, 0.15); color: var(--primary-color);">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"></polyline></svg>
            </span>
            Aktivitas Terbaru Sistem
        </h3>
        
        <div class="activity-list" style="display: flex; flex-direction: column; gap: 16px;">
            <?php
            $activity_query = mysqli_query($conn, "SELECT * FROM log_aktivitas ORDER BY waktu DESC, id DESC LIMIT 10");
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

<?php 
// Close main content wrapper from header.php
echo '</div>'; 
include 'footer.php'; 
include 'alerts.php';
?>