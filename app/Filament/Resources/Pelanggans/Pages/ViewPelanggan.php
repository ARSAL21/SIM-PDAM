<?php

namespace App\Filament\Resources\Pelanggans\Pages;

use App\Filament\Resources\Pelanggans\PelangganResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Support\Facades\DB;
use Filament\Notifications\Notification;

class ViewPelanggan extends ViewRecord
{
    protected static string $resource = PelangganResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
            
            Actions\Action::make('putus_tautan')
                ->label('Putus Tautan Akun')
                ->icon('heroicon-o-link-slash')
                ->color('danger')
                ->visible(fn ($record) => $record->user_id !== null)
                ->requiresConfirmation()
                ->modalHeading('Putus Tautan & Hapus Akun Web')
                ->modalDescription('PERINGATAN: Tindakan ini akan menghapus permanen data akun portal warga (web) yang terhubung. Email warga akan dikosongkan dari sistem dan Nomor Pelanggan ini akan dibebaskan kembali untuk registrasi ulang. Anda yakin?')
                ->modalSubmitActionLabel('Ya, Putus Tautan & Hapus')
                ->action(function ($record) {
                    DB::transaction(function () use ($record) {
                        // Langkah B: Hard delete akun web warga
                        $user = $record->user;
                        if ($user) {
                            $user->delete(); 
                        }
                        
                        // Langkah A: Kosongkan user_id di tabel pelanggan
                        $record->update(['user_id' => null]);
                    });
                    
                    Notification::make()
                        ->title('Tautan berhasil diputus!')
                        ->body('Akun web telah dihapus permanen. Nomor Pelanggan dapat diklaim ulang oleh warga.')
                        ->success()
                        ->send();
                }),
        ];
    }
}
