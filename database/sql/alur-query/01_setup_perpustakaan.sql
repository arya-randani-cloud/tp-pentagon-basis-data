-- =====================================================================
-- 01_setup_perpustakaan.sql
-- Sistem Peminjaman dan Pengembalian Buku - Tim PENTAGON
-- Struktur tabel mengikuti schema.dbml di repo tp-pentagon-basis-data
-- Jalankan SEKALI sebelum 02_alur_query.sql
-- =====================================================================

DROP DATABASE IF EXISTS perpustakaan;
CREATE DATABASE perpustakaan CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE perpustakaan;

-- ---------- STRUKTUR TABEL (sesuai schema.dbml) ----------
CREATE TABLE petugas (
  id          BIGINT AUTO_INCREMENT PRIMARY KEY,
  nama        VARCHAR(100) NOT NULL,
  username    VARCHAR(50)  NOT NULL UNIQUE,
  password    VARCHAR(255) NOT NULL,
  role        VARCHAR(20)  NOT NULL DEFAULT 'petugas',
  no_telepon  VARCHAR(20),
  created_at  TIMESTAMP NULL,
  updated_at  TIMESTAMP NULL
);

CREATE TABLE anggota (
  id            BIGINT AUTO_INCREMENT PRIMARY KEY,
  kode_anggota  VARCHAR(20)  NOT NULL UNIQUE,
  nama          VARCHAR(100) NOT NULL,
  alamat        TEXT,
  no_telepon    VARCHAR(20),
  email         VARCHAR(100),
  created_at    TIMESTAMP NULL,
  updated_at    TIMESTAMP NULL,
  deleted_at    TIMESTAMP NULL,
  INDEX idx_anggota_nama (nama)
);

CREATE TABLE buku (
  id            BIGINT AUTO_INCREMENT PRIMARY KEY,
  kode_buku     VARCHAR(20)  NOT NULL UNIQUE,
  judul         VARCHAR(200) NOT NULL,
  penulis       VARCHAR(100),
  penerbit      VARCHAR(100),
  tahun_terbit  SMALLINT,
  stok          INT NOT NULL DEFAULT 1,
  created_at    TIMESTAMP NULL,
  updated_at    TIMESTAMP NULL,
  deleted_at    TIMESTAMP NULL,
  INDEX idx_buku_judul (judul)
);

CREATE TABLE peminjaman (
  id                   BIGINT AUTO_INCREMENT PRIMARY KEY,
  kode_transaksi       VARCHAR(30) NOT NULL UNIQUE,
  anggota_id           BIGINT NOT NULL,
  buku_id              BIGINT NOT NULL,
  petugas_id           BIGINT NOT NULL,
  tanggal_pinjam       DATETIME NOT NULL,
  tanggal_jatuh_tempo  DATE NULL,
  status               VARCHAR(20) NOT NULL DEFAULT 'dipinjam',
  created_at           TIMESTAMP NULL,
  updated_at           TIMESTAMP NULL,
  INDEX idx_pinjam_tanggal (tanggal_pinjam),
  INDEX idx_pinjam_status (status),
  INDEX idx_pinjam_anggota_status (anggota_id, status),
  CONSTRAINT fk_pinjam_anggota FOREIGN KEY (anggota_id) REFERENCES anggota(id),
  CONSTRAINT fk_pinjam_buku    FOREIGN KEY (buku_id)    REFERENCES buku(id),
  CONSTRAINT fk_pinjam_petugas FOREIGN KEY (petugas_id) REFERENCES petugas(id)
);

CREATE TABLE pengembalian (
  id              BIGINT AUTO_INCREMENT PRIMARY KEY,
  peminjaman_id   BIGINT NOT NULL UNIQUE,
  petugas_id      BIGINT NOT NULL,
  tanggal_kembali DATETIME NOT NULL,
  catatan         TEXT,
  created_at      TIMESTAMP NULL,
  updated_at      TIMESTAMP NULL,
  INDEX idx_kembali_tanggal (tanggal_kembali),
  CONSTRAINT fk_kembali_pinjam  FOREIGN KEY (peminjaman_id) REFERENCES peminjaman(id),
  CONSTRAINT fk_kembali_petugas FOREIGN KEY (petugas_id)    REFERENCES petugas(id)
);

-- ---------- DATA CONTOH ----------
INSERT INTO petugas (nama, username, password, role, no_telepon, created_at) VALUES
 ('Abu Zulkifli',  'abu',   '$2y$10$hashbcryptcontoh1', 'admin',   '081200000001', NOW()),
 ('Siti Rahma',    'siti',  '$2y$10$hashbcryptcontoh2', 'petugas', '081200000002', NOW()),
 ('Budi Santoso',  'budi',  '$2y$10$hashbcryptcontoh3', 'petugas', '081200000003', NOW());

INSERT INTO anggota (kode_anggota, nama, alamat, no_telepon, email, created_at) VALUES
 ('AGT-0001','Andi Pratama',  'Surakarta',  '081311110001','andi@mail.test',  NOW()),
 ('AGT-0002','Bunga Lestari', 'Sukoharjo',  '081311110002','bunga@mail.test', NOW()),
 ('AGT-0003','Citra Dewi',    'Karanganyar','081311110003','citra@mail.test', NOW()),
 ('AGT-0004','Dimas Saputra', 'Surakarta',  '081311110004','dimas@mail.test', NOW()),
 ('AGT-0005','Eka Wulandari', 'Klaten',     '081311110005','eka@mail.test',   NOW()),
 ('AGT-0006','Fajar Nugroho', 'Boyolali',   '081311110006','fajar@mail.test', NOW()),
 ('AGT-0007','Gita Permata',  'Surakarta',  '081311110007','gita@mail.test',  NOW()),
 ('AGT-0008','Hadi Firmansyah','Sragen',    '081311110008','hadi@mail.test',  NOW());

