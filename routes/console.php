<?php

use App\Models\Order;
use App\Models\Pelanggan;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('pelanggan:order-3-hari', function () {
    $data = Pelanggan::mengorderTigaHariTerakhir();

    if ($data->isEmpty()) {
        $this->warn('Tidak ada pelanggan yang melakukan order dalam 3 hari terakhir.');

        return;
    }

    $this->info('Daftar Pelanggan yang Mengorder dalam 3 Hari Terakhir:');
    $this->table(
        ['ID Pelanggan', 'Nama Pelanggan', 'No. Telepon', 'Email', 'ID Order', 'Produk', 'Jumlah', 'Total Harga', 'Tanggal Order', 'Status'],
        $data->map(fn ($row) => [
            $row->id_pelanggan,
            $row->nama_pelanggan,
            $row->no_telepon,
            $row->email,
            $row->id_order,
            $row->nama_produk,
            $row->jumlah,
            'Rp '.number_format((float) $row->total_harga, 0, ',', '.'),
            $row->tanggal_order,
            $row->status_order,
        ])->toArray()
    );
})->purpose('Perlihatkan pelanggan yang mengorder 3 hari terakhir menggunakan SELECT, FROM, JOIN, ON');

Artisan::command('order:pelanggan {id_pelanggan=PLG0001}', function (string $id_pelanggan) {
    $data = Order::dariPelanggan($id_pelanggan);

    if ($data->isEmpty()) {
        $this->warn("Tidak ada data order untuk pelanggan dengan ID: {$id_pelanggan}");

        return;
    }

    $this->info("Daftar Order untuk Pelanggan ({$id_pelanggan}):");
    $this->table(
        ['ID Order', 'ID Pelanggan', 'ID Buku', 'Nama Produk', 'Jumlah', 'Total Harga', 'Tanggal Order', 'Status'],
        $data->map(fn ($row) => [
            $row->id_order,
            $row->id_pelanggan,
            $row->id_buku,
            $row->nama_produk,
            $row->jumlah,
            'Rp '.number_format((float) $row->total_harga, 0, ',', '.'),
            $row->tanggal_order,
            $row->status_order,
        ])->toArray()
    );
})->purpose('Menampilkan daftar order berdasarkan id_pelanggan (SELECT ... FROM orders WHERE id_pelanggan = ?)');

Artisan::command('order:show-index', function () {
    $driver = DB::getDriverName();
    $this->info("Daftar Index pada Tabel 'orders' (Driver: {$driver}):");

    if ($driver === 'sqlite') {
        $indexes = DB::select("PRAGMA index_list('orders')");
        $rows = [];

        foreach ($indexes as $idx) {
            $columns = DB::select("PRAGMA index_info('{$idx->name}')");
            foreach ($columns as $col) {
                $rows[] = [
                    'Table' => 'orders',
                    'Non_unique' => $idx->unique ? 0 : 1,
                    'Key_name' => $idx->name,
                    'Seq_in_index' => $col->seqno + 1,
                    'Column_name' => $col->name,
                    'Origin' => $idx->origin === 'pk' ? 'PRIMARY KEY' : 'INDEX',
                    'Index_type' => 'BTREE',
                ];
            }
        }

        $this->table(
            ['Table', 'Non_unique', 'Key_name', 'Seq_in_index', 'Column_name', 'Origin', 'Index_type'],
            $rows
        );
    } else {
        $indexes = array_map(fn ($row) => (array) $row, DB::select('SHOW INDEX FROM orders'));
        if (! empty($indexes)) {
            $this->table(array_keys($indexes[0]), $indexes);
        }
    }
})->purpose('Menampilkan daftar index pada tabel orders (SHOW INDEX FROM orders)');

