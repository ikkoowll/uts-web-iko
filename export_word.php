<?php
session_start();
require 'config.php';

// Proteksi halaman, pastikan user sudah login
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit;
}

// Log aktivitas
$admin_user = mysqli_real_escape_string($conn, $_SESSION['username']);
$log_aksi = mysqli_real_escape_string($conn, "Mengekspor data anggota ke Word");
mysqli_query($conn, "INSERT INTO log_aktivitas (user, aksi) VALUES ('$admin_user', '$log_aksi')");

header("Content-type: application/vnd.ms-word");
header("Content-Disposition: attachment; filename=Data_Anggota_HIMATIF_" . date('Ymd_His') . ".doc");
header("Pragma: no-cache");
header("Expires: 0");
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Data Anggota HIMATIF</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            color: #333333;
        }
        h2 {
            text-align: center;
            color: #021A54;
            margin-bottom: 5px;
        }
        .subtitle {
            text-align: center;
            font-size: 14px;
            color: #666666;
            margin-bottom: 25px;
        }
        table {
            border-collapse: collapse;
            width: 100%;
            margin-top: 10px;
        }
        th {
            background-color: #021A54;
            color: #ffffff;
            font-weight: bold;
            border: 1px solid #000000;
            padding: 10px;
            text-align: left;
        }
        td {
            border: 1px solid #dddddd;
            padding: 8px;
            text-align: left;
        }
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .center {
            text-align: center;
        }
    </style>
</head>
<body>
    <h2>DATA ANGGOTA INTERNAL HIMATIF</h2>
    <div class="subtitle">Diunduh pada tanggal: <?php echo date('d-m-Y H:i'); ?> WIB</div>
    
    <table>
        <thead>
            <tr>
                <th width="5%" class="center">No</th>
                <th width="20%">NIM</th>
                <th width="35%">Nama Lengkap</th>
                <th width="25%">Divisi</th>
                <th width="15%" class="center">Angkatan</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $query = mysqli_query($conn, "SELECT * FROM anggota_ukm ORDER BY id_user DESC");
            $no = 1;
            while ($row = mysqli_fetch_assoc($query)) {
                echo "<tr>";
                echo "<td class='center'>" . $no++ . "</td>";
                // Menggunakan styling khusus atau kutip agar NIM tidak dikonversi jadi notasi ilmiah di MS Word jika terlalu panjang
                echo "<td>'" . htmlspecialchars($row['nim']) . "</td>";
                echo "<td>" . htmlspecialchars($row['nama']) . "</td>";
                echo "<td>" . htmlspecialchars($row['divisi']) . "</td>";
                echo "<td class='center'>" . htmlspecialchars($row['tahun_angkatan']) . "</td>";
                echo "</tr>";
            }
            ?>
        </tbody>
    </table>
</body>
</html>
<?php
exit;
?>
