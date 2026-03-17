<?php

namespace App\Filament\Resources\InventorySources\Schemas;

use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Schemas\Schema;

class InventorySourceForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([
                Section::make('Basic Information')
                    ->columns(2)
                    ->schema([
                        TextInput::make('code')
                            ->required()
                            ->unique()
                            ->label('Code')
                            ->placeholder('e.g., WH-001'),

                        TextInput::make('name')
                            ->required()
                            ->label('Name')
                            ->placeholder('e.g., Main Warehouse'),
                        
                        Textarea::make('description')
                            ->label('Description')
                            ->columnSpanFull(),
                    ]),

                Section::make('Location')
                    ->columns(2)
                    ->schema([
                        Textarea::make('address')
                            ->label('Address')
                            ->rows(2),
                        
                        TextInput::make('city')
                            ->label('City'),
                        
                        TextInput::make('country')
                            ->label('Country'),
                        
                        TextInput::make('postal_code')
                            ->label('Postal Code'),
                    ]),

                Section::make('Contact Information')
                    ->columns(3)
                    ->schema([
                        TextInput::make('contact_name')
                            ->label('Contact Name'),
                        
                        TextInput::make('contact_email')
                            ->label('Email')
                            ->email(),
                        
                        TextInput::make('contact_phone')
                            ->label('Phone'),
                    ]),

                Section::make('Settings')
                    ->columns(2)
                    ->schema([
                        Toggle::make('is_default')
                            ->label('Default Source')
                            ->helperText('This will be the primary source for order fulfillment'),
                        
                        Toggle::make('is_active')
                            ->label('Active')
                            ->helperText('Inactive sources will not be available for fulfillment'),
                        
                        TextInput::make('priority')
                            ->label('Priority')
                            ->numeric()
                            ->default(0)
                            ->helperText('Higher priority sources are preferred for fulfillment'),
                    ]),
            ]);
    }
}
