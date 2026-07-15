<?php
session_start();
require 'config.php';

// Proteksi halaman: Pastikan user sudah login
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit;
}

// Ambil daftar proker untuk pilihan dropdown
$proker_query = mysqli_query($conn, "SELECT * FROM proker ORDER BY nama_proker ASC");
$proker_list = [];
while ($row = mysqli_fetch_assoc($proker_query)) {
    $proker_list[] = $row;
}

if (isset($_POST['simpan'])) {
    $id_proker = intval($_POST['id_proker']);
    $pelaksanaan_ke = intval($_POST['pelaksanaan_ke']);
    $tanggal_pelaksanaan = mysqli_real_escape_string($conn, $_POST['tanggal_pelaksanaan']);
    $jumlah_peserta = intval($_POST['jumlah_peserta']);
    $total_pengeluaran = intval($_POST['total_pengeluaran']);
    $dampak_ke_himpunan = mysqli_real_escape_string($conn, $_POST['dampak_ke_himpunan']);
    $evaluasi_kegiatan = mysqli_real_escape_string($conn, $_POST['evaluasi_kegiatan']);

    // Validasi data
    if ($id_proker <= 0 || $pelaksanaan_ke <= 0 || empty($tanggal_pelaksanaan) || $jumlah_peserta < 0 || $total_pengeluaran < 0 || empty($dampak_ke_himpunan) || empty($evaluasi_kegiatan)) {
        $error = "Semua field harus diisi dengan benar!";
    } else {
        // Cari nama proker untuk pencatatan log
        $name_q = mysqli_query($conn, "SELECT nama_proker FROM proker WHERE id_proker = '$id_proker'");
        $name_data = mysqli_fetch_assoc($name_q);
        $nama_proker = $name_data['nama_proker'] ?? 'Program Kerja';

        $query = "INSERT INTO pelaksanaan_proker (id_proker, pelaksanaan_ke, tanggal_pelaksanaan, jumlah_peserta, total_pengeluaran, dampak_ke_himpunan, evaluasi_kegiatan) 
                  VALUES ('$id_proker', '$pelaksanaan_ke', '$tanggal_pelaksanaan', '$jumlah_peserta', '$total_pengeluaran', '$dampak_ke_himpunan', '$evaluasi_kegiatan')";

        if (mysqli_query($conn, $query)) {
            // Catat log
            $admin_user = mysqli_real_escape_string($conn, $_SESSION['username']);
            $log_aksi = mysqli_real_escape_string($conn, "Mencatat pelaksanaan ke-" . $pelaksanaan_ke . " untuk proker " . $nama_proker . " dengan " . $jumlah_peserta . " peserta dan pengeluaran Rp " . number_format($total_pengeluaran, 0, ',', '.'));
            mysqli_query($conn, "INSERT INTO log_aktivitas (user, aksi) VALUES ('$admin_user', '$log_aksi')");

            $_SESSION['swal_success'] = 'Realisasi pelaksanaan proker berhasil dicatat!';
            header("Location: dashboard.php");
            exit;
        } else {
            $error = "Gagal mencatat pelaksanaan proker: " . mysqli_error($conn);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Catat Pelaksanaan Proker - SIM HIMATIF</title>
    <link rel="stylesheet" href="style.css?v=1.3">
</head>
<body>

    <div class="navbar">
        <div class="nav-brand">SIM HIMATIF - Realisasi Proker</div>
        <div><a href="dashboard.php">Kembali</a></div>
    </div>

    <div class="container">
        <div class="form-card">
            <h2>Catat Realisasi Pelaksanaan Program Kerja</h2>
            
            <?php if(isset($error)) echo "<p class='error-msg'>$error</p>"; ?>

            <?php if (empty($proker_list)): ?>
                <div class="error-msg" style="text-align: left; background: rgba(245, 158, 11, 0.1); border-color: rgba(245, 158, 11, 0.2); color: #f59e0b;">
                    Belum ada Program Kerja terdaftar di sistem. Silakan <a href="tambah_proker.php" style="color: #fff; font-weight: 700; text-decoration: underline;">tambah program kerja baru</a> terlebih dahulu.
                </div>
            <?php else: ?>
                <form method="POST" action="">
                    <div class="form-group">
                        <label for="id_proker">Pilih Program Kerja</label>
                        <select name="id_proker" id="id_proker" required>
                            <option value="" disabled selected>-- Pilih Program Kerja --</option>
                            <?php foreach ($proker_list as $p): ?>
                                <option value="<?php echo $p['id_proker']; ?>">
                                    <?php echo htmlspecialchars($p['nama_proker']); ?> (Target: <?php echo $p['target_frekuensi']; ?>x)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="pelaksanaan_ke">Pelaksanaan Ke-</label>
                        <input type="number" name="pelaksanaan_ke" id="pelaksanaan_ke" min="1" placeholder="Misal: 1" required>
                    </div>

                    <div class="form-group">
                        <label for="tanggal_pelaksanaan">Tanggal Pelaksanaan</label>
                        <input type="date" name="tanggal_pelaksanaan" id="tanggal_pelaksanaan" required>
                    </div>

                    <div class="form-group">
                        <label for="jumlah_peserta">Jumlah Peserta</label>
                        <input type="number" name="jumlah_peserta" id="jumlah_peserta" min="0" placeholder="Masukkan total peserta hadir" required>
                    </div>

                    <div class="form-group">
                        <label for="total_pengeluaran">Total Pengeluaran (Rp)</label>
                        <input type="number" name="total_pengeluaran" id="total_pengeluaran" min="0" placeholder="Masukkan total biaya yang dihabiskan (Rp)" required>
                    </div>

                    <div class="form-group">
                        <label for="dampak_ke_himpunan">Dampak ke Himpunan</label>
                        <textarea name="dampak_ke_himpunan" id="dampak_ke_himpunan" rows="4" placeholder="Jelaskan dampak pelaksanaan proker ini terhadap himpunan..." required style="width: 100%; padding: 14px 16px; background: rgba(255, 255, 255, 0.04); border: 1px solid rgba(255, 255, 255, 0.08); border-radius: 12px; color: #fff; font-size: 15px; font-family: inherit; transition: all 0.3s ease; resize: vertical;"></textarea>
                    </div>

                    <div class="form-group">
                        <label for="evaluasi_kegiatan">Evaluasi Kegiatan</label>
                        <textarea name="evaluasi_kegiatan" id="evaluasi_kegiatan" rows="4" placeholder="Masukkan poin-poin evaluasi atau kendala selama acara berlangsung..." required style="width: 100%; padding: 14px 16px; background: rgba(255, 255, 255, 0.04); border: 1px solid rgba(255, 255, 255, 0.08); border-radius: 12px; color: #fff; font-size: 15px; font-family: inherit; transition: all 0.3s ease; resize: vertical;"></textarea>
                    </div>

                    <button type="submit" name="simpan">Simpan Pelaksanaan</button>
                </form>
            <?php endif; ?>
        </div>
    </div>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Add focus styles for textareas dynamically to match input style
            const textareas = ['dampak_ke_himpunan', 'evaluasi_kegiatan'];
            textareas.forEach(id => {
                const textarea = document.getElementById(id);
                if (textarea) {
                    textarea.addEventListener('focus', function() {
                        this.style.borderColor = 'var(--primary-color)';
                        this.style.background = 'rgba(255, 255, 255, 0.08)';
                        this.style.boxShadow = '0 0 0 4px rgba(139, 92, 246, 0.15)';
                        this.style.outline = 'none';
                    });
                    textarea.addEventListener('blur', function() {
                        this.style.borderColor = 'rgba(255, 255, 255, 0.08)';
                        this.style.background = 'rgba(255, 255, 255, 0.04)';
                        this.style.boxShadow = 'none';
                    });
                }
            });
        });
    </script>
    <?php include 'footer.php'; ?>
</body>
</html>
