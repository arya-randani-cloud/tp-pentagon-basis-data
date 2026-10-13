<?php

/**
 * DBML to SQL & Laravel Migration Generator
 * Tim PENTAGON - Sistem Peminjaman dan Pengembalian Buku
 */

class DbmlGenerator
{
    private string $content;
    private array $tables = [];
    private array $refs = [];

    public function __construct(string $dbmlFilePath)
    {
        if (!file_exists($dbmlFilePath)) {
            throw new Exception("File DBML tidak ditemukan: {$dbmlFilePath}");
        }
        $this->content = file_get_contents($dbmlFilePath);
        $this->parse();
    }

    private function parse(): void
    {
        $lines = explode("\n", $this->content);
        $currentTable = null;

        foreach ($lines as $line) {
            $line = trim($line);

            // Skip empty lines or single-line comments
            if ($line === '' || str_starts_with($line, '//') || str_starts_with($line, '#')) {
                continue;
            }

            // Detect Table declaration: Table <table_name> {
            if (preg_match('/^Table\s+([a-zA-Z0-9_]+)\s*\{?/i', $line, $matches)) {
                $tableName = strtolower($matches[1]);
                $currentTable = $tableName;
                $this->tables[$currentTable] = [
                    'name' => $tableName,
                    'columns' => [],
                    'note' => ''
                ];
                continue;
            }

            // Close table block
            if ($line === '}' && $currentTable !== null) {
                $currentTable = null;
                continue;
            }

            // Detect Table Note
            if ($currentTable !== null && preg_match('/^Note:\s*[\'"](.*)[\'"]/i', $line, $matches)) {
                $this->tables[$currentTable]['note'] = $matches[1];
                continue;
            }

            // Parse Column inside Table
            if ($currentTable !== null) {
                // e.g.: id_anggota varchar(10) [pk, not null, default: 0, note: '...']
                if (preg_match('/^([a-zA-Z0-9_]+)\s+([a-zA-Z0-9_\(\)]+)(?:\s*\[(.*)\])?/i', $line, $matches)) {
                    $colName = strtolower($matches[1]);
                    $colType = strtolower($matches[2]);
                    $settingsStr = $matches[3] ?? '';

                    $isPk = false;
                    $isNotNull = false;
                    $isUnique = false;
                    $default = null;
                    $note = '';

                    if (!empty($settingsStr)) {
                        $parts = array_map('trim', explode(',', $settingsStr));
                        foreach ($parts as $part) {
                            $lowerPart = strtolower($part);
                            if ($lowerPart === 'pk' || $lowerPart === 'primary key') {
                                $isPk = true;
                                $isNotNull = true;
                            } elseif ($lowerPart === 'not null') {
                                $isNotNull = true;
                            } elseif ($lowerPart === 'null') {
                                $isNotNull = false;
                            } elseif ($lowerPart === 'unique') {
                                $isUnique = true;
                            } elseif (str_starts_with($lowerPart, 'default:')) {
                                $default = trim(substr($part, strlen('default:')));
                                $default = trim($default, '\'"');
                            } elseif (str_starts_with($lowerPart, 'note:')) {
                                $note = trim(substr($part, strlen('note:')));
                                $note = trim($note, '\'"');
                            } elseif (str_starts_with($lowerPart, 'ref:')) {
                                // inline ref: [ref: > target.col]
                                if (preg_match('/ref:\s*([><\-])\s*([a-zA-Z0-9_]+)\.([a-zA-Z0-9_]+)/i', $part, $refMatch)) {
                                    $this->refs[] = [
                                        'from_table' => $currentTable,
                                        'from_col' => $colName,
                                        'to_table' => strtolower($refMatch[2]),
                                        'to_col' => strtolower($refMatch[3]),
                                        'type' => $refMatch[1],
                                        'delete' => 'cascade',
                                        'update' => 'cascade'
                                    ];
                                }
                            }
                        }
                    }

                    $this->tables[$currentTable]['columns'][$colName] = [
                        'name' => $colName,
                        'type' => $colType,
                        'pk' => $isPk,
                        'not_null' => $isNotNull,
                        'unique' => $isUnique,
                        'default' => $default,
                        'note' => $note
                    ];
                }
                continue;
            }

            // Parse standalone Ref:
            // Ref: peminjaman.id_anggota > anggota.id_anggota [delete: cascade, update: cascade]
            if (preg_match('/^Ref(?:\s+[a-zA-Z0-9_]+)?:\s*([a-zA-Z0-9_]+)\.([a-zA-Z0-9_]+)\s*([><\-])\s*([a-zA-Z0-9_]+)\.([a-zA-Z0-9_]+)(?:\s*\[(.*)\])?/i', $line, $matches)) {
                $fromTable = strtolower($matches[1]);
                $fromCol = strtolower($matches[2]);
                $relType = $matches[3];
                $toTable = strtolower($matches[4]);
                $toCol = strtolower($matches[5]);
                $settingsStr = $matches[6] ?? '';

                $onDelete = 'cascade';
                $onUpdate = 'cascade';

                if (!empty($settingsStr)) {
                    if (preg_match('/delete:\s*([a-zA-Z0-9_]+)/i', $settingsStr, $dMatch)) {
                        $onDelete = strtolower($dMatch[1]);
                    }
                    if (preg_match('/update:\s*([a-zA-Z0-9_]+)/i', $settingsStr, $uMatch)) {
                        $onUpdate = strtolower($uMatch[1]);
                    }
                }

                $this->refs[] = [
                    'from_table' => $fromTable,
                    'from_col' => $fromCol,
                    'to_table' => $toTable,
                    'to_col' => $toCol,
                    'type' => $relType,
                    'delete' => $onDelete,
                    'update' => $onUpdate
                ];
            }
        }
    }

