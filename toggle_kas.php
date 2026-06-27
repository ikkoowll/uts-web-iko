<?php
session_start();
require 'config.php';

// Proteksi: Pastikan user sudah login
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit;
}

// Ambil data input
if (isset($_GET['id_anggota']) && isset($_GET['bulan'])) {
    $id_anggota = intval($_GET['id_anggota']);
    $bulan = intval($_GET['bulan']);
    $tahun = isset($_GET['tahun']) ? intval($_GET['tahun']) : intval(date('Y'));

    // Validasi input
    if ($id_anggota > 0 && $bulan >= 1 && $bulan <= 12 && $tahun > 0) {
        
        // Cek status pembayaran saat ini
        $query_check = "SELECT * FROM pembayaran_kas WHERE id_anggota = '$id_anggota' AND id_bulan = '$bulan' AND tahun = '$tahun'";
        $result = mysqli_query($conn, $query_check);

        if (mysqli_num_rows($result) > 0) {
            // Jika sudah ada (artinya sudah lunas), hapus data untuk mengubah status menjadi Belum Bayar
            $query_toggle = "DELETE FROM pembayaran_kas WHERE id_anggota = '$id_anggota' AND id_bulan = '$bulan' AND tahun = '$tahun'";
        } else {
            // Jika belum ada, masukkan data baru untuk mengubah status menjadi Sudah Bayar (Lunas)
            $query_toggle = "INSERT INTO pembayaran_kas (id_anggota, id_bulan, tahun, nominal, tgl_bayar, status_bayar) 
                             VALUES ('$id_anggota', '$bulan', '$tahun', 10000, NOW(), 'Sudah Bayar')";
        }

        mysqli_query($conn, $query_toggle);
    }
}

// Kembali ke dashboard setelah memproses toggle
header("Location: dashboard.php");
exit;
?>
