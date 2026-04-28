<?php

namespace App\Filament\Resources\Tagihans\Pages;

use App\Filament\Resources\PencatatanMeters\PencatatanMeterResource;
use App\Filament\Resources\Tagihans\TagihanResource;
use App\Models\PencatatanMeter;
use App\Services\GenerateTagihanService;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateTagihan extends CreateRecord
{
    protected static string $resource = TagihanResource::class;

    public function mount(): void
    {
        parent::mount();

        $pencatatanId = request()->query('pencatatan_id');

        // Guard 1: Parameter wajib ada
        if (!$pencatatanId) {
            Notification::make()
                ->danger()
                ->title('Akses Ditolak')
                ->body('Tagihan hanya dapat dibuat melalui halaman Pencatatan Meter.')
                ->send();

            $this->redirect(PencatatanMeterResource::getUrl('index'));
            return;
        }

        /** @var PencatatanMeter|null $pencatatan */
        $pencatatan = PencatatanMeter::find($pencatatanId);

        // Guard 2: Data pencatatan harus valid
        if (!$pencatatan) {
            Notification::make()
                ->danger()
                ->title('Data Tidak Ditemukan')
                ->body("Pencatatan meter dengan ID #{$pencatatanId} tidak ditemukan.")
                ->send();

            $this->redirect(PencatatanMeterResource::getUrl('index'));
            return;
        }

        // Guard 3: Tidak boleh duplikat tagihan
        if ($pencatatan->tagihan()->exists()) {
            Notification::make()
                ->warning()
                ->title('Tagihan Sudah Ada')
                ->body("Pencatatan ini sudah memiliki tagihan aktif (#{$pencatatan->tagihan->no_tagihan}).")
                ->send();

            $this->redirect(PencatatanMeterResource::getUrl('view', ['record' => $pencatatan]));
            return;
        }

        // Pre-fill hidden fields
        $this->form->fill([
            'pencatatan_meter_id' => $pencatatan->id,
            'pelanggan_id' => $pencatatan->meterAir->pelanggan_id,
        ]);
    }

    /**
     * Override: Gunakan GenerateTagihanService::execute() secara utuh.
     * Semua logika (kalkulasi, auto-nonaktif meter rusak) tetap terjaga.
     */
    protected function handleRecordCreation(array $data): Model
    {
        $pencatatan = PencatatanMeter::findOrFail($data['pencatatan_meter_id']);

        // Server-side guard: double-check duplikasi
        if ($pencatatan->tagihan()->exists()) {
            Notification::make()
                ->danger()
                ->title('Tagihan Sudah Ada')
                ->body('Pencatatan ini sudah memiliki tagihan aktif.')
                ->send();

            $this->halt();
        }

        return GenerateTagihanService::execute($pencatatan);
    }

    protected function getRedirectUrl(): string
    {
        return TagihanResource::getUrl('view', ['record' => $this->record]);
    }

    protected function getCreatedNotificationTitle(): ?string
    {
        return 'Tagihan berhasil diterbitkan!';
    }
}