    public function getTables(): array
    {
        return $this->tables;
    }

    public function getRefs(): array
    {
        return $this->refs;
    }

    /**
     * Generate MySQL DDL
     */
    public function toMysql(): string
    {
        $out = "-- =====================================================================\n";
        $out .= "-- MySQL DDL Generated from DBML\n";
        $out .= "-- Tim PENTAGON - Sistem Peminjaman dan Pengembalian Buku\n";
        $out .= "-- Generated at: " . date('Y-m-d H:i:s') . "\n";
        $out .= "-- =====================================================================\n\n";
        $out .= "SET FOREIGN_KEY_CHECKS = 0;\n";

        // Reverse drop tables
        $reversedTables = array_reverse(array_keys($this->tables));
        foreach ($reversedTables as $tName) {
            $out .= "DROP TABLE IF EXISTS `{$tName}`;\n";
        }
        $out .= "SET FOREIGN_KEY_CHECKS = 1;\n\n";

        foreach ($this->tables as $tName => $tData) {
            $out .= "-- ---------------------------------------------------------------------\n";
            $out .= "-- Table: {$tName}" . ($tData['note'] ? " ({$tData['note']})" : "") . "\n";
            $out .= "-- ---------------------------------------------------------------------\n";
            $out .= "CREATE TABLE `{$tName}` (\n";
            $colDefs = [];
            $pkCols = [];

            foreach ($tData['columns'] as $cName => $col) {
                $typeStr = strtoupper($col['type']);
                $def = "    `{$cName}` {$typeStr}";

                if ($col['not_null'] || $col['pk']) {
                    $def .= " NOT NULL";
                } else {
                    $def .= " NULL";
                }

                if ($col['default'] !== null) {
                    $val = is_numeric($col['default']) ? $col['default'] : "'{$col['default']}'";
                    $def .= " DEFAULT {$val}";
                }

                if ($col['unique'] && !$col['pk']) {
                    $def .= " UNIQUE";
                }

                if (!empty($col['note'])) {
                    $def .= " COMMENT '" . addslashes($col['note']) . "'";
                }

                if ($col['pk']) {
                    $pkCols[] = "`{$cName}`";
                }

                $colDefs[] = $def;
            }

            if (!empty($pkCols)) {
                $colDefs[] = "    PRIMARY KEY (" . implode(', ', $pkCols) . ")";
            }

            // Foreign keys belonging to this table
            foreach ($this->refs as $ref) {
                if ($ref['from_table'] === $tName) {
                    // Check if target table exists in schema
                    if (isset($this->tables[$ref['to_table']])) {
                        $fkName = "fk_{$tName}_{$ref['from_col']}";
                        $delAction = strtoupper($ref['delete']);
                        $updAction = strtoupper($ref['update']);
                        $colDefs[] = "    CONSTRAINT `{$fkName}` FOREIGN KEY (`{$ref['from_col']}`) REFERENCES `{$ref['to_table']}` (`{$ref['to_col']}`) ON DELETE {$delAction} ON UPDATE {$updAction}";
                    }
                }
            }

            $out .= implode(",\n", $colDefs) . "\n";
            $out .= ") ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;\n\n";
        }

        return $out;
    }

