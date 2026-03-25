<?php

namespace App\Filament\Resources\WarrantyResource\Schemas;

use App\Models\Catalogue\Product;
use App\Models\Sales\Order;
use App\Models\User;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Schema;

class WarrantyForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Tabs::make('Warranty Details')
                    ->tabs([
                        Tabs\Tab::make('Basic Information')
                            ->schema([
                                Section::make('Warranty Assignment')
                                    ->description('Assign warranty to product and customer')
                                    ->schema([
                                        Select::make('product_id')
                                            ->label('Product')
                                            ->required()
                                            ->searchable()
                                            ->getSearchResultsUsing(function (string $search) {
                                                return Product::where('name', 'like', "%{$search}%")
                                                    ->limit(20)
                                                    ->pluck('name', 'id');
                                            })
                                            ->helperText('Select the product this warranty applies to'),

                                        Select::make('user_id')
                                            ->label('Customer')
                                            ->required()
                                            ->searchable()
                                            ->getSearchResultsUsing(function (string $search) {
                                                return User::where('name', 'like', "%{$search}%")
                                                    ->orWhere('email', 'like', "%{$search}%")
                                                    ->limit(20)
                                                    ->pluck('name', 'id');
                                            })
                                            ->helperText('Select the customer who owns this warranty'),

                                        Select::make('order_id')
                                            ->label('Order')
                                            ->searchable()
                                            ->getSearchResultsUsing(function (string $search) {
                                                return Order::where('id', 'like', "%{$search}%")
                                                    ->orWhere('order_number', 'like', "%{$search}%")
                                                    ->limit(20)
                                                    ->pluck('order_number', 'id');
                                            })
                                            ->helperText('Optionally link to the original order')
                                            ->nullable(),
                                    ]),

                                Section::make('Warranty Information')
                                    ->description('Enter warranty details')
                                    ->schema([
                                        Grid::make(2)
                                            ->schema([
                                                TextInput::make('warranty_number')
                                                    ->label('Warranty Number')
                                                    ->disabled()
                                                    ->helperText('Auto-generated warranty number'),

                                                Select::make('warranty_type')
                                                    ->label('Warranty Type')
                                                    ->required()
                                                    ->options([
                                                        'standard' => 'Standard Warranty',
                                                        'extended' => 'Extended Warranty',
                                                        'lifetime' => 'Lifetime Warranty',
                                                        'manufacturer' => 'Manufacturer Warranty',
                                                    ])
                                                    ->default('standard')
                                                    ->helperText('Select the type of warranty'),
                                            ]),

                                        Grid::make(2)
                                            ->schema([
                                                Select::make('status')
                                                    ->label('Status')
                                                    ->required()
                                                    ->options([
                                                        'active' => 'Active',
                                                        'expired' => 'Expired',
                                                        'claimed' => 'Claimed',
                                                        'cancelled' => 'Cancelled',
                                                    ])
                                                    ->default('active')
                                                    ->helperText('Current warranty status'),

                                                Select::make('created_by')
                                                    ->label('Created By')
                                                    ->searchable()
                                                    ->getSearchResultsUsing(function (string $search) {
                                                        return User::where('name', 'like', "%{$search}%")
                                                            ->limit(20)
                                                            ->pluck('name', 'id');
                                                    })
                                                    ->nullable()
                                                    ->helperText('Who created this warranty record'),
                                            ]),
                                    ]),
                            ]),

                        Tabs\Tab::make('Warranty Period')
                            ->schema([
                                Section::make('Coverage Period')
                                    ->description('Set the warranty start and end dates')
                                    ->schema([
                                        Grid::make(2)
                                            ->schema([
                                                DatePicker::make('start_date')
                                                    ->label('Start Date')
                                                    ->required()
                                                    ->beforeOrEqual('end_date')
                                                    ->helperText('When the warranty coverage begins'),

                                                DatePicker::make('end_date')
                                                    ->label('End Date')
                                                    ->required()
                                                    ->afterOrEqual('start_date')
                                                    ->helperText('When the warranty coverage ends'),
                                            ]),
                                    ]),

                                Section::make('Auto-expiration')
                                    ->description('Set up automatic status updates')
                                    ->schema([
                                        TextInput::make('auto_expire_days')
                                            ->label('Auto-expire after (days from start)')
                                            ->numeric()
                                            ->minValue(1)
                                            ->helperText('Optional: Automatically set status to expired after this many days')
                                            ->nullable(),
                                    ]),
                            ]),

                        Tabs\Tab::make('Terms & Instructions')
                            ->schema([
                                Section::make('Terms & Conditions')
                                    ->description('Enter warranty terms and coverage details')
                                    ->schema([
                                        Textarea::make('terms')
                                            ->label('Terms & Conditions')
                                            ->rows(5)
                                            ->placeholder('Enter the warranty terms and conditions...')
                                            ->helperText('Detailed terms of the warranty coverage'),
                                    ]),

                                Section::make('Claim Instructions')
                                    ->description('Instructions for customers to file a claim')
                                    ->schema([
                                        Textarea::make('claim_instructions')
                                            ->label('How to Claim')
                                            ->rows(4)
                                            ->placeholder('Enter step-by-step claim instructions...')
                                            ->helperText('Instructions customers will follow to make a claim'),
                                    ]),

                                Section::make('Coverage Details')
                                    ->description('Additional coverage information')
                                    ->schema([
                                        Textarea::make('coverage_details_json')
                                            ->label('Coverage Details (JSON)')
                                            ->rows(3)
                                            ->placeholder('{"parts": "Covered", "labor": "Covered"}')
                                            ->helperText('Enter coverage details as JSON'),
                                    ]),
                            ]),

                        Tabs\Tab::make('Documentation')
                            ->schema([
                                Section::make('Warranty Document')
                                    ->description('Upload warranty certificate or document')
                                    ->schema([
                                        FileUpload::make('document_path')
                                            ->label('Warranty Document')
                                            ->directory('warranty-documents')
                                            ->acceptedFileTypes(['application/pdf', 'image/*'])
                                            ->maxSize(10240)
                                            ->helperText('Upload PDF or image warranty document (max 10MB)'),
                                    ]),
                            ]),
                    ]),
            ]);
    }
}