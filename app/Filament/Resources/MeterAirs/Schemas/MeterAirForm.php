<?php

namespace App\Filament\Resources\MeterAirs\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

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

                Select::make('melanjutkan_dari_id')
                    ->label('Melanjutkan dari Meter (Oper Kontrak)')
                    ->helperText('Isi hanya jika pelanggan baru menempati lokasi pelanggan lama dan menggunakan meteran fisik yang sama.')
                    ->nullable()
                    ->searchable()
                    ->getSearchResultsUsing(fn (string $search) => \App\Models\MeterAir::where('status', 'Nonaktif')
                         ->whereHas('pelanggan', fn ($q) =>
                            $q->where('status_aktif', false) // pelanggan juga harus nonaktif
                        )
                        ->whereDoesntHave('dilanjutkanOleh')
                        ->where(fn ($q) => $q
                            ->where('nomor_meter', 'like', "%{$search}%")
                            ->orWhereHas('pelanggan.user', fn ($q) => $q->where('name', 'like', "%{$search}%")
                            )
                        )
                        ->with('pelanggan.user')
                        ->limit(10)
                        ->get()
                        ->mapWithKeys(fn ($meter) => [
                            $meter->id => "{$meter->nomor_meter} — " . $meter->pelanggan->user->name
                        ])
                    )
                    ->getOptionLabelUsing(function ($value) {
                        $meter = \App\Models\MeterAir::with('pelanggan.user')->find($value);
                        return $meter ? "{$meter->nomor_meter} — " . $meter->pelanggan->user->name : null;
                    })
                    ->live()
                    ->afterStateUpdated(function ($state, $set) {
                        if (!$state) {
                            $set('tanggal_oper_kontrak', null);
                            return;
                        }
                        $meterLama = \App\Models\MeterAir::with('pencatatanTerakhir')->find($state);
                        if (!$meterLama) return;
                        
                        // Auto-populate dari data meter lama
                        $angkaAwal = $meterLama->pencatatanTerakhir?->angka_akhir ?? $meterLama->angka_awal;
                        $set('angka_awal', $angkaAwal);
                        $set('nomor_meter', $meterLama->nomor_meter);
                        $set('merek', $meterLama->merek);
                        $set('tanggal_pasang', $meterLama->tanggal_pasang);
                        $set('tanggal_oper_kontrak', now()->toDateString());
                    }),

                TextInput::make('nomor_meter')
                    ->label('Nomor Meter')
                    ->rule(function (Get $get, ?Model $record) {
                        return function (string $attribute, mixed $value, \Closure $fail) use ($record) {
                            if (blank($value)) return;
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
                    ->nullable(),

                DatePicker::make('tanggal_pasang')
                    ->label('Tanggal Pasang')
                    ->default(now())
                    ->required(),

                DatePicker::make('tanggal_oper_kontrak')
                    ->label('Tanggal Oper Kontrak')
                    ->helperText('Tanggal pelanggan baru mulai melanjutkan meter dari pelanggan sebelumnya.')
                    ->visible(fn (Get $get) => filled($get('melanjutkan_dari_id')))
                    ->required(fn (Get $get) => filled($get('melanjutkan_dari_id')))
                    ->nullable(),

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
                        'Aktif' => 'Aktif',
                        'Rusak' => 'Rusak',
                        'Diganti' => 'Diganti',
                        'Nonaktif' => 'Nonaktif',
                    ])
                    ->default('Aktif')
                    ->required()
                    ->rules([
                        fn (Get $get, ?Model $record): \Closure => function (string $attribute, $value, \Closure $fail
                        ) use ($get, $record) {
                            if ($value !== 'Aktif') return; // hanya validasi jika status diubah ke Aktif

                            $pelangganId = $record?->pelanggan_id ?? $get('pelanggan_id');
                            if (!$pelangganId) return;

                            // Cek 1 — apakah pelanggannya sendiri masih aktif?
                            $pelanggan = \App\Models\Pelanggan::with('user')->find($pelangganId);
                            if ($pelanggan && !$pelanggan->status_aktif) {
                                $fail(
                                    'Meter tidak dapat diaktifkan karena pelanggan ' .
                                    $pelanggan->user->name . ' sedang nonaktif.'
                                );
                                return; // stop di sini, tidak perlu cek berikutnya
                            }

                            // Cek 2 — apakah sudah ada meter aktif lain untuk pelanggan ini?
                            $hasActive = \App\Models\MeterAir::where('pelanggan_id', $pelangganId)
                                ->where('status', 'Aktif')
                                ->when($record, fn($q) => $q->where('id', '!=', $record->id))
                                ->exists();

                            if ($hasActive) {
                                $fail('Gagal menyimpan. Pelanggan ini sudah memiliki alat meter berstatus Aktif lainnya.');
                            }
                        },
                    ]),
            ]);
    }
}
