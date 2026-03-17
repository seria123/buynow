<?php

namespace App\Filament\Resources\InventorySources\Pages;

use App\Filament\Resources\InventorySources\InventorySourceResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditInventorySource extends EditRecord
{
    protected static string $resource = InventorySourceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
