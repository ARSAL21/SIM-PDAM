<?php

namespace Database\Factories;

use App\Models\GolonganTarif;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<GolonganTarif>
 */
class GolonganTarifFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nama_golongan' => 'Golongan ' . $this->faker->unique()->word(),
            'tarif_per_kubik' => $this->faker->randomElement([5000, 7000, 10000]),
            'biaya_admin' => 2500,
        ];
    }
}
