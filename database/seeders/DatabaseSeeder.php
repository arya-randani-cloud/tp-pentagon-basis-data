<?php

namespace Database\Seeders;

use App\Models\Order;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    /**
     * Jawaban Soal 3:
     * Bangkitkan minimal 150 ribu data dummy tabel menggunakan factory dan seeder di Laravel.
     * Pastikan tabel tersebut berbeda dengan tabel yang sebelumnya sudah terisi 100 ribu data.
     */
    public function run(): void
    {
        DB::disableQueryLog();

        // Bersihkan isi tabel orders jika sebelumnya sudah ada agar tepat 150.000 baris
        DB::table('orders')->delete();

        // 1. Buat 100 data user awal untuk memenuhi konstrain Foreign Key user_id pada tabel orders
        $now = date('Y-m-d H:i:s');
        $defaultPassword = bcrypt('password');
        $users = [];
        for ($i = 1; $i <= 100; $i++) {
            $users[] = [
                'id'         => $i,
                'name'       => "User eCommerce $i",
                'email'      => "user{$i}@ecommerce.test",
                'password'   => $defaultPassword,
                'phone'      => '0812' . str_pad((string) $i, 8, '0', STR_PAD_LEFT),
                'address'    => "Jl. Kampus UAD No. $i, Yogyakarta",
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }
        DB::table('users')->insertOrIgnore($users);

        // 2. Bangkitkan 150.000 data dummy pada tabel orders menggunakan OrderFactory
        $totalData = 150000;
        $batchSize = 100; // Aman untuk batas parameter SQLite maupun MySQL
        $totalBatch = $totalData / $batchSize;

        $this->command->info("Memulai pembangkitan {$totalData} data dummy pada tabel 'orders'...");

        DB::beginTransaction();
        try {
            for ($batch = 1; $batch <= $totalBatch; $batch++) {
                $data = Order::factory()->count($batchSize)->raw();
                Order::insert($data);

                if ($batch % 300 === 0 || $batch === $totalBatch) {
                    $terisi = $batch * $batchSize;
                    $this->command->info("Progress: {$terisi} / {$totalData} baris berhasil dimasukkan.");
                }
            }
            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }

        $this->command->info("Selesai! Total baris di tabel orders: " . Order::count());
    }
}
