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
    
    // Ambil nama anggota sebelum dihapus untuk log
    $member_q = mysqli_query($conn, "SELECT nama FROM anggota_ukm WHERE id_user = '$id'");
    $member_d = mysqli_fetch_assoc($member_q);
    $nama_terhapus = $member_d['nama'] ?? 'Tidak Diketahui';
    
    // Hapus riwayat kas anggota terlebih dahulu untuk menjaga integritas data
    mysqli_query($conn, "DELETE FROM pembayaran_kas WHERE id_anggota = '$id'");
    
    // Query untuk menghapus data berdasarkan ID
    $query = "DELETE FROM anggota_ukm WHERE id_user = '$id'";
    
    if (mysqli_query($conn, $query)) {
        // Catat aktivitas ke log
        $admin_user = mysqli_real_escape_string($conn, $_SESSION['username']);
        $log_aksi = mysqli_real_escape_string($conn, "Menghapus anggota bernama " . $nama_terhapus);
        mysqli_query($conn, "INSERT INTO log_aktivitas (user, aksi) VALUES ('$admin_user', '$log_aksi')");

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