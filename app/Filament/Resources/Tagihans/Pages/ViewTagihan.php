<?php

namespace App\Filament\Resources\Tagihans\Pages;

use App\Filament\Resources\Tagihans\TagihanResource;
use App\Models\Pembayaran;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Components\Fieldset;

class ViewTagihan extends ViewRecord
{
    protected static string $resource = TagihanResource::class;

    protected function getHeaderActions(): array
    {
        $record = $this->getRecord();

        return [
            // ── ACTION: Bayar Tunai (Strict Auto-Fill) ──
            Action::make('bayar_tunai')
                ->label('Bayar Tunai')
                ->icon('heroicon-o-banknotes')
                ->color('success')
                ->hidden(fn () => $record->status_bayar !== 'Belum Bayar')
                ->form([
                    Fieldset::make('Detail Tagihan')
                        ->schema([
                            Placeholder::make('info_pelanggan')
                                ->label('Nama Pelanggan')
                                ->content($record->pelanggan?->user?->name ?? '-'),

                            Placeholder::make('info_no_pelanggan')
                                ->label('No. Pelanggan')
                                ->content($record->pelanggan?->no_pelanggan ?? '-'),

                            Placeholder::make('info_meter')
                                ->label('Nomor Meter')
                                ->content($record->pencatatanMeter?->meterAir?->nomor_meter ?? '-'),

                            Placeholder::make('info_periode')
                                ->label('Periode')
                                ->content($record->pencatatanMeter
                                    ? \Carbon\Carbon::create($record->pencatatanMeter->periode_tahun, $record->pencatatanMeter->periode_bulan)->translatedFormat('F Y')
                                    : '-'
                                ),

                            Placeholder::make('info_pemakaian')
                                ->label('Pemakaian Air')
                                ->content(($record->pencatatanMeter?->pemakaian_m3 ?? 0) . ' m³'),
                        ])
                        ->columns(2),

                    Placeholder::make('info_nominal')
                        ->label('💰 NOMINAL YANG HARUS DIBAYAR')
                        ->content(new \Illuminate\Support\HtmlString(
                            '<span style="font-size: 2em; font-weight: 900; color: #059669;">Rp ' .
                            number_format($record->jumlah_tagihan, 0, ',', '.') .
                            '</span>'
                        )),

                    Placeholder::make('peringatan')
                        ->label('')
                        ->content(new \Illuminate\Support\HtmlString(
                            '<div style="padding: 0.75rem 1rem; background: #FEF3C7; border-radius: 8px; color: #92400E; border: 1px solid #F59E0B;">' .
                            '<strong>⚠️ PERINGATAN:</strong> Pastikan Anda telah menerima <strong>uang fisik sebesar Rp ' .
                            number_format($record->jumlah_tagihan, 0, ',', '.') .
                            '</strong> dari pelanggan sebelum menekan tombol di bawah ini. ' .
                            'Pembayaran yang sudah dikonfirmasi <strong>tidak dapat dibatalkan</strong>.' .
                            '</div>'
                        )),
                ])
                ->modalHeading('Konfirmasi Pembayaran Tunai (Loket)')
                ->modalWidth('lg')
                ->modalSubmitActionLabel('✅ Uang Sudah Diterima — Proses Pembayaran')
                ->action(function () use ($record) {
                    Pembayaran::create([
                        'tagihan_id'         => $record->id,
                        'jumlah_bayar'       => $record->jumlah_tagihan,
                        'tanggal_bayar'      => now(),
                        'metode_bayar'       => 'tunai',
                        'status_pembayaran'  => 'Disetujui',
                        'diverifikasi_oleh'  => auth()->id(),
                        'diverifikasi_pada'  => now(),
                    ]);

                    Notification::make()
                        ->success()
                        ->title('Pembayaran Tunai Berhasil')
                        ->body('Tagihan #' . $record->no_tagihan . ' telah lunas.')
                        ->send();

                    $this->refreshFormData([]);
                }),

            // ── ACTION: Verifikasi Pembayaran Transfer ──
            Action::make('verifikasi_pembayaran')
                ->label('Verifikasi Pembayaran')
                ->icon('heroicon-o-check-badge')
                ->color('primary')
                ->hidden(fn () => $record->status_bayar !== 'Menunggu Verifikasi')
                ->form([
                    Placeholder::make('info_nominal')
                        ->label('Nominal Tagihan')
                        ->content('Rp ' . number_format($record->jumlah_tagihan, 0, ',', '.')),

                    Textarea::make('catatan_admin')
                        ->label('Catatan Admin (Wajib untuk penolakan)')
                        ->placeholder('Contoh: Bukti transfer blur / nominal tidak sesuai...')
                        ->columnSpanFull(),
                ])
                ->modalHeading('Verifikasi Pembayaran Transfer')
                ->modalWidth('lg')
                ->extraModalFooterActions(fn (Action $action) => [
                    $action->makeModalSubmitAction('tolak', ['catatan_admin'])
                        ->label('Tolak')
                        ->color('danger')
                        ->icon('heroicon-o-x-circle')
                        ->action(function (array $data) use ($record) {
                            if (blank($data['catatan_admin'] ?? null)) {
                                Notification::make()
                                    ->danger()
                                    ->title('Catatan admin wajib diisi untuk penolakan!')
                                    ->send();
                                return;
                            }

                            $pembayaran = $record->pembayarans()->where('status_pembayaran', 'Pending')->latest()->first();
                            if ($pembayaran) {
                                $pembayaran->update([
                                    'status_pembayaran'  => 'Ditolak',
                                    'diverifikasi_oleh'  => auth()->id(),
                                    'diverifikasi_pada'  => now(),
                                    'catatan_admin'      => $data['catatan_admin'],
                                ]);
                            }

                            Notification::make()
                                ->warning()
                                ->title('Pembayaran Ditolak')
                                ->body('Alasan: ' . $data['catatan_admin'])
                                ->send();

                            $this->refreshFormData([]);
                        }),
                ])
                ->action(function (array $data) use ($record) {
                    $pembayaran = $record->pembayarans()->where('status_pembayaran', 'Pending')->latest()->first();
                    if ($pembayaran) {
                        $pembayaran->update([
                            'status_pembayaran'  => 'Disetujui',
                            'diverifikasi_oleh'  => auth()->id(),
                            'diverifikasi_pada'  => now(),
                            'catatan_admin'      => $data['catatan_admin'] ?? null,
                        ]);
                    }

                    Notification::make()
                        ->success()
                        ->title('Pembayaran Disetujui')
                        ->body('Tagihan #' . $record->no_tagihan . ' telah lunas.')
                        ->send();

                    $this->refreshFormData([]);
                }),

            // ── Delete (hanya untuk Belum Bayar) ──
            DeleteAction::make()
                ->hidden(fn () => $record->status_bayar !== 'Belum Bayar')
                ->modalHeading('Konfirmasi Hapus Tagihan')
                ->modalDescription('Apakah Anda yakin? Tagihan yang dihapus tidak dapat dikembalikan.'),
        ];
    }
}
