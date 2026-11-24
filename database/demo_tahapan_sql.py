"""
=====================================================================
  DEMONSTRASI TAHAPAN SQL - DATABASE PERPUSTAKAAN
  
  Tahapan:
    1. SELECT dasar
    2. SELECT + WHERE (filter data)
    3. EXPLAIN QUERY PLAN (Before & After index)
    4. SELECT kembali (verifikasi)
    5. SELECT + GROUP BY (agregasi)
    6. INSERT 5 data baru
    7. SELECT kembali (tampilkan data baru)
=====================================================================
"""

import sqlite3
import os
import sys

DB_PATH = os.path.join(os.path.dirname(__file__), "perpustakaan.db")

def print_header(step_num, title):
    print(f"\n{'='*70}")
    print(f"  TAHAP {step_num}: {title}")
    print(f"{'='*70}")

def print_query(sql):
    print(f"\n  SQL Query:")
    print(f"  {'─'*60}")
    for line in sql.strip().split('\n'):
        print(f"  {line}")
    print(f"  {'─'*60}")

def print_table(headers, rows, max_col_width=30):
    """Pretty print table with dynamic column widths"""
    if not rows:
        print("  (Tidak ada data)")
        return
    
    # Calculate column widths
    col_widths = []
    for i, h in enumerate(headers):
        max_w = len(str(h))
        for row in rows:
            val = str(row[i]) if row[i] is not None else "NULL"
            max_w = max(max_w, min(len(val), max_col_width))
        col_widths.append(max_w)
    
    # Print header
    header_line = "  | " + " | ".join(str(h).ljust(col_widths[i]) for i, h in enumerate(headers)) + " |"
    separator = "  +" + "+".join("-" * (w + 2) for w in col_widths) + "+"
    
    print(separator)
    print(header_line)
    print(separator)
    
    # Print rows
    for row in rows:
        vals = []
        for i, val in enumerate(row):
            s = str(val) if val is not None else "NULL"
            if len(s) > max_col_width:
                s = s[:max_col_width-3] + "..."
            vals.append(s.ljust(col_widths[i]))
        print("  | " + " | ".join(vals) + " |")
    
    print(separator)
    print(f"  Total: {len(rows)} rows")

def run_query(cur, sql, params=None):
    """Execute query and return headers + rows"""
    if params:
        cur.execute(sql, params)
    else:
        cur.execute(sql)
    
    if cur.description:
        headers = [desc[0] for desc in cur.description]
        rows = cur.fetchall()
        return headers, rows
    return None, None

