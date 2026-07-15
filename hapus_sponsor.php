<?php
session_start();
require 'config.php';

// Proteksi halaman
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit;
}

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Fetch sponsor info for logging before delete
$sponsor_query = mysqli_query($conn, "SELECT nama_sponsor, nominal_dana FROM sponsor WHERE id_sponsor = '$id'");
$sponsor = mysqli_fetch_assoc($sponsor_query);

if ($sponsor) {
    $nama_sponsor = $sponsor['nama_sponsor'];
    $nominal = $sponsor['nominal_dana'];

    $query = "DELETE FROM sponsor WHERE id_sponsor = '$id'";

    if (mysqli_query($conn, $query)) {
        // Catat log
        $admin_user = mysqli_real_escape_string($conn, $_SESSION['username']);
        $log_aksi = mysqli_real_escape_string($conn, "Menghapus data sponsor " . $nama_sponsor . " sebesar Rp " . number_format($nominal, 0, ',', '.'));
        mysqli_query($conn, "INSERT INTO log_aktivitas (user, aksi) VALUES ('$admin_user', '$log_aksi')");

        $_SESSION['swal_success'] = 'Data sponsor berhasil dihapus!';
    } else {
        $_SESSION['swal_error'] = 'Gagal menghapus data sponsor: ' . mysqli_error($conn);
    }
}

header("Location: sponsor.php");
exit;
?>
