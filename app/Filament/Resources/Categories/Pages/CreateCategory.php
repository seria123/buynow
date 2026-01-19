<?php

namespace App\Filament\Resources\Categories\Pages;

use App\Filament\Resources\Categories\CategoryResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

class CreateCategory extends CreateRecord
{
    protected static string $resource = CategoryResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['creator_id'] = Auth::id();

        // Auto-increment sort_order for parent or child
        $query = \App\Models\Catalogue\Category::query();
        if (! empty($data['parent_id'])) {
            $query->where('parent_id', $data['parent_id']);
        } else {
            $query->whereNull('parent_id');
        }
        $maxSort = $query->max('sort_order');
        $data['sort_order'] = is_numeric($maxSort) ? $maxSort + 1 : 0;

        return $data;
    }
}
