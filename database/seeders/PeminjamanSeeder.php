<?php

namespace Database\Seeders;

use App\Models\Anggota;
use App\Models\Buku;
use App\Models\Peminjaman;
use Illuminate\Database\Seeder;

class PeminjamanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Data Sampel Transaksi Peminjaman Terarah
        $dataPeminjaman = [
            [
                'id_peminjaman' => 'PJ2026001',
                'id_anggota' => 'A0001',
                'id_buku' => 'B0001',
                'id_petugas' => 'PTG01',
                'waktu_peminjaman' => '2026-10-01 09:30:00',
            ],
            [
                'id_peminjaman' => 'PJ2026002',
                'id_anggota' => 'A0002',
                'id_buku' => 'B0002',
                'id_petugas' => 'PTG01',
                'waktu_peminjaman' => '2026-10-03 11:15:00',
            ],
            [
                'id_peminjaman' => 'PJ2026003',
                'id_anggota' => 'A0003',
                'id_buku' => 'B0003',
                'id_petugas' => 'PTG02',
                'waktu_peminjaman' => '2026-10-05 14:00:00',
            ],
            [
                'id_peminjaman' => 'PJ2026004',
                'id_anggota' => 'A0001',
                'id_buku' => 'B0004',
                'id_petugas' => 'PTG02',
                'waktu_peminjaman' => '2026-10-08 10:45:00',
            ],
            [
                'id_peminjaman' => 'PJ2026005',
                'id_anggota' => 'A0004',
                'id_buku' => 'B0005',
                'id_petugas' => 'PTG01',
                'waktu_peminjaman' => '2026-10-10 13:20:00',
            ],
        ];

        foreach ($dataPeminjaman as $transaksi) {
            Peminjaman::updateOrCreate(
                ['id_peminjaman' => $transaksi['id_peminjaman']],
                $transaksi
            );
        }

        // 2. Tambah 5 transaksi acak menggunakan Factory
        Peminjaman::factory(5)->create();
    }
}
