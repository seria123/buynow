<?php

namespace App\Filament\Resources\Products\Pages;

use App\Enums\ProductStatus;
use App\Filament\Resources\Products\ProductResource;
use App\Models\Catalogue\Attribute;
use App\Models\Catalogue\AttributeType;
use App\Models\Catalogue\ProductVariant;
use App\Services\ProductSkuGenerator;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Pages\EditRecord;
use Filament\Schemas\Components\Section;
use Illuminate\Support\Facades\DB;

class EditProduct extends EditRecord
{
    protected static string $resource = ProductResource::class;

    protected function getHeaderActions(): array
    {
        return [
            $this->getAddVariantAction(),
            ActionGroup::make([
                ViewAction::make(),
                DeleteAction::make(),
            ])
                ->label('Actions')
                ->icon('heroicon-o-ellipsis-vertical')
                ->button(),
        ];
    }

    protected function getAddVariantAction(): Action
    {
        $product = $this->record;
        $attributes = $product->attributeFamily?->attributes()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get() ?? collect();

        return Action::make('add_variant')
            ->label('Add Variant')
            ->icon('heroicon-o-plus-circle')
            ->color('success')
            ->modalWidth('7xl')
            ->form([
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
                            ->afterStateUpdated(function ($state, callable $set, $get) use ($product) {
                                if (filled($state) && empty($get('sku'))) {
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
                                function () {
                                    return function (string $attribute, $value, \Closure $fail) {
                                        // Check if SKU exists in product_variants
                                        $variantExists = ProductVariant::where('sku', $value)->exists();

                                        // Check if SKU exists in products table
                                        $productExists = \App\Models\Catalogue\Product::where('sku', $value)->exists();

                                        if ($variantExists || $productExists) {
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
                            ->default(function () use ($product) {
                                $maxSort = ProductVariant::where('product_id', $product->id)
                                    ->max('sort_order');

                                return is_numeric($maxSort) ? $maxSort + 1 : 0;
                            })
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
                                    ->afterStateUpdated(function ($state, callable $set) {
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
                            ->itemLabel(
                                fn (array $state): ?string => $state['attribute_id']
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
            ])
            ->action(function (array $data) use ($product) {
                DB::transaction(function () use ($data, $product) {
                    // Create the variant
                    $variant = ProductVariant::create([
                        'product_id' => $product->id,
                        'sku' => $data['sku'],
                        'name' => $data['name'] ?? null,
                        'price' => $data['price'] ?? null,
                        'compare_price' => $data['compare_price'] ?? null,
                        'cost' => $data['cost'] ?? null,
                        'quantity' => $data['quantity'] ?? 0,
                        'low_stock_threshold' => $data['low_stock_threshold'] ?? null,
                        'is_default' => $data['is_default'] ?? false,
                        'sort_order' => $data['sort_order'] ?? 0,
                    ]);

                    // Create variant options
                    if (isset($data['variantOptions']) && is_array($data['variantOptions'])) {
                        foreach ($data['variantOptions'] as $option) {
                            $variant->variantOptions()->create([
                                'attribute_id' => $option['attribute_id'],
                                'value' => $option['value'],
                            ]);
                        }
                    }

                    // Handle default variant logic
                    if ($variant->is_default) {
                        ProductVariant::where('product_id', $product->id)
                            ->where('id', '!=', $variant->id)
                            ->update(['is_default' => false]);
                    } elseif (! $product->variants()->where('is_default', true)->exists()) {
                        $variant->update(['is_default' => true]);
                    }
                });
            })
            ->successNotificationTitle('Variant created successfully')
            ->after(function () {
                // Refresh the page to update relation managers
                $this->redirect($this->getResource()::getUrl('edit', ['record' => $this->record]));
            })
            ->visible(fn () => filled($product->attribute_family_id));
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Check if status is changing to Pending
        $oldStatus = $this->record->status;
        $newStatus = ProductStatus::from($data['status'] ?? $oldStatus->value);

        // If status is changing to Pending (from Draft or Rejected), send notifications
        if ($newStatus === ProductStatus::Pending && $oldStatus !== ProductStatus::Pending) {
            // Store flag to send notification after save
            $this->shouldNotifyAdmins = true;
        }

        return $data;
    }

    protected function afterSave(): void
    {
        // Send notifications if status changed to Pending
        if (isset($this->shouldNotifyAdmins) && $this->shouldNotifyAdmins) {
            $this->record->refresh();
            $this->record->notifyAdminsForApproval();
        }
    }
}
