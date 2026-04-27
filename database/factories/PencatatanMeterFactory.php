<?php

namespace Database\Factories;

use App\Models\MeterAir;
use App\Models\PencatatanMeter;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PencatatanMeter>
 */
class PencatatanMeterFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $angkaAwal = $this->faker->numberBetween(0, 100);
        $pemakaian = $this->faker->numberBetween(10, 50);
        
        return [
            'meter_air_id' => MeterAir::factory(),
            'periode_bulan' => now()->month,
            'periode_tahun' => now()->year,
            'angka_awal' => $angkaAwal,
            'angka_akhir' => $angkaAwal + $pemakaian,
            'pemakaian_m3' => $pemakaian,
            'dicatat_oleh' => User::factory(),
        ];
    }
}
