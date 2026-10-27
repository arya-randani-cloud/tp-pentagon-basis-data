"""
=============================================================
  GENERATOR DATABASE PERPUSTAKAAN - 100K+ DATA SAMPEL
  Mata Kuliah: Basis Data
  
  Tabel:
    1. kategori        (~25 rows)
    2. penerbit         (~200 rows)
    3. pengarang        (~1,000 rows)
    4. petugas          (~30 rows)
    5. anggota          (~5,000 rows)
    6. buku             (~10,000 rows)
    7. buku_pengarang   (~13,000 rows)
    8. peminjaman       (~75,000 rows)
    9. denda            (~15,000 rows)
    
  Total: ~119,000+ rows
=============================================================
"""

import sqlite3
import random
import string
import os
import time
from datetime import datetime, timedelta

# ─── CONFIG ──────────────────────────────────────────
DB_PATH = os.path.join(os.path.dirname(__file__), "perpustakaan.db")

NUM_KATEGORI = 25
NUM_PENERBIT = 200
NUM_PENGARANG = 1000
NUM_PETUGAS = 30
NUM_ANGGOTA = 5000
NUM_BUKU = 10000
NUM_PEMINJAMAN = 75000

# ─── HELPER DATA ─────────────────────────────────────
NAMA_DEPAN = [
    "Adi", "Agus", "Ahmad", "Aisyah", "Aldi", "Amir", "Andi", "Angga", "Ani", "Anisa",
    "Arif", "Ayu", "Bagas", "Bagus", "Bambang", "Bayu", "Budi", "Cahya", "Citra", "Dani",
    "Dea", "Dedi", "Deni", "Dewi", "Dian", "Dika", "Dimas", "Dina", "Dwi", "Eka",
    "Eko", "Elsa", "Endang", "Erna", "Fajar", "Fani", "Farel", "Farhan", "Fauzi", "Fikri",
    "Galih", "Gilang", "Gunawan", "Hadi", "Hana", "Hendra", "Heri", "Ika", "Ilham", "Imam",
    "Indah", "Indra", "Irfan", "Irma", "Ismail", "Ivan", "Joko", "Kartika", "Kevin", "Kurnia",
    "Lestari", "Lina", "Linda", "Lukman", "Maharani", "Maya", "Mega", "Mira", "Muhammad", "Nanda",
    "Nani", "Nia", "Nisa", "Nova", "Nur", "Nurul", "Oki", "Putra", "Putri", "Rafi",
    "Rahma", "Rani", "Ratna", "Reza", "Ria", "Rina", "Rini", "Rizki", "Rizky", "Rosa",
    "Sari", "Sinta", "Siti", "Sri", "Suci", "Surya", "Taufik", "Tri", "Wahyu", "Wati",
    "Widi", "Wulan", "Yani", "Yoga", "Yudi", "Yuli", "Yusuf", "Zahra", "Zaki", "Zulfa"
]

NAMA_BELAKANG = [
    "Pratama", "Saputra", "Wijaya", "Kusuma", "Hartono", "Santoso", "Hidayat", "Ramadhani",
    "Nugroho", "Setiawan", "Utami", "Purnama", "Lestari", "Wibowo", "Susanto", "Permana",
    "Ramadhan", "Kurniawan", "Cahyani", "Prasetyo", "Handayani", "Fitriani", "Amalia",
    "Maulana", "Puspita", "Hakim", "Syahputra", "Firmansyah", "Suryani", "Wulandari",
    "Putra", "Sanjaya", "Budiman", "Surya", "Gunawan", "Adriansyah", "Mahendra", "Pradana",
    "Anggraini", "Safitri", "Haryanto", "Suryadi", "Prabowo", "Yuniar", "Octavia",
    "Perdana", "Ardiansyah", "Firdaus", "Nuraini", "Hasan"
]

KOTA = [
    "Jakarta", "Bandung", "Surabaya", "Yogyakarta", "Semarang", "Malang", "Medan",
    "Makassar", "Palembang", "Denpasar", "Bogor", "Solo", "Depok", "Tangerang",
    "Bekasi", "Manado", "Padang", "Banjarmasin", "Pontianak", "Balikpapan"
]

JALAN = [
    "Jl. Merdeka", "Jl. Sudirman", "Jl. Gatot Subroto", "Jl. Ahmad Yani", "Jl. Diponegoro",
    "Jl. Thamrin", "Jl. Pahlawan", "Jl. Kartini", "Jl. Imam Bonjol", "Jl. Hayam Wuruk",
    "Jl. Cendrawasih", "Jl. Mawar", "Jl. Melati", "Jl. Kenanga", "Jl. Anggrek",
    "Jl. Dahlia", "Jl. Flamboyan", "Jl. Teratai", "Jl. Bougenville", "Jl. Seroja"
]

