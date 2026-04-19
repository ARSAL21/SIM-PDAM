<?php

namespace App\Filament\Resources\GolonganTarifs;

use App\Filament\Resources\GolonganTarifs\Pages\CreateGolonganTarif;
use App\Filament\Resources\GolonganTarifs\Pages\EditGolonganTarif;
use App\Filament\Resources\GolonganTarifs\Pages\ListGolonganTarifs;
use App\Filament\Resources\GolonganTarifs\Pages\ViewGolonganTarif;
use App\Filament\Resources\GolonganTarifs\Schemas\GolonganTarifForm;
use App\Filament\Resources\GolonganTarifs\Schemas\GolonganTarifInfolist;
use App\Filament\Resources\GolonganTarifs\Tables\GolonganTarifsTable;
use App\Models\GolonganTarif;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class GolonganTarifResource extends Resource
{
    protected static ?string $model = GolonganTarif::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'nama_golongan';

    public static function form(Schema $schema): Schema
    {
        return GolonganTarifForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return GolonganTarifInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return GolonganTarifsTable::configure($table);
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
            'index' => ListGolonganTarifs::route('/'),
            'create' => CreateGolonganTarif::route('/create'),
            'view' => ViewGolonganTarif::route('/{record}'),
            'edit' => EditGolonganTarif::route('/{record}/edit'),
        ];
    }
}
