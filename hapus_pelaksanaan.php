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
    
    // Ambil info pelaksanaan sebelum dihapus untuk log
    $info_q = mysqli_query($conn, "
        SELECT pp.pelaksanaan_ke, p.nama_proker 
        FROM pelaksanaan_proker pp
        JOIN proker p ON pp.id_proker = p.id_proker
        WHERE pp.id_pelaksanaan = '$id'
    ");
    $info_d = mysqli_fetch_assoc($info_q);
    $nama_proker = $info_d['nama_proker'] ?? 'Proker';
    $ke = $info_d['pelaksanaan_ke'] ?? '1';
    
    // Query untuk menghapus data berdasarkan ID
    $query = "DELETE FROM pelaksanaan_proker WHERE id_pelaksanaan = '$id'";
    
    if (mysqli_query($conn, $query)) {
        // Catat aktivitas ke log
        $admin_user = mysqli_real_escape_string($conn, $_SESSION['username']);
        $log_aksi = mysqli_real_escape_string($conn, "Menghapus catatan pelaksanaan ke-" . $ke . " dari proker " . $nama_proker);
        mysqli_query($conn, "INSERT INTO log_aktivitas (user, aksi) VALUES ('$admin_user', '$log_aksi')");

        $_SESSION['swal_success'] = 'Catatan pelaksanaan proker berhasil dihapus!';
        header("Location: dashboard.php");
        exit;
    } else {
        $_SESSION['swal_error'] = 'Gagal menghapus catatan pelaksanaan proker!';
        header("Location: dashboard.php");
        exit;
    }
} else {
    header("Location: dashboard.php");
}
?>