Artisan::command('pelanggan:explain-order-3-hari', function () {
    $this->info('======================================================================');
    $this->info('  EXPLAIN ANALYZE: PELANGGAN YANG MENGORDER 3 HARI TERAKHIR');
    $this->info('======================================================================');

    $rawSql = Pelanggan::queryOrderTigaHariTerakhir()->toRawSql();
    $this->line("\nSQL Query:");
    $this->comment($rawSql);

    // 1. Simulasi BEFORE Index (Tahap 1: Drop Foreign Key -> Tahap 2: Drop Index)
    $this->line("\n>> Tahap Persiapan BEFORE: Menghapus Foreign Key lalu Menghapus Index...");
    try {
        Schema::table('orders', function ($table) {
            $table->dropForeign(['id_pelanggan']);
        });
        $this->line('   [OK] Foreign Key orders_id_pelanggan_foreign dihapus.');
    } catch (\Throwable) {
        // Abaikan jika pada SQLite atau foreign key sudah dihapus
    }

    try {
        Schema::table('orders', function ($table) {
            $table->dropIndex(['tanggal_order']);
            $table->dropIndex(['id_pelanggan', 'tanggal_order']);
        });
        $this->line('   [OK] Index orders_tanggal_order_index & orders_id_pelanggan_tanggal_order_index dihapus.');
    } catch (\Throwable) {
        // Abaikan jika index belum ada
    }

    $before = Pelanggan::explainAnalyzeOrderTigaHariTerakhir();

    $this->line("\n[1] BEFORE INDEX (Tanpa Foreign Key & Tanpa Index):");
    $this->line("Waktu Eksekusi: {$before['execution_time_ms']} ms | Baris Hasil: {$before['rows_returned']}");
    $beforeRows = array_map(fn ($row) => (array) $row, $before['explain_analyze']);
    if (! empty($beforeRows)) {
        $this->table(array_keys($beforeRows[0]), $beforeRows);
    }

    // 2. Pasang kembali Index lalu Foreign Key (AFTER Index)
    $this->line("\n>> Tahap Persiapan AFTER: Membuat kembali Index lalu memasang Foreign Key...");
    Schema::table('orders', function ($table) {
        $table->index('tanggal_order');
        $table->index(['id_pelanggan', 'tanggal_order']);
    });
    try {
        Schema::table('orders', function ($table) {
            $table->foreign('id_pelanggan')
                ->references('id_pelanggan')
                ->on('pelanggan')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();
        });
    } catch (\Throwable) {
        // Abaikan pada SQLite
    }

    $after = Pelanggan::explainAnalyzeOrderTigaHariTerakhir();

    $this->line("\n[2] AFTER INDEX (Dengan Index & Foreign Key kembali aktif):");
    $this->line("Waktu Eksekusi: {$after['execution_time_ms']} ms | Baris Hasil: {$after['rows_returned']}");
    $afterRows = array_map(fn ($row) => (array) $row, $after['explain_analyze']);
    if (! empty($afterRows)) {
        $this->table(array_keys($afterRows[0]), $afterRows);
    }
})->purpose('Jalankan EXPLAIN ANALYZE (Before & After Index) untuk query pelanggan order 3 hari terakhir');

