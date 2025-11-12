<?php

namespace App\Filament\Resources\Roles\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class RolesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Role')
                    ->badge()
                    ->color('success')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('guard_name')
                    ->label('Guard')
                    ->badge()
                    ->color('gray')
                    ->sortable(),
                TextColumn::make('permissions_count')
                    ->label('Permissions')
                    ->counts('permissions')
                    ->badge()
                    ->color('warning')
                    ->sortable(),
                TextColumn::make('users_count')
                    ->label('Users')
                    ->counts('users')
                    ->badge()
                    ->color('primary')
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label('Created')
                    ->since()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
