<?php

namespace App\Filament\Resources\Pembayarans;

use App\Filament\Resources\Pembayarans\Pages\ListPembayarans;
use App\Filament\Resources\Pembayarans\Pages\ViewPembayaran;
use App\Filament\Resources\Pembayarans\Schemas\PembayaranInfolist;
use App\Filament\Resources\Pembayarans\Tables\PembayaransTable;
use App\Models\Pembayaran;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class PembayaranResource extends Resource
{
    protected static ?string $model = Pembayaran::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-clipboard-document-check';

    protected static string|\UnitEnum|null $navigationGroup = 'Manajemen Keuangan';

    protected static ?string $navigationLabel = 'Riwayat Pembayaran';
    protected static ?string $modelLabel = 'Pembayaran';
    protected static ?string $pluralModelLabel = 'Riwayat Pembayaran';
    protected static ?int $navigationSort = 2;

    public static function table(Table $table): Table
    {
        return PembayaransTable::configure($table);
    }

    public static function infolist(Schema $schema): Schema
    {
        return PembayaranInfolist::configure($schema);
    }

    /**
     * IMMUTABLE: Pembayaran tidak boleh diedit.
     * Resource ini bersifat Read-Only sebagai Buku Besar Audit.
     */
    public static function canEdit(Model $record): bool
    {
        return false;
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canDelete(Model $record): bool
    {
        return false;
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPembayarans::route('/'),
            'view' => ViewPembayaran::route('/{record}'),
        ];
    }
}