    /**
     * Generate PostgreSQL DDL
     */
    public function toPostgres(): string
    {
        $out = "-- =====================================================================\n";
        $out .= "-- PostgreSQL DDL Generated from DBML\n";
        $out .= "-- Tim PENTAGON - Sistem Peminjaman dan Pengembalian Buku\n";
        $out .= "-- Generated at: " . date('Y-m-d H:i:s') . "\n";
        $out .= "-- =====================================================================\n\n";

        $reversedTables = array_reverse(array_keys($this->tables));
        foreach ($reversedTables as $tName) {
            $out .= "DROP TABLE IF EXISTS \"{$tName}\" CASCADE;\n";
        }
        $out .= "\n";

        foreach ($this->tables as $tName => $tData) {
            $out .= "-- ---------------------------------------------------------------------\n";
            $out .= "-- Table: {$tName}" . ($tData['note'] ? " ({$tData['note']})" : "") . "\n";
            $out .= "-- ---------------------------------------------------------------------\n";
            $out .= "CREATE TABLE \"{$tName}\" (\n";
            $colDefs = [];
            $pkCols = [];

            foreach ($tData['columns'] as $cName => $col) {
                $type = $col['type'];
                if ($type === 'datetime') {
                    $pgType = 'TIMESTAMP';
                } elseif ($type === 'int') {
                    $pgType = 'INTEGER';
                } else {
                    $pgType = strtoupper($type);
                }

                $def = "    \"{$cName}\" {$pgType}";

                if ($col['not_null'] || $col['pk']) {
                    $def .= " NOT NULL";
                }

                if ($col['default'] !== null) {
                    $val = is_numeric($col['default']) ? $col['default'] : "'{$col['default']}'";
                    $def .= " DEFAULT {$val}";
                }

                if ($col['unique'] && !$col['pk']) {
                    $def .= " UNIQUE";
                }

                if ($col['pk']) {
                    $pkCols[] = "\"{$cName}\"";
                }

                $colDefs[] = $def;
            }

            if (!empty($pkCols)) {
                $colDefs[] = "    PRIMARY KEY (" . implode(', ', $pkCols) . ")";
            }

            // Foreign keys
            foreach ($this->refs as $ref) {
                if ($ref['from_table'] === $tName && isset($this->tables[$ref['to_table']])) {
                    $fkName = "fk_{$tName}_{$ref['from_col']}";
                    $delAction = strtoupper($ref['delete']);
                    $updAction = strtoupper($ref['update']);
                    $colDefs[] = "    CONSTRAINT \"{$fkName}\" FOREIGN KEY (\"{$ref['from_col']}\") REFERENCES \"{$ref['to_table']}\" (\"{$ref['to_col']}\") ON DELETE {$delAction} ON UPDATE {$updAction}";
                }
            }

            $out .= implode(",\n", $colDefs) . "\n";
            $out .= ");\n\n";

            // Add table comments if any
            if (!empty($tData['note'])) {
                $out .= "COMMENT ON TABLE \"{$tName}\" IS '" . addslashes($tData['note']) . "';\n";
            }
            foreach ($tData['columns'] as $cName => $col) {
                if (!empty($col['note'])) {
                    $out .= "COMMENT ON COLUMN \"{$tName}\".\"{$cName}\" IS '" . addslashes($col['note']) . "';\n";
                }
            }
            $out .= "\n";
        }

        return $out;
    }

