<?php

namespace App\Filament\Resources\InventorySources\Pages;

use App\Filament\Resources\InventorySources\InventorySourceResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewInventorySource extends ViewRecord
{
    protected static string $resource = InventorySourceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
