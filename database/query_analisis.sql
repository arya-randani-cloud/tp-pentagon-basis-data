-- =============================================================
--  QUERY ANALISIS DATABASE PERPUSTAKAAN
--  Gunakan query ini di Database Viewer / SQLite extension
-- =============================================================

-- ─────────────────────────────────────────────────────────────
-- LATIHAN: SELECT, WHERE, ANALISIS, GROUP BY, DAN INSERT
-- Jalankan bagian ini berurutan. SQLite menggunakan EXPLAIN QUERY PLAN;
-- SQLite tidak menyediakan EXPLAIN ANALYZE seperti PostgreSQL/MySQL.
-- ─────────────────────────────────────────────────────────────

-- Reset indeks demonstrasi agar perbandingan tetap konsisten saat diulang
DROP INDEX IF EXISTS idx_anggota_tanggal_daftar;

-- 1. SELECT dengan kondisi WHERE (sebelum indeks)
SELECT anggota_id, nama_anggota, nim, tanggal_daftar, status
FROM anggota
WHERE tanggal_daftar BETWEEN '2025-01-01' AND '2026-12-31';

-- 2. Analisis rencana query sebelum indeks
EXPLAIN QUERY PLAN
SELECT anggota_id, nama_anggota, nim, tanggal_daftar, status
FROM anggota
WHERE tanggal_daftar BETWEEN '2025-01-01' AND '2026-12-31';

-- 3. Buat indeks untuk kolom yang digunakan pada WHERE
CREATE INDEX IF NOT EXISTS idx_anggota_tanggal_daftar
ON anggota(tanggal_daftar);

-- 4. Analisis rencana query setelah indeks
EXPLAIN QUERY PLAN
SELECT anggota_id, nama_anggota, nim, tanggal_daftar, status
FROM anggota
WHERE tanggal_daftar BETWEEN '2025-01-01' AND '2026-12-31';

-- 5. SELECT kembali setelah indeks dibuat
SELECT anggota_id, nama_anggota, nim, tanggal_daftar, status
FROM anggota
WHERE tanggal_daftar BETWEEN '2025-01-01' AND '2026-12-31';

-- 6. SELECT dengan GROUP BY untuk menghitung jumlah anggota per status
SELECT status, COUNT(*) AS jumlah_anggota
FROM anggota
GROUP BY status
ORDER BY status;

-- 7. INSERT lima data anggota
INSERT INTO anggota
    (nama_anggota, nim, email, telepon, alamat, jenis_kelamin, tanggal_daftar, status)
VALUES
    ('Dina Pratama', 'DB26IF9001', 'dina.pratama@example.com', '081200000001', 'Bandung', 'P', '2026-10-27', 'aktif'),
    ('Rafi Saputra', 'DB26IF9002', 'rafi.saputra@example.com', '081200000002', 'Jakarta', 'L', '2026-10-27', 'aktif'),
    ('Maya Lestari', 'DB26IF9003', 'maya.lestari@example.com', '081200000003', 'Yogyakarta', 'P', '2026-10-27', 'aktif'),
    ('Fajar Wijaya', 'DB26IF9004', 'fajar.wijaya@example.com', '081200000004', 'Surabaya', 'L', '2026-10-27', 'aktif'),
    ('Nisa Ramadhani', 'DB26IF9005', 'nisa.ramadhani@example.com', '081200000005', 'Semarang', 'P', '2026-10-27', 'aktif')
ON CONFLICT(nim) DO NOTHING;

-- 8. SELECT untuk menampilkan lima data yang baru dimasukkan
SELECT anggota_id, nama_anggota, nim, email, tanggal_daftar, status
FROM anggota
WHERE nim IN ('DB26IF9001', 'DB26IF9002', 'DB26IF9003', 'DB26IF9004', 'DB26IF9005')
ORDER BY nim;

