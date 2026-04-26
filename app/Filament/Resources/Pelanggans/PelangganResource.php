<?php

namespace App\Filament\Resources\Pelanggans;

use App\Filament\Resources\Pelanggans\Pages\CreatePelanggan;
use App\Filament\Resources\Pelanggans\Pages\EditPelanggan;
use App\Filament\Resources\Pelanggans\Pages\ListPelanggans;
use App\Filament\Resources\Pelanggans\Pages\ViewPelanggan;
use App\Filament\Resources\Pelanggans\Schemas\PelangganForm;
use App\Filament\Resources\Pelanggans\Schemas\PelangganInfolist;
use App\Filament\Resources\Pelanggans\Tables\PelanggansTable;
use App\Models\Pelanggan;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class PelangganResource extends Resource
{
    protected static string|\UnitEnum|null $navigationGroup = 'Manajemen Pelanggan';
    protected static ?int $navigationSort = 1;
    protected static ?string $model = Pelanggan::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    /**
     * Guard Delete (Menahan Efek Black Hole)
     * Pelanggan tidak boleh dihapus jika sudah memiliki meter air (meski nonaktif) 
     * atau sudah memiliki tagihan. Cukup matikan toggle "Status Aktif" saja.
     */
    public static function canDelete(Model $record): bool
    {
        // Tolak jika pernah punya meter air atau pernah punya tagihan
        return ! $record->meterAirs()->exists() && ! $record->tagihans()->exists();
    }
    
    public static function form(Schema $schema): Schema
    {
        return PelangganForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        // Memanggil skema yang sudah dipisah ke class tersendiri
        return PelangganInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PelanggansTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPelanggans::route('/'),
            'create' => CreatePelanggan::route('/create'),
            'view' => ViewPelanggan::route('/{record}'),
            'edit' => EditPelanggan::route('/{record}/edit'),
        ];
    }
}
