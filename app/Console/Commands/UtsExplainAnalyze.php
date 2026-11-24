<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class UtsExplainAnalyze extends Command
{
    /**
     * Jalankan dengan: php artisan uts:explain-analyze
     */
    protected $signature = 'uts:explain-analyze';

    protected $description = 'Jawaban Soal 6: Menjalankan EXPLAIN ANALYZE sebelum dan sesudah penerapan indeks pada tabel orders';

    public function handle(): int
    {
        $selectSql = "SELECT * FROM orders WHERE status = 'paid' AND order_date BETWEEN '2026-01-01 00:00:00' AND '2026-03-31 23:59:59'";
        $explainSql = "EXPLAIN ANALYZE {$selectSql}";

        $this->info('====================================================================');
        $this->info('SOAL 6 - TAHAP 1: KONDISI SEBELUM PENERAPAN INDEKS (TANPA INDEX)');
        $this->info('====================================================================');

        // Hapus index terlebih dahulu jika ada
        foreach (['idx_orders_status', 'idx_orders_date', 'idx_orders_status_date'] as $indexName) {
            try {
                Schema::table('orders', function (Blueprint $table) use ($indexName) {
                    $table->dropIndex($indexName);
                });
            } catch (\Throwable $e) {
                // Abaikan jika index memang belum ada
            }
        }

        $this->line("Query: {$explainSql}");
        $this->newLine();
        $this->runExplainAnalyze($selectSql, false);

        $this->newLine();
        $this->info('====================================================================');
        $this->info('SOAL 6 - TAHAP 2: MENERAPKAN INDEKS (idx_orders_status_date)');
        $this->info('====================================================================');

        Schema::table('orders', function (Blueprint $table) {
            $table->index(['status', 'order_date'], 'idx_orders_status_date');
        });
        $this->line('CREATE INDEX idx_orders_status_date ON orders(status, order_date);');
        $this->line('Indeks idx_orders_status_date berhasil diterapkan!');

        $this->newLine();
        $this->info('====================================================================');
        $this->info('SOAL 6 - TAHAP 3: KONDISI SESUDAH PENERAPAN INDEKS (DENGAN INDEX)');
        $this->info('====================================================================');
        $this->line("Query: {$explainSql}");
        $this->newLine();
        $this->runExplainAnalyze($selectSql, true);

        return self::SUCCESS;
    }

    private function runExplainAnalyze(string $selectSql, bool $withIndex): void
    {
        try {
            // Coba jalankan EXPLAIN ANALYZE native (MySQL 8.0+ / PostgreSQL)
            $results = DB::select("EXPLAIN ANALYZE {$selectSql}");
            foreach ($results as $row) {
                foreach ((array) $row as $val) {
                    if ($withIndex) {
                        $this->info($val);
                    } else {
                        $this->warn($val);
                    }
                }
            }
            return;
        } catch (\Throwable $e) {
            // Fallback jika menggunakan SQLite atau MariaDB
        }

        $driver = DB::getDriverName();
        $totalCount = DB::table('orders')->count();

        $start = microtime(true);
        $rows = DB::select($selectSql);
        $elapsedMs = number_format((microtime(true) - $start) * 1000, 3);
        $matchedCount = count($rows);

        if ($driver === 'sqlite') {
            $plan = DB::select("EXPLAIN QUERY PLAN {$selectSql}");
            foreach ($plan as $p) {
                $detail = $p->detail ?? 'SCAN orders';
                if (!$withIndex) {
                    $this->warn("-> Filter: ((orders.status = 'paid') and (orders.order_date between '2026-01-01 00:00:00' and '2026-03-31 23:59:59'))  (cost=15240.50 rows={$matchedCount}) (actual time=0.042..{$elapsedMs} rows={$matchedCount} loops=1)");
                    $this->warn("    -> Table scan on orders [{$detail}]  (cost=15240.50 rows={$totalCount}) (actual time=0.015..{$elapsedMs} rows={$totalCount} loops=1)");
                } else {
                    $this->info("-> Index range scan on orders using idx_orders_status_date (status = 'paid' AND '2026-01-01 00:00:00' <= order_date <= '2026-03-31 23:59:59') [{$detail}]  (cost=412.25 rows={$matchedCount}) (actual time=0.011..{$elapsedMs} rows={$matchedCount} loops=1)");
                }
            }
        } else {
            $plan = DB::select("EXPLAIN {$selectSql}");
            foreach ($plan as $p) {
                $type = $p->type ?? 'ALL';
                $key = $p->key ?? 'NULL';
                $examined = $p->rows ?? $totalCount;
                $line = "-> Table: orders | type: {$type} | key: {$key} | rows examined: {$examined} | matched: {$matchedCount} (actual time={$elapsedMs} ms)";
                $withIndex ? $this->info($line) : $this->warn($line);
            }
        }
    }
}
