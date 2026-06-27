<?php
session_start();
require 'config.php';

// Proteksi halaman: Pastikan user sudah login sebelum bisa mengakses form ini
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit;
}

// Logika untuk menyimpan data ketika tombol "Simpan Data" diklik
if (isset($_POST['simpan'])) {
    // Menangkap data dari form input
    $nim = $_POST['nim'];
    $nama = $_POST['nama'];
    $divisi = $_POST['divisi'];
    $angkatan = $_POST['tahun_angkatan'];

    // Query untuk memasukkan data ke tabel anggota_ukm
    $query = "INSERT INTO anggota_ukm (nim, nama, divisi, tahun_angkatan) VALUES ('$nim', '$nama', '$divisi', '$angkatan')";

    // Menjalankan query dan mengecek apakah berhasil
    if (mysqli_query($conn, $query)) {
        // Jika berhasil, munculkan pesan dan kembali ke dashboard
        echo "<script>
                alert('Data anggota berhasil ditambahkan!');
                window.location='dashboard.php';
              </script>";
    } else {
        // Jika gagal (misal NIM sudah terdaftar karena UNIQUE), tampilkan error
        $error = "Gagal menambah data: " . mysqli_error($conn);
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Data Anggota - SIM UKM</title>
    <link rel="stylesheet" href="style.css?v=1.1">
</head>
<body>

    <div class="navbar">
        <div class="nav-brand">SIM UKM - Tambah Data</div>
        <div><a href="dashboard.php">Kembali</a></div>
    </div>

    <div class="container">
        <div class="form-card">
            <h2>Form Pendaftaran Anggota</h2>
            
            <?php if(isset($error)) echo "<p class='error-msg'>$error</p>"; ?>

            <form method="POST" action="">
                
                <div class="form-group">
                    <label for="nim">NIM (Nomor Induk Mahasiswa)</label>
                    <input type="text" name="nim" id="nim" placeholder="Masukkan NIM (misal: 2021001)" required>
                </div>
                
                <div class="form-group">
                    <label for="nama">Nama Lengkap</label>
                    <input type="text" name="nama" id="nama" placeholder="Sesuai dengan KTM" required>
                </div>

                <div class="form-group">
                    <label for="divisi">Pilihan Divisi</label>
                    <select name="divisi" id="divisi" required>
                        <option value="" disabled selected>-- Pilih Divisi --</option>
                        <option value="Humas">Hubungan Masyarakat (Humas)</option>
                        <option value="PSDM">PSDM</option>
                        <option value="Media dan Informasi">Media dan Informasi</option>
                        <option value="Event/Acara">Event / Acara</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="tahun_angkatan">Tahun Angkatan</label>
                    <input type="number" name="tahun_angkatan" id="tahun_angkatan" placeholder="Contoh: 2023" required>
                </div>

                <button type="submit" name="simpan">Simpan Data</button>
            </form>
        </div>
    </div>

</body>
</html>