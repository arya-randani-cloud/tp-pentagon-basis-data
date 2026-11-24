<?php

namespace Database\Seeders;

use App\Models\Pelanggan;
use Illuminate\Database\Seeder;

class PelangganSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Data Sampel Pelanggan Riil
        $dataPelanggan = [
            [
                'id_pelanggan' => 'PLG0001',
                'nama_pelanggan' => 'Rizky Pratama',
                'alamat' => 'Jl. Telekomunikasi No. 10, Bandung',
                'no_telepon' => '081234560001',
                'email' => 'rizky.pratama@gmail.com',
                'tanggal_daftar' => '2026-08-10',
            ],
            [
                'id_pelanggan' => 'PLG0002',
                'nama_pelanggan' => 'Siti Nurhaliza',
                'alamat' => 'Jl. Sukabirus No. 22, Bandung',
                'no_telepon' => '081234560002',
                'email' => 'siti.nurhaliza@gmail.com',
                'tanggal_daftar' => '2026-09-01',
            ],
            [
                'id_pelanggan' => 'PLG0003',
                'nama_pelanggan' => 'Andi Wijaya',
                'alamat' => 'Jl. Buah Batu No. 45, Bandung',
                'no_telepon' => '081234560003',
                'email' => 'andi.wijaya@gmail.com',
                'tanggal_daftar' => '2026-09-15',
            ],
            [
                'id_pelanggan' => 'PLG0004',
                'nama_pelanggan' => 'Dewi Sartika',
                'alamat' => 'Jl. Bojongsoang No. 99, Bandung',
                'no_telepon' => '081234560004',
                'email' => 'dewi.sartika@gmail.com',
                'tanggal_daftar' => '2026-10-01',
            ],
            [
                'id_pelanggan' => 'PLG0005',
                'nama_pelanggan' => 'Fajar Nugroho',
                'alamat' => 'Jl. Dayeuhkolot No. 17, Bandung',
                'no_telepon' => '081234560005',
                'email' => 'fajar.nugroho@gmail.com',
                'tanggal_daftar' => '2026-10-20',
            ],
        ];

        foreach ($dataPelanggan as $pelanggan) {
            Pelanggan::updateOrCreate(
                ['id_pelanggan' => $pelanggan['id_pelanggan']],
                $pelanggan
            );
        }

        // 2. Tambah 5 data pelanggan acak menggunakan Factory
        Pelanggan::factory(5)->create();
    }
}

