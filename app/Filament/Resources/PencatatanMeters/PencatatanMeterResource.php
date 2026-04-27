<?php

namespace App\Filament\Resources\PencatatanMeters;

use App\Filament\Resources\PencatatanMeters\Pages\CreatePencatatanMeter;
use App\Filament\Resources\PencatatanMeters\Pages\EditPencatatanMeter;
use App\Filament\Resources\PencatatanMeters\Pages\ListPencatatanMeters;
use App\Filament\Resources\PencatatanMeters\Pages\ViewPencatatanMeter;
use App\Filament\Resources\PencatatanMeters\Schemas\PencatatanMeterForm;
use App\Filament\Resources\PencatatanMeters\Schemas\PencatatanMeterInfolist;
use App\Filament\Resources\PencatatanMeters\Tables\PencatatanMetersTable;
use App\Models\PencatatanMeter;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class PencatatanMeterResource extends Resource
{
    protected static ?string $model = PencatatanMeter::class;

    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-pencil-square';
    
    protected static ?string $navigationLabel = 'Pencatatan Meter';
    
    protected static ?string $pluralLabel = 'Pencatatan Meter';
    
    protected static ?string $modelLabel = 'Pencatatan Meter';
    
    protected static string | \UnitEnum | null $navigationGroup = 'Data Meter Air';

    protected static ?int $navigationSort = 4;

    public static function form(Schema $schema): Schema
    {
        return PencatatanMeterForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PencatatanMetersTable::configure($table);
    }

    public static function infolist(Schema $schema): Schema
    {
        return PencatatanMeterInfolist::configure($schema);
    }

    /**
     * Guard Edit — Lapis 1 (Resource-level)
     * Blok edit jika tagihan sudah Lunas.
     */
    /**
     * Guard Edit — Lapis 1 (Resource-level)
     * Blok edit jika tagihan sedang diproses (Menunggu Verifikasi) atau sudah Lunas.
     */
    public static function canEdit(Model $record): bool
    {
        // Jika tagihan ada, cek statusnya
        if ($record->tagihan) {
            $status = $record->tagihan->status_bayar;
            
            if (in_array($status, ['Menunggu Verifikasi', 'Lunas'])) {
                return false; // Sembunyikan tombol Edit
            }
        }
        
        return true;
    }

    /**
     * Guard Delete — Lapis 1 (Resource-level)
     * Blok delete jika:
     * 1. Sudah ada tagihan.
     * 2. Ada pencatatan di periode yang lebih baru (mencegah rantai waktu terputus).
     */
    public static function canDelete(Model $record): bool
    {
        // Aturan 1: Tidak boleh dihapus jika sudah ada tagihan
        if ($record->tagihan()->exists()) {
            return false;
        }

        // Aturan 2: Tidak boleh dihapus jika ada periode yang lebih baru (Middle-Chain Deletion)
        $adaPeriodeLebihBaru = PencatatanMeter::where('meter_air_id', $record->meter_air_id)
            ->where('id', '!=', $record->id)
            ->where(fn ($q) => $q
                ->where('periode_tahun', '>', $record->periode_tahun)
                ->orWhere(fn ($q) => $q
                    ->where('periode_tahun', $record->periode_tahun)
                    ->where('periode_bulan', '>', $record->periode_bulan)
                )
            )
            ->exists();

        if ($adaPeriodeLebihBaru) {
            return false;
        }

        return true;
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPencatatanMeters::route('/'),
            'create' => CreatePencatatanMeter::route('/create'),
            'view' => ViewPencatatanMeter::route('/{record}'),
            'edit' => EditPencatatanMeter::route('/{record}/edit'),
        ];
    }
}
