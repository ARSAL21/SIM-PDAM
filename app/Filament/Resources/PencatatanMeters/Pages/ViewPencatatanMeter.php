<?php

namespace App\Filament\Resources\PencatatanMeters\Pages;

use App\Filament\Resources\PencatatanMeters\PencatatanMeterResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewPencatatanMeter extends ViewRecord
{
    protected static string $resource = PencatatanMeterResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),

            \Filament\Actions\Action::make('generate_tagihan')
                ->label('Generate Tagihan')
                ->icon('heroicon-o-document-plus')
                ->color('success')
                ->hidden(fn () => $this->getRecord()->tagihan()->exists())
                ->requiresConfirmation()
                ->modalHeading('Generate Tagihan')
                ->modalDescription(fn () =>
                    'Tagihan akan digenerate untuk periode ' .
                    \Carbon\Carbon::create(
                        $this->getRecord()->periode_tahun,
                        $this->getRecord()->periode_bulan
                    )->translatedFormat('F Y') .
                    '. Lanjutkan?'
                )
                ->action(function () {
                    $tagihan = \App\Services\GenerateTagihanService::execute(
                        $this->getRecord()
                    );

                    // TODO: Kirim email notifikasi ke pelanggan
                    // Mail::to($tagihan->pelanggan->user->email)
                    //     ->queue(new TagihanBaruMail($tagihan));

                    \Filament\Notifications\Notification::make()
                        ->success()
                        ->title('Tagihan berhasil digenerate.')
                        ->body("No. Tagihan: {$tagihan->no_tagihan}")
                        ->send();

                    $this->refreshFormData([]);
                }),
        ];
    }
}
