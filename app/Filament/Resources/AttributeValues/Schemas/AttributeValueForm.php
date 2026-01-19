<?php

namespace App\Filament\Resources\AttributeValues\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class AttributeValueForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Basic Information')
                    ->schema([
                        Select::make('attribute_id')
                            ->label('Attribute')
                            ->relationship('attribute', 'name', fn ($query) => $query->where('type', 'predefined'))
                            ->required()
                            ->searchable()
                            ->preload()
                            ->helperText('Only attributes with type "Predefined" are shown')
                            ->getOptionLabelFromRecordUsing(fn ($record) => $record->name.' ('.$record->attributeFamily->name.')'),

                        TextInput::make('value')
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true)
                            ->afterStateUpdated(
                                fn ($state, callable $set) => $set('slug', Str::slug($state))
                            )
                            ->helperText('The display value (e.g., "Black", "White", "128GB")'),

                        TextInput::make('slug')
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true, modifyRuleUsing: function ($rule, $get) {
                                $attributeId = $get('attribute_id');
                                if ($attributeId) {
                                    return $rule->where('attribute_id', $attributeId);
                                }

                                return $rule;
                            })
                            ->helperText('Auto-generated from value, but you can customize it'),
                    ]),

                Section::make('Settings')
                    ->schema([
                        TextInput::make('sort_order')
                            ->label('Sort Order')
                            ->numeric()
                            ->default(function ($get) {
                                $attributeId = $get('attribute_id');
                                if (! $attributeId) {
                                    return 0;
                                }
                                $maxSort = \App\Models\Catalogue\AttributeValue::query()
                                    ->where('attribute_id', $attributeId)
                                    ->max('sort_order');

                                return is_numeric($maxSort) ? $maxSort + 1 : 0;
                            })
                            ->required()
                            ->minValue(0)
                            ->helperText('Lower numbers appear first. Autofilled, but you can override.'),

                        Toggle::make('is_active')
                            ->label('Active')
                            ->hiddenOn('create')
                            ->visibleOn('edit')
                            ->required()
                            ->helperText('Inactive values won\'t be visible'),
                    ]),
            ]);
    }
}
