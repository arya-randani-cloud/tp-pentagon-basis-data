-- =====================================================================
-- 02_alur_query.sql
-- Alur: SELECT -> WHERE -> EXPLAIN/ANALYZE (BEFORE) -> CREATE INDEX
--       -> EXPLAIN/ANALYZE (AFTER) -> SELECT -> GROUP BY
--       -> INSERT 5 data -> SELECT (tampilkan output)
-- Database: perpustakaan (jalankan 01_setup_perpustakaan.sql dulu)
--
-- Catatan: di MariaDB perintahnya "ANALYZE SELECT ...".
--          Di MySQL 8 ganti dengan "EXPLAIN ANALYZE SELECT ...".
-- =====================================================================
USE perpustakaan;

-- ---------------------------------------------------------------------
-- 1. SELECT dasar
-- ---------------------------------------------------------------------
SELECT '1. SELECT dasar - 10 buku pertama' AS langkah;
SELECT id, kode_buku, judul, penulis, penerbit, tahun_terbit, stok
FROM buku
ORDER BY id
LIMIT 10;

-- ---------------------------------------------------------------------
-- 2. SELECT + WHERE
--    Kasus: daftar buku yang masih dipinjam dan sudah lewat jatuh tempo
-- ---------------------------------------------------------------------
SELECT '2. SELECT + WHERE - peminjaman terlambat (jatuh tempo < 2026-09-01)' AS langkah;
SELECT p.kode_transaksi, a.nama AS anggota, b.judul, p.tanggal_jatuh_tempo
FROM peminjaman p
JOIN anggota a ON a.id = p.anggota_id
JOIN buku    b ON b.id = p.buku_id
WHERE p.status = 'dipinjam'
  AND p.tanggal_jatuh_tempo < '2026-09-01'
ORDER BY p.tanggal_jatuh_tempo, p.id
LIMIT 10;

SELECT 'Jumlah seluruh peminjaman terlambat' AS keterangan,
       COUNT(*) AS total
FROM peminjaman
WHERE status = 'dipinjam'
  AND tanggal_jatuh_tempo < '2026-09-01';

-- ---------------------------------------------------------------------
-- 3. EXPLAIN / ANALYZE  --  BEFORE (belum ada index di tanggal_jatuh_tempo)
-- ---------------------------------------------------------------------
SELECT '3. BEFORE - EXPLAIN' AS langkah;
EXPLAIN
SELECT p.kode_transaksi, a.nama AS anggota, b.judul, p.tanggal_jatuh_tempo
FROM peminjaman p
JOIN anggota a ON a.id = p.anggota_id
JOIN buku    b ON b.id = p.buku_id
WHERE p.status = 'dipinjam'
  AND p.tanggal_jatuh_tempo < '2026-09-01'
ORDER BY p.tanggal_jatuh_tempo, p.id
LIMIT 10;

SELECT '3. BEFORE - ANALYZE (eksekusi nyata, lihat r_rows)' AS langkah;
ANALYZE
SELECT p.kode_transaksi, a.nama AS anggota, b.judul, p.tanggal_jatuh_tempo
FROM peminjaman p
JOIN anggota a ON a.id = p.anggota_id
JOIN buku    b ON b.id = p.buku_id
WHERE p.status = 'dipinjam'
  AND p.tanggal_jatuh_tempo < '2026-09-01'
ORDER BY p.tanggal_jatuh_tempo, p.id
LIMIT 10;

-- ---------------------------------------------------------------------
-- 4. Optimasi: composite index (status, tanggal_jatuh_tempo)
-- ---------------------------------------------------------------------
SELECT '4. CREATE INDEX' AS langkah;
CREATE INDEX idx_pinjam_status_jatuh_tempo
    ON peminjaman (status, tanggal_jatuh_tempo);
ANALYZE TABLE peminjaman;

-- ---------------------------------------------------------------------
-- 5. EXPLAIN / ANALYZE  --  AFTER
-- ---------------------------------------------------------------------
SELECT '5. AFTER - EXPLAIN' AS langkah;
EXPLAIN
SELECT p.kode_transaksi, a.nama AS anggota, b.judul, p.tanggal_jatuh_tempo
FROM peminjaman p
JOIN anggota a ON a.id = p.anggota_id
JOIN buku    b ON b.id = p.buku_id
WHERE p.status = 'dipinjam'
  AND p.tanggal_jatuh_tempo < '2026-09-01'
ORDER BY p.tanggal_jatuh_tempo, p.id
LIMIT 10;

SELECT '5. AFTER - ANALYZE (eksekusi nyata, lihat r_rows)' AS langkah;
ANALYZE
SELECT p.kode_transaksi, a.nama AS anggota, b.judul, p.tanggal_jatuh_tempo
FROM peminjaman p
JOIN anggota a ON a.id = p.anggota_id
JOIN buku    b ON b.id = p.buku_id
WHERE p.status = 'dipinjam'
  AND p.tanggal_jatuh_tempo < '2026-09-01'
