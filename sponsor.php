<?php
session_start();
require 'config.php';

// Proteksi halaman
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit;
}

// Fetch all sponsors
$query = mysqli_query($conn, "
    SELECT s.*, p.nama_proker 
    FROM sponsor s
    JOIN proker p ON s.id_proker = p.id_proker
    ORDER BY s.id_sponsor DESC
");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Dana Sponsor - SIM HIMATIF</title>
    <link rel="stylesheet" href="style.css?v=1.3">
</head>
<body>

    <div class="navbar">
        <div class="nav-brand">SIM HIMATIF - Sponsor</div>
        <div style="display: flex; gap: 12px; align-items: center;">
            <a href="dashboard.php">Dashboard</a>
            <a href="sponsor.php" class="active-nav">Sponsor</a>
            <a href="logout.php">Logout</a>
        </div>
    </div>

    <div class="container">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; flex-wrap: wrap; gap: 16px;">
            <h2 style="margin-bottom: 0;">Daftar Sponsor Program Kerja</h2>
            <a href="tambah_sponsor.php" class="btn btn-tambah" style="margin-bottom: 0; background: linear-gradient(135deg, #a855f7 0%, #7c3aed 100%);">+ Tambah Sponsor Baru</a>
        </div>

        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th width="5%">No</th>
                        <th width="30%">Nama Sponsor</th>
                        <th width="25%">Program Kerja Tujuan</th>
                        <th width="15%">Nominal Dana</th>
                        <th width="12%">Status Pencairan</th>
                        <th width="13%">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if (mysqli_num_rows($query) > 0) {
                        $no = 1;
                        while ($row = mysqli_fetch_assoc($query)) {
                            $nominal = number_format($row['nominal_dana'], 0, ',', '.');
                            $status_class = $row['status_pencairan'] == 'Cair' ? 'badge-tercapai' : 'badge-belum';
                            $status_text = $row['status_pencairan'] == 'Cair' ? 'Cair' : 'Pending';
                    ?>
                            <tr>
                                <td style="text-align: center;"><?php echo $no++; ?></td>
                                <td style="font-weight: 600; color: var(--title-color);"><?php echo htmlspecialchars($row['nama_sponsor']); ?></td>
                                <td><?php echo htmlspecialchars($row['nama_proker']); ?></td>
                                <td><strong>Rp <?php echo $nominal; ?></strong></td>
                                <td style="text-align: center;">
                                    <span class="proker-badge <?php echo $status_class; ?>" style="display: inline-block; padding: 4px 8px; font-size: 12px;"><?php echo $status_text; ?></span>
                                </td>
                                <td>
                                    <a href="edit_sponsor.php?id=<?php echo $row['id_sponsor']; ?>" class="btn btn-edit">Edit</a>
                                    <a href="hapus_sponsor.php?id=<?php echo $row['id_sponsor']; ?>" class="btn btn-hapus" data-message="Yakin ingin menghapus data sponsor ini?">Hapus</a>
                                </td>
                            </tr>
                    <?php
                        }
                    } else {
                        echo "<tr><td colspan='6' style='text-align: center; color: var(--text-secondary); padding: 24px;'>Belum ada data sponsor. Klik tombol '+ Tambah Sponsor Baru' untuk mencatat.</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>

    <?php include 'footer.php'; ?>
    <?php include 'alerts.php'; ?>
</body>
</html>
