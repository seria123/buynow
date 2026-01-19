<?php

namespace App\Filament\Resources\AttributeFamilies\Pages;

use App\Filament\Resources\AttributeFamilies\AttributeFamilyResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditAttributeFamily extends EditRecord
{
    protected static string $resource = AttributeFamilyResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
