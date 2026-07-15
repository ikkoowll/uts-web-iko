<?php
session_start();
require 'config.php';

// Proteksi halaman
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit;
}

// Fetch proker list for select input
$proker_query = mysqli_query($conn, "SELECT id_proker, nama_proker FROM proker ORDER BY nama_proker ASC");
$proker_list = [];
while ($row = mysqli_fetch_assoc($proker_query)) {
    $proker_list[] = $row;
}

if (isset($_POST['simpan'])) {
    $nama_sponsor = mysqli_real_escape_string($conn, $_POST['nama_sponsor']);
    $nominal_dana = intval($_POST['nominal_dana']);
    $id_proker = intval($_POST['id_proker']);
    $status_pencairan = mysqli_real_escape_string($conn, $_POST['status_pencairan']);

    // Validasi data
    if (empty($nama_sponsor) || $nominal_dana < 0 || $id_proker <= 0 || !in_array($status_pencairan, ['Pending', 'Cair'])) {
        $error = "Semua field harus diisi dengan benar!";
    } else {
        // Cari nama proker untuk log
        $name_q = mysqli_query($conn, "SELECT nama_proker FROM proker WHERE id_proker = '$id_proker'");
        $name_data = mysqli_fetch_assoc($name_q);
        $nama_proker = $name_data['nama_proker'] ?? 'Program Kerja';

        $query = "INSERT INTO sponsor (nama_sponsor, nominal_dana, id_proker, status_pencairan) 
                  VALUES ('$nama_sponsor', '$nominal_dana', '$id_proker', '$status_pencairan')";

        if (mysqli_query($conn, $query)) {
            // Catat log
            $admin_user = mysqli_real_escape_string($conn, $_SESSION['username']);
            $log_aksi = mysqli_real_escape_string($conn, "Menambahkan sponsor " . $nama_sponsor . " sebesar Rp " . number_format($nominal_dana, 0, ',', '.') . " untuk proker " . $nama_proker . " (Status: " . $status_pencairan . ")");
            mysqli_query($conn, "INSERT INTO log_aktivitas (user, aksi) VALUES ('$admin_user', '$log_aksi')");

            $_SESSION['swal_success'] = 'Data sponsor berhasil disimpan!';
            header("Location: sponsor.php");
            exit;
        } else {
            $error = "Gagal menyimpan data sponsor: " . mysqli_error($conn);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Sponsor Baru - SIM HIMATIF</title>
    <link rel="stylesheet" href="style.css?v=1.3">
</head>
<body>

    <div class="navbar">
        <div class="nav-brand">SIM HIMATIF - Tambah Sponsor</div>
        <div><a href="sponsor.php">Kembali</a></div>
    </div>

    <div class="container">
        <div class="form-card">
            <h2>Tambah Data Sponsor Baru</h2>
            
            <?php if(isset($error)) echo "<p class='error-msg'>$error</p>"; ?>

            <?php if (empty($proker_list)): ?>
                <div class="error-msg" style="text-align: left; background: rgba(245, 158, 11, 0.1); border-color: rgba(245, 158, 11, 0.2); color: #f59e0b;">
                    Belum ada Program Kerja terdaftar di sistem. Silakan <a href="tambah_proker.php" style="color: #fff; font-weight: 700; text-decoration: underline;">tambah program kerja baru</a> terlebih dahulu.
                </div>
            <?php else: ?>
                <form method="POST" action="">
                    <div class="form-group">
                        <label for="nama_sponsor">Nama Sponsor</label>
                        <input type="text" name="nama_sponsor" id="nama_sponsor" placeholder="Masukkan nama sponsor (Contoh: Djarum)" required>
                    </div>

                    <div class="form-group">
                        <label for="nominal_dana">Nominal Dana (Rp)</label>
                        <input type="number" name="nominal_dana" id="nominal_dana" min="0" placeholder="Masukkan jumlah dana yang diberikan (Rp)" required>
                    </div>

                    <div class="form-group">
                        <label for="id_proker">Pilih Program Kerja Tujuan</label>
                        <select name="id_proker" id="id_proker" required>
                            <option value="" disabled selected>-- Pilih Program Kerja --</option>
                            <?php foreach ($proker_list as $p): ?>
                                <option value="<?php echo $p['id_proker']; ?>">
                                    <?php echo htmlspecialchars($p['nama_proker']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="status_pencairan">Status Pencairan</label>
                        <select name="status_pencairan" id="status_pencairan" required>
                            <option value="Pending" selected>Pending (Belum Cair)</option>
                            <option value="Cair">Cair (Uang Sudah Masuk)</option>
                        </select>
                    </div>

                    <button type="submit" name="simpan">Simpan Sponsor</button>
                </form>
            <?php endif; ?>
        </div>
    </div>

    <?php include 'footer.php'; ?>
</body>
</html>