-- ─────────────────────────────────────────────────────────────
-- 1. OVERVIEW: Jumlah data per tabel
-- ─────────────────────────────────────────────────────────────
SELECT 'kategori' AS tabel, COUNT(*) AS jumlah FROM kategori
UNION ALL SELECT 'penerbit', COUNT(*) FROM penerbit
UNION ALL SELECT 'pengarang', COUNT(*) FROM pengarang
UNION ALL SELECT 'petugas', COUNT(*) FROM petugas
UNION ALL SELECT 'anggota', COUNT(*) FROM anggota
UNION ALL SELECT 'buku', COUNT(*) FROM buku
UNION ALL SELECT 'buku_pengarang', COUNT(*) FROM buku_pengarang
UNION ALL SELECT 'peminjaman', COUNT(*) FROM peminjaman
UNION ALL SELECT 'denda', COUNT(*) FROM denda;


-- ─────────────────────────────────────────────────────────────
-- 2. ANALISIS KATEGORI: Total peminjaman per kategori
-- ─────────────────────────────────────────────────────────────
SELECT * FROM v_peminjaman_per_kategori;


-- ─────────────────────────────────────────────────────────────
-- 3. TOP 20 BUKU: Buku paling sering dipinjam
-- ─────────────────────────────────────────────────────────────
SELECT * FROM v_buku_terpopuler;


-- ─────────────────────────────────────────────────────────────
-- 4. ANGGOTA PALING AKTIF (Top 50)
-- ─────────────────────────────────────────────────────────────
SELECT * FROM v_anggota_aktif;


-- ─────────────────────────────────────────────────────────────
-- 5. TREN PEMINJAMAN: Per bulan
-- ─────────────────────────────────────────────────────────────
SELECT * FROM v_tren_peminjaman_bulanan;


-- ─────────────────────────────────────────────────────────────
-- 6. RINGKASAN DENDA: Per tahun
-- ─────────────────────────────────────────────────────────────
SELECT * FROM v_ringkasan_denda;


-- ─────────────────────────────────────────────────────────────
-- 7. STATISTIK PENERBIT: Jumlah buku dan peminjaman per penerbit
-- ─────────────────────────────────────────────────────────────
SELECT * FROM v_statistik_penerbit
LIMIT 30;


-- ─────────────────────────────────────────────────────────────
-- 8. DISTRIBUSI JENIS KELAMIN ANGGOTA
-- ─────────────────────────────────────────────────────────────
SELECT 
    CASE jenis_kelamin 
        WHEN 'L' THEN 'Laki-laki' 
        WHEN 'P' THEN 'Perempuan' 
    END AS jenis_kelamin,
    COUNT(*) AS jumlah,
    ROUND(COUNT(*) * 100.0 / (SELECT COUNT(*) FROM anggota), 2) AS persentase
FROM anggota
GROUP BY jenis_kelamin;


-- ─────────────────────────────────────────────────────────────
-- 9. STATUS PEMINJAMAN SAAT INI
-- ─────────────────────────────────────────────────────────────
SELECT 
    status,
    COUNT(*) AS jumlah,
    ROUND(COUNT(*) * 100.0 / (SELECT COUNT(*) FROM peminjaman), 2) AS persentase
FROM peminjaman
GROUP BY status
ORDER BY jumlah DESC;


-- ─────────────────────────────────────────────────────────────
-- 10. DISTRIBUSI BUKU PER TAHUN TERBIT
-- ─────────────────────────────────────────────────────────────
SELECT 
    tahun_terbit,
    COUNT(*) AS jumlah_buku,
    SUM(stok) AS total_stok
FROM buku
GROUP BY tahun_terbit
ORDER BY tahun_terbit;


-- ─────────────────────────────────────────────────────────────
-- 11. BUKU DENGAN STOK HABIS (perlu restok)
-- ─────────────────────────────────────────────────────────────
SELECT 
    b.judul,
    b.isbn,
    k.nama_kategori,
    b.stok,
    COUNT(p.peminjaman_id) AS total_peminjaman
FROM buku b
JOIN kategori k ON b.kategori_id = k.kategori_id
LEFT JOIN peminjaman p ON b.buku_id = p.buku_id
WHERE b.stok = 0
GROUP BY b.buku_id
ORDER BY total_peminjaman DESC
LIMIT 20;


