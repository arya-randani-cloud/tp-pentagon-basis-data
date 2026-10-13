-- =====================================================================
-- MySQL DDL Generated from DBML
-- Tim PENTAGON - Sistem Peminjaman dan Pengembalian Buku
-- Generated at: 2026-10-13 00:54:07
-- =====================================================================

SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS `peminjaman`;
DROP TABLE IF EXISTS `buku`;
DROP TABLE IF EXISTS `anggota`;
SET FOREIGN_KEY_CHECKS = 1;

-- ---------------------------------------------------------------------
-- Table: anggota (Tabel yang menyimpan data identitas anggota perpustakaan)
-- ---------------------------------------------------------------------
CREATE TABLE `anggota` (
    `id_anggota` VARCHAR(10) NOT NULL COMMENT 'Primary Key Anggota',
    `nama_anggota` VARCHAR(100) NOT NULL,
    `alamat` VARCHAR(255) NULL,
    `no_telepon` VARCHAR(15) NULL,
    `email` VARCHAR(100) NULL,
    `tanggal_daftar` DATE NOT NULL,
    PRIMARY KEY (`id_anggota`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- Table: buku (Tabel yang menyimpan data katalog buku perpustakaan)
-- ---------------------------------------------------------------------
CREATE TABLE `buku` (
    `id_buku` VARCHAR(10) NOT NULL COMMENT 'Primary Key Buku',
    `judul_buku` VARCHAR(150) NOT NULL,
    `pengarang` VARCHAR(100) NULL,
    `penerbit` VARCHAR(100) NULL,
    `tahun_terbit` SMALLINT NULL,
    `kategori` VARCHAR(50) NULL,
    `stok` INT NOT NULL DEFAULT 0,
    PRIMARY KEY (`id_buku`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- Table: peminjaman (Tabel transaksi pencatatan peminjaman buku oleh anggota)
-- ---------------------------------------------------------------------
CREATE TABLE `peminjaman` (
    `id_peminjaman` VARCHAR(15) NOT NULL COMMENT 'Primary Key Peminjaman',
    `id_anggota` VARCHAR(10) NOT NULL COMMENT 'FK ke tabel anggota',
    `id_buku` VARCHAR(10) NOT NULL COMMENT 'FK ke tabel buku',
    `id_petugas` VARCHAR(10) NULL COMMENT 'ID Petugas yang mencatat peminjaman',
    `waktu_peminjaman` DATETIME NOT NULL,
    PRIMARY KEY (`id_peminjaman`),
    CONSTRAINT `fk_peminjaman_id_anggota` FOREIGN KEY (`id_anggota`) REFERENCES `anggota` (`id_anggota`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_peminjaman_id_buku` FOREIGN KEY (`id_buku`) REFERENCES `buku` (`id_buku`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

