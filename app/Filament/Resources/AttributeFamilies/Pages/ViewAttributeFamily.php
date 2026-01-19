<?php

namespace App\Filament\Resources\AttributeFamilies\Pages;

use App\Filament\Resources\AttributeFamilies\AttributeFamilyResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewAttributeFamily extends ViewRecord
{
    protected static string $resource = AttributeFamilyResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