-- ─────────────────────────────────────────────────────────────
-- 12. ANGGOTA DENGAN DENDA BELUM BAYAR
-- ─────────────────────────────────────────────────────────────
SELECT 
    a.nama_anggota,
    a.nim,
    COUNT(d.denda_id) AS jumlah_kasus,
    SUM(d.jumlah_denda) AS total_denda_belum_bayar
FROM denda d
JOIN peminjaman p ON d.peminjaman_id = p.peminjaman_id
JOIN anggota a ON p.anggota_id = a.anggota_id
WHERE d.status_bayar = 'belum_bayar'
GROUP BY a.anggota_id
ORDER BY total_denda_belum_bayar DESC
LIMIT 20;


-- ─────────────────────────────────────────────────────────────
-- 13. RATA-RATA DURASI PEMINJAMAN (dalam hari)
-- ─────────────────────────────────────────────────────────────
SELECT 
    k.nama_kategori,
    ROUND(AVG(julianday(p.tanggal_dikembalikan) - julianday(p.tanggal_pinjam)), 1) AS rata_rata_hari,
    MIN(julianday(p.tanggal_dikembalikan) - julianday(p.tanggal_pinjam)) AS min_hari,
    MAX(julianday(p.tanggal_dikembalikan) - julianday(p.tanggal_pinjam)) AS max_hari
FROM peminjaman p
JOIN buku b ON p.buku_id = b.buku_id
JOIN kategori k ON b.kategori_id = k.kategori_id
WHERE p.tanggal_dikembalikan IS NOT NULL
GROUP BY k.nama_kategori
ORDER BY rata_rata_hari DESC;


-- ─────────────────────────────────────────────────────────────
-- 14. PENGARANG DENGAN KARYA TERBANYAK
-- ─────────────────────────────────────────────────────────────
SELECT 
    pg.nama_pengarang,
    pg.negara,
    COUNT(bp.buku_id) AS jumlah_karya,
    GROUP_CONCAT(b.judul, ' | ') AS daftar_judul
FROM pengarang pg
JOIN buku_pengarang bp ON pg.pengarang_id = bp.pengarang_id
JOIN buku b ON bp.buku_id = b.buku_id
GROUP BY pg.pengarang_id
ORDER BY jumlah_karya DESC
LIMIT 15;


-- ─────────────────────────────────────────────────────────────
-- 15. PETUGAS PALING PRODUKTIF
-- ─────────────────────────────────────────────────────────────
SELECT 
    pt.nama_petugas,
    pt.jabatan,
    COUNT(p.peminjaman_id) AS total_transaksi,
    COUNT(CASE WHEN p.status = 'terlambat' THEN 1 END) AS kasus_terlambat
FROM petugas pt
JOIN peminjaman p ON pt.petugas_id = p.petugas_id
GROUP BY pt.petugas_id
ORDER BY total_transaksi DESC;


-- ─────────────────────────────────────────────────────────────
-- 16. DISTRIBUSI BAHASA BUKU
-- ─────────────────────────────────────────────────────────────
SELECT 
    bahasa,
    COUNT(*) AS jumlah_buku,
    ROUND(COUNT(*) * 100.0 / (SELECT COUNT(*) FROM buku), 2) AS persentase
FROM buku
GROUP BY bahasa
ORDER BY jumlah_buku DESC;


-- ─────────────────────────────────────────────────────────────
-- 17. PEMINJAMAN PER HARI DALAM SEMINGGU
-- ─────────────────────────────────────────────────────────────
SELECT 
    CASE CAST(strftime('%w', tanggal_pinjam) AS INTEGER)
        WHEN 0 THEN 'Minggu'
        WHEN 1 THEN 'Senin'
        WHEN 2 THEN 'Selasa'
        WHEN 3 THEN 'Rabu'
        WHEN 4 THEN 'Kamis'
        WHEN 5 THEN 'Jumat'
        WHEN 6 THEN 'Sabtu'
    END AS hari,
    COUNT(*) AS total_peminjaman
