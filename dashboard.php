<?php
session_start();
require 'config.php';

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit;
}

if (isset($_GET['hapus'])) {
    $id = $_GET['hapus'];
    mysqli_query($conn, "DELETE FROM anggota_ukm WHERE id='$id'");
    header("Location: dashboard.php");
}

$result = mysqli_query($conn, "SELECT * FROM anggota_ukm");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard UKM</title>
    <link rel="stylesheet" href="style.css?v=1">
</head>
<body>

    <div class="navbar">
        <div class="nav-brand">SIM UKM - Welcome, <?= $_SESSION['username']; ?>!</div>
        <div><a href="logout.php">Logout</a></div>
    </div>

    <div class="container">
        <h2>Data Anggota Internal UKM</h2>
        <a href="tambah.php" class="btn btn-tambah">+ Tambah Data Anggota</a>
        
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
                <?php $i = 1; while($row = mysqli_fetch_assoc($result)) : ?>
                <tr>
                    <td><?= $i++; ?></td>
                    <td><?= $row['nim']; ?></td>
                    <td><?= $row['nama']; ?></td>
                    <td><?= $row['divisi']; ?></td>
                    <td><?= $row['tahun_angkatan']; ?></td>
                    <td>
                        <a href="edit.php?id=<?= $row['id']; ?>" class="btn btn-edit">Edit</a>
                        <a href="dashboard.php?hapus=<?= $row['id']; ?>" class="btn btn-hapus" onclick="return confirm('Yakin hapus data?')">Hapus</a>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>

</body>
</html>