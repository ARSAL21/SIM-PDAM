<?php

namespace App\Filament\Resources\GolonganTarifs;

use App\Filament\Resources\GolonganTarifs\Pages\ManageGolonganTarifs;
use App\Filament\Resources\GolonganTarifs\Schemas\GolonganTarifForm;
use App\Filament\Resources\GolonganTarifs\Tables\GolonganTarifsTable;
use App\Models\GolonganTarif;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class GolonganTarifResource extends Resource
{
    protected static ?int $navigationSort = 2;
    protected static ?string $model = GolonganTarif::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $modelLabel = 'Pengaturan Biaya';
    protected static ?string $pluralModelLabel = 'Pengaturan Biaya';
    protected static ?string $navigationLabel = 'Pengaturan Biaya';

    protected static ?string $recordTitleAttribute = 'nama_golongan';

    public static function form(Schema $schema): Schema
    {
        return GolonganTarifForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return GolonganTarifsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageGolonganTarifs::route('/'),
        ];
    }
}