-- tabel bantu angka 0-9 (portabel MySQL/MariaDB, tanpa CTE rekursif)
CREATE TEMPORARY TABLE d (n INT);
INSERT INTO d VALUES (0),(1),(2),(3),(4),(5),(6),(7),(8),(9);

-- anggota tambahan AGT-0009 .. AGT-0060
INSERT INTO anggota (kode_anggota, nama, alamat, created_at)
SELECT CONCAT('AGT-', LPAD(x.n, 4, '0')), CONCAT('Anggota ', LPAD(x.n, 3, '0')), 'Surakarta', NOW()
FROM (SELECT a.n + 10*b.n AS n FROM d a, d b) x
WHERE x.n BETWEEN 9 AND 60;

-- buku (10 judul nyata + 50 judul contoh)
INSERT INTO buku (kode_buku, judul, penulis, penerbit, tahun_terbit, stok, created_at) VALUES
 ('BK-0001','Laskar Pelangi',            'Andrea Hirata',         'Bentang Pustaka',   2005, 5, NOW()),
 ('BK-0002','Bumi Manusia',              'Pramoedya Ananta Toer', 'Hasta Mitra',       1980, 4, NOW()),
 ('BK-0003','Filosofi Teras',            'Henry Manampiring',     'Kompas',            2018, 6, NOW()),
 ('BK-0004','Atomic Habits',             'James Clear',           'Gramedia',          2019, 8, NOW()),
 ('BK-0005','Algoritma dan Pemrograman', 'Rinaldi Munir',         'Informatika',       2016, 7, NOW()),
 ('BK-0006','Basis Data',                'Fathansyah',            'Informatika',       2018, 7, NOW()),
 ('BK-0007','Pemrograman Web dengan PHP','Abdul Kadir',           'Andi',              2017, 5, NOW()),
 ('BK-0008','Sapiens',                   'Yuval Noah Harari',     'KPG',               2017, 3, NOW()),
 ('BK-0009','Negeri 5 Menara',           'Ahmad Fuadi',           'Gramedia',          2009, 4, NOW()),
 ('BK-0010','Rich Dad Poor Dad',         'Robert Kiyosaki',       'Gramedia',          2011, 6, NOW());

INSERT INTO buku (kode_buku, judul, penulis, penerbit, tahun_terbit, stok, created_at)
SELECT CONCAT('BK-', LPAD(x.n, 4, '0')),
       CONCAT('Buku Contoh ', LPAD(x.n, 3, '0')),
       CONCAT('Penulis ', 1 + MOD(x.n, 9)),
       ELT(1 + MOD(x.n, 5), 'Gramedia','Informatika','Andi','Kompas','Bentang Pustaka'),
       2005 + MOD(x.n, 20),
       3 + MOD(x.n, 6),
       NOW()
FROM (SELECT a.n + 10*b.n AS n FROM d a, d b) x
WHERE x.n BETWEEN 11 AND 60;

-- peminjaman: 20.000 baris transaksi (deterministik, tanpa RAND)
INSERT INTO peminjaman (kode_transaksi, anggota_id, buku_id, petugas_id,
                        tanggal_pinjam, tanggal_jatuh_tempo, status, created_at)
SELECT CONCAT('TRX-', LPAD(x.n, 6, '0')),
       1 + MOD(x.n * 7 + FLOOR(x.n / 60), 60),
       -- buku 1-6 sengaja lebih populer supaya GROUP BY punya urutan jelas
       IF(MOD(x.n, 4) = 0, 1 + MOD(x.n, 6), 1 + MOD(x.n * 13 + FLOOR(x.n / 7), 60)),
       1 + MOD(x.n, 3),
       TIMESTAMP('2026-01-01') + INTERVAL MOD(x.n * 37 + FLOOR(x.n / 300), 300) DAY + INTERVAL MOD(x.n, 24) HOUR,
       DATE(TIMESTAMP('2026-01-01') + INTERVAL MOD(x.n * 37 + FLOOR(x.n / 300), 300) DAY) + INTERVAL 14 DAY,
       IF(MOD(x.n, 10) < 3, 'dipinjam', 'dikembalikan'),
       NOW()
FROM (SELECT a.n + 10*b.n + 100*c.n + 1000*e.n + 10000*f.n + 1 AS n
      FROM d a, d b, d c, d e, d f) x
WHERE x.n <= 20000;

-- pengembalian untuk semua transaksi berstatus 'dikembalikan'
INSERT INTO pengembalian (peminjaman_id, petugas_id, tanggal_kembali, catatan, created_at)
SELECT p.id,
       1 + MOD(p.id, 3),
       p.tanggal_pinjam + INTERVAL (3 + MOD(p.id, 18)) DAY,
       NULL,
       NOW()
FROM peminjaman p
WHERE p.status = 'dikembalikan';

ANALYZE TABLE petugas, anggota, buku, peminjaman, pengembalian;