FROM peminjaman
GROUP BY CAST(strftime('%w', tanggal_pinjam) AS INTEGER)
ORDER BY CAST(strftime('%w', tanggal_pinjam) AS INTEGER);


-- ─────────────────────────────────────────────────────────────
-- 18. ANALISIS ANGGOTA PER STATUS
-- ─────────────────────────────────────────────────────────────
SELECT 
    status,
    COUNT(*) AS jumlah,
    ROUND(COUNT(*) * 100.0 / (SELECT COUNT(*) FROM anggota), 2) AS persentase
FROM anggota
GROUP BY status
ORDER BY jumlah DESC;


-- ─────────────────────────────────────────────────────────────
-- 19. PERTUMBUHAN ANGGOTA PER TAHUN
-- ─────────────────────────────────────────────────────────────
SELECT 
    strftime('%Y', tanggal_daftar) AS tahun,
    COUNT(*) AS anggota_baru,
    SUM(COUNT(*)) OVER (ORDER BY strftime('%Y', tanggal_daftar)) AS kumulatif
FROM anggota
GROUP BY strftime('%Y', tanggal_daftar)
ORDER BY tahun;


-- ─────────────────────────────────────────────────────────────
-- 20. KORELASI: Kategori vs Keterlambatan
-- ─────────────────────────────────────────────────────────────
SELECT 
    k.nama_kategori,
    COUNT(p.peminjaman_id) AS total_pinjam,
    COUNT(CASE WHEN p.status = 'terlambat' THEN 1 END) AS total_terlambat,
    ROUND(
        COUNT(CASE WHEN p.status = 'terlambat' THEN 1 END) * 100.0 / COUNT(p.peminjaman_id), 2
    ) AS persen_terlambat
FROM peminjaman p
JOIN buku b ON p.buku_id = b.buku_id
JOIN kategori k ON b.kategori_id = k.kategori_id
GROUP BY k.nama_kategori
ORDER BY persen_terlambat DESC;


-- ─────────────────────────────────────────────────────────────
-- 21. PELANGGAN YANG MENGORDER 3 HARI TERAKHIR (SELECT, FROM, JOIN, ON)
--     BESERTA EXPLAIN ANALYZE (BEFORE & AFTER INDEX)
-- Permintaan: "Perlihatkan saya pelanggan yang mengorder 3 hari terakhir"
-- ─────────────────────────────────────────────────────────────

-- =============================================================
-- BAGIAN A: MYSQL 8.0+ (DROP FOREIGN KEY -> DROP INDEX -> EXPLAIN ANALYZE)
-- =============================================================

-- TAHAP 1: Buang Foreign Key terlebih dahulu (agar MySQL mengizinkan drop index)
ALTER TABLE orders DROP FOREIGN KEY orders_id_pelanggan_foreign;

-- TAHAP 2: Buang Index pada tabel orders
DROP INDEX orders_id_pelanggan_tanggal_order_index ON orders;
DROP INDEX orders_tanggal_order_index ON orders;

-- Cek index setelah dihapus (hanya tersisa PRIMARY KEY)
SHOW INDEX FROM orders;

-- TAHAP 3: EXPLAIN & EXPLAIN ANALYZE SEBELUM INDEX (BEFORE)
EXPLAIN
SELECT 
    p.id_pelanggan,
    p.nama_pelanggan,
    p.no_telepon,
    p.email,
    o.id_order,
    o.nama_produk,
    o.jumlah,
    o.total_harga,
    o.tanggal_order,
    o.status_order
FROM pelanggan p
JOIN orders o ON p.id_pelanggan = o.id_pelanggan
WHERE o.tanggal_order >= DATE_SUB(NOW(), INTERVAL 3 DAY)
ORDER BY o.tanggal_order DESC;

EXPLAIN ANALYZE
SELECT 
    p.id_pelanggan,
    p.nama_pelanggan,
    p.no_telepon,
    p.email,
    o.id_order,
    o.nama_produk,
    o.jumlah,
    o.total_harga,
    o.tanggal_order,
    o.status_order