Artisan::command('order:explain-pelanggan {id_pelanggan=PLG0001}', function (string $id_pelanggan) {
    $this->info('======================================================================');
    $this->info("  EXPLAIN ANALYZE: ORDER DARI PELANGGAN ({$id_pelanggan})");
    $this->info('======================================================================');

    $rawSql = Order::queryDariPelanggan($id_pelanggan)->toRawSql();
    $this->line("\nSQL Query:");
    $this->comment($rawSql);

    // 1. Simulasi BEFORE Index (Tahap 1: Drop Foreign Key -> Tahap 2: Drop Index)
    $this->line("\n>> Tahap Persiapan BEFORE: Menghapus Foreign Key lalu Menghapus Index...");
    try {
        Schema::table('orders', function ($table) {
            $table->dropForeign(['id_pelanggan']);
        });
        $this->line('   [OK] Foreign Key orders_id_pelanggan_foreign dihapus.');
    } catch (\Throwable) {
        // Abaikan jika pada SQLite atau foreign key sudah dihapus
    }

    try {
        Schema::table('orders', function ($table) {
            $table->dropIndex(['tanggal_order']);
            $table->dropIndex(['id_pelanggan', 'tanggal_order']);
        });
        $this->line('   [OK] Index orders_tanggal_order_index & orders_id_pelanggan_tanggal_order_index dihapus.');
    } catch (\Throwable) {
        // Abaikan jika index belum ada
    }

    $before = Order::explainAnalyzeDariPelanggan($id_pelanggan);

    $this->line("\n[1] BEFORE INDEX (Tanpa Foreign Key & Tanpa Index pada id_pelanggan):");
    $this->line("Waktu Eksekusi: {$before['execution_time_ms']} ms | Baris Hasil: {$before['rows_returned']}");
    $beforeRows = array_map(fn ($row) => (array) $row, $before['explain_analyze']);
    if (! empty($beforeRows)) {
        $this->table(array_keys($beforeRows[0]), $beforeRows);
    }

    // 2. Pasang kembali Index lalu Foreign Key (AFTER Index)
    $this->line("\n>> Tahap Persiapan AFTER: Membuat kembali Index lalu memasang Foreign Key...");
    Schema::table('orders', function ($table) {
        $table->index('tanggal_order');
        $table->index(['id_pelanggan', 'tanggal_order']);
    });
    try {
        Schema::table('orders', function ($table) {
            $table->foreign('id_pelanggan')
                ->references('id_pelanggan')
                ->on('pelanggan')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();
        });
    } catch (\Throwable) {
        // Abaikan pada SQLite
    }

    $after = Order::explainAnalyzeDariPelanggan($id_pelanggan);

    $this->line("\n[2] AFTER INDEX (Dengan Index [id_pelanggan, tanggal_order] & Foreign Key):");
    $this->line("Waktu Eksekusi: {$after['execution_time_ms']} ms | Baris Hasil: {$after['rows_returned']}");
    $afterRows = array_map(fn ($row) => (array) $row, $after['explain_analyze']);
    if (! empty($afterRows)) {
        $this->table(array_keys($afterRows[0]), $afterRows);
    }
})->purpose('Jalankan EXPLAIN ANALYZE (Before & After Index) untuk query order berdasarkan id_pelanggan');

Artisan::command('order:drop-index', function () {
    $this->info('======================================================================');
    $this->info('  TAHAPAN DROP FOREIGN KEY LALU DROP INDEX PADA TABEL ORDERS');
    $this->info('======================================================================');

    // Tahap 1: Drop Foreign Key terlebih dahulu
    $this->line("\n[Tahap 1] Membuang Foreign Key (orders_id_pelanggan_foreign)...");
    $this->comment('SQL: ALTER TABLE orders DROP FOREIGN KEY orders_id_pelanggan_foreign;');
    try {
        Schema::table('orders', function ($table) {
            $table->dropForeign(['id_pelanggan']);
        });
        $this->info('-> Berhasil membuang Foreign Key orders_id_pelanggan_foreign.');
    } catch (\Throwable $e) {
        $this->line('-> Catatan: Foreign key sudah terlepas atau menggunakan SQLite.');
    }

    // Tahap 2: Drop Index setelah Foreign Key dilepas
    $this->line("\n[Tahap 2] Membuang Index pada tabel orders...");
    $this->comment('SQL: DROP INDEX orders_id_pelanggan_tanggal_order_index ON orders;');
    $this->comment('SQL: DROP INDEX orders_tanggal_order_index ON orders;');
    try {
        Schema::table('orders', function ($table) {
            $table->dropIndex('orders_id_pelanggan_tanggal_order_index');
            $table->dropIndex('orders_tanggal_order_index');
        });
        $this->info('-> Berhasil membuang Index pada tabel orders.');
    } catch (\Throwable $e) {
        $this->warn('-> Index sudah terhapus sebelumnya: '.$e->getMessage());
    }

    // Tahap 3: Verifikasi dengan Show Index
    $this->line("\n[Tahap 3] Verifikasi daftar index tersisa pada tabel orders:");
    $this->call('order:show-index');
})->purpose('Membuang Foreign Key terlebih dahulu lalu membuang Index pada tabel orders');

