<?php

namespace Database\Seeders;

use App\Models\GolonganTarif;
use App\Models\MeterAir;
use App\Models\Pelanggan;
use App\Models\PencatatanMeter;
use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Setup Roles (Sesuaikan jika nama role-mu berbeda)
        Role::firstOrCreate(['name' => 'super_admin']);
        Role::firstOrCreate(['name' => 'admin-PDAM']);
        Role::firstOrCreate(['name' => 'petugas']);
        Role::firstOrCreate(['name' => 'pelanggan']);

        // 2. Setup Akun Petugas (Untuk relasi 'dicatat_oleh' dan 'oper_dilakukan_oleh')
        $petugas = User::factory()->create([
            'name' => 'Petugas Catat',
            'email' => 'petugas@pdam.com',
        ]);
        $petugas->assignRole('petugas');

        // 3. Setup Golongan Tarif Dasar
        $tarifRTA = GolonganTarif::create(['nama_golongan' => 'Rumah Tangga A', 'tarif_per_kubik' => 5000, 'biaya_admin' => 2000]);
        $tarifNiaga = GolonganTarif::create(['nama_golongan' => 'Niaga Kecil', 'tarif_per_kubik' => 12000, 'biaya_admin' => 5000]);

        // ====================================================================
        // SKENARIO 1: Pelanggan Normal (Punya 1 Meter Aktif & Ada Pencatatan)
        // ====================================================================
        $userNormal = User::factory()->create(['name' => 'Budi Normal']);
        $userNormal->assignRole('pelanggan');
        
        $pelangganNormal = Pelanggan::factory()->create([
            'user_id' => $userNormal->id,
            'golongan_tarif_id' => $tarifRTA->id,
        ]);

        $meterNormal = MeterAir::factory()->create([
            'pelanggan_id' => $pelangganNormal->id,
            'angka_awal' => 0,
        ]);

        // Catat bulan lalu
        PencatatanMeter::factory()->create([
            'meter_air_id' => $meterNormal->id,
            'periode_bulan' => now()->subMonth()->month,
            'periode_tahun' => now()->subMonth()->year,
            'angka_awal' => 0,
            'angka_akhir' => 15,
            'pemakaian_m3' => 15,
            'dicatat_oleh' => $petugas->id,
        ]);

        // ====================================================================
        // SKENARIO 2: Meter Rusak & Diganti Baru
        // ====================================================================
        $userGanti = User::factory()->create(['name' => 'Siti Meter Rusak']);
        $userGanti->assignRole('pelanggan');
        
        $pelangganGanti = Pelanggan::factory()->create([
            'user_id' => $userGanti->id,
            'golongan_tarif_id' => $tarifNiaga->id,
        ]);

        // Meter Lama (Rusak)
        $meterRusak = MeterAir::factory()->create([
            'pelanggan_id' => $pelangganGanti->id,
            'nomor_meter' => 'MT-RUSAK-001',
            'status' => 'Rusak',
            'angka_awal' => 0,
        ]);
        
        // Pencatatan sebelum rusak
        PencatatanMeter::factory()->create([
            'meter_air_id' => $meterRusak->id,
            'periode_bulan' => now()->subMonths(2)->month,
            'periode_tahun' => now()->subMonths(2)->year,
            'angka_awal' => 0,
            'angka_akhir' => 120,
            'pemakaian_m3' => 120,
            'dicatat_oleh' => $petugas->id,
        ]);

        // Meter Pengganti (Aktif)
        MeterAir::factory()->create([
            'pelanggan_id' => $pelangganGanti->id,
            'nomor_meter' => 'MT-BARU-002',
            'status' => 'Aktif',
            'angka_awal' => 0, // Reset ke 0 karena alat baru
        ]);

        // ====================================================================
        // SKENARIO 3: Pelanggan Baru Daftar (Belum Punya Meter - Untuk test Filter)
        // ====================================================================
        $userBaru = User::factory()->create(['name' => 'Andi Antrean Pasang']);
        $userBaru->assignRole('pelanggan');
        
        Pelanggan::factory()->create([
            'user_id' => $userBaru->id,
            'golongan_tarif_id' => $tarifRTA->id,
        ]);

        // ====================================================================
        // SKENARIO 4: Oper Kontrak (Lengkap dengan Snapshot)
        // ====================================================================
        // A. Pelanggan Lama (Cabut/Pindah)
        $userLama = User::factory()->create(['name' => 'Pak Joko (Pindah)']);
        $pelangganLama = Pelanggan::factory()->create([
            'user_id' => $userLama->id,
            'golongan_tarif_id' => $tarifRTA->id,
            'status_aktif' => false,
        ]);

        $meterOperLama = MeterAir::factory()->create([
            'pelanggan_id' => $pelangganLama->id,
            'nomor_meter' => 'MT-OPER-999',
            'status' => 'Nonaktif',
            'tanggal_nonaktif' => now()->subDays(5)->toDateString(),
        ]);

        PencatatanMeter::factory()->create([
            'meter_air_id' => $meterOperLama->id,
            'periode_bulan' => now()->subMonth()->month,
            'periode_tahun' => now()->subMonth()->year,
            'angka_awal' => 0,
            'angka_akhir' => 45, // Angka terakhir sebelum dioper
            'pemakaian_m3' => 45,
            'dicatat_oleh' => $petugas->id,
        ]);

        // B. Pelanggan Baru (Penerus)
        $userPenerus = User::factory()->create(['name' => 'Mas Rudi (Penerus)']);
        $pelangganPenerus = Pelanggan::factory()->create([
            'user_id' => $userPenerus->id,
            'golongan_tarif_id' => $tarifRTA->id,
            'alamat' => $pelangganLama->alamat, // Alamat sama
        ]);

        // C. Record Meter Air Operan
        MeterAir::factory()->create([
            'pelanggan_id' => $pelangganPenerus->id,
            'nomor_meter' => $meterOperLama->nomor_meter,
            'merek' => $meterOperLama->merek,
            'status' => 'Aktif',
            'angka_awal' => 45, // Lanjut dari angka terakhir
            'tanggal_oper_kontrak' => now()->toDateString(),
            'melanjutkan_dari_id' => $meterOperLama->id,
            
            // Snapshot Oper
            'oper_dari_nomor_meter' => $meterOperLama->nomor_meter,
            'oper_dari_nama_pelanggan' => $userLama->name,
            'oper_angka_serah_terima' => 45,
            'oper_dari_tanggal_nonaktif' => $meterOperLama->tanggal_nonaktif,
            'oper_dilakukan_oleh' => $petugas->id,
        ]);

        $this->command->info('Database berhasil di-seed dengan Skenario Lengkap (Normal, Rusak/Ganti, Antre, dan Oper Kontrak)!');
    }
}