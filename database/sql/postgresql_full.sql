-- =====================================================================
-- PostgreSQL DDL Generated from DBML
-- Tim PENTAGON - Sistem Peminjaman dan Pengembalian Buku
-- Generated at: 2026-10-13 00:54:44
-- =====================================================================

DROP TABLE IF EXISTS "pengembalian" CASCADE;
DROP TABLE IF EXISTS "peminjaman" CASCADE;
DROP TABLE IF EXISTS "petugas" CASCADE;
DROP TABLE IF EXISTS "buku" CASCADE;
DROP TABLE IF EXISTS "anggota" CASCADE;

-- ---------------------------------------------------------------------
-- Table: anggota (Tabel yang menyimpan data identitas anggota perpustakaan)
-- ---------------------------------------------------------------------
CREATE TABLE "anggota" (
    "id_anggota" VARCHAR(10) NOT NULL,
    "nama_anggota" VARCHAR(100) NOT NULL,
    "alamat" VARCHAR(255),
    "no_telepon" VARCHAR(15),
    "email" VARCHAR(100),
    "tanggal_daftar" DATE NOT NULL,
    PRIMARY KEY ("id_anggota")
);

COMMENT ON TABLE "anggota" IS 'Tabel yang menyimpan data identitas anggota perpustakaan';
COMMENT ON COLUMN "anggota"."id_anggota" IS 'Primary Key Anggota';

-- ---------------------------------------------------------------------
-- Table: buku (Tabel yang menyimpan data katalog buku perpustakaan)
-- ---------------------------------------------------------------------
CREATE TABLE "buku" (
    "id_buku" VARCHAR(10) NOT NULL,
    "judul_buku" VARCHAR(150) NOT NULL,
    "pengarang" VARCHAR(100),
    "penerbit" VARCHAR(100),
    "tahun_terbit" SMALLINT,
    "kategori" VARCHAR(50),
    "stok" INTEGER NOT NULL DEFAULT 0,
    PRIMARY KEY ("id_buku")
);

COMMENT ON TABLE "buku" IS 'Tabel yang menyimpan data katalog buku perpustakaan';
COMMENT ON COLUMN "buku"."id_buku" IS 'Primary Key Buku';

-- ---------------------------------------------------------------------
-- Table: petugas (Tabel yang menyimpan data akun petugas/operator perpustakaan)
-- ---------------------------------------------------------------------
CREATE TABLE "petugas" (
    "id_petugas" VARCHAR(10) NOT NULL,
    "nama_petugas" VARCHAR(100) NOT NULL,
    "username" VARCHAR(50) NOT NULL UNIQUE,
    "password" VARCHAR(255) NOT NULL,
    "hak_akses" VARCHAR(20) NOT NULL,
    "no_telepon" VARCHAR(15),
    PRIMARY KEY ("id_petugas")
);

COMMENT ON TABLE "petugas" IS 'Tabel yang menyimpan data akun petugas/operator perpustakaan';
COMMENT ON COLUMN "petugas"."id_petugas" IS 'Primary Key Petugas';

-- ---------------------------------------------------------------------
-- Table: peminjaman (Tabel transaksi pencatatan peminjaman buku oleh anggota)
-- ---------------------------------------------------------------------
CREATE TABLE "peminjaman" (
    "id_peminjaman" VARCHAR(15) NOT NULL,
    "id_anggota" VARCHAR(10) NOT NULL,
    "id_buku" VARCHAR(10) NOT NULL,
    "id_petugas" VARCHAR(10) NOT NULL,
    "waktu_peminjaman" TIMESTAMP NOT NULL,
    PRIMARY KEY ("id_peminjaman"),
    CONSTRAINT "fk_peminjaman_id_anggota" FOREIGN KEY ("id_anggota") REFERENCES "anggota" ("id_anggota") ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT "fk_peminjaman_id_buku" FOREIGN KEY ("id_buku") REFERENCES "buku" ("id_buku") ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT "fk_peminjaman_id_petugas" FOREIGN KEY ("id_petugas") REFERENCES "petugas" ("id_petugas") ON DELETE CASCADE ON UPDATE CASCADE
);

COMMENT ON TABLE "peminjaman" IS 'Tabel transaksi pencatatan peminjaman buku oleh anggota';
COMMENT ON COLUMN "peminjaman"."id_peminjaman" IS 'Primary Key Peminjaman';
COMMENT ON COLUMN "peminjaman"."id_anggota" IS 'FK ke tabel anggota';
COMMENT ON COLUMN "peminjaman"."id_buku" IS 'FK ke tabel buku';
COMMENT ON COLUMN "peminjaman"."id_petugas" IS 'FK ke tabel petugas';

-- ---------------------------------------------------------------------
-- Table: pengembalian (Tabel transaksi pencatatan pengembalian buku)
-- ---------------------------------------------------------------------
CREATE TABLE "pengembalian" (
    "id_pengembalian" VARCHAR(15) NOT NULL,
    "id_peminjaman" VARCHAR(15) NOT NULL UNIQUE,
    "id_petugas" VARCHAR(10) NOT NULL,
    "waktu_pengembalian" TIMESTAMP NOT NULL,
    PRIMARY KEY ("id_pengembalian"),
    CONSTRAINT "fk_pengembalian_id_peminjaman" FOREIGN KEY ("id_peminjaman") REFERENCES "peminjaman" ("id_peminjaman") ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT "fk_pengembalian_id_petugas" FOREIGN KEY ("id_petugas") REFERENCES "petugas" ("id_petugas") ON DELETE CASCADE ON UPDATE CASCADE
);

COMMENT ON TABLE "pengembalian" IS 'Tabel transaksi pencatatan pengembalian buku';
COMMENT ON COLUMN "pengembalian"."id_pengembalian" IS 'Primary Key Pengembalian';
COMMENT ON COLUMN "pengembalian"."id_peminjaman" IS 'FK ke tabel peminjaman (1:1 / 1:0..1)';
COMMENT ON COLUMN "pengembalian"."id_petugas" IS 'FK ke tabel petugas';

