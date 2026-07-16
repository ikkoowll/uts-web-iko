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

// Ambil daftar sponsor yang statusnya 'Cair'
$sponsors_query = mysqli_query($conn, "SELECT id_sponsor, nama_sponsor, nominal_dana, id_proker FROM sponsor WHERE status_pencairan = 'Cair'");
$sponsors = [];
while ($row = mysqli_fetch_assoc($sponsors_query)) {
    $sponsors[] = $row;
}

if (isset($_POST['simpan'])) {
    $id_proker = intval($_POST['id_proker']);
    $pelaksanaan_ke = intval($_POST['pelaksanaan_ke']);
    $tanggal_pelaksanaan = mysqli_real_escape_string($conn, $_POST['tanggal_pelaksanaan']);
    $jumlah_peserta = intval($_POST['jumlah_peserta']);
    $dana_dari_kas = intval($_POST['dana_dari_kas']);
    $dana_dari_sponsor = intval($_POST['dana_dari_sponsor']);
    $dana_dari_kemahasiswaan = intval($_POST['dana_dari_kemahasiswaan']);
    $dampak_ke_himpunan = mysqli_real_escape_string($conn, $_POST['dampak_ke_himpunan']);
    $evaluasi_kegiatan = mysqli_real_escape_string($conn, $_POST['evaluasi_kegiatan']);

    $total_pengeluaran = $dana_dari_kas + $dana_dari_sponsor + $dana_dari_kemahasiswaan;

    // Validasi data
    if ($id_proker <= 0 || $pelaksanaan_ke <= 0 || empty($tanggal_pelaksanaan) || $jumlah_peserta < 0 || $dana_dari_kas < 0 || $dana_dari_sponsor < 0 || $dana_dari_kemahasiswaan < 0 || empty($dampak_ke_himpunan) || empty($evaluasi_kegiatan)) {
        $error = "Semua field harus diisi dengan benar!";
    } else {
        // Cari nama proker untuk pencatatan log
        $name_q = mysqli_query($conn, "SELECT nama_proker FROM proker WHERE id_proker = '$id_proker'");
        $name_data = mysqli_fetch_assoc($name_q);
        $nama_proker = $name_data['nama_proker'] ?? 'Program Kerja';

        // Mulai transaksi database
        mysqli_begin_transaction($conn);

        try {
            // 1. Simpan pelaksanaan proker
            $query = "INSERT INTO pelaksanaan_proker (id_proker, pelaksanaan_ke, tanggal_pelaksanaan, jumlah_peserta, total_pengeluaran, dana_dari_kas, dana_dari_sponsor, dana_dari_kemahasiswaan, dampak_ke_himpunan, evaluasi_kegiatan) 
                      VALUES ('$id_proker', '$pelaksanaan_ke', '$tanggal_pelaksanaan', '$jumlah_peserta', '$total_pengeluaran', '$dana_dari_kas', '$dana_dari_sponsor', '$dana_dari_kemahasiswaan', '$dampak_ke_himpunan', '$evaluasi_kegiatan')";
            mysqli_query($conn, $query);

            // 2. Jika dana_dari_kas > 0, otomatis tambahkan baris pengeluaran kas umum
            if ($dana_dari_kas > 0) {
                $judul_pengeluaran = mysqli_real_escape_string($conn, "Pengeluaran Proker: " . $nama_proker);
                $keterangan_pengeluaran = mysqli_real_escape_string($conn, "Diambil otomatis dari pelaksanaan ke-" . $pelaksanaan_ke . " proker " . $nama_proker);
                $query_pengeluaran = "INSERT INTO pengeluaran_kas (judul_pengeluaran, jumlah_pengeluaran, tanggal, keterangan) 
                                      VALUES ('$judul_pengeluaran', '$dana_dari_kas', '$tanggal_pelaksanaan', '$keterangan_pengeluaran')";
                mysqli_query($conn, $query_pengeluaran);
            }

            // Catat log
            $admin_user = mysqli_real_escape_string($conn, $_SESSION['username']);
            $log_aksi = mysqli_real_escape_string($conn, "Mencatat pelaksanaan ke-" . $pelaksanaan_ke . " untuk proker " . $nama_proker . " dengan pengeluaran: Kas=Rp " . number_format($dana_dari_kas, 0, ',', '.') . ", Sponsor=Rp " . number_format($dana_dari_sponsor, 0, ',', '.') . ", Kampus=Rp " . number_format($dana_dari_kemahasiswaan, 0, ',', '.'));
            mysqli_query($conn, "INSERT INTO log_aktivitas (user, aksi) VALUES ('$admin_user', '$log_aksi')");

            mysqli_commit($conn);

            $_SESSION['swal_success'] = 'Realisasi pelaksanaan proker berhasil dicatat!';
            header("Location: kinerja.php");
            exit;
        } catch (Exception $e) {
            mysqli_rollback($conn);
            $error = "Gagal mencatat pelaksanaan proker: " . $e->getMessage();
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

                    <div class="form-group" style="background: rgba(255, 255, 255, 0.02); padding: 20px; border-radius: 12px; border: 1px solid rgba(255, 255, 255, 0.05); margin-bottom: 20px;">
                        <h4 style="margin-top: 0; margin-bottom: 15px; color: var(--primary-color); font-size: 16px; font-weight: 700;">Rincian Sumber Dana Kegiatan</h4>
                        
                        <div class="form-group" style="margin-bottom: 15px;">
                            <label for="dana_dari_kas">Dana dari Kas Himpunan (Rp)</label>
                            <input type="number" name="dana_dari_kas" id="dana_dari_kas" min="0" value="0" placeholder="0" required oninput="hitungTotalPengeluaran()">
                            <small style="color: var(--text-secondary); display: block; margin-top: 4px;">*Akan memotong saldo Kas Terkumpul secara otomatis</small>
                        </div>

                        <div class="form-group" style="margin-bottom: 15px;">
                            <label>Dana dari Sponsor (Rp)</label>
                            <input type="hidden" name="dana_dari_sponsor" id="dana_dari_sponsor" value="0">
                            <span id="display_dana_dari_sponsor" style="display: block; font-weight: 700; color: #fbbf24; margin-bottom: 8px; font-size: 15px;">Rp 0</span>
                            
                            <!-- Container Checkbox Sponsor -->
                            <div id="sponsor-checkboxes-container" style="background: rgba(0, 0, 0, 0.2); padding: 12px; border-radius: 8px; border: 1px solid rgba(255,255,255,0.05); max-height: 150px; overflow-y: auto;">
                                <p style="color: var(--text-secondary); font-size: 13px; margin: 0;">Pilih Program Kerja terlebih dahulu untuk melihat daftar sponsor.</p>
                            </div>
                        </div>

                        <div class="form-group" style="margin-bottom: 15px;">
                            <label for="dana_dari_kemahasiswaan">Dana dari Kemahasiswaan Kampus (Rp)</label>
                            <input type="number" name="dana_dari_kemahasiswaan" id="dana_dari_kemahasiswaan" min="0" value="0" placeholder="0" required oninput="hitungTotalPengeluaran()">
                            <small style="color: var(--text-secondary); display: block; margin-top: 4px;">*Akan memotong jatah dana kemahasiswaan master secara otomatis</small>
                        </div>

                        <div style="border-top: 1px solid rgba(255, 255, 255, 0.08); padding-top: 15px; margin-top: 15px; display: flex; justify-content: space-between; align-items: center;">
                            <strong style="color: #fff; font-size: 15px;">Estimasi Total Pengeluaran:</strong>
                            <span id="display_total_pengeluaran" style="color: #10b981; font-size: 18px; font-weight: 800;">Rp 0</span>
                        </div>
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
        const allSponsors = <?php echo json_encode($sponsors); ?>;

        function hitungTotalPengeluaran() {
            const kas = parseInt(document.getElementById('dana_dari_kas').value) || 0;
            const sponsor = parseInt(document.getElementById('dana_dari_sponsor').value) || 0;
            const kemahasiswaan = parseInt(document.getElementById('dana_dari_kemahasiswaan').value) || 0;
            const total = kas + sponsor + kemahasiswaan;
            document.getElementById('display_total_pengeluaran').textContent = 'Rp ' + total.toLocaleString('id-ID');
        }

        document.addEventListener('DOMContentLoaded', function() {
            hitungTotalPengeluaran();

            const prokerSelect = document.getElementById('id_proker');
            const sponsorContainer = document.getElementById('sponsor-checkboxes-container');
            const danaSponsorInput = document.getElementById('dana_dari_sponsor');
            const displayDanaSponsor = document.getElementById('display_dana_dari_sponsor');

            function updateSponsorCheckboxes() {
                const selectedProkerId = parseInt(prokerSelect.value) || 0;
                const filteredSponsors = allSponsors.filter(s => parseInt(s.id_proker) === selectedProkerId);

                sponsorContainer.innerHTML = '';
                
                if (filteredSponsors.length === 0) {
                    sponsorContainer.innerHTML = '<p style="color: var(--text-secondary); font-size: 13px; margin: 0;">Tidak ada sponsor berstatus "Cair" untuk proker ini.</p>';
                    danaSponsorInput.value = 0;
                    displayDanaSponsor.textContent = 'Rp 0';
                    hitungTotalPengeluaran();
                    return;
                }

                filteredSponsors.forEach(s => {
                    const label = document.createElement('label');
                    label.style.display = 'flex';
                    label.style.alignItems = 'center';
                    label.style.gap = '8px';
                    label.style.marginBottom = '8px';
                    label.style.cursor = 'pointer';
                    label.style.fontSize = '14px';
                    label.style.color = '#fff';

                    const checkbox = document.createElement('input');
                    checkbox.type = 'checkbox';
                    checkbox.name = 'sponsors_selected[]';
                    checkbox.value = s.id_sponsor;
                    checkbox.dataset.nominal = s.nominal_dana;
                    checkbox.addEventListener('change', calculateSponsorTotal);

                    label.appendChild(checkbox);
                    label.appendChild(document.createTextNode(`${s.nama_sponsor} (Rp ${parseInt(s.nominal_dana).toLocaleString('id-ID')})`));
                    sponsorContainer.appendChild(label);
                });

                calculateSponsorTotal();
            }

            function calculateSponsorTotal() {
                let total = 0;
                const checkboxes = sponsorContainer.querySelectorAll('input[type="checkbox"]:checked');
                checkboxes.forEach(cb => {
                    total += parseInt(cb.dataset.nominal) || 0;
                });
                danaSponsorInput.value = total;
                displayDanaSponsor.textContent = 'Rp ' + total.toLocaleString('id-ID');
                hitungTotalPengeluaran();
            }

            prokerSelect.addEventListener('change', updateSponsorCheckboxes);

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
