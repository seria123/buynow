<?php

namespace App\Filament\Resources\Roles\RelationManagers;

use Filament\Actions\AttachAction;
use Filament\Actions\DetachAction;
use Filament\Actions\DetachBulkAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Gate;

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
                    ->recordSelectOptionsQuery(fn (Builder $query) => $query->orderBy('name'))
                    ->authorize(fn ($record) => Gate::check('attachPermission', $record)),
            ])
            ->actions([
                DetachAction::make()
                    ->label('Detach')
                    ->authorize(fn ($record) => Gate::check('detachPermission', $record)),
            ])
            ->bulkActions([
                DetachBulkAction::make()
                    ->authorize(fn ($record) => Gate::check('detachPermission', $record)),
            ]);
    }
}


