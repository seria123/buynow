<?php

namespace App\Filament\Resources\AttributeFamilies\Pages;

use App\Filament\Resources\AttributeFamilies\AttributeFamilyResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListAttributeFamilies extends ListRecords
{
    protected static string $resource = AttributeFamilyResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
