<?php
session_start();
require 'config.php'; 

// Proteksi halaman, pastikan user sudah login
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard UKM</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

    <div class="navbar">
        <div class="nav-brand">SIM UKM - Welcome, <?php echo $_SESSION['username']; ?>!</div>
        <div><a href="logout.php" style="background: #cea9e0;">Logout</a></div>
    </div>

    <div class="container">
        <h2>Data Anggota Internal UKM</h2>
        
        <a href="tambah.php" class="btn btn-tambah">+ Tambah Data Anggota</a>

    <div class="content-wrapper">
        <h2>Data Anggota Internal UKM</h2>
    </div>

        <table>
            <thead>
                <tr>
                    <th>No</th>
                    <th>NIM</th>
                    <th>Nama Lengkap</th>
                    <th>Divisi</th>
                    <th>Angkatan</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // Menyiapkan variabel nomor urut
                $no = 1;
                
                // Query untuk mengambil (Read) semua data dari tabel anggota_ukm
                $query = mysqli_query($conn, "SELECT * FROM anggota_ukm ORDER BY id DESC");
                
                // Mengecek apakah ada data di database
                if(mysqli_num_rows($query) > 0) {
                    // Looping data dari database
                    while($row = mysqli_fetch_assoc($query)) {
                ?>
                        <tr>
                            <td><?php echo $no++; ?></td>
                            <td><?php echo $row['nim']; ?></td>
                            <td><?php echo $row['nama']; ?></td>
                            <td><?php echo $row['divisi']; ?></td>
                            <td><?php echo $row['tahun_angkatan']; ?></td>
                            <td>
                                <a href="edit.php?id=<?php echo $row['id']; ?>" class="btn btn-edit">Edit</a>
                                <a href="hapus.php?id=<?php echo $row['id']; ?>" class="btn btn-hapus" onclick="return confirm('Yakin ingin menghapus data ini?');">Hapus</a>
                            </td>
                        </tr>
                <?php 
                    } // Penutup while
                } else {
                    // Jika data masih kosong
                    echo "<tr><td colspan='6' style='text-align: center;'>Belum ada data anggota.</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>

</body>
</html>