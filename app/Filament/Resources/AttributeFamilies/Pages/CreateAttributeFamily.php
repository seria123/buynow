<?php

namespace App\Filament\Resources\AttributeFamilies\Pages;

use App\Filament\Resources\AttributeFamilies\AttributeFamilyResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

class CreateAttributeFamily extends CreateRecord
{
    protected static string $resource = AttributeFamilyResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['creator_id'] = Auth::id();

        // Auto-increment sort_order
        $maxSort = \App\Models\Catalogue\AttributeFamily::query()->max('sort_order');
        $data['sort_order'] = is_numeric($maxSort) ? $maxSort + 1 : 0;

        return $data;
    }
}
