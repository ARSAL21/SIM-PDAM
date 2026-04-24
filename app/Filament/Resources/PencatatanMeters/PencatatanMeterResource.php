<?php

namespace App\Filament\Resources\PencatatanMeters;

use App\Filament\Resources\PencatatanMeters\Pages\CreatePencatatanMeter;
use App\Filament\Resources\PencatatanMeters\Pages\EditPencatatanMeter;
use App\Filament\Resources\PencatatanMeters\Pages\ListPencatatanMeters;
use App\Filament\Resources\PencatatanMeters\Schemas\PencatatanMeterForm;
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

    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return PencatatanMeterForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PencatatanMetersTable::configure($table);
    }

    /**
     * Guard Edit — Lapis 1 (Resource-level)
     * Blok edit jika tagihan sudah Lunas.
     */
    public static function canEdit(Model $record): bool
    {
        if ($record->tagihan?->status_bayar === 'Lunas') {
            return false;
        }
        return true;
    }

    /**
     * Guard Delete — Lapis 1 (Resource-level)
     * Blok delete jika sudah ada tagihan.
     */
    public static function canDelete(Model $record): bool
    {
        return ! $record->tagihan()->exists();
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPencatatanMeters::route('/'),
            'create' => CreatePencatatanMeter::route('/create'),
            'edit' => EditPencatatanMeter::route('/{record}/edit'),
        ];
    }
}
