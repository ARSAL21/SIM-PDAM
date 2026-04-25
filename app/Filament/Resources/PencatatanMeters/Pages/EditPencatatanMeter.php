<?php

namespace App\Filament\Resources\PencatatanMeters\Pages;

use App\Filament\Resources\PencatatanMeters\PencatatanMeterResource;
use Filament\Actions\DeleteAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditPencatatanMeter extends EditRecord
{
    protected static string $resource = PencatatanMeterResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
            //gunakan guard dari resource agar lebih konsisten dan tidak dapat di baypas
            ->hidden(fn () => ! static::getResource()::canDelete($this->getRecord())),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $record = $this->getRecord();
        $angkaAwal = $record->angka_awal;

        // Strict Chronological — server side (exclude ID sendiri)
        $adaPeriodeLebihBaru = \App\Models\PencatatanMeter::where('meter_air_id', $record->meter_air_id)
            ->where('id', '!=', $record->id)
            ->where(fn ($q) => $q
                ->where('periode_tahun', '>', (int) ($data['periode_tahun'] ?? $record->periode_tahun))
                ->orWhere(fn ($q) => $q
                    ->where('periode_tahun', (int) ($data['periode_tahun'] ?? $record->periode_tahun))
                    ->where('periode_bulan', '>', (int) ($data['periode_bulan'] ?? $record->periode_bulan))
                )
            )
            ->exists();

        if ($adaPeriodeLebihBaru) {
            Notification::make()
                ->danger()
                ->title('Periode Tidak Valid')
                ->body(
                    'Sudah ada pencatatan di periode yang lebih baru. ' .
                    'Tidak dapat menyimpan perubahan ini.'
                )
                ->send();

            $this->halt();
        }

        // catatan_koreksi wajib saat edit
        if (blank($data['catatan_koreksi'] ?? null)) {
            Notification::make()
                ->danger()
                ->title('Alasan koreksi wajib diisi.')
                ->send();

            $this->halt();
        }

        $data['pemakaian_m3'] = (int) $data['angka_akhir'] - (int) $angkaAwal;

        return $data;
    }
}
