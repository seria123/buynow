<?php

namespace App\Filament\Resources\InventorySources\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class InventorySourceInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Basic Information')
                    ->schema([
                        TextEntry::make('code')
                            ->label('Code'),
                        
                        TextEntry::make('name')
                            ->label('Name'),
                        
                        TextEntry::make('description')
                            ->label('Description'),
                    ]),

                Section::make('Location')
                    ->schema([
                        TextEntry::make('address')
                            ->label('Address'),
                        
                        TextEntry::make('city')
                            ->label('City'),
                        
                        TextEntry::make('country')
                            ->label('Country'),
                        
                        TextEntry::make('postal_code')
                            ->label('Postal Code'),
                    ]),

                Section::make('Contact Information')
                    ->schema([
                        TextEntry::make('contact_name')
                            ->label('Contact Name'),
                        
                        TextEntry::make('contact_email')
                            ->label('Email'),
                        
                        TextEntry::make('contact_phone')
                            ->label('Phone'),
                    ]),

                Section::make('Settings')
                    ->schema([
                        TextEntry::make('is_default')
                            ->label('Default Source')
                            ->badge()
                            ->color(fn (bool $state): string => $state ? 'success' : 'gray'),
                        
                        TextEntry::make('is_active')
                            ->label('Active')
                            ->badge()
                            ->color(fn (bool $state): string => $state ? 'success' : 'danger'),
                        
                        TextEntry::make('priority')
                            ->label('Priority'),
                    ]),
            ]);
    }
}