    /**
     * Generate SQLite DDL
     */
    public function toSqlite(): string
    {
        $out = "-- =====================================================================\n";
        $out .= "-- SQLite DDL Generated from DBML\n";
        $out .= "-- Tim PENTAGON - Sistem Peminjaman dan Pengembalian Buku\n";
        $out .= "-- Generated at: " . date('Y-m-d H:i:s') . "\n";
        $out .= "-- =====================================================================\n\n";
        $out .= "PRAGMA foreign_keys = OFF;\n";

        $reversedTables = array_reverse(array_keys($this->tables));
        foreach ($reversedTables as $tName) {
            $out .= "DROP TABLE IF EXISTS \"{$tName}\";\n";
        }
        $out .= "PRAGMA foreign_keys = ON;\n\n";

        foreach ($this->tables as $tName => $tData) {
            $out .= "-- ---------------------------------------------------------------------\n";
            $out .= "-- Table: {$tName}\n";
            $out .= "-- ---------------------------------------------------------------------\n";
            $out .= "CREATE TABLE \"{$tName}\" (\n";
            $colDefs = [];
            $pkCols = [];

            foreach ($tData['columns'] as $cName => $col) {
                $type = $col['type'];
                if (str_starts_with($type, 'varchar') || $type === 'text' || $type === 'date' || $type === 'datetime') {
                    $sqlType = 'TEXT';
                } elseif ($type === 'int' || $type === 'smallint' || $type === 'bigint') {
                    $sqlType = 'INTEGER';
                } else {
                    $sqlType = strtoupper($type);
                }

                $def = "    \"{$cName}\" {$sqlType}";

                if ($col['pk']) {
                    $def .= " PRIMARY KEY";
                } elseif ($col['not_null']) {
                    $def .= " NOT NULL";
                }

                if ($col['unique'] && !$col['pk']) {
                    $def .= " UNIQUE";
                }

                if ($col['default'] !== null) {
                    $val = is_numeric($col['default']) ? $col['default'] : "'{$col['default']}'";
                    $def .= " DEFAULT {$val}";
                }

                $colDefs[] = $def;
            }

            // Foreign keys
            foreach ($this->refs as $ref) {
                if ($ref['from_table'] === $tName && isset($this->tables[$ref['to_table']])) {
                    $delAction = strtoupper($ref['delete']);
                    $updAction = strtoupper($ref['update']);
                    $colDefs[] = "    FOREIGN KEY (\"{$ref['from_col']}\") REFERENCES \"{$ref['to_table']}\" (\"{$ref['to_col']}\") ON DELETE {$delAction} ON UPDATE {$updAction}";
                }
            }

            $out .= implode(",\n", $colDefs) . "\n";
            $out .= ");\n\n";
        }

        return $out;
    }

    /**
     * Generate Laravel Migration File Content
     */
    public function toLaravelMigration(): string
    {
        $code = "<?php\n\n";
        $code .= "use Illuminate\Database\Migrations\Migration;\n";
        $code .= "use Illuminate\Database\Schema\Blueprint;\n";
        $code .= "use Illuminate\Support\Facades\Schema;\n\n";
        $code .= "return new class extends Migration\n";
        $code .= "{\n";
        $code .= "    /**\n";
        $code .= "     * Run the migrations.\n";
        $code .= "     */\n";
        $code .= "    public function up(): void\n";
        $code .= "    {\n";

        foreach ($this->tables as $tName => $tData) {
            $code .= "        Schema::create('{$tName}', function (Blueprint \$table) {\n";

            foreach ($tData['columns'] as $cName => $col) {
                $line = "            ";
                $type = $col['type'];

                if (preg_match('/varchar\((\d+)\)/i', $type, $vMatch)) {
                    $len = $vMatch[1];
                    $line .= "\$table->string('{$cName}', {$len})";
                } elseif ($type === 'smallint') {
                    $line .= "\$table->smallInteger('{$cName}')";
                } elseif ($type === 'int' || $type === 'integer') {
                    $line .= "\$table->integer('{$cName}')";
                } elseif ($type === 'date') {
                    $line .= "\$table->date('{$cName}')";
                } elseif ($type === 'datetime') {
                    $line .= "\$table->dateTime('{$cName}')";
                } elseif ($type === 'text') {
                    $line .= "\$table->text('{$cName}')";
                } else {
                    $line .= "\$table->string('{$cName}')";
                }

                if ($col['pk']) {
                    $line .= "->primary()";
                } elseif (!$col['not_null']) {
                    $line .= "->nullable()";
                }

                if ($col['unique'] && !$col['pk']) {
                    $line .= "->unique()";
                }

                if ($col['default'] !== null) {
                    if (is_numeric($col['default'])) {
                        $line .= "->default({$col['default']})";
                    } else {
                        $line .= "->default('{$col['default']}')";
                    }
                }

                if (!empty($col['note'])) {
                    $line .= "->comment('" . addslashes($col['note']) . "')";
                }

                $line .= ";\n";
                $code .= $line;
            }

            // Timestamps
            $code .= "            \$table->timestamps();\n";

            // Foreign keys
            $hasFk = false;
            foreach ($this->refs as $ref) {
                if ($ref['from_table'] === $tName && isset($this->tables[$ref['to_table']])) {
                    if (!$hasFk) {
                        $code .= "\n";
                        $hasFk = true;
                    }
                    $code .= "            \$table->foreign('{$ref['from_col']}')\n";
                    $code .= "                  ->references('{$ref['to_col']}')\n";
                    $code .= "                  ->on('{$ref['to_table']}')\n";
                    $code .= "                  ->cascadeOnDelete()\n";
                    $code .= "                  ->cascadeOnUpdate();\n";
                }
            }

            $code .= "        });\n\n";
        }

        $code .= "    }\n\n";
        $code .= "    /**\n";
        $code .= "     * Reverse the migrations.\n";
        $code .= "     */\n";
        $code .= "    public function down(): void\n";
        $code .= "    {\n";

        $reversed = array_reverse(array_keys($this->tables));
        foreach ($reversed as $tName) {
            $code .= "        Schema::dropIfExists('{$tName}');\n";
        }

        $code .= "    }\n";
        $code .= "};\n";

        return $code;
    }
}

