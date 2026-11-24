<?php

namespace Database\Seeders;

use App\Models\Anggota;
use Illuminate\Database\Seeder;

class AnggotaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Data Sampel Riil Mahasiswa / Anggota
        $dataAnggota = [
            [
                'id_anggota' => 'A0001',
                'nama_anggota' => 'Abu Zulkifli',
                'alamat' => 'Jl. Telekomunikasi No. 1, Bandung',
                'no_telepon' => '081234567890',
                'email' => 'abzul03@gmail.com',
                'tanggal_daftar' => '2026-01-10',
            ],
            [
                'id_anggota' => 'A0002',
                'nama_anggota' => 'Arya Randani',
                'alamat' => 'Jl. Sukabirus No. 45, Bandung',
                'no_telepon' => '081298765432',
                'email' => 'arya.randani@gmail.com',
                'tanggal_daftar' => '2026-01-15',
            ],
            [
                'id_anggota' => 'A0003',
                'nama_anggota' => 'Budi Santoso',
                'alamat' => 'Jl. Bojongsoang No. 88, Bandung',
                'no_telepon' => '082155443322',
                'email' => 'budi.santoso@gmail.com',
                'tanggal_daftar' => '2026-02-01',
            ],
            [
                'id_anggota' => 'A0004',
                'nama_anggota' => 'Citra Lestari',
                'alamat' => 'Jl. Buah Batu No. 12, Bandung',
                'no_telepon' => '085711223344',
                'email' => 'citra.lestari@gmail.com',
                'tanggal_daftar' => '2026-02-14',
            ],
            [
                'id_anggota' => 'A0005',
                'nama_anggota' => 'Dimas Pratama',
                'alamat' => 'Jl. Dayeuhkolot No. 23, Bandung',
                'no_telepon' => '087899887766',
                'email' => 'dimas.pratama@gmail.com',
                'tanggal_daftar' => '2026-03-01',
            ],
        ];

        foreach ($dataAnggota as $anggota) {
            Anggota::updateOrCreate(
                ['id_anggota' => $anggota['id_anggota']],
                $anggota
            );
        }

        // 2. Tambah 5 data acak menggunakan Factory
        Anggota::factory(5)->create();
    }
}
