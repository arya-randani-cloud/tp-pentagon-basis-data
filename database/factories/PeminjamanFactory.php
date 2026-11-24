<?php

namespace Database\Factories;

use App\Models\Anggota;
use App\Models\Buku;
use App\Models\Peminjaman;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Peminjaman>
 */
class PeminjamanFactory extends Factory
{
    protected $model = Peminjaman::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'id_peminjaman' => sprintf('PJ%06d', fake()->unique()->numberBetween(1000, 999999)),
            'id_anggota' => Anggota::inRandomOrder()->value('id_anggota') ?? Anggota::factory(),
            'id_buku' => Buku::inRandomOrder()->value('id_buku') ?? Buku::factory(),
            'id_petugas' => sprintf('PTG%02d', fake()->numberBetween(1, 5)),
            'waktu_peminjaman' => fake()->dateTimeBetween('-3 months', 'now')->format('Y-m-d H:i:s'),
        ];
    }
}
