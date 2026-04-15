<?php

namespace App\Filament\Resources\PromotionResource\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Repeater;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Schema;
use App\Models\Catalogue\Product;

class PromotionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Tabs::make('Steps')
                    ->tabs([
                        Tabs\Tab::make('Basic Information')
                            ->schema([
                                Section::make('Promotion Details')
                                    ->description('Enter the basic information for this promotion')
                                    ->schema([
                                        TextInput::make('name')
                                            ->label('Promotion Name')
                                            ->required()
                                            ->maxLength(255)
                                            ->placeholder('e.g., Summer Sale 2024')
                                            ->helperText('Internal name for this promotion'),

                                        TextInput::make('code')
                                            ->label('Promo Code')
                                            ->maxLength(50)
                                            ->placeholder('e.g., SUMMER20')
                                            ->helperText('Leave empty for automatic promotions, enter code for coupon-based')
                                            ->unique(ignoreRecord: true)
                                            ->live(onBlur: true)
                                            ->afterStateUpdated(fn ($state, callable $set) => $set('code', $state ? strtoupper($state) : null)),

                                        Select::make('promotion_type')
                                            ->label('Promotion Type')
                                            ->required()
                                            ->options([
                                                'percentage' => 'Percentage Discount (e.g., 10% off)',
                                                'fixed' => 'Fixed Amount Discount (e.g., KSh 500 off)',
                                                'buy_one_get_one' => 'Buy 1 Get 1',
                                                'free_shipping' => 'Free Shipping',
                                                'bundle' => 'Bundle Deal',
                                                'flash_sale' => 'Flash Sale',
                                            ])
                                            ->reactive()
                                            ->default('percentage')
                                            ->helperText('Select the type of promotion'),
                                    ]),

                                Section::make('Discount Value')
                                    ->description('Set the discount value')
                                    ->schema([
                                        TextInput::make('value')
                                            ->label('Discount Value')
                                            ->numeric()
                                            ->minValue(0)
                                            ->step(0.01)
                                            ->placeholder(function (callable $get) {
                                                $type = $get('promotion_type');
                                                if ($type === 'percentage') {
                                                    return 'e.g., 20';
                                                }
                                                return 'e.g., 500';
                                            })
                                            ->helperText(function (callable $get) {
                                                $type = $get('promotion_type');
                                                if ($type === 'percentage') {
                                                    return 'Enter percentage (0-100)';
                                                }
                                                return 'Enter fixed amount in KSh';
                                            })
                                            ->visible(fn (callable $get) => in_array($get('promotion_type'), ['percentage', 'fixed']))
                                            ->required(fn (callable $get) => in_array($get('promotion_type'), ['percentage', 'fixed'])),

                                        TextInput::make('minimum_order_amount')
                                            ->label('Minimum Order Amount')
                                            ->numeric()
                                            ->minValue(0)
                                            ->step(0.01)
                                            ->prefix('KSh ')
                                            ->placeholder('0.00')
                                            ->helperText('Minimum cart total required to use this promotion (leave empty for no minimum)')
                                            ->default(null),
                                    ]),
                            ]),

                        Tabs\Tab::make('Application Rules')
                            ->schema([
                                Section::make('Which products does this apply to?')
                                    ->description('Configure which products or categories this promotion applies to')
                                    ->schema([
                                        Select::make('apply_to')
                                            ->label('Apply To')
                                            ->required()
                                            ->options([
                                                'all' => 'All Products',
                                                'category' => 'Specific Category',
                                                'products' => 'Specific Products',
                                                'customer_group' => 'Specific Customer Group',
                                            ])
                                            ->reactive()
                                            ->default('all')
                                            ->helperText('Choose which products this promotion applies to'),

                                        Select::make('category_id')
                                            ->label('Category')
                                            ->relationship('category', 'name')
                                            ->nullable()
                                            ->searchable()
                                            ->preload()
                                            ->visible(fn (callable $get) => $get('apply_to') === 'category')
                                            ->helperText('Select the category this promotion applies to'),

                                        TextInput::make('product_ids')
                                            ->label('Product IDs')
                                            ->placeholder('Enter product IDs separated by commas')
                                            ->helperText('Enter product UUIDs separated by commas (e.g., id1,id2,id3)')
                                            ->visible(fn (callable $get) => $get('apply_to') === 'products')
                                            ->dehydrateStateUsing(fn ($state) => $state ? array_map('trim', explode(',', $state)) : []),

                                        Select::make('customer_group_id')
                                            ->label('Customer Group')
                                            ->relationship('customerGroup', 'name')
                                            ->nullable()
                                            ->searchable()
                                            ->preload()
                                            ->visible(fn (callable $get) => $get('apply_to') === 'customer_group')
                                            ->helperText('Select the customer group this promotion applies to'),
                                    ]),

                                Section::make('Buy 1 Get 1 Settings')
                                    ->description('Configure Buy 1 Get 1 promotions')
                                    ->schema([
                                        Grid::make(3)
                                            ->schema([
                                                TextInput::make('buy_quantity')
                                                    ->label('Buy Quantity')
                                                    ->numeric()
                                                    ->minValue(1)
                                                    ->default(1)
                                                    ->placeholder('1'),

                                                TextInput::make('get_quantity')
                                                    ->label('Get Quantity')
                                                    ->numeric()
                                                    ->minValue(1)
                                                    ->default(1)
                                                    ->placeholder('1'),

                                                TextInput::make('minimum_quantity')
                                                    ->label('Min. Quantity to Qualify')
                                                    ->numeric()
                                                    ->minValue(1)
                                                    ->placeholder('e.g., 2'),
                                            ]),
                                    ])
                                    ->visible(fn (callable $get) => $get('promotion_type') === 'buy_one_get_one'),

                                Section::make('Bundle Settings')
                                    ->description('Configure bundle deals')
                                    ->schema([
                                        TextInput::make('bundle_product_ids')
                                            ->label('Bundle Product IDs')
                                            ->placeholder('Enter product IDs for the bundle')
                                            ->helperText('Enter product UUIDs separated by commas')
                                            ->dehydrateStateUsing(fn ($state) => $state ? array_map('trim', explode(',', $state)) : []),

                                        TextInput::make('bundle_discount_percentage')
                                            ->label('Bundle Discount %')
                                            ->numeric()
                                            ->minValue(0)
                                            ->maxValue(100)
                                            ->placeholder('e.g., 20')
                                            ->helperText('Discount percentage for the bundle'),
                                    ])
                                    ->visible(fn (callable $get) => $get('promotion_type') === 'bundle'),

                                Section::make('Flash Sale Settings')
                                    ->description('Configure flash sale options')
                                    ->schema([
                                        Toggle::make('is_flash_sale')
                                            ->label('This is a Flash Sale')
                                            ->helperText('Enable for time-limited flash sales'),

                                        TextInput::make('flash_sale_duration_minutes')
                                            ->label('Duration (minutes)')
                                            ->numeric()
                                            ->minValue(1)
                                            ->placeholder('e.g., 120 for 2 hours')
                                            ->visible(fn (callable $get) => $get('is_flash_sale')),
                                    ])
                                    ->visible(fn (callable $get) => $get('promotion_type') === 'flash_sale'),
                            ]),

                        Tabs\Tab::make('Usage & Validity')
                            ->schema([
                                Section::make('Usage Limits')
                                    ->description('Control how many times this promotion can be used')
                                    ->schema([
                                        Grid::make(2)
                                            ->schema([
                                                TextInput::make('usage_limit')
                                                    ->label('Total Usage Limit')
                                                    ->integer()
                                                    ->minValue(1)
                                                    ->nullable()
                                                    ->placeholder('e.g., 1000')
                                                    ->helperText('Maximum number of times this code can be used (leave empty for unlimited)'),

                                                TextInput::make('max_uses_per_user')
                                                    ->label('Per User Limit')
                                                    ->integer()
                                                    ->minValue(0)
                                                    ->default(0)
                                                    ->placeholder('e.g., 1')
                                                    ->helperText('Maximum uses per user (0 = unlimited)'),
                                            ]),

                                        TextInput::make('used_count')
                                            ->label('Times Used')
                                            ->integer()
                                            ->minValue(0)
                                            ->disabled()
                                            ->default(0)
                                            ->helperText('Number of times this promotion has been used'),
                                    ]),

                                Section::make('Validity Period')
                                    ->description('Set when the promotion is active')
                                    ->schema([
                                        Grid::make(2)
                                            ->schema([
                                                DatePicker::make('starts_at')
                                                    ->label('Start Date')
                                                    ->nullable()
                                                    ->beforeOrEqual('expires_at')
                                                    ->helperText('When the promotion becomes active (leave empty for immediate)'),

                                                DatePicker::make('expires_at')
                                                    ->label('End Date')
                                                    ->nullable()
                                                    ->afterOrEqual('starts_at')
                                                    ->helperText('When the promotion expires'),
                                            ]),
                                    ]),

                                Section::make('Priority')
                                    ->description('Set priority when multiple promotions apply')
                                    ->schema([
                                        TextInput::make('priority')
                                            ->label('Priority')
                                            ->integer()
                                            ->minValue(0)
                                            ->default(0)
                                            ->placeholder('0')
                                            ->helperText('Higher priority promotions are applied first (0 = lowest, 100 = highest)'),
                                    ]),
                            ]),

                        Tabs\Tab::make('Products')
                            ->schema([
                                Section::make('Assign Products')
                                    ->description('Select products to include in this promotion')
                                    ->schema([
                                        Select::make('products')
                                            ->label('Products')
                                            ->relationship('products', 'name')
                                            ->multiple()
                                            ->searchable()
                                            ->preload()
                                            ->helperText('Select products to include in this promotion'),

                                        Repeater::make('productPromotions')
                                            ->label('Product-Specific Settings')
                                            ->schema([
                                                Select::make('product_id')
                                                    ->label('Product')
                                                    ->options(Product::pluck('name', 'id'))
                                                    ->searchable()
                                                    ->required(),

                                                TextInput::make('discounted_price')
                                                    ->label('Discounted Price')
                                                    ->numeric()
                                                    ->minValue(0)
                                                    ->step(0.01)
                                                    ->prefix('KSh ')
                                                    ->placeholder('Leave empty to auto-calculate'),

                                                TextInput::make('stock_limit')
                                                    ->label('Stock Limit')
                                                    ->integer()
                                                    ->minValue(0)
                                                    ->placeholder('Leave empty for unlimited')
                                                    ->helperText('Limited stock for flash sales'),
                                            ])
                                            ->columns(3)
                                            ->visible(fn (callable $get) => $get('apply_to') === 'products'),
                                    ]),
                            ]),

                        Tabs\Tab::make('Status')
                            ->schema([
                                Section::make('Promotion Status')
                                    ->description('Enable or disable this promotion')
                                    ->schema([
                                        Toggle::make('is_active')
                                            ->label('Active')
                                            ->default(true)
                                            ->helperText('Enable to make this promotion available to customers'),

                                        TextInput::make('description')
                                            ->label('Description')
                                            ->maxLength(500)
                                            ->placeholder('Enter a description for this promotion')
                                            ->helperText('Optional description shown to customers'),
                                    ]),
                            ]),
                    ]),
            ]);
    }
}