<?php

namespace App\Filament\Resources\Brands\Pages;

use App\Filament\Resources\Brands\BrandResource;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewBrand extends ViewRecord
{
    protected static string $resource = BrandResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ActionGroup::make([
                EditAction::make(),
                Action::make('toggle_active')
                    ->label(fn () => $this->record->is_active ? 'Deactivate' : 'Activate')
                    ->icon(fn () => $this->record->is_active ? 'heroicon-o-x-circle' : 'heroicon-o-check-circle')
                    ->color(fn () => $this->record->is_active ? 'warning' : 'success')
                    ->requiresConfirmation()
                    ->modalHeading(fn () => $this->record->is_active ? 'Deactivate Brand' : 'Activate Brand')
                    ->modalDescription(fn () => $this->record->is_active
                        ? 'Are you sure you want to deactivate this brand? It will no longer be visible.'
                        : 'Are you sure you want to activate this brand?')
                    ->action(function () {
                        $this->record->update(['is_active' => ! $this->record->is_active]);
                        $this->refreshFormData([
                            'is_active',
                        ]);
                    })
                    ->successNotificationTitle(fn () => $this->record->is_active ? 'Brand activated successfully' : 'Brand deactivated successfully'),
                DeleteAction::make(),
            ])
                ->label('Actions')
                ->icon('heroicon-m-ellipsis-vertical')
                ->size('md')
                ->color('primary')
                ->button(),
        ];
    }
}
