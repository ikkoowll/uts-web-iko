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

        // Ambil nama anggota untuk logging
        $member_q = mysqli_query($conn, "SELECT nama FROM anggota_ukm WHERE id_user = '$id_anggota'");
        $member_d = mysqli_fetch_assoc($member_q);
        $nama = $member_d['nama'] ?? 'Tidak Diketahui';

        $months = [
            1 => 'Jan', 2 => 'Feb', 3 => 'Mar', 4 => 'Apr',
            5 => 'Mei', 6 => 'Jun', 7 => 'Jul', 8 => 'Agt',
            9 => 'Sep', 10 => 'Okt', 11 => 'Nov', 12 => 'Des'
        ];
        $bulan_nama = $months[$bulan] ?? 'Bulan';

        $admin_user = mysqli_real_escape_string($conn, $_SESSION['username']);

        if (mysqli_num_rows($result) > 0) {
            // Jika sudah ada (artinya sudah lunas), hapus data untuk mengubah status menjadi Belum Bayar
            $query_toggle = "DELETE FROM pembayaran_kas WHERE id_anggota = '$id_anggota' AND id_bulan = '$bulan' AND tahun = '$tahun'";
            $log_aksi = mysqli_real_escape_string($conn, "Mengubah status kas " . $nama . " bulan " . $bulan_nama . " menjadi Belum Bayar");
        } else {
            // Jika belum ada, masukkan data baru untuk mengubah status menjadi Sudah Bayar (Lunas)
            $query_toggle = "INSERT INTO pembayaran_kas (id_anggota, id_bulan, tahun, nominal, tgl_bayar, status_bayar) 
                             VALUES ('$id_anggota', '$bulan', '$tahun', 10000, NOW(), 'Sudah Bayar')";
            $log_aksi = mysqli_real_escape_string($conn, "Mengubah status kas " . $nama . " bulan " . $bulan_nama . " menjadi Done");
        }

        if (mysqli_query($conn, $query_toggle)) {
            mysqli_query($conn, "INSERT INTO log_aktivitas (user, aksi) VALUES ('$admin_user', '$log_aksi')");
        }
    }
}

// Kembali ke dashboard setelah memproses toggle
header("Location: dashboard.php");
exit;
?>
