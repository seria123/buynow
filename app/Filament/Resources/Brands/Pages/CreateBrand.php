<?php

namespace App\Filament\Resources\Brands\Pages;

use App\Filament\Resources\Brands\BrandResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

class CreateBrand extends CreateRecord
{
    protected static string $resource = BrandResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['creator_id'] = Auth::id();

        // Auto-increment sort_order
        $maxSort = \App\Models\Catalogue\Brand::query()->max('sort_order');
        $data['sort_order'] = is_numeric($maxSort) ? $maxSort + 1 : 0;

        return $data;
    }
}
