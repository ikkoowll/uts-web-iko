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
    $id = $_GET['id'];
    
    // Hapus riwayat kas anggota terlebih dahulu untuk menjaga integritas data
    mysqli_query($conn, "DELETE FROM pembayaran_kas WHERE id_anggota = '$id'");
    
    // Query untuk menghapus data berdasarkan ID
    $query = "DELETE FROM anggota_ukm WHERE id_user = '$id'";
    
    if (mysqli_query($conn, $query)) {
        echo "<script>
                alert('Data berhasil dihapus!');
                window.location='dashboard.php';
              </script>";
    } else {
        echo "<script>
                alert('Gagal menghapus data!');
                window.location='dashboard.php';
              </script>";
    }
} else {
    // Jika tidak ada ID di URL, kembalikan ke dashboard
    header("Location: dashboard.php");
}
?>