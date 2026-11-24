<?php

namespace Database\Factories;

use App\Models\Anggota;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Anggota>
 */
class AnggotaFactory extends Factory
{
    protected $model = Anggota::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'id_anggota' => sprintf('A%04d', fake()->unique()->numberBetween(100, 9999)),
            'nama_anggota' => fake('id_ID')->name(),
            'alamat' => fake('id_ID')->address(),
            'no_telepon' => fake('id_ID')->numerify('081#########'),
            'email' => fake()->unique()->safeEmail(),
            'tanggal_daftar' => fake()->dateTimeBetween('-2 years', 'now')->format('Y-m-d'),
        ];
    }
}
