<?php

namespace App\Filament\Resources\Tagihans\Tables;

use App\Filament\Resources\Tagihans\TagihanResource;
use Carbon\Carbon;
use Filament\Actions\Action as ActionsAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Fieldset;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

class TagihansTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->recordUrl(fn ($record) => TagihanResource::getUrl('view', ['record' => $record]))
            ->columns([
                TextColumn::make('no_tagihan')
                    ->label('No. Invoice')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->copyable()
                    ->copyMessage('No. tagihan disalin!'),

                TextColumn::make('pelanggan.user.name')
                    ->label('Pelanggan')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('periode')
                    ->label('Periode')
                    ->getStateUsing(fn ($record) => Carbon::create(
                        $record->pencatatanMeter->periode_tahun,
                        $record->pencatatanMeter->periode_bulan
                    )->translatedFormat('M Y'))
                    ->sortable(query: fn ($query, $direction) => $query
                        ->join('pencatatan_meter', 'tagihan.pencatatan_meter_id', '=', 'pencatatan_meter.id')
                        ->orderBy('pencatatan_meter.periode_tahun', $direction)
                        ->orderBy('pencatatan_meter.periode_bulan', $direction)
                    ),

                TextColumn::make('pencatatanMeter.pemakaian_m3')
                    ->label('Pemakaian')
                    ->suffix(' m³')
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('jumlah_tagihan')
                    ->label('Total Tagihan')
                    ->money('IDR', locale: 'id')
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('status_bayar')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Belum Bayar'         => 'warning',
                        'Menunggu Verifikasi' => 'info',
                        'Lunas'               => 'success',
                        default               => 'gray',
                    })
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('Tanggal Terbit')
                    ->date('d M Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status_bayar')
                    ->label('Status Bayar')
                    ->options([
                        'Belum Bayar'         => 'Belum Bayar',
                        'Menunggu Verifikasi' => 'Menunggu Verifikasi',
                        'Lunas'               => 'Lunas',
                    ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->recordActions([
                // ── ACTION: Bayar Tunai (Strict Auto-Fill) ──
                ActionsAction::make('bayar_tunai')
                    ->label('Bayar Tunai')
                    ->icon('heroicon-o-banknotes')
                    ->color('success')
                    ->hidden(fn ($record) => $record->status_bayar !== 'Belum Bayar')
                    ->form(fn ($record) => [
                        Fieldset::make('Detail Tagihan')
                            ->schema([
                                \Filament\Forms\Components\Placeholder::make('info_pelanggan')
                                    ->label('Nama Pelanggan')
                                    ->content($record->pelanggan?->user?->name ?? '-'),

                                \Filament\Forms\Components\Placeholder::make('info_no_pelanggan')
                                    ->label('No. Pelanggan')
                                    ->content($record->pelanggan?->no_pelanggan ?? '-'),

                                \Filament\Forms\Components\Placeholder::make('info_meter')
                                    ->label('Nomor Meter')
                                    ->content($record->pencatatanMeter?->meterAir?->nomor_meter ?? '-'),

                                \Filament\Forms\Components\Placeholder::make('info_periode')
                                    ->label('Periode')
                                    ->content($record->pencatatanMeter
                                        ? \Carbon\Carbon::create($record->pencatatanMeter->periode_tahun, $record->pencatatanMeter->periode_bulan)->translatedFormat('F Y')
                                        : '-'
                                    ),

                                \Filament\Forms\Components\Placeholder::make('info_pemakaian')
                                    ->label('Pemakaian Air')
                                    ->content(($record->pencatatanMeter?->pemakaian_m3 ?? 0) . ' m³'),
                            ])
                            ->columns(2),

                        \Filament\Forms\Components\Placeholder::make('info_nominal')
                            ->label('💰 NOMINAL YANG HARUS DIBAYAR')
                            ->content(new \Illuminate\Support\HtmlString(
                                '<span style="font-size: 2em; font-weight: 900; color: #059669;">Rp ' .
                                number_format($record->jumlah_tagihan, 0, ',', '.') .
                                '</span>'
                            )),

                        \Filament\Forms\Components\Placeholder::make('peringatan')
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
                    ->modalSubmitActionLabel('Uang Sudah Diterima — Proses Pembayaran')
                    ->action(function ($record) {
                        \App\Models\Pembayaran::create([
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
                    }),

                // ── ACTION: Verifikasi Pembayaran Transfer ──
                ActionsAction::make('verifikasi_pembayaran')
                    ->label('Verifikasi')
                    ->icon('heroicon-o-check-badge')
                    ->color('primary')
                    ->hidden(fn ($record) => $record->status_bayar !== 'Menunggu Verifikasi')
                    ->form([
                        \Filament\Forms\Components\Placeholder::make('info_nominal')
                            ->label('Nominal Tagihan')
                            ->content(fn ($record) => 'Rp ' . number_format($record->jumlah_tagihan, 0, ',', '.')),

                        \Filament\Forms\Components\Textarea::make('catatan_admin')
                            ->label('Catatan Admin (Wajib untuk penolakan)')
                            ->placeholder('Contoh: Bukti transfer blur / nominal tidak sesuai...')
                            ->columnSpanFull(),
                    ])
                    ->modalHeading('Verifikasi Pembayaran Transfer')
                    ->modalWidth('lg')
                    ->extraModalFooterActions(fn (ActionsAction $action) => [
                        $action->makeModalSubmitAction('tolak', ['catatan_admin'])
                            ->label('Tolak')
                            ->color('danger')
                            ->icon('heroicon-o-x-circle')
                            ->action(function (array $data, $record) {
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
                            }),
                    ])
                    ->action(function (array $data, $record) {
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
                    }),

                DeleteAction::make()
                    ->disabled(fn ($record) => $record->status_bayar !== 'Belum Bayar')
                    ->tooltip(fn ($record) => $record->status_bayar !== 'Belum Bayar'
                        ? 'Tidak dapat dihapus — tagihan sudah diproses atau lunas.'
                        : 'Hapus tagihan'
                    )
                    ->before(function (DeleteAction $action, Model $record) {
                        if ($record->status_bayar !== 'Belum Bayar') {
                            Notification::make()
                                ->danger()
                                ->title('Aksi Ditolak!')
                                ->body('Tagihan yang sudah diproses atau lunas tidak dapat dihapus.')
                                ->send();
                            $action->cancel();
                        }
                    }),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->successNotification(null)
                        ->action(function (Collection $records) {
                            $blocked = $records->filter(fn ($record) =>
                                $record->status_bayar !== 'Belum Bayar'
                            );
                            $safeRecords = $records->diff($blocked);

                            if ($safeRecords->isNotEmpty()) {
                                $safeRecords->each->delete();
                                Notification::make()
                                    ->success()
                                    ->title('Penghapusan Berhasil')
                                    ->body("{$safeRecords->count()} tagihan telah dihapus.")
                                    ->send();
                            }

                            if ($blocked->isNotEmpty()) {
                                Notification::make()
                                    ->danger()
                                    ->title('Sebagian Aksi Ditolak!')
                                    ->body("{$blocked->count()} tagihan tidak dapat dihapus karena sudah diproses atau lunas.")
                                    ->send();
                            }
                        }),
                ]),
            ]);
    }
}