// CLI Execution handler
if (php_sapi_name() === 'cli' && basename(__FILE__) === basename($_SERVER['SCRIPT_FILENAME'] ?? '')) {
    $baseDir = dirname(__DIR__);
    $dbmlFile = $argv[1] ?? ($baseDir . '/database/schema.dbml');
    $outputDir = $baseDir . '/database/sql';

    if (!is_dir($outputDir)) {
        mkdir($outputDir, 0777, true);
    }

    echo "========================================================\n";
    echo "  DBML to SQL & Laravel Migration Generator\n";
    echo "  Tim PENTAGON - Sistem Peminjaman dan Pengembalian Buku\n";
    echo "========================================================\n\n";
    echo "Membaca DBML dari: {$dbmlFile}\n";

    $gen = new DbmlGenerator($dbmlFile);
    $tables = $gen->getTables();
    echo "Ditemukan " . count($tables) . " tabel: " . implode(', ', array_keys($tables)) . "\n\n";

    $prefix = (basename($dbmlFile) === 'schema_full.dbml') ? '_full' : '';

    // 1. MySQL
    $mysqlFile = "{$outputDir}/mysql{$prefix}.sql";
    file_put_contents($mysqlFile, $gen->toMysql());
    echo "[OK] Generated MySQL DDL      -> {$mysqlFile}\n";

    // 2. PostgreSQL
    $pgFile = "{$outputDir}/postgresql{$prefix}.sql";
    file_put_contents($pgFile, $gen->toPostgres());
    echo "[OK] Generated PostgreSQL DDL -> {$pgFile}\n";

    // 3. SQLite
    $sqliteFile = "{$outputDir}/sqlite{$prefix}.sql";
    file_put_contents($sqliteFile, $gen->toSqlite());
    echo "[OK] Generated SQLite DDL     -> {$sqliteFile}\n";

    // 4. Laravel Migration
    if (empty($prefix)) {
        $migrationFile = $baseDir . '/database/migrations/2026_10_13_000001_create_sistem_perpustakaan_tables.php';
        file_put_contents($migrationFile, $gen->toLaravelMigration());
        echo "[OK] Generated Migration      -> {$migrationFile}\n";
    } else {
        $migrationFile = $baseDir . '/database/migrations/2026_10_13_000002_create_sistem_perpustakaan_full_tables.php';
        file_put_contents($migrationFile, $gen->toLaravelMigration());
        echo "[OK] Generated Full Migration -> {$migrationFile}\n";
    }

    echo "\nSelesai! Semua skema database berhasil digenerate.\n";
}
