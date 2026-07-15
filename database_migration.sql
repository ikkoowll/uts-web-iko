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

-- Ensure users table has email and reset token columns for forgot password features
ALTER TABLE `users` ADD COLUMN IF NOT EXISTS `email` VARCHAR(100) NOT NULL DEFAULT 'admin@himatif.org' AFTER `username`;
ALTER TABLE `users` ADD COLUMN IF NOT EXISTS `reset_token` VARCHAR(255) NULL AFTER `password`;
ALTER TABLE `users` ADD COLUMN IF NOT EXISTS `reset_expiry` DATETIME NULL AFTER `reset_token`;

-- Ensure pelaksanaan_proker table has total_pengeluaran and evaluasi_kegiatan columns
ALTER TABLE `pelaksanaan_proker` ADD COLUMN IF NOT EXISTS `total_pengeluaran` INT NOT NULL DEFAULT 0 AFTER `jumlah_peserta`;
ALTER TABLE `pelaksanaan_proker` ADD COLUMN IF NOT EXISTS `evaluasi_kegiatan` TEXT NULL AFTER `dampak_ke_himpunan`;