FROM pelanggan p
JOIN orders o ON p.id_pelanggan = o.id_pelanggan
WHERE o.tanggal_order >= DATE_SUB(NOW(), INTERVAL 3 DAY)
ORDER BY o.tanggal_order DESC;

-- TAHAP 4: Tambahkan kembali Index lalu pasang kembali Foreign Key (AFTER)
CREATE INDEX orders_tanggal_order_index ON orders(tanggal_order);
CREATE INDEX orders_id_pelanggan_tanggal_order_index ON orders(id_pelanggan, tanggal_order);

ALTER TABLE orders
    ADD CONSTRAINT orders_id_pelanggan_foreign
    FOREIGN KEY (id_pelanggan) REFERENCES pelanggan(id_pelanggan)
    ON DELETE CASCADE ON UPDATE CASCADE;

-- 4. EXPLAIN & EXPLAIN ANALYZE SESUDAH INDEX (AFTER)
EXPLAIN
SELECT 
    p.id_pelanggan,
    p.nama_pelanggan,
    p.no_telepon,
    p.email,
    o.id_order,
    o.nama_produk,
    o.jumlah,
    o.total_harga,
    o.tanggal_order,
    o.status_order
FROM pelanggan p
JOIN orders o ON p.id_pelanggan = o.id_pelanggan
WHERE o.tanggal_order >= DATE_SUB(NOW(), INTERVAL 3 DAY)
ORDER BY o.tanggal_order DESC;

EXPLAIN ANALYZE
SELECT 
    p.id_pelanggan,
    p.nama_pelanggan,
    p.no_telepon,
    p.email,
    o.id_order,
    o.nama_produk,
    o.jumlah,
    o.total_harga,
    o.tanggal_order,
    o.status_order
FROM pelanggan p
JOIN orders o ON p.id_pelanggan = o.id_pelanggan
WHERE o.tanggal_order >= DATE_SUB(NOW(), INTERVAL 3 DAY)
ORDER BY o.tanggal_order DESC;


-- =============================================================
-- BAGIAN B: SQLITE (EXPLAIN QUERY PLAN - BEFORE & AFTER INDEX)
-- =============================================================

-- 1. Hapus index untuk simulasi BEFORE
DROP INDEX IF EXISTS orders_tanggal_order_index;
DROP INDEX IF EXISTS orders_id_pelanggan_tanggal_order_index;

-- 2. EXPLAIN QUERY PLAN SEBELUM INDEX (BEFORE)
EXPLAIN QUERY PLAN
SELECT 
    p.id_pelanggan,
    p.nama_pelanggan,
    p.no_telepon,
    p.email,
    o.id_order,
    o.nama_produk,
    o.jumlah,
    o.total_harga,
    o.tanggal_order,
    o.status_order
FROM pelanggan p
JOIN orders o ON p.id_pelanggan = o.id_pelanggan
WHERE o.tanggal_order >= datetime('now', '-3 days')
ORDER BY o.tanggal_order DESC;

-- 3. Buat Index untuk optimasi
CREATE INDEX IF NOT EXISTS orders_tanggal_order_index ON orders(tanggal_order);
CREATE INDEX IF NOT EXISTS orders_id_pelanggan_tanggal_order_index ON orders(id_pelanggan, tanggal_order);

-- 4. EXPLAIN QUERY PLAN SESUDAH INDEX (AFTER)
EXPLAIN QUERY PLAN
SELECT 
    p.id_pelanggan,
    p.nama_pelanggan,
    p.no_telepon,
    p.email,
    o.id_order,
    o.nama_produk,
    o.jumlah,
    o.total_harga,
    o.tanggal_order,
    o.status_order
FROM pelanggan p
JOIN orders o ON p.id_pelanggan = o.id_pelanggan
WHERE o.tanggal_order >= datetime('now', '-3 days')
ORDER BY o.tanggal_order DESC;


