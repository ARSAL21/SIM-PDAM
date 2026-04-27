<?php

namespace Database\Factories;

use App\Models\MeterAir;
use App\Models\Pelanggan;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MeterAir>
 */
class MeterAirFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'pelanggan_id' => Pelanggan::factory(),
            'nomor_meter' => 'MT-' . date('Y') . '-' . $this->faker->unique()->numerify('#####'),
            'merek' => $this->faker->randomElement(['Linflow', 'Onda', 'Amico']),
            'tanggal_pasang' => $this->faker->dateTimeBetween('-1 year', 'now')->format('Y-m-d'),
            'angka_awal' => 0,
            'status' => 'Aktif',
        ];
    }
}
