<?php

namespace App\Filament\Resources\Categories\Pages;

use App\Filament\Resources\Categories\CategoryResource;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewCategory extends ViewRecord
{
    protected static string $resource = CategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ActionGroup::make([
                EditAction::make(),
                Action::make('activate')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn ($record) => ! $record->is_active)
                    ->requiresConfirmation()
                    ->modalHeading('Activate Category')
                    ->modalDescription('Are you sure you want to activate this category?')
                    ->action(fn ($record) => $record->update(['is_active' => true])),
                Action::make('deactivate')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn ($record) => $record->is_active)
                    ->requiresConfirmation()
                    ->modalHeading('Deactivate Category')
                    ->modalDescription('Are you sure you want to deactivate this category?')
                    ->action(fn ($record) => $record->update(['is_active' => false])),
            ])
                ->label('Actions')
                ->button(),
        ];
    }
}
