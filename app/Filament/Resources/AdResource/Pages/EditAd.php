<?php

namespace App\Filament\Resources\AdResource\Pages;

use App\Filament\Resources\AdResource\AdResource;
use Filament\Resources\Pages\EditRecord;

class EditAd extends EditRecord
{
    protected static string $resource = AdResource::class;

    protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl('index');
    }
}
