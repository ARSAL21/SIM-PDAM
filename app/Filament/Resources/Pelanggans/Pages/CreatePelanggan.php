<?php

namespace App\Filament\Resources\Pelanggans\Pages;

use App\Filament\Resources\Pelanggans\PelangganResource;
use App\Models\GolonganTarif;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;

class CreatePelanggan extends CreateRecord
{
    protected static string $resource = PelangganResource::class;

    /**
     * 1. PROACTIVE INTERCEPTION (Satpam Pintu Depan)
     * Cegah admin masuk dan mengisi form jika tarif belum ada.
     */
    public function mount(): void
    {
        if (! GolonganTarif::exists()) {
            Notification::make()
                ->warning()
                ->title('Tarif Belum Di-set!')
                ->body('Anda tidak dapat menambah pelanggan sebelum menentukan pengaturan biaya/tarif air di sistem.')
                ->persistent()
                ->actions([
                    Action::make('buat_tarif')
                        ->label('Set Tarif Sekarang')
                        ->button()
                        ->color('warning')
                        ->url(\App\Filament\Resources\GolonganTarifs\GolonganTarifResource::getUrl('index')),
                ])
                ->send();

            // Lemparkan kembali ke halaman list sebelum form sempat dirender
            redirect()->to(PelangganResource::getUrl('index'));
            return;
        }

        parent::mount();
    }

    /**
     * 2. UI MASKING (Asisten Tak Terlihat)
     * Tarif Tunggal Desa Lanto: Injeksi ID otomatis agar admin tidak perlu memilih manual.
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $golongan = GolonganTarif::first();
        
        if ($golongan) {
            $data['golongan_tarif_id'] = $golongan->id;
        }

        return $data;
    }

    // /**
    //  * UI Masking: Tarif Tunggal Desa Lanto.
    //  * Karena hanya ada satu tarif, kita inject ID-nya secara paksa di level logic,
    //  * bukan di level form UI. Ini menjamin data selalu terisi.
    //  */
    // protected function mutateFormDataBeforeCreate(array $data): array
    // {
    //     $data['golongan_tarif_id'] = 1;

    //     return $data;
    // }
}
