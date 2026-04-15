<?php

namespace App\Filament\Resources\AdResource\Pages;

use App\Filament\Resources\AdResource\AdResource;
use Filament\Resources\Pages\ViewRecord;

class ViewAd extends ViewRecord
{
    protected static string $resource = AdResource::class;

    public static function getRedirectUrl(): string
    {
        return static::getResource()::getUrl('index');
    }
}
