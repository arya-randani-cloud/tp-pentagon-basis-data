-- =====================================================================
-- SQLite DDL Generated from DBML
-- Tim PENTAGON - Sistem Peminjaman dan Pengembalian Buku
-- Generated at: 2026-10-13 00:54:44
-- =====================================================================

PRAGMA foreign_keys = OFF;
DROP TABLE IF EXISTS "pengembalian";
DROP TABLE IF EXISTS "peminjaman";
DROP TABLE IF EXISTS "petugas";
DROP TABLE IF EXISTS "buku";
DROP TABLE IF EXISTS "anggota";
PRAGMA foreign_keys = ON;

-- ---------------------------------------------------------------------
-- Table: anggota
-- ---------------------------------------------------------------------
CREATE TABLE "anggota" (
    "id_anggota" TEXT PRIMARY KEY,
    "nama_anggota" TEXT NOT NULL,
    "alamat" TEXT,
    "no_telepon" TEXT,
    "email" TEXT,
    "tanggal_daftar" TEXT NOT NULL
);

-- ---------------------------------------------------------------------
-- Table: buku
-- ---------------------------------------------------------------------
CREATE TABLE "buku" (
    "id_buku" TEXT PRIMARY KEY,
    "judul_buku" TEXT NOT NULL,
    "pengarang" TEXT,
    "penerbit" TEXT,
    "tahun_terbit" INTEGER,
    "kategori" TEXT,
    "stok" INTEGER NOT NULL DEFAULT 0
);

-- ---------------------------------------------------------------------
-- Table: petugas
-- ---------------------------------------------------------------------
CREATE TABLE "petugas" (
    "id_petugas" TEXT PRIMARY KEY,
    "nama_petugas" TEXT NOT NULL,
    "username" TEXT NOT NULL UNIQUE,
    "password" TEXT NOT NULL,
    "hak_akses" TEXT NOT NULL,
    "no_telepon" TEXT
);

-- ---------------------------------------------------------------------
-- Table: peminjaman
-- ---------------------------------------------------------------------
CREATE TABLE "peminjaman" (
    "id_peminjaman" TEXT PRIMARY KEY,
    "id_anggota" TEXT NOT NULL,
    "id_buku" TEXT NOT NULL,
    "id_petugas" TEXT NOT NULL,
    "waktu_peminjaman" TEXT NOT NULL,
    FOREIGN KEY ("id_anggota") REFERENCES "anggota" ("id_anggota") ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY ("id_buku") REFERENCES "buku" ("id_buku") ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY ("id_petugas") REFERENCES "petugas" ("id_petugas") ON DELETE CASCADE ON UPDATE CASCADE
);

-- ---------------------------------------------------------------------
-- Table: pengembalian
-- ---------------------------------------------------------------------
CREATE TABLE "pengembalian" (
    "id_pengembalian" TEXT PRIMARY KEY,
    "id_peminjaman" TEXT NOT NULL UNIQUE,
    "id_petugas" TEXT NOT NULL,
    "waktu_pengembalian" TEXT NOT NULL,
    FOREIGN KEY ("id_peminjaman") REFERENCES "peminjaman" ("id_peminjaman") ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY ("id_petugas") REFERENCES "petugas" ("id_petugas") ON DELETE CASCADE ON UPDATE CASCADE
);

