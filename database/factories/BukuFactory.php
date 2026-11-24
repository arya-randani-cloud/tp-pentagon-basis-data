<?php

namespace Database\Factories;

use App\Models\Buku;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Buku>
 */
class BukuFactory extends Factory
{
    protected $model = Buku::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $kategoriList = ['Basis Data', 'Pemrograman Web', 'Sistem Informasi', 'Kecerdasan Buatan', 'Jaringan Komputer', 'Sains', 'Sastra'];
        $penerbitList = ['Informatika Bandung', 'Gramedia Pustaka Utama', 'Penerbit Andi', 'Erlangga', 'Gava Media'];

        return [
            'id_buku' => sprintf('B%04d', fake()->unique()->numberBetween(100, 9999)),
            'judul_buku' => ucwords(fake()->words(4, true)),
            'pengarang' => fake('id_ID')->name(),
            'penerbit' => fake()->randomElement($penerbitList),
            'tahun_terbit' => fake()->numberBetween(2015, 2026),
            'kategori' => fake()->randomElement($kategoriList),
            'stok' => fake()->numberBetween(2, 25),
        ];
    }
}
