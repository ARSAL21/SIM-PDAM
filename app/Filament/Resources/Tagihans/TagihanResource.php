<?php

namespace App\Filament\Resources\Tagihans;

use App\Filament\Resources\Tagihans\Pages\CreateTagihan;
use App\Filament\Resources\Tagihans\Pages\ListTagihans;
use App\Filament\Resources\Tagihans\Pages\ViewTagihan;
use App\Filament\Resources\Tagihans\Schemas\TagihanForm;
use App\Filament\Resources\Tagihans\Schemas\TagihanInfolist;
use App\Filament\Resources\Tagihans\Tables\TagihansTable;
use App\Models\Tagihan;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class TagihanResource extends Resource
{
    protected static ?string $model = Tagihan::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-banknotes';

    protected static string|\UnitEnum|null $navigationGroup = 'Manajemen Keuangan';

    protected static ?string $navigationLabel = 'Data Tagihan';
    protected static ?string $modelLabel = 'Tagihan';
    protected static ?string $pluralModelLabel = 'Tagihan';
    protected static ?int $navigationSort = 1;
    protected static ?string $recordTitleAttribute = 'no_tagihan';

    public static function form(Schema $schema): Schema
    {
        return TagihanForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return TagihansTable::configure($table);
    }

    public static function infolist(Schema $schema): Schema
    {
        return TagihanInfolist::configure($schema);
    }

    /**
     * IMMUTABLE: Tagihan tidak boleh diedit manual.
     * Merusak Single Source of Truth.
     */
    public static function canEdit(Model $record): bool
    {
        return false;
    }

    /**
     * Guard Delete: Hanya tagihan 'Belum Bayar' yang boleh dihapus.
     */
    public static function canDelete(Model $record): bool
    {
        return $record->status_bayar === 'Belum Bayar';
    }

    public static function getPages(): array
    {
        return [
            'index' => ListTagihans::route('/'),
            'create' => CreateTagihan::route('/create'),
            'view' => ViewTagihan::route('/{record}'),
            // NO edit page — Tagihan is IMMUTABLE
        ];
    }
}
