-- Database Migration for SIM HIMATIF Performance tracking

CREATE TABLE IF NOT EXISTS `proker` (
  `id_proker` INT AUTO_INCREMENT PRIMARY KEY,
  `nama_proker` VARCHAR(255) NOT NULL,
  `target_frekuensi` INT NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `pelaksanaan_proker` (
  `id_pelaksanaan` INT AUTO_INCREMENT PRIMARY KEY,
  `id_proker` INT NOT NULL,
  `pelaksanaan_ke` INT NOT NULL,
  `tanggal_pelaksanaan` DATE NOT NULL,
  `jumlah_peserta` INT NOT NULL,
  `total_pengeluaran` INT NOT NULL DEFAULT 0,
  `dana_dari_kas` INT NOT NULL DEFAULT 0,
  `dana_dari_sponsor` INT NOT NULL DEFAULT 0,
  `dana_dari_kemahasiswaan` INT NOT NULL DEFAULT 0,
  `dampak_ke_himpunan` TEXT NOT NULL,
  `evaluasi_kegiatan` TEXT NULL,
  FOREIGN KEY (`id_proker`) REFERENCES `proker` (`id_proker`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Seed default prokers if empty
INSERT INTO `proker` (`nama_proker`, `target_frekuensi`) 
SELECT * FROM (
  SELECT 'Rapat Kerja (Raker)' AS nama_proker, 1 AS target UNION ALL
  SELECT 'Latihan Dasar Kepemimpinan (LDKM)', 1 UNION ALL
  SELECT 'HIMATIF Sharing Session & Webinar', 4 UNION ALL
  SELECT 'GATHERING & Akrab Informatika', 2 UNION ALL
  SELECT 'Pengabdian Masyarakat (Desa Binaan)', 2 UNION ALL
  SELECT 'HIMATIF Dev Competitions', 1
) tmp
WHERE NOT EXISTS (
  SELECT 1 FROM `proker` LIMIT 1
);

-- Create master student budget table
CREATE TABLE IF NOT EXISTS `dana_kemahasiswaan_master` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `anggaran` INT NOT NULL DEFAULT 5500000,
  `periode` VARCHAR(50) NOT NULL DEFAULT '2026/2027'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Seed master budget default
INSERT INTO `dana_kemahasiswaan_master` (`anggaran`, `periode`)
SELECT 5500000, '2026/2027' FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `dana_kemahasiswaan_master` LIMIT 1);

-- Create sponsor table
CREATE TABLE IF NOT EXISTS `sponsor` (
  `id_sponsor` INT AUTO_INCREMENT PRIMARY KEY,
  `nama_sponsor` VARCHAR(255) NOT NULL,
  `nominal_dana` INT NOT NULL DEFAULT 0,
  `id_proker` INT NOT NULL,
  `status_pencairan` ENUM('Pending', 'Cair') NOT NULL DEFAULT 'Pending',
  FOREIGN KEY (`id_proker`) REFERENCES `proker` (`id_proker`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Ensure users table has email and reset token columns for forgot password features
ALTER TABLE `users` ADD COLUMN IF NOT EXISTS `email` VARCHAR(100) NOT NULL DEFAULT 'admin@himatif.org' AFTER `username`;
ALTER TABLE `users` ADD COLUMN IF NOT EXISTS `reset_token` VARCHAR(255) NULL AFTER `password`;
ALTER TABLE `users` ADD COLUMN IF NOT EXISTS `reset_expiry` DATETIME NULL AFTER `reset_token`;

-- Ensure pelaksanaan_proker table has total_pengeluaran and evaluasi_kegiatan columns
ALTER TABLE `pelaksanaan_proker` ADD COLUMN IF NOT EXISTS `total_pengeluaran` INT NOT NULL DEFAULT 0 AFTER `jumlah_peserta`;
ALTER TABLE `pelaksanaan_proker` ADD COLUMN IF NOT EXISTS `dana_dari_kas` INT NOT NULL DEFAULT 0 AFTER `jumlah_peserta`;
ALTER TABLE `pelaksanaan_proker` ADD COLUMN IF NOT EXISTS `dana_dari_sponsor` INT NOT NULL DEFAULT 0 AFTER `dana_dari_kas`;
ALTER TABLE `pelaksanaan_proker` ADD COLUMN IF NOT EXISTS `dana_dari_kemahasiswaan` INT NOT NULL DEFAULT 0 AFTER `dana_dari_sponsor`;
ALTER TABLE `pelaksanaan_proker` ADD COLUMN IF NOT EXISTS `evaluasi_kegiatan` TEXT NULL AFTER `dampak_ke_himpunan`;
