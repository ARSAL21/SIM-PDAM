<?php

namespace App\Filament\Resources\MeterAirs\Schemas;

use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\HtmlString;

class MeterAirForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('pelanggan_id')
                    ->label('Pelanggan')
                    ->relationship(
                        name: 'pelanggan',
                        titleAttribute: 'no_pelanggan',
                        modifyQueryUsing: function (Builder $query, string $operation) {
                            $query->with('user');

                            if ($operation === 'create') {
                                $query
                                    ->where('status_aktif', true)
                                    ->whereDoesntHave('meterAirs', function ($subQuery) {
                                        $subQuery->where('status', 'Aktif');
                                    });
                            }

                            return $query;
                        }
                    )
                    ->getOptionLabelFromRecordUsing(fn (Model $record) => "{$record->user->name} ({$record->no_pelanggan})")
                    ->searchable(['no_pelanggan'])
                    ->preload()
                    ->disabledOn('edit')
                    ->required(),

                TextInput::make('nomor_meter')
                    ->label('Nomor Meter')
                    ->rule(function (Get $get, ?Model $record) {
                        return function (string $attribute, mixed $value, \Closure $fail) use ($record) {
                            if (blank($value)) return;

                            // Guard baru — blok edit nomor meter jika sudah punya penerus
                            if ($record && $record->dilanjutkanOleh) {
                                if ($value !== $record->nomor_meter) {
                                    $fail(
                                        'Nomor meter tidak dapat diubah karena meter ini sudah ' .
                                        'dioper kontrak ke pelanggan lain. Identitas fisik meter ' .
                                        'harus tetap sama untuk menjaga keterlacakan riwayat.'
                                    );
                                    return;
                                }
                            }

                            // Validasi existing — nomor aktif tidak boleh duplikat
                            $exists = \App\Models\MeterAir::where('nomor_meter', $value)
                                ->where('status', 'Aktif')
                                ->when($record, fn($q) => $q->whereNot('id', $record->id))
                                ->exists();

                            if ($exists) {
                                $fail("Nomor meter {$value} sudah digunakan oleh meter lain yang masih berstatus Aktif.");
                            }
                        };
                    })
                    ->required()
                    ->placeholder('Contoh: MT-2026-XYZ'),

                TextInput::make('merek')
                    ->label('Merek')
                    ->minLength(4)
                    ->maxLength(35)
                    ->required(),

                DatePicker::make('tanggal_pasang')
                    ->label('Tanggal Pasang')
                    ->default(now())
                    ->required(),

                TextInput::make('angka_awal')
                    ->label('Angka Awal')
                    ->numeric()
                    ->minValue(0)
                    ->default(0)
                    ->required()
                    ->disabledOn('edit')
                    ->dehydrated(true)
                    ->helperText('Angka pada meteran saat pertama kali dipasang ke rumah pelanggan.'),

                Select::make('status')
                    ->label('Status')
                    ->options([
                        'Aktif'    => 'Aktif',
                        'Rusak'    => 'Rusak',
                        'Nonaktif' => 'Nonaktif',
                    ])
                    ->live()
                    ->afterStateUpdated(function ($state, $set) {
                        if ($state === 'Aktif') {
                            $set('keterangan', null);  // Kosongkan keterangan saat di-set Aktif
                        }
                    })
                    ->default('Aktif')
                    ->required()
                    ->rules([
                        fn (Get $get, ?Model $record): \Closure => function (
                            string $attribute,
                            $value,
                            \Closure $fail
                        ) use ($get, $record) {
                            if ($value === 'Aktif' && $record) {
                                $keterangan = $record->keterangan ?? '';
                                if (str_contains($keterangan, '[STATUS_FISIK:RUSAK]')) {
                                    $fail('TIDAK DAPAT DIAKTIFKAN: Alat ini memiliki rekam jejak kerusakan fisik. Gunakan unit meteran baru.');
                                }
                            }
                            // Guard baru — blok reaktivasi meter yang sudah dioper kontrak
                            if ($value === 'Aktif' && $record?->dilanjutkanOleh) {
                                $penerus = $record->dilanjutkanOleh->pelanggan->user->name;
                                $fail(
                                    'Meter ini tidak dapat diaktifkan kembali karena sudah ' .
                                    "dioper kontrak ke {$penerus}. Jika pelanggan ini memerlukan " .
                                    'sambungan baru, buat data meter air baru.'
                                );
                                return;
                            }

                            if ($value !== 'Aktif') return;

                            $pelangganId = $record?->pelanggan_id ?? $get('pelanggan_id');
                            if (!$pelangganId) return;

                            // Guard existing — cek pelanggan nonaktif
                            $pelanggan = \App\Models\Pelanggan::with('user')->find($pelangganId);
                            if ($pelanggan && !$pelanggan->status_aktif) {
                                $fail(
                                    'Meter tidak dapat diaktifkan karena pelanggan ' .
                                    $pelanggan->user->name . ' sedang nonaktif.'
                                );
                                return;
                            }

                            // Guard existing — cek duplikat meter aktif
                            $hasActive = \App\Models\MeterAir::where('pelanggan_id', $pelangganId)
                                ->where('status', 'Aktif')
                                ->when($record, fn($q) => $q->where('id', '!=', $record->id))
                                ->exists();

                            if ($hasActive) {
                                $fail('Pelanggan ini sudah memiliki alat meter berstatus Aktif lainnya.');
                            }
                        },
                    ]),
                    Placeholder::make('warning_nonaktif')
                        ->label('')
                        ->visible(fn (Get $get) => $get('status') === 'Nonaktif')
                        ->content(new HtmlString('
                            <div style="padding: 1rem; background: #FEF2F2; border-radius: 8px; color: #991B1B; border: 1px solid #F87171;">
                                <strong> PERINGATAN KERAS:</strong> Menyetel status ke <strong>Nonaktif</strong> akan membuat pelanggan ini tidak bisa dicatat lagi meteran airnya selamanya (kecuali diaktifkan kembali). Pastikan Final Billing sudah dibayar!
                            </div>
                        ')),

                    Textarea::make('keterangan')
                        ->label('Alasan/Keterangan Status')
                        ->placeholder('Contoh: Pelanggan berhenti berlangganan atau ganti meteran...')
                        // 1. UX TWEAK: Hanya muncul jika status BUKAN Aktif
                        ->visible(fn (Get $get) => in_array($get('status'), ['Rusak', 'Nonaktif']))
                        ->required(fn (Get $get) => $get('status') === 'Nonaktif') 
                        ->columnSpanFull()
                        ->helperText('Jika menonaktifkan atau menandai rusak, alasan wajib dicantumkan untuk keperluan audit.'),

                // 2. THE INTENTIONAL FRICTION (Checkbox Persetujuan)
                Checkbox::make('konfirmasi_perubahan')
                        ->label('Saya sadar, yakin, dan bertanggung jawab atas perubahan status meteran ini.')
                        // Hanya muncul untuk aksi destruktif
                        ->visible(fn (Get $get) => in_array($get('status'), ['Rusak', 'Nonaktif']))
                        ->accepted() // Memaksa wajib dicentang (Validasi bawaan Laravel)
                        ->dehydrated(false) //ANGAN simpan ke database (Hanya untuk UI)
                        ->columnSpanFull(),
                ]);
    }
}