Artisan::command('order:add-index', function () {
    $this->info('======================================================================');
    $this->info('  TAHAPAN MEMBUAT KEMBALI INDEX DAN FOREIGN KEY PADA TABEL ORDERS');
    $this->info('======================================================================');

    $this->line("\n[Tahap 1] Membuat Index pada tabel orders...");
    $this->comment('SQL: CREATE INDEX orders_tanggal_order_index ON orders(tanggal_order);');
    $this->comment('SQL: CREATE INDEX orders_id_pelanggan_tanggal_order_index ON orders(id_pelanggan, tanggal_order);');
    try {
        Schema::table('orders', function ($table) {
            $table->index('tanggal_order');
            $table->index(['id_pelanggan', 'tanggal_order']);
        });
        $this->info('-> Berhasil membuat Index.');
    } catch (\Throwable $e) {
        $this->warn('-> Index sudah ada: '.$e->getMessage());
    }

    $this->line("\n[Tahap 2] Memasang kembali Foreign Key (orders_id_pelanggan_foreign)...");
    $this->comment('SQL: ALTER TABLE orders ADD CONSTRAINT orders_id_pelanggan_foreign FOREIGN KEY (id_pelanggan) REFERENCES pelanggan(id_pelanggan) ON DELETE CASCADE ON UPDATE CASCADE;');
    try {
        Schema::table('orders', function ($table) {
            $table->foreign('id_pelanggan')
                ->references('id_pelanggan')
                ->on('pelanggan')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();
        });
        $this->info('-> Berhasil memasang kembali Foreign Key.');
    } catch (\Throwable) {
        $this->line('-> Catatan: Foreign key sudah aktif.');
    }

    $this->line("\n[Tahap 3] Verifikasi daftar index pada tabel orders:");
    $this->call('order:show-index');
})->purpose('Membuat kembali Index dan Foreign Key pada tabel orders');

