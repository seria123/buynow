<?php

namespace App\Filament\Resources\Users\RelationManagers;

use App\Policies\RolePolicy;
use Filament\Actions\AttachAction;
use Filament\Actions\DetachAction;
use Filament\Actions\DetachBulkAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class RolesRelationManager extends RelationManager
{
    protected static string $relationship = 'roles';

    protected static ?string $recordTitleAttribute = 'name';

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Role')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('guard_name')
                    ->label('Guard')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->headerActions([
                AttachAction::make()
                    ->label('Attach Role')
                    ->preloadRecordSelect()
                    ->recordSelectSearchColumns(['name'])
                    ->recordSelectOptionsQuery(fn (Builder $query) => $query->orderBy('name'))
                    ->authorize(fn () => app(RolePolicy::class)->attachRoleToUser(auth()->user())),
            ])
            ->actions([
                DetachAction::make()
                    ->label('Detach')
                    ->authorize(fn () => app(RolePolicy::class)->detachRoleFromUser(auth()->user())),
            ])
            ->bulkActions([
                DetachBulkAction::make()
                    ->authorize(fn () => app(RolePolicy::class)->detachRoleFromUser(auth()->user())),
            ]);
    }
}