NAMA_KATEGORI = [
    ("Fiksi", "Novel, cerpen, dan karya fiksi lainnya"),
    ("Non-Fiksi", "Buku berdasarkan fakta dan informasi nyata"),
    ("Sains & Teknologi", "Buku tentang ilmu pengetahuan dan teknologi"),
    ("Matematika", "Buku tentang matematika murni dan terapan"),
    ("Sejarah", "Buku tentang peristiwa dan tokoh sejarah"),
    ("Filsafat", "Buku tentang pemikiran dan filsafat"),
    ("Agama", "Buku tentang keagamaan dan spiritualitas"),
    ("Psikologi", "Buku tentang perilaku dan kejiwaan manusia"),
    ("Ekonomi & Bisnis", "Buku tentang ekonomi, bisnis, dan manajemen"),
    ("Hukum", "Buku tentang peraturan dan hukum"),
    ("Pendidikan", "Buku tentang dunia pendidikan dan pedagogik"),
    ("Bahasa & Sastra", "Buku tentang linguistik dan karya sastra"),
    ("Kedokteran", "Buku tentang ilmu kedokteran dan kesehatan"),
    ("Teknik", "Buku tentang teknik sipil, mesin, elektro, dll"),
    ("Informatika", "Buku tentang komputer, pemrograman, dan IT"),
    ("Seni & Budaya", "Buku tentang seni rupa, musik, dan budaya"),
    ("Olahraga", "Buku tentang olahraga dan kebugaran"),
    ("Biografi", "Kisah hidup tokoh-tokoh terkenal"),
    ("Ensiklopedia", "Kumpulan informasi lengkap dan menyeluruh"),
    ("Kamus & Referensi", "Buku referensi dan kamus"),
    ("Anak-anak", "Buku bacaan untuk anak-anak"),
    ("Komik & Grafis", "Komik, manga, dan novel grafis"),
    ("Self-Help", "Buku pengembangan diri dan motivasi"),
    ("Masakan", "Buku resep dan kuliner"),
    ("Perjalanan", "Buku tentang travel dan wisata"),
]

NAMA_PENERBIT = [
    "Gramedia Pustaka Utama", "Erlangga", "Mizan", "Kompas Gramedia", "Bentang Pustaka",
    "Republika Penerbit", "Grasindo", "Elex Media Komputindo", "Deepublish", "Gadjah Mada University Press",
    "Andi Publisher", "Prenada Media", "Raja Grafindo Persada", "Salemba Empat", "Kencana",
    "Bumi Aksara", "Rineka Cipta", "Sinar Grafika", "Alfabeta", "Rosda Karya",
    "Pustaka Jaya", "Balai Pustaka", "Obor Indonesia", "LP3ES", "Yayasan Pustaka Obor",
    "Penerbit ITB", "UI Press", "UGM Press", "Airlangga University Press", "Undip Press",
    "Pustaka Pelajar", "LKiS", "INSISTPress", "Marjin Kiri", "Banana Publisher",
    "Noura Books", "Falcon Publishing", "Qanita", "Tiga Serangkai", "Wacana Media"
]

JUDUL_PREFIX = [
    "Pengantar", "Dasar-Dasar", "Metode", "Teori", "Analisis", "Panduan", "Kumpulan",
    "Prinsip", "Konsep", "Studi", "Strategi", "Rahasia", "Filosofi", "Sejarah",
    "Perkembangan", "Evolusi", "Fondasi", "Eksplorasi", "Pemahaman", "Implementasi"
]

JUDUL_SUBJECT = [
    "Ilmu Komputer", "Basis Data", "Algoritma", "Pemrograman Python", "Machine Learning",
    "Kecerdasan Buatan", "Jaringan Komputer", "Sistem Operasi", "Matematika Diskrit",
    "Struktur Data", "Rekayasa Perangkat Lunak", "Keamanan Siber", "Cloud Computing",
    "Internet of Things", "Data Science", "Statistika", "Kalkulus", "Fisika Modern",
    "Kimia Organik", "Biologi Molekuler", "Ekonomi Makro", "Ekonomi Mikro",
    "Manajemen Strategis", "Akuntansi Dasar", "Pemasaran Digital", "Psikologi Sosial",
    "Psikologi Kognitif", "Filsafat Barat", "Filsafat Timur", "Sosiologi",
    "Antropologi Budaya", "Hukum Perdata", "Hukum Pidana", "Sastra Indonesia",
    "Bahasa Inggris", "Komunikasi Massa", "Jurnalistik", "Fotografi", "Desain Grafis",
    "Arsitektur Modern", "Teknik Sipil", "Teknik Mesin", "Teknik Elektro",
    "Lingkungan Hidup", "Geografi", "Astronomi", "Robotika", "Blockchain",
    "Deep Learning", "Web Development", "Mobile Development", "DevOps",
    "Sistem Informasi", "Manajemen Proyek", "Kepemimpinan", "Kewirausahaan",
    "Investasi Saham", "Perbankan Syariah", "Logika Matematika", "Aljabar Linear"
]

