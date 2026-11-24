<?php

namespace Database\Factories;

use App\Models\Pelanggan;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Pelanggan>
 */
class PelangganFactory extends Factory
{
    protected $model = Pelanggan::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'id_pelanggan' => sprintf('PLG%04d', fake()->unique()->numberBetween(100, 9999)),
            'nama_pelanggan' => fake('id_ID')->name(),
            'alamat' => fake('id_ID')->address(),
            'no_telepon' => fake('id_ID')->numerify('081#########'),
            'email' => fake()->unique()->safeEmail(),
            'tanggal_daftar' => fake()->dateTimeBetween('-1 year', 'now')->format('Y-m-d'),
        ];
    }
}

