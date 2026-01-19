<?php

namespace App\Filament\Resources\Attributes\Pages;

use App\Filament\Resources\Attributes\AttributeResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

class CreateAttribute extends CreateRecord
{
    protected static string $resource = AttributeResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['creator_id'] = Auth::id();

        // Auto-increment sort_order within the attribute family
        $familyId = $data['attribute_family_id'] ?? null;
        if ($familyId) {
            $maxSort = \App\Models\Catalogue\Attribute::query()
                ->where('attribute_family_id', $familyId)
                ->max('sort_order');
            $data['sort_order'] = is_numeric($maxSort) ? $maxSort + 1 : 0;
        } else {
            $data['sort_order'] = 0;
        }

        return $data;
    }
}
