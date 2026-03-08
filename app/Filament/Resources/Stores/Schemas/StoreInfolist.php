<?php

namespace App\Filament\Resources\Stores\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class StoreInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Store Information')
                    ->schema([
                        TextEntry::make('name')
                            ->label('Store Name')
                            ->size('lg')
                            ->weight('bold')
                            ->icon('heroicon-o-building-storefront')
                            ->iconColor('primary'),

                        TextEntry::make('description')
                            ->label('Description')
                            ->columnSpanFull()
                            ->placeholder('No description provided'),
                    ])
                    ->columns(2),

                Section::make('Contact Information')
                    ->schema([
                        TextEntry::make('email')
                            ->label('Email')
                            ->icon('heroicon-o-envelope')
                            ->copyable()
                            ->copyMessage('Email copied!')
                            ->placeholder('No email provided'),

                        TextEntry::make('phone')
                            ->label('Phone')
                            ->icon('heroicon-o-phone')
                            ->copyable()
                            ->copyMessage('Phone copied!')
                            ->placeholder('No phone provided'),
                    ])
                    ->columns(2),

                Section::make('Address')
                    ->schema([
                        TextEntry::make('address')
                            ->label('Street Address')
                            ->columnSpanFull()
                            ->placeholder('No address provided'),

                        TextEntry::make('city')
                            ->label('City')
                            ->icon('heroicon-o-map-pin')
                            ->placeholder('No city provided'),

                        TextEntry::make('state')
                            ->label('State/Province')
                            ->placeholder('No state provided'),

                        TextEntry::make('country')
                            ->label('Country')
                            ->placeholder('No country provided'),

                        TextEntry::make('postal_code')
                            ->label('Postal Code')
                            ->placeholder('No postal code provided'),
                    ])
                    ->columns(2),

                Section::make('Location')
                    ->schema([
                        TextEntry::make('latitude')
                            ->label('Latitude')
                            ->icon('heroicon-o-map')
                            ->placeholder('Not set'),

                        TextEntry::make('longitude')
                            ->label('Longitude')
                            ->icon('heroicon-o-map')
                            ->placeholder('Not set'),
                    ])
                    ->columns(2),

                Section::make('Status & Metadata')
                    ->schema([
                        IconEntry::make('is_active')
                            ->label('Status')
                            ->boolean()
                            ->trueIcon('heroicon-o-check-circle')
                            ->falseIcon('heroicon-o-x-circle')
                            ->trueColor('success')
                            ->falseColor('danger'),

                        TextEntry::make('created_at')
                            ->label('Created At')
                            ->dateTime('M d, Y H:i')
                            ->icon('heroicon-o-calendar')
                            ->iconColor('info'),

                        TextEntry::make('updated_at')
                            ->label('Updated At')
                            ->dateTime('M d, Y H:i')
                            ->icon('heroicon-o-clock')
                            ->iconColor('warning'),
                    ])
                    ->columns(3),
            ]);
    }
}
