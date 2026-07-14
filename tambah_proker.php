<?php
session_start();
require 'config.php';

// Proteksi halaman: Pastikan user sudah login
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit;
}

if (isset($_POST['simpan'])) {
    $nama_proker = mysqli_real_escape_string($conn, $_POST['nama_proker']);
    $target = intval($_POST['target']);

    if (empty($nama_proker) || $target <= 0) {
        $error = "Nama proker dan target frekuensi harus diisi dengan benar!";
    } else {
        $query = "INSERT INTO proker (nama_proker, target_frekuensi_dalam_1_periode) VALUES ('$nama_proker', '$target')";
        if (mysqli_query($conn, $query)) {
            // Catat log
            $admin_user = mysqli_real_escape_string($conn, $_SESSION['username']);
            $log_aksi = mysqli_real_escape_string($conn, "Menambahkan Program Kerja baru: " . $nama_proker . " (Target: " . $target . " kali)");
            mysqli_query($conn, "INSERT INTO log_aktivitas (user, aksi) VALUES ('$admin_user', '$log_aksi')");

            $_SESSION['swal_success'] = 'Program kerja berhasil ditambahkan!';
            header("Location: dashboard.php");
            exit;
        } else {
            $error = "Gagal menambahkan program kerja: " . mysqli_error($conn);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Program Kerja - SIM HIMATIF</title>
    <link rel="stylesheet" href="style.css?v=1.2">
</head>
<body>

    <div class="navbar">
        <div class="nav-brand">SIM HIMATIF - Tambah Proker</div>
        <div><a href="dashboard.php">Kembali</a></div>
    </div>

    <div class="container">
        <div class="form-card">
            <h2>Tambah Program Kerja Baru</h2>
            
            <?php if(isset($error)) echo "<p class='error-msg'>$error</p>"; ?>

            <form method="POST" action="">
                <div class="form-group">
                    <label for="nama_proker">Nama Program Kerja</label>
                    <input type="text" name="nama_proker" id="nama_proker" placeholder="Masukkan nama program kerja (misal: IT Web Seminar)" required>
                </div>
                
                <div class="form-group">
                    <label for="target">Target Frekuensi (dalam 1 periode)</label>
                    <input type="number" name="target" id="target" min="1" placeholder="Masukkan target frekuensi (misal: 4)" required>
                </div>

                <button type="submit" name="simpan">Simpan Proker</button>
            </form>
        </div>
    </div>
    <?php include 'footer.php'; ?>
</body>
</html>
