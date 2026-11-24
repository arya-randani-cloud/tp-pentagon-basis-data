<?php

namespace Database\Seeders;

use App\Models\Buku;
use Illuminate\Database\Seeder;

class BukuSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Data Sampel Buku Nyata
        $dataBuku = [
            [
                'id_buku' => 'B0001',
                'judul_buku' => 'Sistem Basis Data: Teori dan Implementasi',
                'pengarang' => 'Fathansyah',
                'penerbit' => 'Informatika Bandung',
                'tahun_terbit' => 2020,
                'kategori' => 'Basis Data',
                'stok' => 10,
            ],
            [
                'id_buku' => 'B0002',
                'judul_buku' => 'Pemrograman Web Modern dengan Laravel & React',
                'pengarang' => 'Budi Raharjo',
                'penerbit' => 'Informatika Bandung',
                'tahun_terbit' => 2024,
                'kategori' => 'Pemrograman Web',
                'stok' => 8,
            ],
            [
                'id_buku' => 'B0003',
                'judul_buku' => 'Struktur Data dan Algoritma dengan Python',
                'pengarang' => 'Rinaldi Munir',
                'penerbit' => 'Informatika Bandung',
                'tahun_terbit' => 2022,
                'kategori' => 'Algoritma',
                'stok' => 12,
            ],
            [
                'id_buku' => 'B0004',
                'judul_buku' => 'Rekayasa Perangkat Lunak Terstruktur dan Berorientasi Objek',
                'pengarang' => 'Rosa A.S. & M. Shalahuddin',
                'penerbit' => 'Informatika Bandung',
                'tahun_terbit' => 2021,
                'kategori' => 'Rekayasa Perangkat Lunak',
                'stok' => 15,
            ],
            [
                'id_buku' => 'B0005',
                'judul_buku' => 'Laskar Pelangi',
                'pengarang' => 'Andrea Hirata',
                'penerbit' => 'Bentang Pustaka',
                'tahun_terbit' => 2018,
                'kategori' => 'Sastra / Novel',
                'stok' => 5,
            ],
        ];

        foreach ($dataBuku as $buku) {
            Buku::updateOrCreate(
                ['id_buku' => $buku['id_buku']],
                $buku
            );
        }

        // 2. Tambah 5 data acak menggunakan Factory
        Buku::factory(5)->create();
    }
}
