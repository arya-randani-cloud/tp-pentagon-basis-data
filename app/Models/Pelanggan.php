<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class Pelanggan extends Model
{
    use HasFactory;

    protected $table = 'pelanggan';
    protected $primaryKey = 'id_pelanggan';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = [
        'id_pelanggan',
        'nama_pelanggan',
        'alamat',
        'no_telepon',
        'email',
        'tanggal_daftar',
    ];

    protected $casts = [
        'tanggal_daftar' => 'date',
    ];

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class, 'id_pelanggan', 'id_pelanggan');
    }

    /**
     * Query Builder untuk pelanggan yang mengorder dalam 3 hari terakhir
     * menggunakan SELECT, FROM, JOIN, dan ON.
     */
    public static function queryOrderTigaHariTerakhir(): Builder
    {
        return DB::query()
            ->select(
                'pelanggan.id_pelanggan',
                'pelanggan.nama_pelanggan',
                'pelanggan.no_telepon',
                'pelanggan.email',
                'orders.id_order',
                'orders.nama_produk',
                'orders.jumlah',
                'orders.total_harga',
                'orders.tanggal_order',
                'orders.status_order'
            )
            ->from('pelanggan')
            ->join('orders', function ($join) {
                $join->on('pelanggan.id_pelanggan', '=', 'orders.id_pelanggan');
            })
            ->where('orders.tanggal_order', '>=', now()->subDays(3))
            ->orderByDesc('orders.tanggal_order');
    }

    /**
     * Menampilkan koleksi pelanggan yang mengorder dalam 3 hari terakhir.
     */
    public static function mengorderTigaHariTerakhir(): Collection
    {
        return self::queryOrderTigaHariTerakhir()->get();
    }

    /**
     * Menjalankan EXPLAIN / EXPLAIN ANALYZE terhadap query pelanggan order 3 hari terakhir.
     *
     * @return array<string, mixed>
     */
    public static function explainAnalyzeOrderTigaHariTerakhir(): array
    {
        $query = self::queryOrderTigaHariTerakhir();
        $rawSql = $query->toRawSql();
        $driver = DB::getDriverName();

        $start = hrtime(true);
        $results = $query->get();
        $executionTimeMs = round((hrtime(true) - $start) / 1_000_000, 4);

        if ($driver === 'sqlite') {
            $explainPlan = DB::select("EXPLAIN QUERY PLAN {$rawSql}");
            $explainAnalyze = $explainPlan;
        } else {
            $explainPlan = DB::select("EXPLAIN {$rawSql}");
            try {
                $explainAnalyze = DB::select("EXPLAIN ANALYZE {$rawSql}");
            } catch (\Throwable) {
                // Fallback untuk MariaDB yang menggunakan ANALYZE FORMAT=JSON atau EXPLAIN EXTENDED
                $explainAnalyze = $explainPlan;
            }
        }

        return [
            'driver' => $driver,
            'sql' => $rawSql,
            'execution_time_ms' => $executionTimeMs,
            'rows_returned' => $results->count(),
            'explain_plan' => $explainPlan,
            'explain_analyze' => $explainAnalyze,
        ];
    }
}