def main():
    conn = sqlite3.connect(DB_PATH)
    cur = conn.cursor()
    
    print("\n" + "=" * 70)
    print("  DEMONSTRASI TAHAPAN SQL - DATABASE PERPUSTAKAAN")
    print("  Database: perpustakaan.db (121,000+ records)")
    print("=" * 70)

    # ================================================================
    # TAHAP 1: SELECT DASAR
    # ================================================================
    print_header("1", "SELECT (Menampilkan Data Dasar)")
    
    # 1a. SELECT semua kolom dari tabel buku
    sql_1a = """
SELECT buku_id, judul, isbn, tahun_terbit, stok, bahasa
FROM buku
LIMIT 10;
"""
    print("\n  >> 1a. Menampilkan 10 data buku pertama")
    print_query(sql_1a)
    headers, rows = run_query(cur, sql_1a)
    print("\n  Output:")
    print_table(headers, rows)

    # 1b. SELECT dari tabel anggota
    sql_1b = """
SELECT anggota_id, nama_anggota, nim, jenis_kelamin, status
FROM anggota
LIMIT 10;
"""
    print("\n  >> 1b. Menampilkan 10 data anggota pertama")
    print_query(sql_1b)
    headers, rows = run_query(cur, sql_1b)
    print("\n  Output:")
    print_table(headers, rows)

    # 1c. SELECT dengan kolom spesifik + alias
    sql_1c = """
SELECT 
    p.peminjaman_id AS 'No',
    a.nama_anggota AS 'Peminjam',
    b.judul AS 'Judul Buku',
    p.tanggal_pinjam AS 'Tgl Pinjam',
    p.status AS 'Status'
FROM peminjaman p
JOIN anggota a ON p.anggota_id = a.anggota_id
JOIN buku b ON p.buku_id = b.buku_id
LIMIT 10;
"""
    print("\n  >> 1c. SELECT dengan JOIN dan Alias")
    print_query(sql_1c)
    headers, rows = run_query(cur, sql_1c)
    print("\n  Output:")
    print_table(headers, rows)

    # ================================================================
    # TAHAP 2: SELECT + WHERE (Filter Data)
    # ================================================================
    print_header("2", "SELECT + WHERE (Filter Data)")
    
    # 2a. WHERE dengan kondisi tunggal
    sql_2a = """
SELECT buku_id, judul, tahun_terbit, stok, bahasa
FROM buku
WHERE tahun_terbit >= 2020
LIMIT 10;
"""
    print("\n  >> 2a. Buku yang terbit tahun 2020 ke atas")
    print_query(sql_2a)
    headers, rows = run_query(cur, sql_2a)
    print("\n  Output:")
    print_table(headers, rows)

    # 2b. WHERE dengan AND
    sql_2b = """
SELECT buku_id, judul, stok, bahasa, tahun_terbit
FROM buku
WHERE stok = 0 AND bahasa = 'Indonesia'
LIMIT 10;
"""
    print("\n  >> 2b. Buku stok habis DAN berbahasa Indonesia")
    print_query(sql_2b)
    headers, rows = run_query(cur, sql_2b)
    print("\n  Output:")
    print_table(headers, rows)

    # 2c. WHERE dengan OR dan LIKE
    sql_2c = """
SELECT anggota_id, nama_anggota, nim, status, tanggal_daftar
FROM anggota
WHERE status = 'suspended' OR nama_anggota LIKE '%Budi%'
LIMIT 10;
"""
    print("\n  >> 2c. Anggota suspended ATAU nama mengandung 'Budi'")
    print_query(sql_2c)
    headers, rows = run_query(cur, sql_2c)
    print("\n  Output:")
    print_table(headers, rows)

    # 2d. WHERE dengan BETWEEN
    sql_2d = """
SELECT 
    p.peminjaman_id,
    a.nama_anggota,
    b.judul,
    p.tanggal_pinjam,
    p.status
FROM peminjaman p
JOIN anggota a ON p.anggota_id = a.anggota_id
JOIN buku b ON p.buku_id = b.buku_id
WHERE p.tanggal_pinjam BETWEEN '2025-01-01' AND '2025-06-30'
  AND p.status = 'terlambat'
LIMIT 10;
"""
    print("\n  >> 2d. Peminjaman terlambat di semester 1 tahun 2025")
    print_query(sql_2d)
    headers, rows = run_query(cur, sql_2d)
    print("\n  Output:")
    print_table(headers, rows)

    # 2e. WHERE dengan IN dan subquery
    sql_2e = """
SELECT anggota_id, nama_anggota, nim, status
FROM anggota
WHERE anggota_id IN (
    SELECT DISTINCT anggota_id 
    FROM peminjaman 
    WHERE status = 'terlambat'
)
LIMIT 10;
"""
    print("\n  >> 2e. Anggota yang pernah terlambat (subquery IN)")
    print_query(sql_2e)
    headers, rows = run_query(cur, sql_2e)
    print("\n  Output:")
    print_table(headers, rows)

    # ================================================================
    # TAHAP 3: EXPLAIN QUERY PLAN - BEFORE & AFTER
    # ================================================================
    print_header("3", "EXPLAIN QUERY PLAN (Analisis Before & After)")
    
    print("""
  Tujuan: Membandingkan performa query SEBELUM dan SESUDAH
  menggunakan INDEX untuk membuktikan optimasi database.
  """)

    # --- BEFORE: Drop index dulu untuk simulasi ---
    print("  ┌─────────────────────────────────────────────────┐")
    print("  │  3a. BEFORE - Tanpa Index (SCAN penuh)          │")
    print("  └─────────────────────────────────────────────────┘")
    
    # Hapus index yang ada untuk demo
    cur.execute("DROP INDEX IF EXISTS idx_peminjaman_status")
    cur.execute("DROP INDEX IF EXISTS idx_peminjaman_tgl_pinjam")
    conn.commit()
    
    sql_explain_before = """
EXPLAIN QUERY PLAN
SELECT 
    p.peminjaman_id,
    a.nama_anggota,
    b.judul,
    p.tanggal_pinjam,
    p.status
FROM peminjaman p
JOIN anggota a ON p.anggota_id = a.anggota_id
JOIN buku b ON p.buku_id = b.buku_id
WHERE p.status = 'terlambat'
  AND p.tanggal_pinjam BETWEEN '2025-01-01' AND '2025-12-31';
"""
    print_query(sql_explain_before)
    headers, rows = run_query(cur, sql_explain_before)
    print("\n  Output BEFORE (tanpa index):")
    print_table(headers, rows)
    
    print("""
  >> Analisis BEFORE:
     - Database melakukan FULL TABLE SCAN pada tabel peminjaman
     - Artinya SQLite harus membaca SEMUA 75,000 baris satu per satu
     - Ini sangat LAMBAT untuk tabel besar!
  """)

    # Ukur waktu query tanpa index
    import time
    
    sql_actual = """
SELECT COUNT(*) AS total
FROM peminjaman p
JOIN anggota a ON p.anggota_id = a.anggota_id
JOIN buku b ON p.buku_id = b.buku_id
WHERE p.status = 'terlambat'
  AND p.tanggal_pinjam BETWEEN '2025-01-01' AND '2025-12-31';
"""
    start = time.perf_counter()
    for _ in range(100):  # Run 100x for measurable time
        cur.execute(sql_actual)
        cur.fetchall()
    time_before = (time.perf_counter() - start) / 100 * 1000  # ms
    print(f"  Waktu rata-rata (100 eksekusi): {time_before:.3f} ms")

    # --- AFTER: Buat index ---
    print("\n  ┌─────────────────────────────────────────────────┐")
    print("  │  3b. AFTER - Dengan Index (optimasi)            │")
    print("  └─────────────────────────────────────────────────┘")
    
    sql_create_idx = """
-- Membuat index pada kolom yang sering di-filter
CREATE INDEX idx_peminjaman_status ON peminjaman(status);
CREATE INDEX idx_peminjaman_tgl_pinjam ON peminjaman(tanggal_pinjam);

-- Index gabungan (composite) untuk query yang sering digunakan
CREATE INDEX IF NOT EXISTS idx_peminjaman_status_tanggal 
ON peminjaman(status, tanggal_pinjam);
"""
    print_query(sql_create_idx)
    cur.executescript(sql_create_idx)
    conn.commit()
    print("\n  >> 3 INDEX berhasil dibuat!")
    
    # EXPLAIN setelah index
    print_query(sql_explain_before.replace("BEFORE", "AFTER"))
    headers, rows = run_query(cur, sql_explain_before)
    print("\n  Output AFTER (dengan index):")
    print_table(headers, rows)
    
    # Ukur waktu query dengan index
    start = time.perf_counter()
    for _ in range(100):
        cur.execute(sql_actual)
        cur.fetchall()
    time_after = (time.perf_counter() - start) / 100 * 1000  # ms
    print(f"\n  Waktu rata-rata (100 eksekusi): {time_after:.3f} ms")
    
    speedup = time_before / time_after if time_after > 0 else 0
    print(f"""
  >> Analisis AFTER:
     - Database menggunakan INDEX untuk langsung mencari data
     - Tidak perlu scan semua 75,000 baris
     
  ┌──────────────────────────────────────────────────────┐
  │  PERBANDINGAN PERFORMA                               │
  ├──────────────────────────────────────────────────────┤
  │  BEFORE (tanpa index) : {time_before:>8.3f} ms                  │
  │  AFTER  (dengan index): {time_after:>8.3f} ms                  │
  │  Speedup              : {speedup:>8.2f}x lebih cepat          │
  └──────────────────────────────────────────────────────┘
  """)

    # ================================================================
    # TAHAP 4: SELECT KEMBALI (Verifikasi setelah optimasi)
    # ================================================================
    print_header("4", "SELECT KEMBALI (Verifikasi Data)")
    
    # 4a. SELECT setelah index — query berjalan lebih cepat
    sql_4a = """
SELECT 
    p.peminjaman_id,
    a.nama_anggota,
    b.judul,
    p.tanggal_pinjam,
    p.tanggal_dikembalikan,
    p.status
FROM peminjaman p
JOIN anggota a ON p.anggota_id = a.anggota_id
JOIN buku b ON p.buku_id = b.buku_id
WHERE p.status = 'terlambat'
  AND p.tanggal_pinjam BETWEEN '2025-01-01' AND '2025-06-30'
LIMIT 10;
"""
    print("\n  >> 4a. Data peminjaman terlambat (semester 1 2025) — sekarang lebih cepat")
    print_query(sql_4a)
    headers, rows = run_query(cur, sql_4a)
    print("\n  Output:")
    print_table(headers, rows)

    # 4b. SELECT dengan COUNT untuk verifikasi
    sql_4b = """
SELECT 
    status,
    COUNT(*) AS jumlah
FROM peminjaman
WHERE tanggal_pinjam BETWEEN '2025-01-01' AND '2025-12-31'
GROUP BY status;
"""
    print("\n  >> 4b. Ringkasan status peminjaman tahun 2025")
    print_query(sql_4b)
    headers, rows = run_query(cur, sql_4b)
    print("\n  Output:")
    print_table(headers, rows)

    # ================================================================
    # TAHAP 5: SELECT + GROUP BY (Agregasi Data)
    # ================================================================
    print_header("5", "SELECT + GROUP BY (Agregasi Data)")
    
    # 5a. GROUP BY sederhana dengan COUNT
    sql_5a = """
SELECT 
    k.nama_kategori,
    COUNT(b.buku_id) AS jumlah_buku,
    SUM(b.stok) AS total_stok,
    ROUND(AVG(b.jumlah_halaman)) AS avg_halaman
FROM buku b
JOIN kategori k ON b.kategori_id = k.kategori_id
GROUP BY k.nama_kategori
ORDER BY jumlah_buku DESC;
"""
    print("\n  >> 5a. Jumlah buku per kategori (GROUP BY)")
    print_query(sql_5a)
    headers, rows = run_query(cur, sql_5a)
    print("\n  Output:")
    print_table(headers, rows)

    # 5b. GROUP BY dengan HAVING
    sql_5b = """
SELECT 
    a.nama_anggota,
    a.nim,
    COUNT(p.peminjaman_id) AS total_pinjam,
    SUM(CASE WHEN p.status = 'terlambat' THEN 1 ELSE 0 END) AS kali_terlambat
FROM anggota a
JOIN peminjaman p ON a.anggota_id = p.anggota_id
GROUP BY a.anggota_id
HAVING total_pinjam > 25
ORDER BY total_pinjam DESC
LIMIT 15;
"""
    print("\n  >> 5b. Anggota dengan > 25 peminjaman (GROUP BY + HAVING)")
    print_query(sql_5b)
    headers, rows = run_query(cur, sql_5b)
    print("\n  Output:")
    print_table(headers, rows)

    # 5c. GROUP BY per bulan (tren)
    sql_5c = """
SELECT 
    strftime('%Y-%m', tanggal_pinjam) AS bulan,
    COUNT(*) AS total_peminjaman,
    COUNT(CASE WHEN status = 'terlambat' THEN 1 END) AS terlambat,
    COUNT(CASE WHEN status = 'dikembalikan' THEN 1 END) AS tepat_waktu,
    ROUND(COUNT(CASE WHEN status = 'terlambat' THEN 1 END) * 100.0 / COUNT(*), 1) AS persen_terlambat
FROM peminjaman
WHERE tanggal_pinjam BETWEEN '2025-01-01' AND '2025-12-31'
GROUP BY strftime('%Y-%m', tanggal_pinjam)
ORDER BY bulan;
"""
    print("\n  >> 5c. Tren peminjaman bulanan tahun 2025 (GROUP BY bulan)")
    print_query(sql_5c)
    headers, rows = run_query(cur, sql_5c)
    print("\n  Output:")
    print_table(headers, rows)

    # 5d. GROUP BY dengan multiple aggregate
    sql_5d = """
SELECT 
    strftime('%Y', p.tanggal_pinjam) AS tahun,
    COUNT(d.denda_id) AS kasus_denda,
    SUM(d.jumlah_denda) AS total_denda,
    ROUND(AVG(d.jumlah_denda)) AS avg_denda,
    MAX(d.jumlah_denda) AS denda_max,
    SUM(CASE WHEN d.status_bayar = 'lunas' THEN 1 ELSE 0 END) AS sudah_lunas,
    SUM(CASE WHEN d.status_bayar = 'belum_bayar' THEN 1 ELSE 0 END) AS belum_bayar
FROM denda d
JOIN peminjaman p ON d.peminjaman_id = p.peminjaman_id
GROUP BY tahun
ORDER BY tahun;
"""
    print("\n  >> 5d. Statistik denda per tahun (GROUP BY + aggregasi)")
    print_query(sql_5d)
    headers, rows = run_query(cur, sql_5d)
    print("\n  Output:")
    print_table(headers, rows)

    # ================================================================
    # TAHAP 6: INSERT 5 DATA BARU
    # ================================================================
    print_header("6", "INSERT (Menambahkan 5 Data Baru)")
    
    # 6a. Hapus data demo lama (jika ada dari run sebelumnya)
    cur.execute("DELETE FROM peminjaman WHERE anggota_id IN (SELECT anggota_id FROM anggota WHERE nim LIKE '2026IF990%')")
    cur.execute("DELETE FROM anggota WHERE nim LIKE '2026IF990%'")
    conn.commit()

    # INSERT 5 anggota baru
    sql_6a = """
INSERT INTO anggota (nama_anggota, nim, email, telepon, alamat, jenis_kelamin, tanggal_daftar, status)
VALUES 
    ('Arya Randani',    '2026IF9901', 'arya.randani@student.ac.id',    '081234567890', 'Jl. Merdeka No. 10, Bandung',    'L', '2026-10-27', 'aktif'),
    ('Siti Nurhaliza',  '2026IF9902', 'siti.nurhaliza@student.ac.id',  '081234567891', 'Jl. Sudirman No. 25, Jakarta',   'P', '2026-10-27', 'aktif'),
    ('Budi Hartono',    '2026IF9903', 'budi.hartono@student.ac.id',    '081234567892', 'Jl. Diponegoro No. 5, Surabaya', 'L', '2026-10-27', 'aktif'),
    ('Dewi Lestari',    '2026IF9904', 'dewi.lestari@student.ac.id',    '081234567893', 'Jl. Kartini No. 15, Yogyakarta', 'P', '2026-10-27', 'aktif'),
    ('Rizky Pratama',   '2026IF9905', 'rizky.pratama@student.ac.id',   '081234567894', 'Jl. Pahlawan No. 30, Semarang',  'L', '2026-10-27', 'aktif');
"""
    print("\n  >> 6a. INSERT 5 anggota baru")
    print_query(sql_6a)
    cur.executescript(sql_6a)
    conn.commit()
    print("\n  >> 5 anggota baru berhasil ditambahkan!")

    # 6b. INSERT 5 peminjaman baru untuk anggota tersebut
    # Dapatkan ID anggota yang baru saja ditambahkan
    cur.execute("SELECT anggota_id, nama_anggota FROM anggota WHERE nim LIKE '2026IF990%' ORDER BY anggota_id")
    new_members = cur.fetchall()
    
    print(f"\n  ID anggota baru: {[m[0] for m in new_members]}")
    
    sql_6b = f"""
INSERT INTO peminjaman (anggota_id, buku_id, petugas_id, tanggal_pinjam, tanggal_harus_kembali, status, catatan)
VALUES 
    ({new_members[0][0]}, 1,  1, '2026-10-27', '2026-11-10', 'dipinjam', 'Peminjaman buku pertama'),
    ({new_members[1][0]}, 50, 2, '2026-10-27', '2026-11-10', 'dipinjam', 'Untuk tugas kuliah'),
    ({new_members[2][0]}, 100,3, '2026-10-27', '2026-11-03', 'dipinjam', 'Referensi skripsi'),
    ({new_members[3][0]}, 200,1, '2026-10-27', '2026-11-03', 'dipinjam', 'Bahan presentasi'),
    ({new_members[4][0]}, 500,2, '2026-10-27', '2026-11-10', 'dipinjam', 'Tugas basis data');
"""
    print("\n  >> 6b. INSERT 5 peminjaman baru untuk anggota baru")
    print_query(sql_6b)
    cur.execute(f"""
INSERT INTO peminjaman (anggota_id, buku_id, petugas_id, tanggal_pinjam, tanggal_harus_kembali, status, catatan)
VALUES 
    ({new_members[0][0]}, 1,  1, '2026-10-27', '2026-11-10', 'dipinjam', 'Peminjaman buku pertama'),
    ({new_members[1][0]}, 50, 2, '2026-10-27', '2026-11-10', 'dipinjam', 'Untuk tugas kuliah'),
    ({new_members[2][0]}, 100,3, '2026-10-27', '2026-11-03', 'dipinjam', 'Referensi skripsi'),
    ({new_members[3][0]}, 200,1, '2026-10-27', '2026-11-03', 'dipinjam', 'Bahan presentasi'),
    ({new_members[4][0]}, 500,2, '2026-10-27', '2026-11-10', 'dipinjam', 'Tugas basis data')
""")
    conn.commit()
    print("\n  >> 5 peminjaman baru berhasil ditambahkan!")

    # ================================================================
    # TAHAP 7: SELECT KEMBALI (Tampilkan Data yang Baru Di-INSERT)
    # ================================================================
    print_header("7", "SELECT KEMBALI (Verifikasi Data Baru)")
    
    # 7a. Tampilkan anggota baru
    sql_7a = """
SELECT 
    anggota_id, 
    nama_anggota, 
    nim, 
    email, 
    jenis_kelamin, 
    tanggal_daftar, 
    status
FROM anggota
WHERE nim LIKE '2026IF990%'
ORDER BY anggota_id;
"""
    print("\n  >> 7a. Verifikasi 5 anggota baru yang ditambahkan")
    print_query(sql_7a)
    headers, rows = run_query(cur, sql_7a)
    print("\n  Output:")
    print_table(headers, rows)

    # 7b. Tampilkan peminjaman baru dengan JOIN
    sql_7b = """
SELECT 
    p.peminjaman_id,
    a.nama_anggota,
    a.nim,
    b.judul AS buku_dipinjam,
    pt.nama_petugas AS dilayani_oleh,
    p.tanggal_pinjam,
    p.tanggal_harus_kembali,
    p.status,
    p.catatan
FROM peminjaman p
JOIN anggota a ON p.anggota_id = a.anggota_id
JOIN buku b ON p.buku_id = b.buku_id
JOIN petugas pt ON p.petugas_id = pt.petugas_id
WHERE a.nim LIKE '2026IF990%'
ORDER BY p.peminjaman_id;
"""
    print("\n  >> 7b. Verifikasi 5 peminjaman baru (JOIN lengkap)")
    print_query(sql_7b)
    headers, rows = run_query(cur, sql_7b)
    print("\n  Output:")
    print_table(headers, rows)

    # 7c. Cek total data setelah INSERT
    sql_7c = """
SELECT 'anggota' AS tabel, COUNT(*) AS total FROM anggota
UNION ALL SELECT 'peminjaman', COUNT(*) FROM peminjaman
UNION ALL SELECT 'buku', COUNT(*) FROM buku
UNION ALL SELECT 'denda', COUNT(*) FROM denda;
"""
    print("\n  >> 7c. Total data setelah INSERT")
    print_query(sql_7c)
    headers, rows = run_query(cur, sql_7c)
    print("\n  Output:")
    print_table(headers, rows)

    # ================================================================
    # RINGKASAN
    # ================================================================
    print(f"\n{'='*70}")
    print("  RINGKASAN DEMONSTRASI")
    print(f"{'='*70}")
    print("""
  Tahap 1: SELECT        - Menampilkan data dasar dari tabel
  Tahap 2: WHERE         - Filter data dengan kondisi (AND, OR, LIKE, 
                           BETWEEN, IN, subquery)
  Tahap 3: EXPLAIN       - Analisis performa BEFORE/AFTER index
  Tahap 4: SELECT        - Verifikasi data setelah optimasi
  Tahap 5: GROUP BY      - Agregasi data (COUNT, SUM, AVG, MAX, HAVING)
  Tahap 6: INSERT        - Menambahkan 5 anggota + 5 peminjaman baru
  Tahap 7: SELECT        - Verifikasi data yang baru ditambahkan
  """)
    print(f"{'='*70}")
    
    conn.close()

if __name__ == "__main__":
    main()
