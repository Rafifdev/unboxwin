-- =====================================================================
-- DATABASE SCHEMA: SPIN WHEEL & QR BATCH VALIDATION
-- Engine: MySQL 5.7+ / MariaDB 10.2+ (InnoDB, UTF-8 MB4)
-- Database: spin_wheel_db
-- =====================================================================

SET FOREIGN_KEY_CHECKS = 0;

-- ---------------------------------------------------------------------
-- 1. Table: batches (Master Batch Produksi QR)
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS `batches`;
CREATE TABLE `batches` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `batch_produksi` VARCHAR(50) NOT NULL,
  `no_awal` BIGINT UNSIGNED NOT NULL DEFAULT 1,
  `no_akhir` BIGINT UNSIGNED NOT NULL DEFAULT 1,
  `jumlah_produk` INT UNSIGNED NOT NULL DEFAULT 0,
  `jumlah_terverifikasi` INT UNSIGNED NOT NULL DEFAULT 0,
  `tgl_expired` DATE NOT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_batch_produksi` (`batch_produksi`),
  INDEX `idx_tgl_expired` (`tgl_expired`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- 2. Table: unique_qr_tokens (Detail Token per Produk)
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS `unique_qr_tokens`;
CREATE TABLE `unique_qr_tokens` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `batch_id` INT UNSIGNED NOT NULL,
  `no_produk` BIGINT UNSIGNED NOT NULL,
  `qr_token` VARCHAR(20) NOT NULL,
  `status_verifikasi` ENUM('UNVERIFIED', 'VERIFIED') NOT NULL DEFAULT 'UNVERIFIED',
  `tanggal_verifikasi` DATETIME NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_no_produk` (`no_produk`),
  UNIQUE KEY `uniq_qr_token` (`qr_token`),
  INDEX `idx_batch_id` (`batch_id`),
  INDEX `idx_status_verifikasi` (`status_verifikasi`),
  CONSTRAINT `fk_tokens_batch` FOREIGN KEY (`batch_id`) REFERENCES `batches` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- 3. Table: transactions (Data Transaksi & Omzet untuk Validasi)
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS `transactions`;
CREATE TABLE `transactions` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `no_pesanan` VARCHAR(100) NOT NULL,
  `tanggal_transaksi` DATETIME NOT NULL,
  `nama_cabang` VARCHAR(100) NOT NULL,
  `detail_barang` TEXT NULL DEFAULT NULL,
  `qty` INT NOT NULL DEFAULT 1,
  `harga_barang` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
  `sub_total` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
  `ongkir` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
  `total_bayar` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
  `purchase_specialist` VARCHAR(100) NULL DEFAULT NULL,
  `no_purchase_specialist` VARCHAR(30) NULL DEFAULT NULL,
  `status_transaksi` VARCHAR(50) NOT NULL DEFAULT 'COMPLETED',
  `is_used_spin` TINYINT(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_no_pesanan` (`no_pesanan`),
  INDEX `idx_tanggal_transaksi` (`tanggal_transaksi`),
  INDEX `idx_is_used_spin` (`is_used_spin`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- 4. Table: prizes (Master Hadiah & Konfigurasi Algoritma)
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS `prizes`;
CREATE TABLE `prizes` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `nama_hadiah` VARCHAR(100) NOT NULL,
  `kategori_hadiah` ENUM('UTAMA', 'REGULER', 'HIBURAN/VOUCHER') NOT NULL DEFAULT 'REGULER',
  `nilai_hadiah` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
  `stok_tersedia` INT NOT NULL DEFAULT 0,
  `max_frekuensi_bulanan` INT NOT NULL DEFAULT 0,
  `max_frekuensi_tahunan` INT NOT NULL DEFAULT 0,
  `min_omzet_tercapai` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
  `bobot_probabilitas` INT NOT NULL DEFAULT 10,
  `warna_slice` VARCHAR(20) NOT NULL DEFAULT '#E67E22',
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`),
  INDEX `idx_is_active` (`is_active`),
  INDEX `idx_kategori_hadiah` (`kategori_hadiah`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- 5. Table: spin_histories (Riwayat Spin & Klaim User)
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS `spin_histories`;
CREATE TABLE `spin_histories` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `qr_token_id` INT UNSIGNED NULL DEFAULT NULL,
  `no_pesanan` VARCHAR(100) NOT NULL,
  `nama_pemenang` VARCHAR(100) NOT NULL,
  `no_whatsapp` VARCHAR(30) NOT NULL,
  `alamat_pengiriman` TEXT NULL DEFAULT NULL,
  `prize_id` INT UNSIGNED NOT NULL,
  `jenis_spin` VARCHAR(50) NOT NULL DEFAULT 'Spin Wheel',
  `waktu_spin` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `batas_waktu_klaim` DATETIME NOT NULL,
  `status_klaim` ENUM('Unclaimed', 'Claimed', 'Expired') NOT NULL DEFAULT 'Unclaimed',
  `status_hadiah` ENUM('Pending', 'Diproses', 'Dikirim', 'Selesai') NOT NULL DEFAULT 'Pending',
  `no_resi` VARCHAR(100) NULL DEFAULT NULL,
  `jasa_kirim` VARCHAR(50) NULL DEFAULT NULL,
  `status_wa` ENUM('Belum Broadcast', 'Sudah Broadcast') NOT NULL DEFAULT 'Belum Broadcast',
  PRIMARY KEY (`id`),
  INDEX `idx_no_pesanan` (`no_pesanan`),
  INDEX `idx_no_whatsapp` (`no_whatsapp`),
  INDEX `idx_status_klaim` (`status_klaim`),
  INDEX `idx_status_hadiah` (`status_hadiah`),
  INDEX `idx_waktu_spin` (`waktu_spin`),
  CONSTRAINT `fk_histories_token` FOREIGN KEY (`qr_token_id`) REFERENCES `unique_qr_tokens` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_histories_prize` FOREIGN KEY (`prize_id`) REFERENCES `prizes` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;
