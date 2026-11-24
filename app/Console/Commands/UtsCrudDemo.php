<?php

namespace App\Console\Commands;

use App\Models\Order;
use Illuminate\Console\Command;

class UtsCrudDemo extends Command
{
    /**
     * Jalankan dengan: php artisan uts:crud-demo
     */
    protected $signature = 'uts:crud-demo';

    protected $description = 'Jawaban Soal 4: Demonstrasi Query melalui Model Eloquent pada Laravel (Insert, Select All, Update, Delete)';

    public function handle(): int
    {
        $this->info('==========================================================');
        $this->info('SOAL 4a. [5 Poin] INSERT DATA BARU MELALUI MODEL');
        $this->info('==========================================================');

        $orderBaru = Order::create([
            'user_id'        => 1,
            'invoice_number' => 'INV-UTS-20261110-001',
            'total_price'    => 750000.00,
            'status'         => 'pending',
            'order_date'     => now()->format('Y-m-d H:i:s'),
        ]);

        $this->line('Data berhasil ditambahkan:');
        $this->table(
            ['ID', 'User ID', 'Invoice Number', 'Total Price', 'Status', 'Order Date'],
            [[$orderBaru->id, $orderBaru->user_id, $orderBaru->invoice_number, $orderBaru->total_price, $orderBaru->status, $orderBaru->order_date]]
        );

        $this->newLine();
        $this->info('==========================================================');
        $this->info('SOAL 4b. [5 Poin] MENGAMBIL SELURUH BARIS PADA TABEL');
        $this->info('==========================================================');
        $this->line('Kode Eloquent: $semuaOrder = Order::all();');
        // Menampilkan total seluruh baris dan 5 sampel baris pertama agar terminal tetap rapi saat di-screenshot
        $totalBaris = Order::count();
        $sampelBaris = Order::limit(5)->get(['id', 'user_id', 'invoice_number', 'total_price', 'status', 'order_date']);
        $this->line("Total seluruh baris yang terambil pada tabel orders: {$totalBaris} baris.");
        $this->table(
            ['ID', 'User ID', 'Invoice Number', 'Total Price', 'Status', 'Order Date'],
            $sampelBaris->toArray()
        );

        $this->newLine();
        $this->info('==========================================================');
        $this->info('SOAL 4c. [5 Poin] MEMPERBARUI DATA PADA SATU BARIS TERTENTU');
        $this->info('==========================================================');

        $orderUpdate = Order::where('invoice_number', 'INV-UTS-20261110-001')->first();
        $orderUpdate->update([
            'status'      => 'paid',
            'total_price' => 825000.00,
        ]);

        $this->line("Data ID {$orderUpdate->id} berhasil diperbarui:");
        $this->table(
            ['ID', 'User ID', 'Invoice Number', 'Total Price (Baru)', 'Status (Baru)', 'Updated At'],
            [[$orderUpdate->id, $orderUpdate->user_id, $orderUpdate->invoice_number, $orderUpdate->total_price, $orderUpdate->status, $orderUpdate->updated_at]]
        );

        $this->newLine();
        $this->info('==========================================================');
        $this->info('SOAL 4d. [5 Poin] MENGHAPUS SATU BARIS TERTENTU');
        $this->info('==========================================================');

        $orderHapus = Order::where('invoice_number', 'INV-UTS-20261110-001')->first();
        $idDihapus = $orderHapus->id;
        $orderHapus->delete();

        $cekData = Order::find($idDihapus);
        $this->line("Data dengan ID {$idDihapus} (INV-UTS-20261110-001) berhasil dihapus!");
        $this->line('Hasil pencarian ulang Order::find(' . $idDihapus . '): ' . ($cekData === null ? 'NULL (Sudah Terhapus)' : 'Masih Ada'));

        return self::SUCCESS;
    }
}