ORDER BY p.tanggal_jatuh_tempo, p.id
LIMIT 10;

-- ---------------------------------------------------------------------
-- 6. Kembali ke SELECT (hasil harus sama, hanya lebih efisien)
-- ---------------------------------------------------------------------
SELECT '6. SELECT ulang setelah index - hasil identik dengan langkah 2' AS langkah;
SELECT p.kode_transaksi, a.nama AS anggota, b.judul, p.tanggal_jatuh_tempo
FROM peminjaman p
JOIN anggota a ON a.id = p.anggota_id
JOIN buku    b ON b.id = p.buku_id
WHERE p.status = 'dipinjam'
  AND p.tanggal_jatuh_tempo < '2026-09-01'
ORDER BY p.tanggal_jatuh_tempo, p.id
LIMIT 10;

-- ---------------------------------------------------------------------
-- 7. SELECT + GROUP BY
-- ---------------------------------------------------------------------
SELECT '7a. GROUP BY status - jumlah transaksi per status' AS langkah;
SELECT status, COUNT(*) AS jumlah_transaksi
FROM peminjaman
GROUP BY status
ORDER BY jumlah_transaksi DESC;

SELECT '7b. GROUP BY buku - 5 buku paling sering dipinjam' AS langkah;
SELECT b.kode_buku, b.judul, COUNT(*) AS kali_dipinjam
FROM peminjaman p
JOIN buku b ON b.id = p.buku_id
GROUP BY b.id, b.kode_buku, b.judul
ORDER BY kali_dipinjam DESC, b.id
LIMIT 5;

SELECT '7c. GROUP BY petugas - transaksi per petugas (HAVING > 6000)' AS langkah;
SELECT pt.nama AS petugas, COUNT(*) AS jumlah_transaksi
FROM peminjaman p
JOIN petugas pt ON pt.id = p.petugas_id
GROUP BY pt.id, pt.nama
HAVING COUNT(*) > 6000
ORDER BY jumlah_transaksi DESC;

-- ---------------------------------------------------------------------
-- 8. INSERT 5 data peminjaman baru
-- ---------------------------------------------------------------------
SELECT '8. INSERT 5 data peminjaman baru' AS langkah;
INSERT INTO peminjaman
  (kode_transaksi, anggota_id, buku_id, petugas_id,
   tanggal_pinjam, tanggal_jatuh_tempo, status, created_at)
VALUES
  ('TRX-020001', 1, 5, 1, '2026-10-27 08:40:00', '2026-11-10', 'dipinjam', NOW()),  -- Andi    - Algoritma dan Pemrograman
  ('TRX-020002', 2, 6, 1, '2026-10-27 08:45:00', '2026-11-10', 'dipinjam', NOW()),  -- Bunga   - Basis Data
  ('TRX-020003', 3, 1, 2, '2026-10-27 09:05:00', '2026-11-10', 'dipinjam', NOW()),  -- Citra   - Laskar Pelangi
  ('TRX-020004', 4, 4, 2, '2026-10-27 09:20:00', '2026-11-10', 'dipinjam', NOW()),  -- Dimas   - Atomic Habits
  ('TRX-020005', 5, 7, 3, '2026-10-27 09:35:00', '2026-11-10', 'dipinjam', NOW());  -- Eka     - Pemrograman Web dengan PHP

SELECT ROW_COUNT() AS baris_ditambahkan;

-- ---------------------------------------------------------------------
-- 9. SELECT lagi - tampilkan hasil INSERT dan ringkasan terbaru
-- ---------------------------------------------------------------------
SELECT '9a. SELECT - 5 transaksi yang baru ditambahkan' AS langkah;
SELECT p.kode_transaksi, a.kode_anggota, a.nama AS anggota, b.judul,
       pt.nama AS petugas, p.tanggal_pinjam, p.tanggal_jatuh_tempo, p.status
FROM peminjaman p
JOIN anggota a  ON a.id  = p.anggota_id
JOIN buku    b  ON b.id  = p.buku_id
JOIN petugas pt ON pt.id = p.petugas_id
WHERE p.kode_transaksi >= 'TRX-020001'
ORDER BY p.kode_transaksi;

SELECT '9b. GROUP BY status setelah INSERT (dipinjam bertambah 5)' AS langkah;
SELECT status, COUNT(*) AS jumlah_transaksi
FROM peminjaman
GROUP BY status
ORDER BY jumlah_transaksi DESC;

SELECT '9c. Total baris peminjaman' AS langkah;
SELECT COUNT(*) AS total_peminjaman FROM peminjaman;
