<?php

namespace App\Filament\Resources\Attributes\Schemas;

use App\Models\Catalogue\AttributeType;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class AttributeForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Basic Information')
                    ->schema([
                        Select::make('attribute_family_id')
                            ->label('Attribute Family')
                            ->relationship('attributeFamily', 'name')
                            ->required()
                            ->searchable()
                            ->preload()
                            ->helperText('Select the attribute family this attribute belongs to'),

                        TextInput::make('name')
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true)
                            ->afterStateUpdated(
                                fn ($state, callable $set) => $set('slug', Str::slug($state))
                            ),

                        TextInput::make('slug')
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true, modifyRuleUsing: function ($rule, $get) {
                                return $rule->where('attribute_family_id', $get('attribute_family_id'));
                            })
                            ->helperText('Auto-generated from name, but you can customize it'),

                        Select::make('type')
                            ->label('Type')
                            ->options([
                                AttributeType::Predefined->value => AttributeType::Predefined->label(),
                                AttributeType::Manual->value => AttributeType::Manual->label(),
                            ])
                            ->required()
                            ->default(AttributeType::Predefined->value)
                            ->native(false)
                            ->live()
                            ->formatStateUsing(function ($state) {
                                if ($state instanceof AttributeType) {
                                    return $state->value;
                                }

                                return $state ?? AttributeType::Predefined->value;
                            })
                            ->dehydrateStateUsing(function ($state) {
                                if ($state instanceof AttributeType) {
                                    return $state->value;
                                }

                                return $state ?? AttributeType::Predefined->value;
                            })
                            ->helperText('Predefined: Has predefined values (e.g., Color → Black, White). Manual: User enters value (e.g., Weight, Dimensions)'),

                        Textarea::make('description')
                            ->rows(3)
                            ->maxLength(1000)
                            ->helperText('Brief description of the attribute'),
                    ]),

                Section::make('Settings')
                    ->schema([
                        TextInput::make('sort_order')
                            ->label('Sort Order')
                            ->numeric()
                            ->default(function ($get) {
                                $familyId = $get('attribute_family_id');
                                if (! $familyId) {
                                    return 0;
                                }
                                $maxSort = \App\Models\Catalogue\Attribute::query()
                                    ->where('attribute_family_id', $familyId)
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
                            ->helperText('Inactive attributes won\'t be visible'),
                    ]),
            ]);
    }
}
