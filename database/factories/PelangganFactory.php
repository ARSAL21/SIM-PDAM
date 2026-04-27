<?php

namespace Database\Factories;

use App\Models\GolonganTarif;
use App\Models\Pelanggan;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Pelanggan>
 */
class PelangganFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        static $urutan = 1;

        return [
            'user_id' => User::factory(),
            'golongan_tarif_id' => GolonganTarif::factory(),
            // Generate PAM-YYMM-XXXX
            'no_pelanggan' => 'PAM-' . date('ym') . '-' . str_pad($urutan++, 4, '0', STR_PAD_LEFT),
            'alamat' => $this->faker->address(),
            'no_hp' => $this->faker->phoneNumber(),
            'status_aktif' => true,
        ];
    }
}