JUDUL_NOVEL = [
    "Laskar Pelangi", "Ayat-Ayat Cinta", "Negeri 5 Menara", "Sang Pemimpi",
    "Perahu Kertas", "Dilan 1990", "Pulang", "Bumi Manusia", "Tetralogi Buru",
    "Ronggeng Dukuh Paruk", "Saman", "Cantik Itu Luka", "Supernova", "Filosofi Kopi",
    "Hujan", "Laut Bercerita", "Aroma Karsa", "Entrok", "Gadis Kretek",
    "Si Anak Pintar", "Rumah Kaca", "Jejak Langkah", "Anak Semua Bangsa",
    "Tenggelamnya Kapal Van Der Wijck", "Di Bawah Lindungan Ka'bah",
    "Senja di Jakarta", "Atheis", "Belenggu", "Layar Terkembang", "Salah Asuhan"
]

NEGARA = [
    "Indonesia", "Malaysia", "Singapura", "Jepang", "Korea Selatan",
    "China", "India", "Amerika Serikat", "Inggris", "Jerman",
    "Prancis", "Australia", "Belanda", "Rusia", "Brasil"
]

STATUS_ANGGOTA = ["aktif", "tidak_aktif", "suspended"]
STATUS_PEMINJAMAN = ["dipinjam", "dikembalikan", "terlambat"]
JABATAN_PETUGAS = ["Kepala Perpustakaan", "Pustakawan", "Admin", "Staff Layanan", "Staff IT"]

# ─── UTILITY FUNCTIONS ───────────────────────────────

def random_nama():
    return f"{random.choice(NAMA_DEPAN)} {random.choice(NAMA_BELAKANG)}"

def random_alamat():
    return f"{random.choice(JALAN)} No. {random.randint(1, 200)}, {random.choice(KOTA)}"

def random_telepon():
    prefix = random.choice(["0812", "0813", "0821", "0822", "0851", "0852", "0857", "0858", "0878", "0877"])
    return prefix + ''.join(random.choices(string.digits, k=8))

def random_email(nama):
    nama_parts = nama.lower().replace(" ", ".")
    domain = random.choice(["gmail.com", "yahoo.com", "outlook.com", "mail.com", "student.ac.id"])
    return f"{nama_parts}{random.randint(1, 999)}@{domain}"

def random_isbn():
    return f"978-{random.randint(0,9)}-{random.randint(1000,9999)}-{random.randint(1000,9999)}-{random.randint(0,9)}"

def random_date(start_year, end_year):
    start = datetime(start_year, 1, 1)
    end = datetime(end_year, 12, 31)
    delta = end - start
    random_days = random.randint(0, delta.days)
    return (start + timedelta(days=random_days)).strftime("%Y-%m-%d")

def random_nim():
    tahun = random.randint(2018, 2026)
    jurusan = random.choice(["IF", "SI", "TI", "TK", "MI"])
    return f"{tahun}{jurusan}{random.randint(1000, 9999)}"

def judul_buku_random(kategori_id):
    if kategori_id == 1:  # Fiksi
        base = random.choice(JUDUL_NOVEL)
        suffix = random.choice(["", " Vol. 2", " - Edisi Baru", " (Revisi)", ""])
        return base + suffix
    else:
        return f"{random.choice(JUDUL_PREFIX)} {random.choice(JUDUL_SUBJECT)}"

# ─── MAIN GENERATOR ──────────────────────────────────

