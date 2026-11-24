<?php

namespace Database\Seeders;

use App\Models\Order;
use Illuminate\Database\Seeder;

class OrderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Data Sampel Order Terarah (3 hari terakhir & lebih dari 3 hari lalu)
        $dataOrders = [
            // --- Order dalam 3 hari terakhir (akan tampil pada query) ---
            [
                'id_order' => 'ORD2026001',
                'id_pelanggan' => 'PLG0001',
                'id_buku' => 'B0001',
                'nama_produk' => 'Sistem Basis Data: Teori dan Implementasi',
                'jumlah' => 2,
                'total_harga' => 180000,
                'tanggal_order' => now()->subHours(5)->format('Y-m-d H:i:s'),
                'status_order' => 'selesai',
            ],
            [
                'id_order' => 'ORD2026002',
                'id_pelanggan' => 'PLG0002',
                'id_buku' => 'B0002',
                'nama_produk' => 'Pemrograman Web Modern dengan Laravel & React',
                'jumlah' => 1,
                'total_harga' => 110000,
                'tanggal_order' => now()->subDay()->format('Y-m-d H:i:s'),
                'status_order' => 'diproses',
            ],
            [
                'id_order' => 'ORD2026003',
                'id_pelanggan' => 'PLG0003',
                'id_buku' => 'B0003',
                'nama_produk' => 'Struktur Data dan Algoritma dengan Python',
                'jumlah' => 3,
                'total_harga' => 270000,
                'tanggal_order' => now()->subDays(2)->format('Y-m-d H:i:s'),
                'status_order' => 'dikirim',
            ],
            // --- Order lebih dari 3 hari yang lalu (sebagai pembanding filter) ---
            [
                'id_order' => 'ORD2026004',
                'id_pelanggan' => 'PLG0004',
                'id_buku' => 'B0004',
                'nama_produk' => 'Rekayasa Perangkat Lunak Terstruktur dan Berorientasi Objek',
                'jumlah' => 1,
                'total_harga' => 95000,
                'tanggal_order' => now()->subDays(7)->format('Y-m-d H:i:s'),
                'status_order' => 'selesai',
            ],
            [
                'id_order' => 'ORD2026005',
                'id_pelanggan' => 'PLG0005',
                'id_buku' => 'B0005',
                'nama_produk' => 'Laskar Pelangi',
                'jumlah' => 2,
                'total_harga' => 150000,
                'tanggal_order' => now()->subDays(14)->format('Y-m-d H:i:s'),
                'status_order' => 'selesai',
            ],
        ];

        foreach ($dataOrders as $order) {
            Order::updateOrCreate(
                ['id_order' => $order['id_order']],
                $order
            );
        }

        // 2. Tambah 3 order acak dalam 3 hari terakhir & 2 order acak umum menggunakan Factory
        Order::factory(3)->tigaHariTerakhir()->create();
        Order::factory(2)->create();
    }
}

