<?php
session_start();
require 'config.php';

// Proteksi halaman, pastikan user sudah login
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit;
}

// Logika menghapus catatan pengeluaran
if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    
    // Ambil judul pengeluaran sebelum dihapus untuk log
    $exp_q = mysqli_query($conn, "SELECT judul_pengeluaran FROM pengeluaran_kas WHERE id = '$id'");
    $exp_d = mysqli_fetch_assoc($exp_q);
    $judul_terhapus = $exp_d['judul_pengeluaran'] ?? 'Tidak Diketahui';
    
    $query = "DELETE FROM pengeluaran_kas WHERE id = '$id'";
    if (mysqli_query($conn, $query)) {
        // Catat aktivitas ke log
        $admin_user = mysqli_real_escape_string($conn, $_SESSION['username']);
        $log_aksi = mysqli_real_escape_string($conn, "Menghapus catatan pengeluaran kas: " . $judul_terhapus);
        mysqli_query($conn, "INSERT INTO log_aktivitas (user, aksi) VALUES ('$admin_user', '$log_aksi')");

        echo "<script>
                alert('Data pengeluaran berhasil dihapus!');
                window.location='pengeluaran.php';
              </script>";
        exit;
    } else {
        echo "<script>
                alert('Gagal menghapus data pengeluaran: " . mysqli_error($conn) . "');
                window.location='pengeluaran.php';
              </script>";
        exit;
    }
} else {
    header("Location: pengeluaran.php");
    exit;
}
?>
