-- Create table for document types
CREATE TABLE `master_jenis_dokumen` (
  `id_jenis_dokumen` int(11) NOT NULL AUTO_INCREMENT,
  `kode_jenis` varchar(20) NOT NULL,
  `nama_jenis` varchar(100) NOT NULL,
  `deskripsi` text DEFAULT NULL,
  `template_path` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `created_by` int(11) DEFAULT NULL,
  `updated_by` int(11) DEFAULT NULL,
  PRIMARY KEY (`id_jenis_dokumen`),
  UNIQUE KEY `kode_jenis` (`kode_jenis`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default document types
INSERT INTO `master_jenis_dokumen` (`kode_jenis`, `nama_jenis`, `deskripsi`) VALUES
('BA', 'Berita Acara', 'Dokumen berita acara untuk kepentingan pelaporan dan dokumentasi'),
('PI', 'Pakta Integritas', 'Dokumen pakta integritas untuk pernyataan kesanggupan dan komitmen'),
('DH', 'Daftar Hadir', 'Dokumen daftar hadir untuk pencatatan kehadiran peserta'),
('LP', 'Laporan', 'Dokumen laporan kegiatan atau hasil pelaksanaan'),
('LL', 'Lain-lain', 'Dokumen lainnya sesuai kebutuhan');
