<?php
session_start();
require 'config.php';

// Pastikan user sudah login
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit;
}

// Mengecek apakah ada ID yang dikirim lewat URL
if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    
    // Ambil info proker sebelum dihapus untuk log
    $info_q = mysqli_query($conn, "SELECT nama_proker FROM proker WHERE id_proker = '$id'");
    $info_d = mysqli_fetch_assoc($info_q);
    $nama_proker = $info_d['nama_proker'] ?? 'Proker';
    
    // Query untuk menghapus data berdasarkan ID
    $query = "DELETE FROM proker WHERE id_proker = '$id'";
    
    if (mysqli_query($conn, $query)) {
        // Catat aktivitas ke log
        $admin_user = mysqli_real_escape_string($conn, $_SESSION['username']);
        $log_aksi = mysqli_real_escape_string($conn, "Menghapus Program Kerja: " . $nama_proker);
        mysqli_query($conn, "INSERT INTO log_aktivitas (user, aksi) VALUES ('$admin_user', '$log_aksi')");

        $_SESSION['swal_success'] = 'Program kerja berhasil dihapus!';
        header("Location: dashboard.php");
        exit;
    } else {
        $_SESSION['swal_error'] = 'Gagal menghapus program kerja!';
        header("Location: dashboard.php");
        exit;
    }
} else {
    header("Location: dashboard.php");
}
?>
