<?php

namespace App\Filament\Resources\MeterAirs\Pages;

use App\Filament\Resources\MeterAirs\MeterAirResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditMeterAir extends EditRecord
{
    protected static string $resource = MeterAirResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make()
                ->before(function (DeleteAction $action) {
                    $record = $this->record;

                    if ($record->pencatatanMeters()->exists()) {
                        Notification::make()
                            ->danger()
                            ->title('Aksi Ditolak!')
                            ->body('Meter air ini tidak dapat dihapus karena sudah memiliki riwayat pencatatan.')
                            ->send();

                        $action->cancel();
                        return;
                    }

                    if ($record->status === 'Aktif') {
                        Notification::make()
                            ->danger()
                            ->title('Aksi Ditolak!')
                            ->body('Meter air berstatus Aktif tidak dapat dihapus. Ubah statusnya terlebih dahulu.')
                            ->send();

                        $action->cancel();
                    }
                }),
        ];
    }

    // EditMeterAir.php
    protected function mutateFormDataBeforeSave(array $data): array
    {
        $record = $this->getRecord();

        // Paksa pelanggan_id tidak bisa berubah
        $data['pelanggan_id'] = $record->pelanggan_id;

        // Guard server — meter yang sudah dioper tidak boleh diaktifkan
        if (isset($data['status']) && $data['status'] === 'Aktif' && $record->dilanjutkanOleh) {
            $this->halt();
            Notification::make()
                ->danger()
                ->title('Aksi Ditolak')
                ->body(
                    'Meter ini tidak dapat diaktifkan kembali karena sudah dioper kontrak. ' .
                    'Buat data meter air baru jika diperlukan sambungan baru.'
                )
                ->send();
        }

        // Guard server — nomor meter tidak boleh diubah jika sudah dioper
        if ($record->dilanjutkanOleh && isset($data['nomor_meter'])) {
            if ($data['nomor_meter'] !== $record->nomor_meter) {
                $data['nomor_meter'] = $record->nomor_meter; // paksa kembali ke nilai asal
                Notification::make()
                    ->warning()
                    ->title('Nomor Meter Tidak Diubah')
                    ->body(
                        'Nomor meter tidak dapat diubah karena meter ini sudah dioper kontrak. ' .
                        'Perubahan diabaikan.'
                    )
                    ->send();
            }
        }

        return $data;
    }
}
