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
        // Ambil angka_awal dari database — bukan dari form (sudah disabled)
        $angkaAwal = $this->getRecord()->angka_awal;
        // Hitung ulang pemakaian berdasarkan angka_akhir yang baru
        $data['pemakaian_m3'] = (int) $data['angka_akhir'] - (int) $angkaAwal;

        // Safeguard server — catatan_koreksi wajib ada saat edit
        if (blank($data['catatan_koreksi'] ?? null)) {
            $this->halt(); // stop proses save
            Notification::make()
                ->title('Alasan koreksi wajib diisi.')
                ->danger()
                ->send();
        }

        return $data;
    }
}
