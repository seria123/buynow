<?php

namespace App\Filament\Resources\PromotionResource\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Schema;

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
                                            ->required()
                                            ->maxLength(50)
                                            ->placeholder('e.g., SUMMER20')
                                            ->helperText('Code customers will enter at checkout')
                                            ->unique(ignoreRecord: true)
                                            ->live(onBlur: true)
                                            ->afterStateUpdated(fn ($state, callable $set) => $set('code', strtoupper($state))),
                                    ]),

                                Section::make('Discount Settings')
                                    ->description('Configure how the discount will be applied')
                                    ->schema([
                                        Select::make('type')
                                            ->label('Discount Type')
                                            ->required()
                                            ->options([
                                                'percentage' => 'Percentage (%)',
                                                'fixed' => 'Fixed Amount',
                                            ])
                                            ->reactive()
                                            ->helperText('Choose whether to apply a percentage or fixed discount'),

                                        TextInput::make('value')
                                            ->label('Discount Value')
                                            ->required()
                                            ->numeric()
                                            ->minValue(0)
                                            ->rule(function ($get) {
                                                $type = $get('type');
                                                if ($type === 'percentage') {
                                                    return function ($attribute, $value, $fail) {
                                                        if ($value > 100) {
                                                            $fail('Percentage discount cannot exceed 100%');
                                                        }
                                                    };
                                                }
                                                return null;
                                            })
                                            ->helperText(function ($get) {
                                                if ($get('type') === 'percentage') {
                                                    return 'Enter a value between 0 and 100';
                                                }
                                                return 'Enter the fixed discount amount';
                                            }),

                                        TextInput::make('minimum_order_amount')
                                            ->label('Minimum Order Amount')
                                            ->numeric()
                                            ->minValue(0)
                                            ->step(0.01)
                                            ->prefix('$')
                                            ->placeholder('0.00')
                                            ->helperText('Minimum cart total required to use this promotion (leave empty for no minimum)')
                                            ->default(null),
                                    ]),
                            ]),

                        Tabs\Tab::make('Usage & Validity')
                            ->schema([
                                Section::make('Usage Limits')
                                    ->description('Control how many times this promotion can be used')
                                    ->schema([
                                        TextInput::make('usage_limit')
                                            ->label('Usage Limit')
                                            ->integer()
                                            ->minValue(1)
                                            ->nullable()
                                            ->placeholder('e.g., 100')
                                            ->helperText('Maximum number of times this code can be used (leave empty for unlimited)'),

                                        TextInput::make('used_count')
                                            ->label('Times Used')
                                            ->integer()
                                            ->minValue(0)
                                            ->disabled()
                                            ->default(0)
                                            ->helperText('Number of times this promotion has been used'),
                                    ]),

                                Section::make('Validity Period')
                                    ->description('Set when this promotion is active')
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
                                    ]),
                            ]),
                    ]),
            ]);
    }
}