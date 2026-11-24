-- =====================================================================
-- MODUL PRAKTIKUM SISTEM BASIS DATA • CONCURRENCY CONTROL
-- Simulasi Locking & Strict Two-Phase Locking (2PL)
-- Studi Kasus: Transaksi Stok Buku pada Database Perpustakaan Pentagon
-- =====================================================================

-- ─────────────────────────────────────────────────────────────────────
-- 2. PERSIAPAN LINGKUNGAN (SETUP DATABASE & TABEL)
-- Jalankan pada Terminal 1
-- ─────────────────────────────────────────────────────────────────────
CREATE DATABASE IF NOT EXISTS perpustakaan_pentagon;
USE perpustakaan_pentagon;

CREATE TABLE IF NOT EXISTS buku (
    id_buku VARCHAR(20) PRIMARY KEY,
    judul_buku VARCHAR(255) NOT NULL,
    pengarang VARCHAR(100),
    penerbit VARCHAR(100),
    tahun_terbit SMALLINT,
    kategori VARCHAR(100),
    stok INT NOT NULL DEFAULT 0
) ENGINE=InnoDB;

-- Set kondisi awal: Buku B0005 (Laskar Pelangi) dengan stok awal = 5 eksemplar
INSERT INTO buku (id_buku, judul_buku, pengarang, penerbit, tahun_terbit, kategori, stok)
VALUES ('B0005', 'Laskar Pelangi', 'Andrea Hirata', 'Bentang Pustaka', 2018, 'Sastra / Novel', 5)
ON DUPLICATE KEY UPDATE stok = 5;

SELECT id_buku, judul_buku, stok FROM buku WHERE id_buku = 'B0005';


-- ─────────────────────────────────────────────────────────────────────
-- 3. SKENARIO A (BEFORE): TANPA LOCKING (ANOMALI LOST UPDATE)
-- Terminal 1 (Rizky - PLG0001: Pinjam/Order 4 buku)
-- Terminal 2 (Siti  - PLG0002: Pinjam/Order 3 buku)
-- ─────────────────────────────────────────────────────────────────────

-- [Step 1] Jalankan di Terminal 1 & Terminal 2:
BEGIN;

-- [Step 2] Jalankan di Terminal 1 (Rizky membaca stok awal = 5):
SELECT stok FROM buku WHERE id_buku = 'B0005';

-- [Step 3] Jalankan di Terminal 2 (Siti juga membaca stok awal = 5 karena tanpa lock):
SELECT stok FROM buku WHERE id_buku = 'B0005';

-- [Step 4] Jalankan di Terminal 1 (Rizky mengurangi stok 5 - 4 = 1 lalu COMMIT):
UPDATE buku SET stok = 5 - 4 WHERE id_buku = 'B0005';
COMMIT;

-- [Step 5] Jalankan di Terminal 2 (Siti menghitung dari pembacaan lama 5 - 3 = 2 lalu COMMIT):
UPDATE buku SET stok = 5 - 3 WHERE id_buku = 'B0005';
COMMIT;

-- [Verifikasi Hasil Akhir Skenario A - Inkonsisten / Lost Update]:
-- Buku yang keluar = 4 + 3 = 7 eksemplar (melebihi stok awal 5),
-- tetapi stok tercatat masih tersisa 2 eksemplar (Update Rizky hilang/tertindih).
SELECT id_buku, judul_buku, stok FROM buku WHERE id_buku = 'B0005';


-- ─────────────────────────────────────────────────────────────────────
-- 4. SKENARIO B (AFTER): EXCLUSIVE LOCK (X) & STRICT 2PL (FOR UPDATE)
-- ─────────────────────────────────────────────────────────────────────

-- Reset stok awal buku B0005 kembali ke 5 eksemplar:
UPDATE buku SET stok = 5 WHERE id_buku = 'B0005';

-- [Step 1] Jalankan di Terminal 1 & Terminal 2 (Growing Phase):
BEGIN;

-- [Step 2] Jalankan di Terminal 1 (Memasang Exclusive Lock / X-Lock pada baris B0005):
SELECT stok FROM buku WHERE id_buku = 'B0005' FOR UPDATE;

-- [Step 3] Jalankan di Terminal 2 (Akan TERBLOKIR / WAITING karena X-Lock ditahan Terminal 1):
SELECT stok FROM buku WHERE id_buku = 'B0005' FOR UPDATE;

-- [Step 4] Jalankan di Terminal 1 (Mengurangi stok menjadi 5 - 4 = 1, kunci belum dilepas):
UPDATE buku SET stok = stok - 4 WHERE id_buku = 'B0005';

-- [Step 5] Jalankan di Terminal 1 (Shrinking Phase - melepas X-Lock serentak saat COMMIT):
COMMIT;
-- Saat Terminal 1 COMMIT, Terminal 2 otomatis terbuka dan menerima output stok terbaru = 1.

-- [Step 6] Jalankan di Terminal 2 (Karena sisa stok = 1 < kebutuhan Siti 3 buku, batalkan transaksi):
ROLLBACK;

-- [Verifikasi Hasil Akhir Skenario B - Konsisten]:
SELECT id_buku, judul_buku, stok FROM buku WHERE id_buku = 'B0005';


-- ─────────────────────────────────────────────────────────────────────
-- 5. INSPEKSI KUNCI AKTIF (LIVE LOCK MONITORING DI TERMINAL 3)
-- Jalankan pada Terminal 3 saat Skenario B berada di Step 3 atau Step 4
-- (sebelum Terminal 1 menjalankan COMMIT)
-- ─────────────────────────────────────────────────────────────────────

-- A. Inspeksi pada MySQL 8.4 (InnoDB):
SELECT 
    engine_transaction_id,
    object_schema,
    object_name,
    index_name,
    lock_type,
    lock_mode,
    lock_status,
    lock_data
FROM performance_schema.data_locks
WHERE object_schema = 'perpustakaan_pentagon'
  AND object_name = 'buku';

-- B. Inspeksi pada PostgreSQL (jika menggunakan PostgreSQL):
-- SELECT pid, locktype, relation::regclass AS tabel, mode, granted
-- FROM pg_locks
-- WHERE relation = 'buku'::regclass;
