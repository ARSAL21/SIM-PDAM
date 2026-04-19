<?php

namespace App\Filament\Resources\Users\Tables;

use App\Models\User;
use Filament\Actions\BulkActionGroup as ActionsBulkActionGroup;
use Filament\Actions\DeleteAction as ActionsDeleteAction;
use Filament\Actions\DeleteBulkAction as ActionsDeleteBulkAction;
use Filament\Actions\EditAction as ActionsEditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable(),
                TextColumn::make('email')
                    ->label('Email address')
                    ->searchable(),
                TextColumn::make('roles.name')
                    ->label('Role')
                    ->badge()
                    ->separator(','),
                TextColumn::make('email_verified_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                ActionsEditAction::make(),
                // Lapis 1: Sembunyikan tombol delete untuk admin-PDAM
                ActionsDeleteAction::make()
                    ->hidden(fn (User $record): bool => $record->hasRole('admin-PDAM')),
            ])
            ->toolbarActions([
                ActionsBulkActionGroup::make([
                    ActionsDeleteBulkAction::make(),
                ]),
            ])
            // Lapis 2: Mencegah checkbox dicentang pada baris admin-PDAM untuk mass delete
            ->checkIfRecordIsSelectableUsing(
                fn (User $record): bool => ! $record->hasRole('admin-PDAM')
            );
    }
}
