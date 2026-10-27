-- =============================================================
--  QUERY ANALISIS DATABASE PERPUSTAKAAN
--  Gunakan query ini di Database Viewer / SQLite extension
-- =============================================================

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
