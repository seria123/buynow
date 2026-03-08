<?php

namespace App\Filament\Resources\Stores\Schemas;

use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class StoreForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Basic Information')
                    ->schema([
                        TextInput::make('name')
                            ->required()
                            ->maxLength(255)
                            ->label('Store Name')
                            ->placeholder('Enter store name'),

                        Textarea::make('description')
                            ->rows(3)
                            ->maxLength(1000)
                            ->label('Description')
                            ->placeholder('Brief description of the store'),
                    ]),

                Section::make('Contact Information')
                    ->schema([
                        TextInput::make('email')
                            ->email()
                            ->maxLength(255)
                            ->label('Email')
                            ->placeholder('store@example.com'),

                        TextInput::make('phone')
                            ->tel()
                            ->maxLength(50)
                            ->label('Phone')
                            ->placeholder('+1 234 567 8900'),
                    ]),

                Section::make('Address')
                    ->schema([
                        Textarea::make('address')
                            ->rows(2)
                            ->maxLength(500)
                            ->label('Street Address')
                            ->placeholder('Enter street address'),

                        TextInput::make('city')
                            ->maxLength(100)
                            ->label('City')
                            ->placeholder('Enter city'),

                        TextInput::make('state')
                            ->maxLength(100)
                            ->label('State/Province')
                            ->placeholder('Enter state or province'),

                        TextInput::make('country')
                            ->maxLength(100)
                            ->label('Country')
                            ->placeholder('Enter country'),

                        TextInput::make('postal_code')
                            ->maxLength(20)
                            ->label('Postal Code')
                            ->placeholder('Enter postal code'),
                    ]),

                Section::make('Location Coordinates')
                    ->description('Optional: Enter coordinates for map display in store locator')
                    ->schema([
                        TextInput::make('latitude')
                            ->numeric()
                            ->step(0.000001)
                            ->label('Latitude')
                            ->placeholder('e.g., -1.2921'),

                        TextInput::make('longitude')
                            ->numeric()
                            ->step(0.000001)
                            ->label('Longitude')
                            ->placeholder('e.g., 36.8219'),
                    ]),

                Section::make('Settings')
                    ->schema([
                        Toggle::make('is_active')
                            ->label('Active')
                            ->default(true)
                            ->helperText('Inactive stores won\'t be visible in the store locator'),
                    ]),
            ]);
    }
}
