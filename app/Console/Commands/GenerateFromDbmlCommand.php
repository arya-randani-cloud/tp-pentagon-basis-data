<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Exception;

class GenerateFromDbmlCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'dbml:generate 
                            {file=database/schema.dbml : Path ke file DBML}
                            {--full : Generate skema lengkap 5 tabel (database/schema_full.dbml)}
                            {--migrate : Jalankan php artisan migrate setelah generate}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate otomatis SQL (MySQL, SQLite, PostgreSQL) dan Migrations dari file DBML';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $file = $this->argument('file');
        if ($this->option('full')) {
            $file = 'database/schema_full.dbml';
        }

        $basePath = base_path();
        $fullPath = str_starts_with($file, '/') || preg_match('/^[A-Z]:/i', $file) 
            ? $file 
            : $basePath . '/' . $file;

        if (!file_exists($fullPath)) {
            $this->error("File DBML tidak ditemukan di: {$fullPath}");
            return Command::FAILURE;
        }

        $this->info("========================================================");
        $this->info("  DBML to SQL & Laravel Migration Generator");
        $this->info("  Tim PENTAGON - Sistem Peminjaman dan Pengembalian Buku");
        $this->info("========================================================");
        $this->line("Membaca file DBML: <comment>{$file}</comment>");

        require_once $basePath . '/database/generate.php';

        try {
            $gen = new \DbmlGenerator($fullPath);
            $tables = $gen->getTables();
            $this->info("Ditemukan " . count($tables) . " tabel: " . implode(', ', array_keys($tables)));

            $outputDir = $basePath . '/database/sql';
            if (!is_dir($outputDir)) {
                mkdir($outputDir, 0777, true);
            }

            $prefix = (basename($fullPath) === 'schema_full.dbml') ? '_full' : '';

            // 1. MySQL
            $mysqlPath = "{$outputDir}/mysql{$prefix}.sql";
            file_put_contents($mysqlPath, $gen->toMysql());
            $this->info("✔ [MySQL]      -> database/sql/mysql{$prefix}.sql");

            // 2. PostgreSQL
            $pgPath = "{$outputDir}/postgresql{$prefix}.sql";
            file_put_contents($pgPath, $gen->toPostgres());
            $this->info("✔ [PostgreSQL] -> database/sql/postgresql{$prefix}.sql");

            // 3. SQLite
            $sqlitePath = "{$outputDir}/sqlite{$prefix}.sql";
            file_put_contents($sqlitePath, $gen->toSqlite());
            $this->info("✔ [SQLite]     -> database/sql/sqlite{$prefix}.sql");

            // 4. Migration
            if (empty($prefix)) {
                $migPath = $basePath . '/database/migrations/2026_10_13_000001_create_sistem_perpustakaan_tables.php';
                file_put_contents($migPath, $gen->toLaravelMigration());
                $this->info("✔ [Migration]  -> database/migrations/2026_10_13_000001_create_sistem_perpustakaan_tables.php");
            }

            if ($this->option('migrate')) {
                $this->line("\nMenjalankan: php artisan migrate --force");
                $this->call('migrate', ['--force' => true]);
            }

            $this->info("\nSukses! Semua skema database berhasil di-generate secara otomatis.");
            return Command::SUCCESS;
        } catch (Exception $e) {
            $this->error("Error: " . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
