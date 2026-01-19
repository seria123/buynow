<?php

namespace App\Filament\Resources\Products\RelationManagers;

use App\Models\Catalogue\Attribute;
use App\Models\Catalogue\AttributeType;
use App\Models\Catalogue\ProductVariant;
use App\Services\ProductSkuGenerator;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\SpatieMediaLibraryImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class ProductVariantsRelationManager extends RelationManager
{
    protected static string $relationship = 'variants';

    protected static ?string $recordTitleAttribute = 'sku';

    public function form(Schema $schema): Schema
    {
        $product = $this->getOwnerRecord();
        $attributes = $product->attributeFamily?->attributes()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get() ?? collect();

        return $schema
            ->components([
                Section::make('Variant Information')
                    ->description('Basic variant details and identification')
                    ->icon('heroicon-o-cube')
                    ->schema([
                        TextInput::make('name')
                            ->label('Variant Name')
                            ->maxLength(255)
                            ->placeholder('e.g., Black / 128GB')
                            ->helperText('Enter the variant name to auto-generate SKU')
                            ->live(onBlur: true)
                            ->afterStateUpdated(function ($state, callable $set, $get) {
                                if (filled($state) && empty($get('sku'))) {
                                    $product = $this->getOwnerRecord();
                                    $skuGenerator = app(ProductSkuGenerator::class);
                                    $sku = $skuGenerator->generateForVariantFromName($product->category_id, $state);
                                    $set('sku', $sku);
                                }
                            })
                            ->columnSpanFull(),

                        TextInput::make('sku')
                            ->label('SKU')
                            ->required()
                            ->rules([
                                function ($get) {
                                    return function (string $attribute, $value, \Closure $fail) use ($get) {
                                        // Check if SKU exists in product_variants (excluding current record)
                                        $variantQuery = \App\Models\Catalogue\ProductVariant::where('sku', $value);
                                        if ($get('id')) {
                                            $variantQuery->where('id', '!=', $get('id'));
                                        }

                                        // Check if SKU exists in products table
                                        $productExists = \App\Models\Catalogue\Product::where('sku', $value)->exists();

                                        if ($variantQuery->exists() || $productExists) {
                                            $fail('This SKU is already in use by another product or variant.');
                                        }
                                    };
                                },
                            ])
                            ->maxLength(255)
                            ->placeholder('Auto-generated from variant name')
                            ->helperText('Unique Stock Keeping Unit for this variant (auto-generated from name, but you can customize)')
                            ->columnSpanFull(),

                        Toggle::make('is_default')
                            ->label('Default Variant')
                            ->helperText('Mark this as the default variant for the product')
                            ->inline(false)
                            ->columnSpan(1),

                        TextInput::make('sort_order')
                            ->label('Sort Order')
                            ->numeric()
                            ->default(0)
                            ->minValue(0)
                            ->helperText('Lower numbers appear first')
                            ->columnSpan(1),
                    ])
                    ->columns(2),

                Section::make('Variant Options')
                    ->description('Select attribute values that define this variant')
                    ->icon('heroicon-o-list-bullet')
                    ->schema([
                        Repeater::make('variantOptions')
                            ->label('Options')
                            ->relationship('variantOptions')
                            ->schema([
                                Select::make('attribute_id')
                                    ->label('Attribute')
                                    ->options(function () use ($attributes) {
                                        return $attributes->pluck('name', 'id')->toArray();
                                    })
                                    ->required()
                                    ->searchable()
                                    ->live()
                                    ->rules([
                                        function ($get) {
                                            return function (string $attribute, $value, \Closure $fail) use ($get) {
                                                if (! $value) {
                                                    return;
                                                }

                                                // Get all attribute IDs from the repeater
                                                $allOptions = $get('../../variantOptions') ?? [];
                                                $attributeIds = collect($allOptions)
                                                    ->pluck('attribute_id')
                                                    ->filter()
                                                    ->toArray();

                                                // Count occurrences of this attribute
                                                $count = count(array_filter($attributeIds, fn ($id) => $id === $value));

                                                if ($count > 1) {
                                                    $attributeName = Attribute::find($value)?->name ?? 'Attribute';
                                                    $fail("The {$attributeName} attribute can only be used once per variant.");
                                                }
                                            };
                                        },
                                    ])
                                    ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                        // Clear value when attribute changes
                                        $set('value', '');
                                    })
                                    ->helperText('Select an attribute (each attribute can only be used once per variant)'),

                                Select::make('value')
                                    ->label('Value')
                                    ->options(function ($get) {
                                        $attributeId = $get('attribute_id');
                                        if (! $attributeId) {
                                            return [];
                                        }

                                        $attribute = Attribute::find($attributeId);
                                        if (! $attribute || $attribute->type !== AttributeType::Predefined) {
                                            return [];
                                        }

                                        return $attribute->attributeValues()
                                            ->where('is_active', true)
                                            ->orderBy('sort_order')
                                            ->pluck('value', 'value')
                                            ->toArray();
                                    })
                                    ->visible(function ($get) {
                                        $attributeId = $get('attribute_id');
                                        if (! $attributeId) {
                                            return false;
                                        }
                                        $attribute = Attribute::find($attributeId);

                                        return $attribute && $attribute->type === AttributeType::Predefined;
                                    })
                                    ->searchable()
                                    ->required(function ($get) {
                                        $attributeId = $get('attribute_id');
                                        if (! $attributeId) {
                                            return false;
                                        }
                                        $attribute = Attribute::find($attributeId);

                                        return $attribute && $attribute->type === AttributeType::Predefined;
                                    })
                                    ->helperText('Select a predefined value'),

                                TextInput::make('value')
                                    ->label('Value')
                                    ->visible(function ($get) {
                                        $attributeId = $get('attribute_id');
                                        if (! $attributeId) {
                                            return false;
                                        }
                                        $attribute = Attribute::find($attributeId);

                                        return $attribute && $attribute->type === AttributeType::Manual;
                                    })
                                    ->required(function ($get) {
                                        $attributeId = $get('attribute_id');
                                        if (! $attributeId) {
                                            return false;
                                        }
                                        $attribute = Attribute::find($attributeId);

                                        return $attribute && $attribute->type === AttributeType::Manual;
                                    })
                                    ->maxLength(255)
                                    ->helperText('Enter the attribute value'),
                            ])
                            ->columns(2)
                            ->minItems(1)
                            ->defaultItems(1)
                            ->addActionLabel('Add Option')
                            ->reorderable(false)
                            ->itemLabel(fn (array $state): ?string => $state['attribute_id']
                                    ? Attribute::find($state['attribute_id'])?->name.': '.($state['value'] ?? '')
                                    : 'New Option'
                            )
                            ->helperText('Define the attribute values that make this variant unique (e.g., Color: Black, Storage: 128GB)'),
                    ])
                    ->collapsible(),

                Section::make('Pricing')
                    ->description('Optional price overrides (falls back to product price if not set)')
                    ->icon('heroicon-o-currency-dollar')
                    ->schema([
                        TextInput::make('price')
                            ->label('Price Override')
                            ->numeric()
                            ->prefix('KES')
                            ->step(0.01)
                            ->minValue(0)
                            ->placeholder('0.00')
                            ->helperText('Optional: Override product price for this variant')
                            ->columnSpan(1),

                        TextInput::make('compare_price')
                            ->label('Compare at Price')
                            ->numeric()
                            ->prefix('KES')
                            ->step(0.01)
                            ->minValue(0)
                            ->placeholder('0.00')
                            ->helperText('Optional: Original price for discount display')
                            ->columnSpan(1),

                        TextInput::make('cost')
                            ->label('Cost per Item')
                            ->numeric()
                            ->prefix('KES')
                            ->step(0.01)
                            ->minValue(0)
                            ->placeholder('0.00')
                            ->helperText('Optional: Internal cost for profit calculations')
                            ->columnSpan(1),
                    ])
                    ->columns(3)
                    ->collapsed(),

                Section::make('Inventory')
                    ->description('Stock management for this variant')
                    ->icon('heroicon-o-cube-transparent')
                    ->schema([
                        TextInput::make('quantity')
                            ->label('Stock Quantity')
                            ->required()
                            ->numeric()
                            ->default(0)
                            ->minValue(0)
                            ->placeholder('0')
                            ->helperText('Available stock for this variant')
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

                Section::make('Variant Images')
                    ->description('Optional variant-specific images (falls back to product images if not set)')
                    ->icon('heroicon-o-photo')
                    ->schema([
                        SpatieMediaLibraryFileUpload::make('thumbnail')
                            ->collection('thumbnail')
                            ->label('Variant Thumbnail')
                            ->image()
                            ->imageEditor()
                            ->imageEditorAspectRatios([
                                null,
                                '1:1',
                                '4:3',
                                '16:9',
                            ])
                            ->maxSize(2048)
                            ->helperText('Optional: Variant-specific thumbnail image. Max 2MB.')
                            ->imagePreviewHeight('300')
                            ->panelLayout('grid')
                            ->columnSpanFull(),

                        SpatieMediaLibraryFileUpload::make('images')
                            ->collection('images')
                            ->label('Variant Gallery')
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
                            ->helperText('Optional: Variant-specific gallery images. Upload up to 10 images. Max 2MB each.')
                            ->imagePreviewHeight('300')
                            ->panelLayout('grid')
                            ->reorderable()
                            ->columnSpanFull(),
                    ])
                    ->collapsed(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('sku')
            ->columns([
                SpatieMediaLibraryImageColumn::make('thumbnail')
                    ->label('Image')
                    ->collection('thumbnail')
                    ->size(50)
                    ->placeholder('No image'),

                TextColumn::make('sku')
                    ->label('SKU')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->icon('heroicon-o-hashtag')
                    ->iconColor('primary')
                    ->copyable()
                    ->copyMessage('SKU copied!')
                    ->copyMessageDuration(1500),

                TextColumn::make('display_name')
                    ->label('Variant')
                    ->getStateUsing(function (ProductVariant $record): string {
                        return $record->display_name;
                    })
                    ->searchable()
                    ->badge()
                    ->color('info')
                    ->wrap(),

                TextColumn::make('options')
                    ->label('Options')
                    ->getStateUsing(function (ProductVariant $record): string {
                        return $record->getOptionsDisplayString();
                    })
                    ->badge()
                    ->color('gray')
                    ->wrap()
                    ->toggleable(),

                TextColumn::make('effective_price')
                    ->label('Price')
                    ->getStateUsing(function (ProductVariant $record): float {
                        return $record->effective_price;
                    })
                    ->money('KES')
                    ->sortable(query: function ($query, string $direction) {
                        return $query->orderBy('price', $direction)
                            ->orderByRaw('(SELECT price FROM products WHERE products.id = product_variants.product_id)', $direction);
                    })
                    ->icon('heroicon-o-currency-dollar')
                    ->iconColor('success'),

                TextColumn::make('quantity')
                    ->label('Stock')
                    ->sortable()
                    ->badge()
                    ->color(fn ($state) => $state > 0 ? 'success' : 'danger')
                    ->icon('heroicon-o-archive-box'),

                IconColumn::make('is_default')
                    ->label('Default')
                    ->boolean()
                    ->trueIcon('heroicon-o-star')
                    ->falseIcon('heroicon-o-star')
                    ->trueColor('warning')
                    ->falseColor('gray')
                    ->alignCenter()
                    ->sortable()
                    ->tooltip(fn ($state) => $state ? 'Default variant' : 'Not default'),

                TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime('M d, Y')
                    ->sortable()
                    ->icon('heroicon-o-calendar')
                    ->iconColor('info')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                CreateAction::make()
                    ->modalWidth('7xl')
                    ->mutateFormDataUsing(function (array $data): array {
                        $data['product_id'] = $this->getOwnerRecord()->id;

                        // Auto-generate sort order if not set
                        if (! isset($data['sort_order'])) {
                            $maxSort = ProductVariant::where('product_id', $data['product_id'])
                                ->max('sort_order');
                            $data['sort_order'] = is_numeric($maxSort) ? $maxSort + 1 : 0;
                        }

                        return $data;
                    })
                    ->after(function (Model $record) {
                        // If this is the first variant and no default exists, set it as default
                        if (! $this->getOwnerRecord()->variants()->where('is_default', true)->exists()) {
                            $record->update(['is_default' => true]);
                        }
                    }),
            ])
            ->actions([
                Action::make('set_default')
                    ->label('Set as Default')
                    ->icon('heroicon-o-star')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->action(function (ProductVariant $record) {
                        DB::transaction(function () use ($record) {
                            // Unset all other defaults for this product
                            ProductVariant::where('product_id', $record->product_id)
                                ->where('id', '!=', $record->id)
                                ->update(['is_default' => false]);

                            // Set this as default
                            $record->update(['is_default' => true]);
                        });
                    })
                    ->successNotificationTitle('Variant set as default')
                    ->visible(fn (ProductVariant $record) => ! $record->is_default),

                Action::make('duplicate')
                    ->label('Duplicate')
                    ->icon('heroicon-o-document-duplicate')
                    ->color('info')
                    ->requiresConfirmation()
                    ->action(function (ProductVariant $record) {
                        DB::transaction(function () use ($record) {
                            $newVariant = $record->replicate();
                            $newVariant->sku = $record->sku.'-COPY-'.time();
                            $newVariant->is_default = false;
                            $newVariant->sort_order = ProductVariant::where('product_id', $record->product_id)
                                ->max('sort_order') + 1;
                            $newVariant->save();

                            // Duplicate variant options
                            foreach ($record->variantOptions as $option) {
                                $newOption = $option->replicate();
                                $newOption->product_variant_id = $newVariant->id;
                                $newOption->save();
                            }
                        });
                    })
                    ->successNotificationTitle('Variant duplicated'),

                EditAction::make()
                    ->modalWidth('7xl'),
                DeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    BulkAction::make('set_default')
                        ->label('Set as Default')
                        ->icon('heroicon-o-star')
                        ->color('warning')
                        ->requiresConfirmation()
                        ->action(function ($records) {
                            DB::transaction(function () use ($records) {
                                $firstRecord = $records->first();
                                if ($firstRecord) {
                                    // Unset all defaults for this product
                                    ProductVariant::where('product_id', $firstRecord->product_id)
                                        ->update(['is_default' => false]);

                                    // Set the first selected as default
                                    $firstRecord->update(['is_default' => true]);
                                }
                            });
                        })
                        ->successNotificationTitle('Default variant updated')
                        ->deselectRecordsAfterCompletion(),

                    DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateActions([
                CreateAction::make()
                    ->mutateFormDataUsing(function (array $data): array {
                        $data['product_id'] = $this->getOwnerRecord()->id;

                        return $data;
                    }),
            ])
            ->defaultSort('sort_order')
            ->reorderable('sort_order');
    }

    public static function canViewForRecord($ownerRecord, string $pageClass): bool
    {
        return filled($ownerRecord->attribute_family_id);
    }
}
