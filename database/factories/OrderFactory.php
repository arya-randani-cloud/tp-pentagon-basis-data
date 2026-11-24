<?php

namespace Database\Factories;

use App\Models\Order;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Order>
 */
class OrderFactory extends Factory
{
    protected $model = Order::class;

    protected static int $sequenceCounter = 1;

    public function definition(): array
    {
        $n = self::$sequenceCounter++;
        $statuses = ['pending', 'paid', 'shipped', 'completed', 'cancelled'];
        $now = date('Y-m-d H:i:s');

        // Distribusi tanggal transaksi antara 2025-01-01 s/d 2026-06-01
        $baseTimestamp = 1735689600; // 2025-01-01 00:00:00
        $orderTimestamp = $baseTimestamp + (($n * 293) % 44000000);

        return [
            'user_id'        => ($n % 100) + 1,
            'invoice_number' => sprintf('INV-2026-%07d-%s', $n, strtoupper(substr(md5((string) $n), 0, 5))),
            'total_price'    => (($n % 950) + 15) * 1000,
            'status'         => $statuses[$n % 5],
            'order_date'     => date('Y-m-d H:i:s', $orderTimestamp),
            'created_at'     => $now,
            'updated_at'     => $now,
        ];
    }
}
