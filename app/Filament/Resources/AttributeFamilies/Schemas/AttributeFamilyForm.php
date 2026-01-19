<?php

namespace App\Filament\Resources\AttributeFamilies\Schemas;

use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class AttributeFamilyForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Basic Information')
                    ->schema([
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
                            ->unique(ignoreRecord: true)
                            ->helperText('Auto-generated from name, but you can customize it'),

                        Textarea::make('description')
                            ->rows(3)
                            ->maxLength(1000)
                            ->helperText('Brief description of the attribute family'),
                    ]),

                Section::make('Settings')
                    ->schema([
                        TextInput::make('sort_order')
                            ->label('Sort Order')
                            ->numeric()
                            ->default(function () {
                                $maxSort = \App\Models\Catalogue\AttributeFamily::query()->max('sort_order');

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
                            ->helperText('Inactive attribute families won\'t be visible'),
                    ]),
            ]);
    }
}
