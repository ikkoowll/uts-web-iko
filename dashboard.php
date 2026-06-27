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
        <div><a href="logout.php">Logout</a></div>
    </div>

    <div class="container">
        <h2>Data Anggota Internal UKM</h2>
        
        <!-- Statistik Grid -->
        <div class="stats-grid">
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
        
        <a href="tambah.php" class="btn btn-tambah">+ Tambah Data Anggota</a>

        <div class="table-wrapper">
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
                    $query = mysqli_query($conn, "SELECT * FROM anggota_ukm ORDER BY id_user DESC");
                    
                    // Mengecek apakah ada data di database
                    if(mysqli_num_rows($query) > 0) {
                        // Looping data dari database
                        while($row = mysqli_fetch_assoc($query)) {
                    ?>
                            <tr>
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
                                    <a href="edit.php?id=<?php echo $row['id_user']; ?>" class="btn btn-edit">Edit</a>
                                    <a href="hapus.php?id=<?php echo $row['id_user']; ?>" class="btn btn-hapus" onclick="return confirm('Yakin ingin menghapus data ini?');">Hapus</a>
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
    </div>

</body>
</html>