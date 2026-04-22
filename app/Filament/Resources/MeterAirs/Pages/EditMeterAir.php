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
}
