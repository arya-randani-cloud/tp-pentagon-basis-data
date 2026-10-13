-- =====================================================================
-- PostgreSQL DDL Generated from DBML
-- Tim PENTAGON - Sistem Peminjaman dan Pengembalian Buku
-- Generated at: 2026-10-13 00:54:07
-- =====================================================================

DROP TABLE IF EXISTS "peminjaman" CASCADE;
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
-- Table: peminjaman (Tabel transaksi pencatatan peminjaman buku oleh anggota)
-- ---------------------------------------------------------------------
CREATE TABLE "peminjaman" (
    "id_peminjaman" VARCHAR(15) NOT NULL,
    "id_anggota" VARCHAR(10) NOT NULL,
    "id_buku" VARCHAR(10) NOT NULL,
    "id_petugas" VARCHAR(10),
    "waktu_peminjaman" TIMESTAMP NOT NULL,
    PRIMARY KEY ("id_peminjaman"),
    CONSTRAINT "fk_peminjaman_id_anggota" FOREIGN KEY ("id_anggota") REFERENCES "anggota" ("id_anggota") ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT "fk_peminjaman_id_buku" FOREIGN KEY ("id_buku") REFERENCES "buku" ("id_buku") ON DELETE CASCADE ON UPDATE CASCADE
);

COMMENT ON TABLE "peminjaman" IS 'Tabel transaksi pencatatan peminjaman buku oleh anggota';
COMMENT ON COLUMN "peminjaman"."id_peminjaman" IS 'Primary Key Peminjaman';
COMMENT ON COLUMN "peminjaman"."id_anggota" IS 'FK ke tabel anggota';
COMMENT ON COLUMN "peminjaman"."id_buku" IS 'FK ke tabel buku';
COMMENT ON COLUMN "peminjaman"."id_petugas" IS 'ID Petugas yang mencatat peminjaman';

