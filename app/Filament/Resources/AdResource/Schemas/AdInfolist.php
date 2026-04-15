<?php

namespace App\Filament\Resources\AdResource\Schemas;

use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Support\Enums\FontWeight;

class AdInfolist
{
    public static function configure(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                TextEntry::make('title')
                    ->weight(FontWeight::Bold)
                    ->size('lg'),
                TextEntry::make('subtitle')
                    ->size('sm')
                    ->color('gray'),
                TextEntry::make('description'),
                ImageEntry::make('image')
                    ->label('Ad Image')
                    ->size(200)
                    ->disk('public'),
                ImageEntry::make('mobile_image')
                    ->label('Mobile Image')
                    ->size(100)
                    ->disk('public'),
                TextEntry::make('link')
                    ->url(),
                TextEntry::make('position')
                    ->badge()
                    ->color('primary'),
                TextEntry::make('type')
                    ->badge()
                    ->color('success'),
                TextEntry::make('sort_order'),
                TextEntry::make('active')
                    ->badge()
                    ->color(fn (bool $state): string => $state ? 'success' : 'danger'),
                TextEntry::make('start_date')
                    ->dateTime(),
                TextEntry::make('end_date')
                    ->dateTime(),
                TextEntry::make('clicks'),
                TextEntry::make('impressions'),
                TextEntry::make('product.name')
                    ->label('Linked Product'),
                TextEntry::make('category.name')
                    ->label('Linked Category'),
            ]);
    }
}
