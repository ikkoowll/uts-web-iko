<?php
session_start();
require 'config.php';

// Pastikan user sudah login
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit;
}

// 1. Ambil ID dari URL
if (!isset($_GET['id'])) {
    header("Location: dashboard.php");
    exit;
}
$id = $_GET['id'];

// 2. Ambil data lama dari database untuk diisikan ke dalam form
$query = mysqli_query($conn, "SELECT * FROM anggota_ukm WHERE id_user = '$id'");
$data = mysqli_fetch_assoc($query);

// Jika data tidak ditemukan di database
if (!$data) {
    $_SESSION['swal_error'] = 'Data tidak ditemukan!';
    header("Location: dashboard.php");
    exit;
}

// 3. Logika untuk menyimpan perubahan ketika tombol "Update Data" diklik
if (isset($_POST['update'])) {
    $nim = $_POST['nim'];
    $nama = $_POST['nama'];
    $divisi = $_POST['divisi'];
    $angkatan = $_POST['tahun_angkatan'];
    // Query UPDATE untuk mengubah data lama menjadi data baru
    $update_query = "UPDATE anggota_ukm SET nim='$nim', nama='$nama', divisi='$divisi', tahun_angkatan='$angkatan' WHERE id_user='$id'";

    if (mysqli_query($conn, $update_query)) {
        // Catat aktivitas ke log
        $admin_user = mysqli_real_escape_string($conn, $_SESSION['username']);
        $log_aksi = mysqli_real_escape_string($conn, "Mengubah data anggota bernama " . $nama);
        mysqli_query($conn, "INSERT INTO log_aktivitas (user, aksi) VALUES ('$admin_user', '$log_aksi')");

        $_SESSION['swal_success'] = 'Data anggota berhasil diperbarui!';
        header("Location: dashboard.php");
        exit;
    } else {
        $error = "Gagal mengupdate data: " . mysqli_error($conn);
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Data Anggota - SIM HIMATIF</title>
    <link rel="stylesheet" href="style.css?v=1.3">
</head>
<body>

    <div class="navbar">
        <div class="nav-brand">SIM HIMATIF - Edit Data</div>
        <div><a href="dashboard.php">Batal / Kembali</a></div>
    </div>

    <div class="container">
        <div class="form-card">
            <h2>Edit Data Anggota</h2>
            
            <?php if(isset($error)) echo "<p class='error-msg'>$error</p>"; ?>

            <form method="POST" action="">
                
                <div class="form-group">
                    <label for="nim">NIM</label>
                    <input type="text" name="nim" id="nim" value="<?php echo $data['nim']; ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="nama">Nama Lengkap</label>
                    <input type="text" name="nama" id="nama" value="<?php echo $data['nama']; ?>" required>
                </div>

                <div class="form-group">
                    <label for="divisi">Pilihan Divisi</label>
                    <select name="divisi" id="divisi" required>
                        <option value="Humas" <?php if($data['divisi'] == 'Humas') echo 'selected'; ?>>Hubungan Masyarakat (Humas)</option>
                        <option value="PSDM" <?php if($data['divisi'] == 'PSDM') echo 'selected'; ?>>PSDM</option>
                        <option value="Media dan Informasi" <?php if($data['divisi'] == 'Media dan Informasi') echo 'selected'; ?>>Media dan Informasi</option>
                        <option value="Event/Acara" <?php if($data['divisi'] == 'Event/Acara') echo 'selected'; ?>>Event / Acara</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="tahun_angkatan">Tahun Angkatan</label>
                    <input type="number" name="tahun_angkatan" id="tahun_angkatan" value="<?php echo $data['tahun_angkatan']; ?>" required>
                </div>



                <button type="submit" name="update">Update Data</button>
            </form>
        </div>
    </div>
    <?php include 'footer.php'; ?>
</body>
</html>