def main():
    start_time = time.time()
    
    # Hapus database lama jika ada
    if os.path.exists(DB_PATH):
        os.remove(DB_PATH)
    
    conn = sqlite3.connect(DB_PATH)
    cur = conn.cursor()
    cur.execute("PRAGMA journal_mode=WAL")
    cur.execute("PRAGMA synchronous=OFF")
    cur.execute("PRAGMA temp_store=MEMORY")
    
    print("=" * 60)
    print("  GENERATOR DATABASE PERPUSTAKAAN")
    print("  Target: 100K+ Records")
    print("=" * 60)
    
    # ─── CREATE TABLES ───────────────────────────────
    print("\n[1/10] Membuat skema tabel...")
    
    cur.executescript("""
    -- =============================================
    -- TABEL KATEGORI
    -- =============================================
    CREATE TABLE IF NOT EXISTS kategori (
        kategori_id     INTEGER PRIMARY KEY AUTOINCREMENT,
        nama_kategori   VARCHAR(100) NOT NULL,
        deskripsi       TEXT,
        created_at      DATETIME DEFAULT CURRENT_TIMESTAMP
    );

    -- =============================================
    -- TABEL PENERBIT
    -- =============================================
    CREATE TABLE IF NOT EXISTS penerbit (
        penerbit_id     INTEGER PRIMARY KEY AUTOINCREMENT,
        nama_penerbit   VARCHAR(200) NOT NULL,
        alamat          TEXT,
        telepon         VARCHAR(20),
        email           VARCHAR(100),
        created_at      DATETIME DEFAULT CURRENT_TIMESTAMP
    );

    -- =============================================
    -- TABEL PENGARANG
    -- =============================================
    CREATE TABLE IF NOT EXISTS pengarang (
        pengarang_id    INTEGER PRIMARY KEY AUTOINCREMENT,
        nama_pengarang  VARCHAR(200) NOT NULL,
        negara          VARCHAR(100),
        bio             TEXT,
        created_at      DATETIME DEFAULT CURRENT_TIMESTAMP
    );

    -- =============================================
    -- TABEL PETUGAS
    -- =============================================
    CREATE TABLE IF NOT EXISTS petugas (
        petugas_id      INTEGER PRIMARY KEY AUTOINCREMENT,
        nama_petugas    VARCHAR(200) NOT NULL,
        email           VARCHAR(100) UNIQUE,
        telepon         VARCHAR(20),
        jabatan         VARCHAR(50) NOT NULL,
        tanggal_masuk   DATE,
        status          VARCHAR(20) DEFAULT 'aktif',
        created_at      DATETIME DEFAULT CURRENT_TIMESTAMP
    );

    -- =============================================
    -- TABEL ANGGOTA
    -- =============================================
    CREATE TABLE IF NOT EXISTS anggota (
        anggota_id      INTEGER PRIMARY KEY AUTOINCREMENT,
        nama_anggota    VARCHAR(200) NOT NULL,
        nim             VARCHAR(20) UNIQUE,
        email           VARCHAR(100),
        telepon         VARCHAR(20),
        alamat          TEXT,
        jenis_kelamin   CHAR(1) CHECK(jenis_kelamin IN ('L', 'P')),
        tanggal_daftar  DATE NOT NULL,
        status          VARCHAR(20) DEFAULT 'aktif',
        created_at      DATETIME DEFAULT CURRENT_TIMESTAMP
    );

    -- =============================================
    -- TABEL BUKU
    -- =============================================
    CREATE TABLE IF NOT EXISTS buku (
        buku_id         INTEGER PRIMARY KEY AUTOINCREMENT,
        judul           VARCHAR(300) NOT NULL,
        isbn            VARCHAR(30) UNIQUE,
        kategori_id     INTEGER NOT NULL,
        penerbit_id     INTEGER NOT NULL,
        tahun_terbit    INTEGER,
        jumlah_halaman  INTEGER,
        stok            INTEGER DEFAULT 1,
        rak             VARCHAR(10),
        bahasa          VARCHAR(50) DEFAULT 'Indonesia',
        created_at      DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (kategori_id) REFERENCES kategori(kategori_id),
        FOREIGN KEY (penerbit_id) REFERENCES penerbit(penerbit_id)
    );

    -- =============================================
    -- TABEL BUKU_PENGARANG (Many-to-Many)
    -- =============================================
    CREATE TABLE IF NOT EXISTS buku_pengarang (
        buku_id         INTEGER NOT NULL,
        pengarang_id    INTEGER NOT NULL,
        PRIMARY KEY (buku_id, pengarang_id),
        FOREIGN KEY (buku_id) REFERENCES buku(buku_id),
        FOREIGN KEY (pengarang_id) REFERENCES pengarang(pengarang_id)
    );

    -- =============================================
    -- TABEL PEMINJAMAN
    -- =============================================
    CREATE TABLE IF NOT EXISTS peminjaman (
        peminjaman_id       INTEGER PRIMARY KEY AUTOINCREMENT,
        anggota_id          INTEGER NOT NULL,
        buku_id             INTEGER NOT NULL,
        petugas_id          INTEGER NOT NULL,
        tanggal_pinjam      DATE NOT NULL,
        tanggal_harus_kembali DATE NOT NULL,
        tanggal_dikembalikan DATE,
        status              VARCHAR(20) DEFAULT 'dipinjam',
        catatan             TEXT,
        created_at          DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (anggota_id) REFERENCES anggota(anggota_id),
        FOREIGN KEY (buku_id) REFERENCES buku(buku_id),
        FOREIGN KEY (petugas_id) REFERENCES petugas(petugas_id)
    );

    -- =============================================
    -- TABEL DENDA
    -- =============================================
    CREATE TABLE IF NOT EXISTS denda (
        denda_id            INTEGER PRIMARY KEY AUTOINCREMENT,
        peminjaman_id       INTEGER NOT NULL UNIQUE,
        jumlah_denda        DECIMAL(10, 2) NOT NULL,
        jumlah_hari_terlambat INTEGER NOT NULL,
        status_bayar        VARCHAR(20) DEFAULT 'belum_bayar',
        tanggal_bayar       DATE,
        created_at          DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (peminjaman_id) REFERENCES peminjaman(peminjaman_id)
    );
    """)
    
    conn.commit()
    print("   ✓ 9 tabel berhasil dibuat")

    # ─── INSERT KATEGORI ─────────────────────────────
    print(f"\n[2/10] Mengisi tabel kategori ({NUM_KATEGORI} rows)...")
    kategori_data = [(nama, desc) for nama, desc in NAMA_KATEGORI]
    cur.executemany("INSERT INTO kategori (nama_kategori, deskripsi) VALUES (?, ?)", kategori_data)
    conn.commit()
    print(f"   ✓ {NUM_KATEGORI} kategori ditambahkan")

    # ─── INSERT PENERBIT ─────────────────────────────
    print(f"\n[3/10] Mengisi tabel penerbit ({NUM_PENERBIT} rows)...")
    penerbit_data = []
    used_penerbit = set()
    for base_name in NAMA_PENERBIT:
        used_penerbit.add(base_name)
        penerbit_data.append((
            base_name, random_alamat(), random_telepon(),
            base_name.lower().replace(" ", ".").replace(".", "", 1)[:20] + "@publisher.co.id"
        ))
    # Tambah penerbit random sampai 200
    while len(penerbit_data) < NUM_PENERBIT:
        kota = random.choice(KOTA)
        name = f"Penerbit {random.choice(NAMA_DEPAN)} {kota}"
        if name not in used_penerbit:
            used_penerbit.add(name)
            penerbit_data.append((
                name, random_alamat(), random_telepon(),
                f"info{random.randint(1,999)}@penerbit.co.id"
            ))
    
    cur.executemany(
        "INSERT INTO penerbit (nama_penerbit, alamat, telepon, email) VALUES (?, ?, ?, ?)",
        penerbit_data
    )
    conn.commit()
    print(f"   ✓ {len(penerbit_data)} penerbit ditambahkan")

    # ─── INSERT PENGARANG ────────────────────────────
    print(f"\n[4/10] Mengisi tabel pengarang ({NUM_PENGARANG} rows)...")
    pengarang_data = []
    for i in range(NUM_PENGARANG):
        nama = random_nama()
        negara = random.choice(NEGARA)
        bio = f"Penulis produktif dari {negara} dengan berbagai karya di bidang literatur."
        pengarang_data.append((nama, negara, bio))
    
    cur.executemany(
        "INSERT INTO pengarang (nama_pengarang, negara, bio) VALUES (?, ?, ?)",
        pengarang_data
    )
    conn.commit()
    print(f"   ✓ {NUM_PENGARANG} pengarang ditambahkan")

    # ─── INSERT PETUGAS ──────────────────────────────
    print(f"\n[5/10] Mengisi tabel petugas ({NUM_PETUGAS} rows)...")
    petugas_data = []
    for i in range(NUM_PETUGAS):
        nama = random_nama()
        email = random_email(nama)
        telepon = random_telepon()
        jabatan = random.choice(JABATAN_PETUGAS)
        tanggal_masuk = random_date(2015, 2025)
        petugas_data.append((nama, email, telepon, jabatan, tanggal_masuk))
    
    cur.executemany(
        "INSERT INTO petugas (nama_petugas, email, telepon, jabatan, tanggal_masuk) VALUES (?, ?, ?, ?, ?)",
        petugas_data
    )
    conn.commit()
    print(f"   ✓ {NUM_PETUGAS} petugas ditambahkan")

    # ─── INSERT ANGGOTA ──────────────────────────────
    print(f"\n[6/10] Mengisi tabel anggota ({NUM_ANGGOTA} rows)...")
    anggota_data = []
    used_nim = set()
    for i in range(NUM_ANGGOTA):
        nama = random_nama()
        # Generate unique NIM
        nim = random_nim()
        while nim in used_nim:
            nim = random_nim()
        used_nim.add(nim)
        
        email = random_email(nama)
        telepon = random_telepon()
        alamat = random_alamat()
        jk = random.choice(["L", "P"])
        tanggal_daftar = random_date(2019, 2026)
        status = random.choices(STATUS_ANGGOTA, weights=[85, 10, 5])[0]
        anggota_data.append((nama, nim, email, telepon, alamat, jk, tanggal_daftar, status))
    
    cur.executemany(
        """INSERT INTO anggota 
        (nama_anggota, nim, email, telepon, alamat, jenis_kelamin, tanggal_daftar, status) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)""",
        anggota_data
    )
    conn.commit()
    print(f"   ✓ {NUM_ANGGOTA} anggota ditambahkan")

    # ─── INSERT BUKU ─────────────────────────────────
    print(f"\n[7/10] Mengisi tabel buku ({NUM_BUKU} rows)...")
    buku_data = []
    used_isbn = set()
    bahasa_list = ["Indonesia", "Inggris", "Jepang", "Mandarin", "Arab"]
    rak_list = [f"{chr(65+i)}{j}" for i in range(10) for j in range(1, 21)]  # A1-J20
    
    for i in range(NUM_BUKU):
        kategori_id = random.randint(1, NUM_KATEGORI)
        judul = judul_buku_random(kategori_id)
        
        isbn = random_isbn()
        while isbn in used_isbn:
            isbn = random_isbn()
        used_isbn.add(isbn)
        
        penerbit_id = random.randint(1, len(penerbit_data))
        tahun_terbit = random.randint(1990, 2026)
        jumlah_halaman = random.randint(50, 1200)
        stok = random.randint(0, 20)
        rak = random.choice(rak_list)
        bahasa = random.choices(bahasa_list, weights=[70, 20, 4, 4, 2])[0]
        buku_data.append((judul, isbn, kategori_id, penerbit_id, tahun_terbit, jumlah_halaman, stok, rak, bahasa))
    
    cur.executemany(
        """INSERT INTO buku 
        (judul, isbn, kategori_id, penerbit_id, tahun_terbit, jumlah_halaman, stok, rak, bahasa)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)""",
        buku_data
    )
    conn.commit()
    print(f"   ✓ {NUM_BUKU} buku ditambahkan")

    # ─── INSERT BUKU_PENGARANG ───────────────────────
    print(f"\n[8/10] Mengisi tabel buku_pengarang...")
    buku_pengarang_set = set()
    for buku_id in range(1, NUM_BUKU + 1):
        # Setiap buku punya 1-3 pengarang
        num_authors = random.choices([1, 2, 3], weights=[60, 30, 10])[0]
        authors = random.sample(range(1, NUM_PENGARANG + 1), num_authors)
        for pengarang_id in authors:
            buku_pengarang_set.add((buku_id, pengarang_id))
    
    buku_pengarang_data = list(buku_pengarang_set)
    cur.executemany(
        "INSERT INTO buku_pengarang (buku_id, pengarang_id) VALUES (?, ?)",
        buku_pengarang_data
    )
    conn.commit()
    print(f"   ✓ {len(buku_pengarang_data)} relasi buku-pengarang ditambahkan")

    # ─── INSERT PEMINJAMAN ───────────────────────────
    print(f"\n[9/10] Mengisi tabel peminjaman ({NUM_PEMINJAMAN} rows)...")
    peminjaman_data = []
    denda_data = []
    denda_id_counter = 0
    
    BATCH_SIZE = 10000
    catatan_options = [
        None, None, None, None, None,  # mostly NULL
        "Buku dalam kondisi baik",
        "Perpanjangan 1 minggu",
        "Buku dikembalikan dengan sedikit kerusakan",
        "Anggota meminta perpanjangan",
        "Dikembalikan via drop box"
    ]
    
    for batch_start in range(0, NUM_PEMINJAMAN, BATCH_SIZE):
        batch_end = min(batch_start + BATCH_SIZE, NUM_PEMINJAMAN)
        batch_peminjaman = []
        batch_denda = []
        
        for i in range(batch_start, batch_end):
            anggota_id = random.randint(1, NUM_ANGGOTA)
            buku_id = random.randint(1, NUM_BUKU)
            petugas_id = random.randint(1, NUM_PETUGAS)
            
            # Tanggal pinjam antara 2020-2026
            tgl_pinjam_dt = datetime(2020, 1, 1) + timedelta(days=random.randint(0, 2490))
            tanggal_pinjam = tgl_pinjam_dt.strftime("%Y-%m-%d")
            
            # Durasi pinjam 7-14 hari
            durasi_pinjam = random.choice([7, 14])
            tgl_harus_kembali_dt = tgl_pinjam_dt + timedelta(days=durasi_pinjam)
            tanggal_harus_kembali = tgl_harus_kembali_dt.strftime("%Y-%m-%d")
            
            # Tentukan status
            roll = random.random()
            if roll < 0.10:  # 10% masih dipinjam
                tanggal_dikembalikan = None
                status = "dipinjam"
            elif roll < 0.30:  # 20% terlambat
                hari_terlambat = random.randint(1, 60)
                tgl_kembali_dt = tgl_harus_kembali_dt + timedelta(days=hari_terlambat)
                tanggal_dikembalikan = tgl_kembali_dt.strftime("%Y-%m-%d")
                status = "terlambat"
                
                # Hitung denda (Rp 1.000 - Rp 5.000 per hari)
                denda_per_hari = random.choice([1000, 2000, 3000, 5000])
                jumlah_denda = hari_terlambat * denda_per_hari
                status_bayar = random.choices(
                    ["lunas", "belum_bayar"],
                    weights=[60, 40]
                )[0]
                tanggal_bayar = None
                if status_bayar == "lunas":
                    tanggal_bayar = (tgl_kembali_dt + timedelta(days=random.randint(0, 14))).strftime("%Y-%m-%d")
                
                denda_id_counter += 1
                batch_denda.append((
                    batch_start + len(batch_peminjaman) + 1,  # peminjaman_id
                    jumlah_denda,
                    hari_terlambat,
                    status_bayar,
                    tanggal_bayar
                ))
            else:  # 70% dikembalikan tepat waktu
                hari_kembali = random.randint(1, durasi_pinjam)
                tgl_kembali_dt = tgl_pinjam_dt + timedelta(days=hari_kembali)
                tanggal_dikembalikan = tgl_kembali_dt.strftime("%Y-%m-%d")
                status = "dikembalikan"
            
            catatan = random.choice(catatan_options)
            batch_peminjaman.append((
                anggota_id, buku_id, petugas_id,
                tanggal_pinjam, tanggal_harus_kembali, tanggal_dikembalikan,
                status, catatan
            ))
        
        cur.executemany(
            """INSERT INTO peminjaman 
            (anggota_id, buku_id, petugas_id, tanggal_pinjam, tanggal_harus_kembali, 
             tanggal_dikembalikan, status, catatan) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)""",
            batch_peminjaman
        )
        
        if batch_denda:
            cur.executemany(
                """INSERT INTO denda 
                (peminjaman_id, jumlah_denda, jumlah_hari_terlambat, status_bayar, tanggal_bayar)
                VALUES (?, ?, ?, ?, ?)""",
                batch_denda
            )
            denda_data.extend(batch_denda)
        
        conn.commit()
        pct = int((batch_end / NUM_PEMINJAMAN) * 100)
        print(f"   ... {batch_end:,}/{NUM_PEMINJAMAN:,} ({pct}%)")
    
    print(f"   ✓ {NUM_PEMINJAMAN:,} peminjaman ditambahkan")
    print(f"   ✓ {len(denda_data):,} denda ditambahkan")

    # ─── CREATE INDEXES ──────────────────────────────
    print(f"\n[10/10] Membuat index untuk optimasi query...")
    cur.executescript("""
    CREATE INDEX IF NOT EXISTS idx_buku_kategori ON buku(kategori_id);
    CREATE INDEX IF NOT EXISTS idx_buku_penerbit ON buku(penerbit_id);
    CREATE INDEX IF NOT EXISTS idx_buku_tahun ON buku(tahun_terbit);
    CREATE INDEX IF NOT EXISTS idx_buku_rak ON buku(rak);
    CREATE INDEX IF NOT EXISTS idx_peminjaman_anggota ON peminjaman(anggota_id);
    CREATE INDEX IF NOT EXISTS idx_peminjaman_buku ON peminjaman(buku_id);
    CREATE INDEX IF NOT EXISTS idx_peminjaman_petugas ON peminjaman(petugas_id);
    CREATE INDEX IF NOT EXISTS idx_peminjaman_status ON peminjaman(status);
    CREATE INDEX IF NOT EXISTS idx_peminjaman_tgl_pinjam ON peminjaman(tanggal_pinjam);
    CREATE INDEX IF NOT EXISTS idx_peminjaman_tgl_kembali ON peminjaman(tanggal_harus_kembali);
    CREATE INDEX IF NOT EXISTS idx_anggota_status ON anggota(status);
    CREATE INDEX IF NOT EXISTS idx_anggota_nim ON anggota(nim);
    CREATE INDEX IF NOT EXISTS idx_denda_status ON denda(status_bayar);
    CREATE INDEX IF NOT EXISTS idx_pengarang_negara ON pengarang(negara);
    """)
    conn.commit()
    print("   ✓ 14 index berhasil dibuat")

    # ─── CREATE VIEWS (untuk analisis) ───────────────
    print("\n   Membuat VIEW untuk analisis data...")
    cur.executescript("""
    -- View: Statistik peminjaman per kategori
    CREATE VIEW IF NOT EXISTS v_peminjaman_per_kategori AS
    SELECT 
        k.nama_kategori,
        COUNT(p.peminjaman_id) AS total_peminjaman,
        COUNT(CASE WHEN p.status = 'dipinjam' THEN 1 END) AS sedang_dipinjam,
        COUNT(CASE WHEN p.status = 'dikembalikan' THEN 1 END) AS sudah_dikembalikan,
        COUNT(CASE WHEN p.status = 'terlambat' THEN 1 END) AS terlambat
    FROM peminjaman p
    JOIN buku b ON p.buku_id = b.buku_id
    JOIN kategori k ON b.kategori_id = k.kategori_id
    GROUP BY k.nama_kategori
    ORDER BY total_peminjaman DESC;

    -- View: Top 20 buku paling sering dipinjam
    CREATE VIEW IF NOT EXISTS v_buku_terpopuler AS
    SELECT 
        b.buku_id,
        b.judul,
        k.nama_kategori,
        p2.nama_penerbit,
        COUNT(pm.peminjaman_id) AS jumlah_dipinjam
    FROM buku b
    JOIN kategori k ON b.kategori_id = k.kategori_id
    JOIN penerbit p2 ON b.penerbit_id = p2.penerbit_id
    JOIN peminjaman pm ON b.buku_id = pm.buku_id
    GROUP BY b.buku_id
    ORDER BY jumlah_dipinjam DESC
    LIMIT 20;

    -- View: Anggota dengan peminjaman terbanyak
    CREATE VIEW IF NOT EXISTS v_anggota_aktif AS
    SELECT 
        a.anggota_id,
        a.nama_anggota,
        a.nim,
        COUNT(p.peminjaman_id) AS total_peminjaman,
        SUM(CASE WHEN p.status = 'terlambat' THEN 1 ELSE 0 END) AS total_terlambat,
        COALESCE(SUM(d.jumlah_denda), 0) AS total_denda
    FROM anggota a
    JOIN peminjaman p ON a.anggota_id = p.anggota_id
    LEFT JOIN denda d ON p.peminjaman_id = d.peminjaman_id
    GROUP BY a.anggota_id
    ORDER BY total_peminjaman DESC
    LIMIT 50;

    -- View: Tren peminjaman per bulan
    CREATE VIEW IF NOT EXISTS v_tren_peminjaman_bulanan AS
    SELECT 
        strftime('%Y-%m', tanggal_pinjam) AS bulan,
        COUNT(*) AS total_peminjaman,
        COUNT(CASE WHEN status = 'terlambat' THEN 1 END) AS total_terlambat,
        COUNT(CASE WHEN status = 'dikembalikan' THEN 1 END) AS tepat_waktu
    FROM peminjaman
    GROUP BY strftime('%Y-%m', tanggal_pinjam)
    ORDER BY bulan;

    -- View: Ringkasan denda
    CREATE VIEW IF NOT EXISTS v_ringkasan_denda AS
    SELECT 
        strftime('%Y', p.tanggal_pinjam) AS tahun,
        COUNT(d.denda_id) AS jumlah_kasus,
        SUM(d.jumlah_denda) AS total_denda,
        AVG(d.jumlah_denda) AS rata_rata_denda,
        MAX(d.jumlah_denda) AS denda_tertinggi,
        SUM(CASE WHEN d.status_bayar = 'lunas' THEN d.jumlah_denda ELSE 0 END) AS sudah_dibayar,
        SUM(CASE WHEN d.status_bayar = 'belum_bayar' THEN d.jumlah_denda ELSE 0 END) AS belum_dibayar
    FROM denda d
    JOIN peminjaman p ON d.peminjaman_id = p.peminjaman_id
    GROUP BY tahun
    ORDER BY tahun;

    -- View: Statistik per penerbit
    CREATE VIEW IF NOT EXISTS v_statistik_penerbit AS
    SELECT 
        p.nama_penerbit,
        COUNT(DISTINCT b.buku_id) AS jumlah_buku,
        SUM(b.stok) AS total_stok,
        COUNT(pm.peminjaman_id) AS total_peminjaman
    FROM penerbit p
    LEFT JOIN buku b ON p.penerbit_id = b.penerbit_id
    LEFT JOIN peminjaman pm ON b.buku_id = pm.buku_id
    GROUP BY p.penerbit_id
    ORDER BY total_peminjaman DESC;

    -- View: Distribusi stok per kategori dan rak
    CREATE VIEW IF NOT EXISTS v_distribusi_stok AS
    SELECT 
        k.nama_kategori,
        b.rak,
        COUNT(b.buku_id) AS jumlah_judul,
        SUM(b.stok) AS total_stok,
        AVG(b.jumlah_halaman) AS rata_rata_halaman
    FROM buku b
    JOIN kategori k ON b.kategori_id = k.kategori_id
    GROUP BY k.nama_kategori, b.rak
    ORDER BY k.nama_kategori, b.rak;
    """)
    conn.commit()
    print("   ✓ 7 VIEW analisis berhasil dibuat")

    # ─── SUMMARY ─────────────────────────────────────
    print("\n" + "=" * 60)
    print("  RINGKASAN DATABASE")
    print("=" * 60)
    
    tables = [
        "kategori", "penerbit", "pengarang", "petugas", "anggota",
        "buku", "buku_pengarang", "peminjaman", "denda"
    ]
    total = 0
    for table in tables:
        cur.execute(f"SELECT COUNT(*) FROM {table}")
        count = cur.fetchone()[0]
        total += count
        print(f"  {table:<20s} : {count:>10,} rows")
    
    print(f"  {'─' * 34}")
    print(f"  {'TOTAL':<20s} : {total:>10,} rows")
    
    elapsed = time.time() - start_time
    print(f"\n  Waktu eksekusi   : {elapsed:.2f} detik")
    
    # File size
    conn.close()
    db_size = os.path.getsize(DB_PATH)
    print(f"  Ukuran database  : {db_size / (1024*1024):.2f} MB")
    print(f"  Lokasi file      : {DB_PATH}")
    print(f"\n  ✅ Database siap dibuka di Database Viewer!")
    print("=" * 60)


if __name__ == "__main__":
    main()
