<?php

namespace App\Filament\Resources\MeterAirs;

use App\Filament\Resources\MeterAirs\Pages\CreateMeterAir;
use App\Filament\Resources\MeterAirs\Pages\EditMeterAir;
use App\Filament\Resources\MeterAirs\Pages\ListMeterAirs;
use App\Filament\Resources\MeterAirs\Pages\ViewMeterAir;
use App\Filament\Resources\MeterAirs\Schemas\MeterAirForm;
use App\Filament\Resources\MeterAirs\Schemas\MeterAirInfolist;
use App\Filament\Resources\MeterAirs\Tables\MeterAirsTable;
use App\Models\MeterAir;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class MeterAirResource extends Resource
{
    protected static ?string $model = MeterAir::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static string|\UnitEnum|null $navigationGroup = 'Data Meter Air';

    protected static ?int $navigationSort = 1;

    protected static ?string $recordTitleAttribute = 'nomor_meter';

    public static function form(Schema $schema): Schema
    {
        return MeterAirForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return MeterAirInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return MeterAirsTable::configure($table);
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
            'index' => ListMeterAirs::route('/'),
            'create' => CreateMeterAir::route('/create'),
            'view' => ViewMeterAir::route('/{record}'),
            'edit' => EditMeterAir::route('/{record}/edit'),
        ];
    }
}
