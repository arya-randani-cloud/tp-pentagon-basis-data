# tp-pentagon-basis-data
Semua tugas dari mata kuliah Basis Data disimpan di sini.

---

# Konkurensi
**Simulasi Locking & Strict Two-Phase Locking (2PL) — Studi Kasus Transaksi Stok Buku Perpustakaan Pentagon**

## 1. Deskripsi Studi Kasus Konkurensi
Simulasi ini membuktikan mekanisme **Exclusive Lock (X)** dan protokol **Strict Two-Phase Locking (Strict 2PL)** dalam mencegah anomali **Lost Update** pada transaksi stok buku di **Perpustakaan Pentagon**:
- **Kondisi Awal**: Buku **Laskar Pelangi (`id_buku = 'B0005'`)** memiliki **stok awal 5 eksemplar** (`stok = 5`).
- **Aksi Konkuren**: Pada waktu yang bersamaan, dua sesi melakukan transaksi pada baris buku `B0005`:
  - **Terminal 1 (Rizky / `PLG0001`)**: Meminjam/mengorder **4 eksemplar** (`-4 stok`).
  - **Terminal 2 (Siti / `PLG0002`)**: Meminjam/mengorder **3 eksemplar** (`-3 stok`).
- **Target Konsistensi**: Total permintaan adalah **7 eksemplar** (`4 + 3`), sedangkan stok hanya **5 eksemplar**. Sistem wajib menyetujui transaksi pertama (sisa stok = **1 eksemplar**) dan membatalkan (`ROLLBACK`) transaksi kedua karena sisa stok tidak mencukupi.

---

## 2. Cara Run di Terminal

### A. Menjalankan Simulasi Step-by-Step Otomatis (Laravel Artisan)

#### 1. Menggunakan Database SQLite (Tanpa Perlu Nyalakan MySQL Server)
Jalankan perintah berikut di terminal (PowerShell) untuk melihat seluruh tahapan **Skenario A (Before: Lost Update)** dan **Skenario B (After: Exclusive Lock & Strict 2PL)**:
```powershell
$env:DB_CONNECTION="sqlite"; $env:DB_DATABASE="database/database.sqlite"; php artisan buku:simulasi-2pl
```

Untuk **Mode Interaktif** (menekan tombol `ENTER` untuk maju satu langkah demi satu langkah dari Step 1 s/d Step 6):
```powershell
$env:DB_CONNECTION="sqlite"; $env:DB_DATABASE="database/database.sqlite"; php artisan buku:simulasi-2pl --interaktif
```

#### 2. Menggunakan Database MySQL (`perpustakaan_pentagon`)
Pastikan service MySQL aktif dan konfigurasi `.env` mengarah ke `DB_CONNECTION=mysql` & `DB_DATABASE=perpustakaan_pentagon`, lalu jalankan:
```bash
php artisan migrate --seed
php artisan buku:simulasi-2pl
```
Atau dengan mode interaktif per-step:
```bash
php artisan buku:simulasi-2pl --interaktif
```

---

### B. Cara Run Praktik Multi-Terminal (3 Terminal MySQL Berdampingan)
Seluruh skrip SQL tersedia di file [`database/simulasi_locking_2pl_perpustakaan.sql`](database/simulasi_locking_2pl_perpustakaan.sql).

Buka **3 Terminal** berdampingan dan masuk ke MySQL CLI di masing-masing terminal:
```bash
mysql -u root -p perpustakaan_pentagon
```

#### Skenario A (Before): Tanpa Locking (Anomali *Lost Update*)
```sql
-- Reset stok awal buku B0005 = 5
UPDATE buku SET stok = 5 WHERE id_buku = 'B0005';

-- [Step 1] Jalankan di Terminal 1 & Terminal 2:
BEGIN;

-- [Step 2] Jalankan di Terminal 1 (Output: 5):
SELECT stok FROM buku WHERE id_buku = 'B0005';

-- [Step 3] Jalankan di Terminal 2 (Output: 5, karena baris tidak dikunci):
SELECT stok FROM buku WHERE id_buku = 'B0005';

-- [Step 4] Jalankan di Terminal 1 (Stok menjadi 5 - 4 = 1):
UPDATE buku SET stok = 5 - 4 WHERE id_buku = 'B0005';
COMMIT;

-- [Step 5] Jalankan di Terminal 2 (Menghitung dari pembacaan lama 5 - 3 = 2, menimpa update Rizky!):
UPDATE buku SET stok = 5 - 3 WHERE id_buku = 'B0005';
COMMIT;

-- Cek Hasil Akhir (Inkonsisten / Lost Update -> stok tercatat 2):
SELECT id_buku, judul_buku, stok FROM buku WHERE id_buku = 'B0005';
```

#### Skenario B (After): Exclusive Lock (`FOR UPDATE`) & Strict 2PL
```sql
-- Reset stok awal buku B0005 = 5
UPDATE buku SET stok = 5 WHERE id_buku = 'B0005';

-- [Step 1] Jalankan di Terminal 1 & Terminal 2 (Growing Phase):
BEGIN;

-- [Step 2] Jalankan di Terminal 1 (Memasang Exclusive Lock / X-Lock pada B0005, Output: 5):
SELECT stok FROM buku WHERE id_buku = 'B0005' FOR UPDATE;

-- [Step 3] Jalankan di Terminal 2 (Kursor Terminal 2 akan TERBLOKIR / WAITING):
SELECT stok FROM buku WHERE id_buku = 'B0005' FOR UPDATE;

-- [Inspeksi Lock di Terminal 3 saat Terminal 2 sedang WAITING]:
SELECT engine_transaction_id, object_name, lock_type, lock_mode, lock_status, lock_data
FROM performance_schema.data_locks
WHERE object_schema = 'perpustakaan_pentagon' AND object_name = 'buku';

-- [Step 4] Jalankan di Terminal 1 (Mengubah stok menjadi 1, X-Lock masih ditahan):
UPDATE buku SET stok = stok - 4 WHERE id_buku = 'B0005';

-- [Step 5] Jalankan di Terminal 1 (Shrinking Phase - melepas X-Lock saat COMMIT):
COMMIT;
-- (Seketika Terminal 2 otomatis terbuka dan menampilkan stok terbaru = 1)

-- [Step 6] Jalankan di Terminal 2 (Karena sisa stok 1 < kebutuhan 3 buku, batalkan transaksi):
ROLLBACK;

-- Cek Hasil Akhir (Konsisten -> stok akhir = 1):
SELECT id_buku, judul_buku, stok FROM buku WHERE id_buku = 'B0005';
```

---

## 3. Perintah Artisan Tambahan (Query, Index, & Explain Analyze)
```powershell
# Menampilkan pelanggan yang mengorder dalam 3 hari terakhir (SELECT, FROM, JOIN, ON)
php artisan pelanggan:order-3-hari

# Menampilkan order berdasarkan id_pelanggan (SELECT, FROM, WHERE)
php artisan order:pelanggan PLG0001

# Menampilkan daftar index pada tabel orders (SHOW INDEX FROM orders)
php artisan order:show-index

# Simulasi EXPLAIN ANALYZE Before & After Index
php artisan pelanggan:explain-order-3-hari
php artisan order:explain-pelanggan PLG0001

# Tahapan membuang Foreign Key terlebih dahulu lalu Drop Index (dan mengembalikannya)
php artisan order:drop-index
php artisan order:add-index
```
