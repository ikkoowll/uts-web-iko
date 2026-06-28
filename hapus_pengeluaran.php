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
    
    $query = "DELETE FROM pengeluaran_kas WHERE id = '$id'";
    if (mysqli_query($conn, $query)) {
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
