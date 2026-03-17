<?php

namespace App\Filament\Resources\InventorySources\Pages;

use App\Filament\Resources\InventorySources\InventorySourceResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateInventorySource extends CreateRecord
{
    protected static string $resource = InventorySourceResource::class;
}
