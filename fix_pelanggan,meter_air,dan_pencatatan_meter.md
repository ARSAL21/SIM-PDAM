nah ada satu hal yang saya baru sadari, yaitu pada metera air (misal mt-001) yang sudah rusak misalnya milik pelanggan A dan si pelanggan A sudah menggantinya ke meteran baru (misal mt-002), nah pada view meteran lama yaitu meteran yang rusak, tidak direksi informasi bahwa meteran ini sudah rusak dan sudah di ganti ke meteran baru, sehingga tidak ada data yang menjelaskan bahwa meteran ini rusak dan sudah di ganti ke meteran baru. nah selain itu, apa bila si pelanggan A masih memiliki tagihan yang belum di bayar di meteran rusak nya maka di wajibkan membayar lagi kan, dan setelah membayar itu secara otomatis meteran lama yang awalnya rusak itu akan nonkatif otomatis, nah hal ini juga menghilangkan data historynya bahwa meteran ini sebetulnya rusak dan di ganti sudah di ganti baru dan pelanggan yang bersangkutan sudah membayar tagihannya sehingga meteran ini akan nonaktif secara otomatis. nah dengan begini teknisi lapangan atau admin bisa saja mengaktifkan kembali atau menggunakan kembali meteran itu yang padahal sudah rusak, mereka mengganggap meteran ini hanya di nonaktif kan saja bukan nonaktif karena rusak

    udit Trail" (Kehilangan Jejak Audit) akibat tumpang tindih State Machine (Siklus Status).

Ini adalah masalah klasik di sistem ERP/PDAM:

Fisik barang Rusak.

Secara administratif tagihan, barang menjadi Nonaktif (tutup buku).

Karena kolom status cuma ada satu, status fisik (Rusak) tertimpa oleh status administratif (Nonaktif). Akibatnya, teknisi gudang tertipu dan mengira meteran itu masih sehat.

kita tidak perlu membuat kolom baru di database untuk menyelesaikan ini. Kita akan menggunakan kombinasi Smart Query (Pendeteksi Pewaris) dan Strict Guard (Pengunci Status).

Mari kita eksekusi 3 langkah pamungkas untuk menutup celah berbahaya ini:

🛠️ Langkah 1: Modifikasi Infolist Meter (Melacak "Siapa Penggantinya?")
Kita tidak punya relasi foreign key diganti_oleh_id, tapi kita punya otak logika! Kita bisa melacak meteran pengganti dengan mencari meteran lain milik pelanggan yang sama, yang tanggal pasangnya lebih baru.

Buka App\Filament\Resources\MeterAirs\Schemas\MeterAirInfolist.php. Tambahkan Section baru ini tepat di bawah Section Identitas Fisik:

PHP
                // TAMBAHKAN SECTION BARU INI
                ComponentsSection::make('Jejak Pergantian Alat (Audit Trail)')
                    ->icon('heroicon-o-arrow-path-rounded-square')
                    ->description('Mendeteksi apakah meteran ini sudah diganti dengan unit baru.')
                    ->visible(fn ($record) => in_array($record->status, ['Rusak', 'Nonaktif', 'Diganti']))
                    ->schema([
                        TextEntry::make('meter_pengganti')
                            ->label('Meter Pengganti (Penerus)')
                            ->html()
                            ->state(function ($record) {
                                // Cari meteran milik pelanggan yang sama, yang id-nya lebih besar (dibuat setelah ini) 
                                // ATAU tanggal pasangnya lebih baru
                                $meterBaru = \App\Models\MeterAir::where('pelanggan_id', $record->pelanggan_id)
                                    ->where('id', '>', $record->id)
                                    ->orderBy('id', 'asc')
                                    ->first();

                                if ($meterBaru) {
                                    $url = \App\Filament\Resources\MeterAirs\MeterAirResource::getUrl('view', ['record' => $meterBaru->id]);
                                    return "<span style='color: #047857; font-weight: bold;'>Telah diganti dengan: {$meterBaru->nomor_meter}</span> <br> <a href='{$url}' style='color: #2563EB; text-decoration: underline; font-size: 0.9em;'>Lihat Meter Baru &rarr;</a>";
                                }

                                return "<span style='color: #6B7280;'>Belum ada meteran pengganti yang tercatat untuk pelanggan ini.</span>";
                            }),
                    ]),
✨ Efek: Sekarang, jika admin membuka meteran MT-001 yang rusak, sistem akan otomatis memberitahu: "Telah diganti dengan MT-002" beserta link untuk melompat ke data MT-002.

Langkah 2: Kunci Permanen di GenerateTagihanService
Kita harus memastikan bahwa ketika tagihan terakhir (Final Billing) di- generate, jejak "Rusak" tidak hilang ditelan status "Nonaktif". Kita akan memberikan Stempel Permanen di keterangannya.

Langkah 3: Guard Anti-Reaktivasi di MeterAirForm
Sekarang stempel [PERMANEN NONAKTIF - ALAT RUSAK] sudah ada. Kita harus membuat Form Validation (Guard) yang membaca stempel tersebut dan memblokir Admin/Teknisi yang nakal atau ceroboh yang mencoba mengubah statusnya kembali menjadi "Aktif".