-- ─────────────────────────────────────────────────────────────
-- 22. MENAMPILKAN ORDER DARI PELANGGAN TERTENTU (id_pelanggan)
--     SELECT ... FROM orders WHERE id_pelanggan = '...'
--     BESERTA EXPLAIN ANALYZE BEFORE & AFTER INDEX
-- ─────────────────────────────────────────────────────────────

-- 1. Hapus index untuk melihat kondisi BEFORE (Tanpa Index)
DROP INDEX IF EXISTS orders_id_pelanggan_tanggal_order_index;
DROP INDEX IF EXISTS orders_tanggal_order_index;

-- 2. EXPLAIN QUERY PLAN / EXPLAIN ANALYZE SEBELUM INDEX (BEFORE)
-- (Untuk MySQL 8.0+ ganti EXPLAIN QUERY PLAN menjadi EXPLAIN ANALYZE)
EXPLAIN QUERY PLAN
SELECT 
    id_order,
    id_pelanggan,
    id_buku,
    nama_produk,
    jumlah,
    total_harga,
    tanggal_order,
    status_order
FROM orders
WHERE id_pelanggan = 'PLG0001'
ORDER BY tanggal_order DESC;

-- 3. Buat Index pada id_pelanggan & tanggal_order (AFTER)
CREATE INDEX IF NOT EXISTS orders_tanggal_order_index ON orders(tanggal_order);
CREATE INDEX IF NOT EXISTS orders_id_pelanggan_tanggal_order_index ON orders(id_pelanggan, tanggal_order);

-- 4. EXPLAIN QUERY PLAN / EXPLAIN ANALYZE SESUDAH INDEX (AFTER)
EXPLAIN QUERY PLAN
SELECT 
    id_order,
    id_pelanggan,
    id_buku,
    nama_produk,
    jumlah,
    total_harga,
    tanggal_order,
    status_order
FROM orders
WHERE id_pelanggan = 'PLG0001'
ORDER BY tanggal_order DESC;

-- 5. Tampilkan hasil data SELECT setelah Index
SELECT 
    id_order,
    id_pelanggan,
    id_buku,
    nama_produk,
    jumlah,
    total_harga,
    tanggal_order,
    status_order
FROM orders
WHERE id_pelanggan = 'PLG0001'
ORDER BY tanggal_order DESC;


-- ─────────────────────────────────────────────────────────────
-- 23. SHOW INDEX PADA TABEL ORDERS
-- ─────────────────────────────────────────────────────────────

-- A. Versi MySQL / MariaDB:
SHOW INDEX FROM orders;

-- B. Versi SQLite:
PRAGMA index_list('orders');
PRAGMA index_info('orders_id_pelanggan_tanggal_order_index');
PRAGMA index_info('orders_tanggal_order_index');


-- ─────────────────────────────────────────────────────────────
-- 24. SIMULASI LOCKING & STRICT TWO-PHASE LOCKING (2PL)
--     Studi Kasus: Transaksi Stok Buku Perpustakaan Pentagon
-- ─────────────────────────────────────────────────────────────

-- Reset stok awal buku B0005 (Laskar Pelangi) menjadi 5 eksemplar
UPDATE buku SET stok = 5 WHERE id_buku = 'B0005';

-- Skenario B (Strict 2PL dengan FOR UPDATE):
-- Terminal 1:
-- BEGIN;
-- SELECT stok FROM buku WHERE id_buku = 'B0005' FOR UPDATE;
-- UPDATE buku SET stok = stok - 4 WHERE id_buku = 'B0005';
-- COMMIT;

-- Terminal 2:
-- BEGIN;
-- SELECT stok FROM buku WHERE id_buku = 'B0005' FOR UPDATE; -- (Waiting hingga Terminal 1 COMMIT)
-- ROLLBACK; -- (Dibatalkan karena sisa stok = 1, tidak cukup untuk pinjam/order 3 buku)

-- Terminal 3 (Live Lock Monitoring MySQL InnoDB):
SELECT 
    engine_transaction_id,
    object_name,
    lock_type,
    lock_mode,
    lock_status,
    lock_data
FROM performance_schema.data_locks
WHERE object_name = 'buku';
