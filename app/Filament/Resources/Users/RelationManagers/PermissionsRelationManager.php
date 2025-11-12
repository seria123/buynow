<?php

namespace App\Filament\Resources\Users\RelationManagers;

use Filament\Actions\AttachAction;
use Filament\Actions\DetachAction;
use Filament\Actions\DetachBulkAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class PermissionsRelationManager extends RelationManager
{
    protected static string $relationship = 'permissions';

    protected static ?string $recordTitleAttribute = 'name';

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Permission')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('guard_name')
                    ->label('Guard')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->headerActions([
                AttachAction::make()
                    ->label('Attach Permission')
                    ->preloadRecordSelect()
                    ->recordSelectSearchColumns(['name'])
                    ->recordSelectOptionsQuery(fn (Builder $query) => $query->orderBy('name')),
            ])
            ->actions([
                DetachAction::make()
                    ->label('Detach'),
            ])
            ->bulkActions([
                DetachBulkAction::make(),
            ]);
    }
}


