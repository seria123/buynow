<?php

namespace App\Filament\Resources\AdResource\Schemas;

use App\Models\Ad;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class AdForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('title')
                    ->required()
                    ->maxLength(255),
                TextInput::make('subtitle')
                    ->maxLength(255),
                Textarea::make('description')
                    ->rows(3),
                FileUpload::make('image')
                    ->required()
                    ->image()
                    ->disk('public')
                    ->directory('ads')
                    ->maxFiles(1),
                FileUpload::make('mobile_image')
                    ->image()
                    ->disk('public')
                    ->directory('ads')
                    ->maxFiles(1)
                    ->helperText('Optional - image specifically for mobile devices'),
                TextInput::make('link')
                    ->url()
                    ->maxLength(500),
                Select::make('position')
                    ->options([
                        'home' => 'Home Page',
                        'category' => 'Category Page',
                        'product' => 'Product Page',
                        'cart' => 'Cart Page',
                        'banner' => 'Banner Zone',
                    ])
                    ->default('home')
                    ->required(),
                Select::make('type')
                    ->options([
                        'banner' => 'Banner Ad',
                        'promotion' => 'Promotion',
                        'featured' => 'Featured Product',
                        'flash_sale' => 'Flash Sale',
                        'category' => 'Category Link',
                    ])
                    ->default('banner')
                    ->required(),
                TextInput::make('sort_order')
                    ->numeric()
                    ->default(0),
                Toggle::make('active')
                    ->default(true),
                DateTimePicker::make('start_date'),
                DateTimePicker::make('end_date'),
                Select::make('product_id')
                    ->relationship('product', 'name')
                    ->searchable()
                    ->preload()
                    ->helperText('Link to a specific product'),
                Select::make('category_id')
                    ->relationship('category', 'name')
                    ->searchable()
                    ->preload()
                    ->helperText('Link to a specific category'),
            ]);
    }
}
