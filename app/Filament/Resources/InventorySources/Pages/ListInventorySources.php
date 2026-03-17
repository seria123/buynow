<?php

namespace App\Filament\Resources\InventorySources\Pages;

use App\Filament\Resources\InventorySources\InventorySourceResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListInventorySources extends ListRecords
{
    protected static string $resource = InventorySourceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
