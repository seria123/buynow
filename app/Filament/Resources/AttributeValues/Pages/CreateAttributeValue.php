<?php

namespace App\Filament\Resources\AttributeValues\Pages;

use App\Filament\Resources\AttributeValues\AttributeValueResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

class CreateAttributeValue extends CreateRecord
{
    protected static string $resource = AttributeValueResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['creator_id'] = Auth::id();

        // Auto-increment sort_order within the attribute
        $attributeId = $data['attribute_id'] ?? null;
        if ($attributeId) {
            $maxSort = \App\Models\Catalogue\AttributeValue::query()
                ->where('attribute_id', $attributeId)
                ->max('sort_order');
            $data['sort_order'] = is_numeric($maxSort) ? $maxSort + 1 : 0;
        } else {
            $data['sort_order'] = 0;
        }

        return $data;
    }
}