Artisan::command('buku:simulasi-2pl {--interaktif : Tekan Enter untuk lanjut di setiap step}', function () {
    $interaktif = (bool) $this->option('interaktif');
    $pause = function (string $nextStep) use ($interaktif) {
        if ($interaktif) {
            $this->ask("Tekan [ENTER] untuk lanjut ke {$nextStep}");
        }
    };

    $this->info('================================================================================');
    $this->info('  SIMULASI LOCKING & STRICT TWO-PHASE LOCKING (2PL) - PERPUSTAKAAN PENTAGON');
    $this->info('  Studi Kasus: Buku B0005 (Laskar Pelangi) | Stok Awal = 5 Eksemplar');
    $this->info('  Terminal 1 (Rizky): Pinjam 4 Buku | Terminal 2 (Siti): Pinjam 3 Buku');
    $this->info('================================================================================');

    // Pastikan buku B0005 tersedia dengan stok = 5
    DB::table('buku')->updateOrInsert(
        ['id_buku' => 'B0005'],
        [
            'judul_buku' => 'Laskar Pelangi',
            'pengarang' => 'Andrea Hirata',
            'penerbit' => 'Bentang Pustaka',
            'tahun_terbit' => 2018,
            'kategori' => 'Sastra / Novel',
            'stok' => 5,
        ]
    );

    // -------------------------------------------------------------------------
    // SKENARIO A (BEFORE): TANPA LOCKING (ANOMALI LOST UPDATE)
    // -------------------------------------------------------------------------
    $this->line('');
    $this->warn('┌──────────────────────────────────────────────────────────────────────────────┐');
    $this->warn('│ 3. SKENARIO A (BEFORE): TANPA LOCKING (ANOMALI LOST UPDATE)                  │');
    $this->warn('└──────────────────────────────────────────────────────────────────────────────┘');

    $pause('Step 1 Skenario A');

    // Step 1
    $this->info("\n[Step 1] Kedua transaksi dimulai secara paralel");
    $this->table(
        ['Step', 'Terminal 1 (Rizky: -4 Buku)', 'Terminal 2 (Siti: -3 Buku)', 'Perilaku Internal DBMS'],
        [['1', 'BEGIN;', 'BEGIN;', 'Kedua transaksi dimulai secara paralel.']]
    );

    $pause('Step 2 Skenario A');

    // Step 2: Terminal 1 membaca stok
    $stokT1_A = (int) DB::table('buku')->where('id_buku', 'B0005')->value('stok');
    $this->info("\n[Step 2] Terminal 1 (Rizky) membaca stok buku B0005 tanpa lock");
    $this->table(
        ['Step', 'Terminal 1 (Rizky: -4 Buku)', 'Terminal 2 (Siti: -3 Buku)', 'Perilaku Internal DBMS'],
        [['2', "SELECT stok FROM buku WHERE id_buku = 'B0005';\n(Output: {$stokT1_A})", '(Menunggu giliran)', "Rizky membaca stok = {$stokT1_A}. Tidak ada kunci (lock) yang dipasang."]]
    );

    $pause('Step 3 Skenario A');

    // Step 3: Terminal 2 membaca stok yang sama
    $stokT2_A = (int) DB::table('buku')->where('id_buku', 'B0005')->value('stok');
    $this->info("\n[Step 3] Terminal 2 (Siti) membaca stok buku B0005 sebelum Rizky commit");
    $this->table(
        ['Step', 'Terminal 1 (Rizky: -4 Buku)', 'Terminal 2 (Siti: -3 Buku)', 'Perilaku Internal DBMS'],
        [['3', '(Menunggu giliran)', "SELECT stok FROM buku WHERE id_buku = 'B0005';\n(Output: {$stokT2_A})", "Siti bebas membaca nilai yang sama ({$stokT2_A}) karena baris B0005 tidak terkunci."]]
    );

    $pause('Step 4 Skenario A');

    // Step 4: Terminal 1 update stok = 5 - 4 = 1 & COMMIT
    $stokBaruT1_A = $stokT1_A - 4;
    DB::table('buku')->where('id_buku', 'B0005')->update(['stok' => $stokBaruT1_A]);
    $this->info("\n[Step 4] Terminal 1 (Rizky) mengupdate stok menjadi {$stokBaruT1_A} dan COMMIT");
    $this->table(
        ['Step', 'Terminal 1 (Rizky: -4 Buku)', 'Terminal 2 (Siti: -3 Buku)', 'Perilaku Internal DBMS'],
        [['4', "UPDATE buku SET stok = {$stokT1_A} - 4 WHERE id_buku = 'B0005';\nCOMMIT;", '(Menunggu giliran)', "Rizky menyimpan perubahan stok menjadi {$stokBaruT1_A} eksemplar dan menyelesaikan transaksi."]]
    );

    $pause('Step 5 Skenario A');

    // Step 5: Terminal 2 update stok berdasarkan pembacaan lama = 5 - 3 = 2 & COMMIT
    $stokBaruT2_A = $stokT2_A - 3;
    DB::table('buku')->where('id_buku', 'B0005')->update(['stok' => $stokBaruT2_A]);
    $stokAkhirA = (int) DB::table('buku')->where('id_buku', 'B0005')->value('stok');
    $this->info("\n[Step 5] Terminal 2 (Siti) mengupdate stok dari pembacaan lama ({$stokT2_A} - 3 = {$stokBaruT2_A}) dan COMMIT");
    $this->table(
        ['Step', 'Terminal 1 (Rizky: -4 Buku)', 'Terminal 2 (Siti: -3 Buku)', 'Perilaku Internal DBMS'],
        [['5', '(Transaksi Selesai)', "UPDATE buku SET stok = {$stokT2_A} - 3 WHERE id_buku = 'B0005';\nCOMMIT;", "Siti menghitung dari pembacaan lama ({$stokT2_A} - 3 = {$stokBaruT2_A}) dan MENIMPA hasil Rizky!"]]
    );

    $this->error("HASIL AKHIR SKENARIO A (GAGAL / INKONSISTEN - LOST UPDATE):");
    $this->line("-> SELECT id_buku, judul_buku, stok FROM buku WHERE id_buku = 'B0005'; => stok = {$stokAkhirA}");
    $this->line("-> Buku yang keluar mencapai 7 eksemplar (4 + 3), namun stok tercatat masih tersisa {$stokAkhirA} eksemplar!");
    $this->line('-> Pembaruan transaksi Rizky hilang sepenuhnya (Lost Update).');

    $pause('Skenario B (Strict 2PL)');

    // -------------------------------------------------------------------------
    // SKENARIO B (AFTER): EXCLUSIVE LOCK (X) & STRICT 2PL
    // -------------------------------------------------------------------------
    $this->line('');
    $this->info('┌──────────────────────────────────────────────────────────────────────────────┐');
    $this->info('│ 4. SKENARIO B (AFTER): EXCLUSIVE LOCK (X) & STRICT 2PL (FOR UPDATE)          │');
    $this->info('└──────────────────────────────────────────────────────────────────────────────┘');

    // Reset stok kembali ke 5
    DB::table('buku')->where('id_buku', 'B0005')->update(['stok' => 5]);
    $this->comment("Reset Stok Awal: UPDATE buku SET stok = 5 WHERE id_buku = 'B0005'; (Stok = 5)");

    // Step 1
    $this->info("\n[Step 1] Kedua transaksi memulai Growing Phase");
    $this->table(
        ['Step', 'Terminal 1 (Rizky: -4 Buku)', 'Terminal 2 (Siti: -3 Buku)', 'Perilaku Internal DBMS'],
        [['1', 'BEGIN;', 'BEGIN;', 'Memasuki Growing Phase (fase pengumpulan kunci).']]
    );

    $pause('Step 2 Skenario B');

    // Step 2: Terminal 1 SELECT ... FOR UPDATE
    $stokT1_B = (int) DB::table('buku')->where('id_buku', 'B0005')->value('stok');
    $this->info("\n[Step 2] Terminal 1 (Rizky) menjalankan SELECT ... FOR UPDATE (Memasang Exclusive Lock / X-Lock)");
    $this->table(
        ['Step', 'Terminal 1 (Rizky: -4 Buku)', 'Terminal 2 (Siti: -3 Buku)', 'Perilaku Internal DBMS'],
        [['2', "SELECT stok FROM buku WHERE id_buku = 'B0005' FOR UPDATE;\n(Output: {$stokT1_B})", '(Menunggu giliran)', "DBMS memasang Exclusive Lock (X) pada baris id_buku = 'B0005' untuk Terminal 1."]]
    );

    $pause('Step 3 Skenario B');

    // Step 3: Terminal 2 mencoba SELECT ... FOR UPDATE namun TERBLOKIR (WAITING)
    $this->info("\n[Step 3] Terminal 2 (Siti) mencoba SELECT ... FOR UPDATE tetapi TERBLOKIR (WAITING)");
    $this->table(
        ['Step', 'Terminal 1 (Rizky: -4 Buku)', 'Terminal 2 (Siti: -3 Buku)', 'Perilaku Internal DBMS'],
        [['3', '(Jangan jalankan COMMIT dulu)', "SELECT stok FROM buku WHERE id_buku = 'B0005' FOR UPDATE;", 'Terblokir (Waiting): Kursor Terminal 2 berhenti menunggu karena kunci X masih ditahan Rizky.']]
    );

    // Tampilkan Live Lock Monitoring (Section 5 Handout) tepat di antara Step 3 & Step 4
    $this->line("\n   >> [5. Inspeksi Kunci Aktif di Terminal 3 saat Step 3/4]:");
    $this->comment("      SELECT engine_transaction_id, object_name, lock_type, lock_mode, lock_status, lock_data FROM performance_schema.data_locks WHERE object_name = 'buku';");
    $this->table(
        ['engine_transaction_id', 'object_name', 'lock_type', 'lock_mode', 'lock_status', 'lock_data'],
        [
            ['TRX_TERMINAL_1 (Rizky)', 'buku', 'RECORD', 'X', 'GRANTED', "'B0005'"],
            ['TRX_TERMINAL_2 (Siti)', 'buku', 'RECORD', 'X', 'WAITING', "'B0005'"],
        ]
    );

    $pause('Step 4 Skenario B');

    // Step 4: Terminal 1 mengurangi stok menjadi 5 - 4 = 1 (belum COMMIT)
    $stokBaruT1_B = $stokT1_B - 4;
    DB::table('buku')->where('id_buku', 'B0005')->update(['stok' => $stokBaruT1_B]);
    $this->info("\n[Step 4] Terminal 1 (Rizky) mengupdate stok menjadi {$stokBaruT1_B} (Kunci X masih ditahan sesuai Strict 2PL)");
    $this->table(
        ['Step', 'Terminal 1 (Rizky: -4 Buku)', 'Terminal 2 (Siti: -3 Buku)', 'Perilaku Internal DBMS'],
        [['4', "UPDATE buku SET stok = stok - 4 WHERE id_buku = 'B0005';", '(Masih tertahan / antre)', "Stok diubah menjadi {$stokBaruT1_B} eksemplar. Sesuai aturan Strict 2PL, kunci X belum dilepas."]]
    );

    $pause('Step 5 Skenario B');

    // Step 5: Terminal 1 COMMIT -> Shrinking Phase -> Terminal 2 otomatis terbuka dan membaca stok = 1
    $stokT2_B = (int) DB::table('buku')->where('id_buku', 'B0005')->value('stok');
    $this->info("\n[Step 5] Terminal 1 menjalankan COMMIT (Shrinking Phase) -> Terminal 2 otomatis terbuka!");
    $this->table(
        ['Step', 'Terminal 1 (Rizky: -4 Buku)', 'Terminal 2 (Siti: -3 Buku)', 'Perilaku Internal DBMS'],
        [['5', 'COMMIT;', "(Otomatis terbuka! Output: {$stokT2_B})", "Memasuki Shrinking Phase: kunci X dilepas serentak saat COMMIT. Siti langsung menerima stok terbaru ({$stokT2_B})."]]
    );

    $pause('Step 6 Skenario B');

    // Step 6: Terminal 2 mengecek stok (1 < 3) lalu ROLLBACK
    $this->info("\n[Step 6] Terminal 2 (Siti) membaca stok = {$stokT2_B} (tidak cukup untuk pinjam 3 buku) -> ROLLBACK");
    $this->table(
        ['Step', 'Terminal 1 (Rizky: -4 Buku)', 'Terminal 2 (Siti: -3 Buku)', 'Perilaku Internal DBMS'],
        [['6', '(Transaksi Selesai)', 'ROLLBACK;', "Karena sisa stok ({$stokT2_B}) < kebutuhan Siti (3 buku), transaksi Siti dibatalkan dengan aman."]]
    );

    $stokAkhirB = (int) DB::table('buku')->where('id_buku', 'B0005')->value('stok');
    $this->info("HASIL AKHIR SKENARIO B (BERHASIL / KONSISTEN DENGAN STRICT 2PL):");
    $this->line("-> SELECT id_buku, judul_buku, stok FROM buku WHERE id_buku = 'B0005'; => stok = {$stokAkhirB} eksemplar");
    $this->line('-> Transaksi Rizky (4 buku) berhasil disimpan, dan transaksi Siti (3 buku) ditolak/ROLLBACK karena sisa stok hanya 1 eksemplar.');
})->purpose('Menampilkan simulasi step-by-step Skenario A (Lost Update) & Skenario B (Strict 2PL) studi kasus Perpustakaan Pentagon');
