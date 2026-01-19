<?php

namespace App\Filament\Resources\Products\Schemas;

use App\Enums\ProductStatus;
use App\Filament\Resources\Products\Pages\CreateProduct;
use App\Services\ProductSkuGenerator;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class ProductForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                // Product Details Section
                Section::make('Product Details')
                    ->description('Essential product information and identification')
                    ->icon('heroicon-o-cube')
                    ->schema([
                        TextInput::make('name')
                            ->label('Product Name')
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true)
                            ->afterStateUpdated(
                                fn ($state, callable $set) => $set('slug', Str::slug($state))
                            )
                            ->placeholder('e.g., Premium Wireless Headphones')
                            ->helperText('Enter a clear, descriptive product name')
                            ->columnSpan(2),

                        TextInput::make('slug')
                            ->label('URL Slug')
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true)
                            ->placeholder('premium-wireless-headphones')
                            ->helperText('Auto-generated from name, customize if needed')
                            ->columnSpan(2),

                        TextInput::make('sku')
                            ->label('SKU')
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true)
                            ->placeholder('Auto-generated from category pattern')
                            ->helperText('Read-only on create; generated as PREFIX-UUID using category settings.')
                            ->readOnlyOn('create')
                            ->columnSpan(2),
                    ])
                    ->columns(2)
                    ->collapsible(),

                // Classification Section
                Section::make('Classification')
                    ->description('Categorize and organize your product')
                    ->icon('heroicon-o-tag')
                    ->schema([
                        Select::make('category_id')
                            ->label('Category')
                            ->relationship('category', 'name', fn ($query) => $query->where('is_active', true))
                            ->required()
                            ->searchable()
                            ->preload()
                            ->live()
                            ->afterStateUpdated(function ($state, callable $set, callable $get, Select $component) {
                                $livewire = $component->getContainer()->getLivewire();

                                if (! $livewire instanceof CreateProduct) {
                                    return;
                                }

                                $set('sku', app(ProductSkuGenerator::class)->generate($state));
                            })
                            ->placeholder('Select a category')
                            ->helperText('Primary product category')
                            ->native(false)
                            ->columnSpan(1),

                        Select::make('brand_id')
                            ->label('Brand')
                            ->relationship('brand', 'name', fn ($query) => $query->where('is_active', true))
                            ->searchable()
                            ->preload()
                            ->placeholder('Select a brand (optional)')
                            ->helperText('Product manufacturer or brand')
                            ->native(false)
                            ->columnSpan(1),

                        Select::make('attribute_family_id')
                            ->label('Attribute Family')
                            ->relationship('attributeFamily', 'name', fn ($query) => $query->where('is_active', true))
                            ->required()
                            ->searchable()
                            ->preload()
                            ->live()
                            ->afterStateUpdated(function ($state, callable $set) {
                                // Clear attribute values when family changes
                                $set('attribute_values', []);
                            })
                            ->placeholder('Select attribute family')
                            ->helperText('Determines available product attributes')
                            ->native(false)
                            ->columnSpan(2),
                    ])
                    ->columns(2)
                    ->collapsible(),

                // Product Description Section
                Section::make('Product Description')
                    ->description('Describe your product to potential customers')
                    ->icon('heroicon-o-document-text')
                    ->schema([
                        Textarea::make('short_description')
                            ->label('Short Description')
                            ->rows(3)
                            ->maxLength(500)
                            ->placeholder('A brief, compelling summary that highlights key features...')
                            ->helperText('Brief summary (max 500 characters) - appears in product listings')
                            ->columnSpanFull(),

                        Textarea::make('description')
                            ->label('Full Description')
                            ->rows(6)
                            ->placeholder('Provide detailed information about the product, including features, specifications, and benefits...')
                            ->helperText('Comprehensive product details - appears on product page')
                            ->columnSpanFull(),
                    ])
                    ->collapsible(),

                // Media Section
                Section::make('Product Media')
                    ->description('Upload high-quality product images to showcase your product')
                    ->icon('heroicon-o-photo')
                    ->schema([
                        SpatieMediaLibraryFileUpload::make('thumbnail')
                            ->collection('thumbnail')
                            ->label('Primary Thumbnail')
                            ->image()
                            ->imageEditor()
                            ->imageEditorAspectRatios([
                                null,
                                '1:1',
                                '4:3',
                                '16:9',
                            ])
                            ->maxSize(2048)
                            ->helperText('Main product image displayed in listings and search results. Recommended: Square (1:1) ratio. Max 2MB. Auto-resized to 500x500px.')
                            ->imagePreviewHeight('300')
                            ->panelLayout('grid')
                            ->removeUploadedFileButtonPosition('right')
                            ->uploadButtonPosition('left')
                            ->uploadProgressIndicatorPosition('left')
                            ->columnSpanFull(),

                        SpatieMediaLibraryFileUpload::make('images')
                            ->collection('images')
                            ->label('Product Gallery')
                            ->image()
                            ->imageEditor()
                            ->imageEditorAspectRatios([
                                null,
                                '16:9',
                                '4:3',
                                '1:1',
                            ])
                            ->multiple()
                            ->maxSize(2048)
                            ->maxFiles(10)
                            ->helperText('Additional product images showing different angles, details, and usage. Upload up to 10 images. Max 2MB each. Accepts JPEG, PNG, GIF, WebP.')
                            ->imagePreviewHeight('300')
                            ->panelLayout('grid')
                            ->removeUploadedFileButtonPosition('right')
                            ->uploadButtonPosition('left')
                            ->uploadProgressIndicatorPosition('left')
                            ->reorderable()
                            ->columnSpanFull(),
                    ])
                    ->collapsible(),

                // Pricing Section
                Section::make('Pricing')
                    ->description('Set product pricing and cost information')
                    ->icon('heroicon-o-currency-dollar')
                    ->schema([
                        TextInput::make('price')
                            ->label('Selling Price')
                            ->required()
                            ->numeric()
                            ->prefix('KES')
                            ->step(0.01)
                            ->minValue(0)
                            ->placeholder('0.00')
                            ->helperText('Current selling price')
                            ->columnSpan(1),

                        TextInput::make('compare_price')
                            ->label('Compare at Price')
                            ->numeric()
                            ->prefix('KES')
                            ->step(0.01)
                            ->minValue(0)
                            ->placeholder('0.00')
                            ->helperText('Original price (shows discount if higher than selling price)')
                            ->columnSpan(1),

                        TextInput::make('cost')
                            ->label('Cost per Item')
                            ->numeric()
                            ->prefix('KES')
                            ->step(0.01)
                            ->minValue(0)
                            ->placeholder('0.00')
                            ->helperText('Your cost (for profit margin calculations)')
                            ->columnSpan(1),
                    ])
                    ->columns(3)
                    ->collapsible(),

                // Inventory Section
                Section::make('Inventory')
                    ->description('Manage stock levels and inventory tracking')
                    ->icon('heroicon-o-cube-transparent')
                    ->schema([
                        TextInput::make('quantity')
                            ->label('Stock Quantity')
                            ->numeric()
                            ->default(0)
                            ->minValue(0)
                            ->placeholder('0')
                            ->helperText('Available stock (only for simple products without variants). If you plan to create variants, stock will be managed at the variant level.')
                            ->suffix('units')
                            ->columnSpan(1),

                        TextInput::make('low_stock_threshold')
                            ->label('Low Stock Alert')
                            ->numeric()
                            ->minValue(0)
                            ->placeholder('5')
                            ->helperText('Get notified when stock falls below this level')
                            ->suffix('units')
                            ->columnSpan(1),
                    ])
                    ->columns(2)
                    ->collapsible(),

                // Status & Visibility Section
                Section::make('Status & Visibility')
                    ->description('Control product status and visibility settings')
                    ->icon('heroicon-o-eye')
                    ->schema([
                        Select::make('status')
                            ->label('Review Status')
                            ->options([
                                ProductStatus::Draft->value => ProductStatus::Draft->label(),
                                ProductStatus::Pending->value => ProductStatus::Pending->label(),
                                ProductStatus::Approved->value => ProductStatus::Approved->label(),
                                ProductStatus::Rejected->value => ProductStatus::Rejected->label(),
                            ])
                            ->required()
                            ->default(ProductStatus::Draft->value)
                            ->native(false)
                            ->helperText('Current review workflow status')
                            ->columnSpan(1),

                        Toggle::make('published')
                            ->label('Published')
                            ->helperText('Make product visible to customers (requires approved status)')
                            ->disabled(fn ($get) => $get('status') !== ProductStatus::Approved->value)
                            ->visible(fn ($get) => $get('status') === ProductStatus::Approved->value)
                            ->inline(false)
                            ->columnSpan(1),

                        Toggle::make('is_featured')
                            ->label('Featured Product')
                            ->helperText('Highlight this product in featured sections')
                            ->inline(false)
                            ->columnSpan(1),
                    ])
                    ->columns(3)
                    ->collapsible(),

                // Review Information Section
                Section::make('Review Information')
                    ->description('Administrative notes and review feedback')
                    ->icon('heroicon-o-clipboard-document-check')
                    ->schema([
                        Textarea::make('review_notes')
                            ->label('Reviewer Notes')
                            ->rows(4)
                            ->maxLength(1000)
                            ->visible(function ($get) {
                                $status = $get('status');

                                return in_array($status, [ProductStatus::Approved->value, ProductStatus::Rejected->value]);
                            })
                            ->placeholder('Add notes about the review decision...')
                            ->helperText('Internal notes from the product reviewer')
                            ->columnSpanFull(),
                    ])
                    ->visible(function ($get) {
                        $status = $get('status');

                        return in_array($status, [ProductStatus::Approved->value, ProductStatus::Rejected->value]);
                    })
                    ->hiddenOn('create')
                    ->collapsed(),
            ]);
    }
}
