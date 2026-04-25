<?php

namespace App\Filament\Pages;

use App\Models\MeterAir;
use App\Models\Pelanggan;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Wizard;
use Filament\Schemas\Components\Wizard\Step;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Schemas\Schema;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\HtmlString;

class OperKontrak extends Page implements HasSchemas
{
    use InteractsWithSchemas;

    protected string $view = 'filament.pages.oper-kontrak';

    protected static ?string $navigationLabel = 'Proses Oper Kontrak';

    protected static string|\UnitEnum|null $navigationGroup = 'Data Meter Air';

    protected static ?int $navigationSort = 4;

    protected static ?string $title = 'Proses Oper Kontrak';

    // Form state
    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill();
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Wizard::make([

                    // ── STEP 1 ─────────────────────────────────────────
                    Step::make('Pilih Meter Lama')
                        ->description('Pilih meter dari pelanggan yang telah berhenti')
                        ->icon('heroicon-o-magnifying-glass')
                        ->schema([
                            Select::make('meter_lama_id')
                                ->label('Meter yang Akan Dioper')
                                ->required()
                                ->searchable()
                                ->getSearchResultsUsing(fn (string $search) =>
                                    MeterAir::where('status', 'Nonaktif')
                                        ->whereHas('pelanggan', fn ($q) =>
                                            $q->where('status_aktif', false)
                                        )
                                        ->whereDoesntHave('dilanjutkanOleh')
                                        ->where(fn ($q) => $q
                                            ->where('nomor_meter', 'like', "%{$search}%")
                                            ->orWhereHas('pelanggan.user', fn ($q) =>
                                                $q->where('name', 'like', "%{$search}%")
                                            )
                                        )
                                        ->with('pelanggan.user')
                                        ->limit(10)
                                        ->get()
                                        ->mapWithKeys(fn ($meter) => [
                                            $meter->id => "[{$meter->nomor_meter}] " .
                                                          $meter->pelanggan->user->name
                                        ])
                                )
                                ->getOptionLabelUsing(function ($value) {
                                    $meter = MeterAir::with('pelanggan.user')->find($value);
                                    return $meter
                                        ? "[{$meter->nomor_meter}] {$meter->pelanggan->user->name}"
                                        : null;
                                })
                                ->live()
                                ->afterStateUpdated(function ($state, $set) {
                                    if (!$state) return;
                                    $meter = MeterAir::with([
                                        'pelanggan.user',
                                        'pencatatanTerakhir',
                                    ])->find($state);
                                    if (!$meter) return;

                                    $set('preview_nomor_meter', $meter->nomor_meter);
                                    $set('preview_nama_pelanggan_lama', $meter->pelanggan->user->name);
                                    $set('preview_alamat_lama', $meter->pelanggan->alamat);
                                    $set('preview_angka_terakhir',
                                        $meter->pencatatanTerakhir?->angka_akhir
                                        ?? $meter->angka_awal
                                    );
                                    $set('preview_tanggal_nonaktif', $meter->tanggal_nonaktif);
                                    
                                    // Isi otomatis angka awal override di Step 3
                                    $set('angka_awal_override', 
                                        $meter->pencatatanTerakhir?->angka_akhir 
                                        ?? $meter->angka_awal
                                    );
                                }),

                            Placeholder::make('info_meter_lama')
                                ->label('')
                                ->visible(fn ($get) => filled($get('meter_lama_id')))
                                ->content(fn ($get) => new HtmlString(
                                    '<div style="padding:1rem; background:#F0FDF4;
                                                 border-radius:8px; font-size:13px;
                                                 border: 1px solid #86EFAC;">
                                        <div style="font-weight:600; margin-bottom:0.5rem;
                                                    color:#166534;">
                                            Detail Meter yang Dipilih
                                        </div>
                                        <table style="width:100%; border-collapse:collapse;">
                                            <tr>
                                                <td style="padding:3px 8px 3px 0; color:#374151; width:160px;">
                                                    Nomor Meter
                                                </td>
                                                <td style="color:#111827; font-weight:500;">
                                                    ' . e($get('preview_nomor_meter')) . '
                                                </td>
                                            </tr>
                                            <tr>
                                                <td style="padding:3px 8px 3px 0; color:#374151;">
                                                    Pelanggan Lama
                                                </td>
                                                <td style="color:#111827; font-weight:500;">
                                                    ' . e($get('preview_nama_pelanggan_lama')) . '
                                                </td>
                                            </tr>
                                            <tr>
                                                <td style="padding:3px 8px 3px 0; color:#374151;">
                                                    Alamat
                                                </td>
                                                <td style="color:#111827; font-weight:500;">
                                                    ' . e($get('preview_alamat_lama')) . '
                                                </td>
                                            </tr>
                                            <tr>
                                                <td style="padding:3px 8px 3px 0; color:#374151;">
                                                    Angka Terakhir
                                                </td>
                                                <td style="color:#111827; font-weight:500;">
                                                    ' . number_format((int)$get('preview_angka_terakhir')) . ' m³
                                                </td>
                                            </tr>
                                        </table>
                                    </div>'
                                )),

                            // Hidden fields untuk carry data antar step
                            TextInput::make('preview_nomor_meter')->hidden()->dehydrated(false),
                            TextInput::make('preview_nama_pelanggan_lama')->hidden()->dehydrated(false),
                            TextInput::make('preview_alamat_lama')->hidden()->dehydrated(false),
                            TextInput::make('preview_angka_terakhir')->hidden()->dehydrated(false),
                            TextInput::make('preview_tanggal_nonaktif')->hidden()->dehydrated(false),
                        ]),

                    // ── STEP 2 ─────────────────────────────────────────
                    Step::make('Pilih Pelanggan Baru')
                        ->description('Pilih pelanggan yang akan menempati lokasi ini')
                        ->icon('heroicon-o-user-plus')
                        ->schema([
                            Placeholder::make('reminder_alamat')
                                ->label('')
                                ->content(fn ($get) => new HtmlString(
                                    '<div style="padding:0.75rem; background:#FEF3C7;
                                                 border-radius:8px; color:#92400E;
                                                 font-size:13px;
                                                 border: 1px solid #FCD34D;">
                                        <strong>⚠ Perhatian:</strong> Pastikan pelanggan baru
                                        menempati alamat yang sama dengan pelanggan sebelumnya.
                                        <br/><br/>
                                        <strong>Alamat pelanggan lama:</strong><br/>
                                        ' . e($get('preview_alamat_lama')) . '
                                    </div>'
                                )),

                            Select::make('pelanggan_baru_id')
                                ->label('Pelanggan Baru')
                                ->required()
                                ->searchable()
                                ->getSearchResultsUsing(fn (string $search) =>
                                    Pelanggan::where('status_aktif', true)
                                        ->whereDoesntHave('meterAirs', fn ($q) =>
                                            $q->where('status', 'Aktif')
                                        )
                                        ->whereHas('user', fn ($q) =>
                                            $q->where('name', 'like', "%{$search}%")
                                        )
                                        ->with('user')
                                        ->limit(10)
                                        ->get()
                                        ->mapWithKeys(fn ($p) => [
                                            $p->id => "{$p->user->name} ({$p->no_pelanggan})"
                                        ])
                                )
                                ->getOptionLabelUsing(function ($value) {
                                    $p = Pelanggan::with('user')->find($value);
                                    return $p
                                        ? "{$p->user->name} ({$p->no_pelanggan})"
                                        : null;
                                })
                                ->live()
                                ->afterStateUpdated(function ($state, $set) {
                                    if (!$state) return;
                                    $pelanggan = Pelanggan::with('user')->find($state);
                                    if (!$pelanggan) return;
                                    $set('preview_alamat_baru', $pelanggan->alamat);
                                    $set('preview_nama_pelanggan_baru', $pelanggan->user->name);
                                }),

                            Placeholder::make('info_pelanggan_baru')
                                ->label('')
                                ->visible(fn ($get) => filled($get('pelanggan_baru_id')))
                                ->content(fn ($get) => new HtmlString(
                                    '<div style="padding:1rem; background:#EFF6FF;
                                                 border-radius:8px; font-size:13px;
                                                 border: 1px solid #93C5FD;">
                                        <div style="font-weight:600; margin-bottom:0.5rem;
                                                    color:#1E40AF;">
                                            Alamat Pelanggan Baru
                                        </div>
                                        <div style="color:#1E3A8A; font-weight:500;">
                                            ' . e($get('preview_alamat_baru')) . '
                                        </div>
                                    </div>'
                                )),

                            Checkbox::make('konfirmasi_alamat_sama')
                                ->label('Saya konfirmasi bahwa pelanggan baru menempati 
                                         alamat yang sama dengan pelanggan sebelumnya.')
                                ->visible(fn ($get) => filled($get('pelanggan_baru_id')))
                                ->required(fn ($get) => filled($get('pelanggan_baru_id')))
                                ->accepted(fn ($get) => filled($get('pelanggan_baru_id')))
                                ->validationMessages([
                                    'accepted' => 'Konfirmasi alamat wajib dicentang 
                                                   sebelum melanjutkan.',
                                ]),

                            TextInput::make('preview_alamat_baru')->hidden()->dehydrated(false),
                            TextInput::make('preview_nama_pelanggan_baru')->hidden()->dehydrated(false),
                        ]),

                    // ── STEP 3 ─────────────────────────────────────────
                    Step::make('Detail & Konfirmasi')
                        ->description('Isi detail teknis dan konfirmasi oper kontrak')
                        ->icon('heroicon-o-clipboard-document-check')
                        ->schema([
                            Placeholder::make('ringkasan_oper')
                                ->label('')
                                ->content(fn ($get) => new HtmlString(
                                    '<div style="padding:1rem; background:#F5F3FF;
                                                 border-radius:8px; font-size:13px;
                                                 border: 1px solid #C4B5FD;">
                                        <div style="font-weight:600; margin-bottom:0.75rem;
                                                    color:#4C1D95;">
                                            Ringkasan Oper Kontrak
                                        </div>
                                        <table style="width:100%; border-collapse:collapse;">
                                            <tr>
                                                <td style="padding:3px 8px 3px 0; color:#374151; width:160px;">
                                                    Nomor Meter
                                                </td>
                                                <td style="color:#111827; font-weight:500;">
                                                    ' . e($get('preview_nomor_meter')) . '
                                                </td>
                                            </tr>
                                            <tr>
                                                <td style="padding:3px 8px 3px 0; color:#374151;">
                                                    Dari Pelanggan
                                                </td>
                                                <td style="color:#111827;">
                                                    ' . e($get('preview_nama_pelanggan_lama')) . '
                                                </td>
                                            </tr>
                                            <tr>
                                                <td style="padding:3px 8px 3px 0; color:#374151;">
                                                    Ke Pelanggan
                                                </td>
                                                <td style="color:#111827; font-weight:500;">
                                                    ' . e($get('preview_nama_pelanggan_baru')) . '
                                                </td>
                                            </tr>
                                            <tr>
                                                <td style="padding:3px 8px 3px 0; color:#374151;">
                                                    Angka Serah Terima
                                                </td>
                                                <td style="color:#111827; font-weight:500;">
                                                    ' . number_format((int)$get('preview_angka_terakhir')) . ' m³
                                                </td>
                                            </tr>
                                        </table>
                                    </div>'
                                )),

                            DatePicker::make('tanggal_oper_kontrak')
                                ->label('Tanggal Oper Kontrak')
                                ->required()
                                ->default(now())
                                ->helperText('Tanggal resmi serah terima sambungan air.'),

                            TextInput::make('angka_awal_override')
                                ->label('Angka Awal Meter (m³)')
                                ->numeric()
                                ->nullable()
                                ->helperText(
                                    'Terisi otomatis dari angka terakhir meter lama. ' .
                                    'Ubah jika angka fisik di lapangan berbeda.'
                                )
                                ->afterStateHydrated(function ($set, $get) {
                                    // Kosongkan agar tidak menimpa jika sudah ada state
                                }),
                        ]),

                ])
                ->submitAction(
                    \Filament\Actions\Action::make('submit')
                        ->label('Selesaikan Oper Kontrak')
                        ->submit('save')
                ),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $data = $this->form->getState();

        $meterLama = MeterAir::with([
            'pelanggan.user',
            'pencatatanTerakhir',
        ])->find($data['meter_lama_id']);

        if (!$meterLama) {
            Notification::make()->danger()->title('Meter lama tidak ditemukan.')->send();
            return;
        }

        // Buat record meter baru untuk pelanggan baru
        MeterAir::create([
            'pelanggan_id'              => $data['pelanggan_baru_id'],
            'nomor_meter'               => $meterLama->nomor_meter,
            'merek'                     => $meterLama->merek,
            'tanggal_pasang'            => $data['tanggal_oper_kontrak'],
            'angka_awal'                => $data['angka_awal_override'],
            'status'                    => 'Aktif',

            // Jejak relasi
            'melanjutkan_dari_id'       => $meterLama->id,
            'tanggal_oper_kontrak'      => $data['tanggal_oper_kontrak'],

            // Snapshot permanen
            'oper_dari_nomor_meter'     => $meterLama->nomor_meter,
            'oper_dari_nama_pelanggan'  => $meterLama->pelanggan->user->name,
            'oper_angka_serah_terima'   => $data['angka_awal_override'],
            'oper_dari_tanggal_nonaktif'=> $meterLama->tanggal_nonaktif,
            'oper_dilakukan_oleh'       => auth()->id(),
        ]);

        Notification::make()
            ->success()
            ->title('Oper kontrak berhasil diproses.')
            ->send();

        $this->redirect(ListOperKontrak::getUrl());
    }
}