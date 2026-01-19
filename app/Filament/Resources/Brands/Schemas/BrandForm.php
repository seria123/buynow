<?php

namespace App\Filament\Resources\Brands\Schemas;

use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class BrandForm
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
                            ->helperText('Brief description of the brand'),
                    ]),

                Section::make('Media')
                    ->schema([
                        SpatieMediaLibraryFileUpload::make('logo')
                            ->collection('logo')
                            ->image()
                            ->imageEditor()
                            ->imageEditorAspectRatios([
                                null,
                                '16:9',
                                '4:3',
                                '1:1',
                            ])
                            ->maxSize(2048)
                            ->helperText('Upload brand logo (max 2MB). Accepts JPEG, PNG, GIF, WebP, SVG.')
                            ->imagePreviewHeight('250')
                            ->panelLayout('integrated')
                            ->removeUploadedFileButtonPosition('right')
                            ->uploadButtonPosition('left')
                            ->uploadProgressIndicatorPosition('left'),
                    ]),

                Section::make('Additional Details')
                    ->schema([
                        TextInput::make('website')
                            ->url()
                            ->maxLength(255)
                            ->placeholder('https://example.com')
                            ->helperText('Brand website URL'),

                        TextInput::make('sort_order')
                            ->label('Sort Order')
                            ->numeric()
                            ->default(function () {
                                $maxSort = \App\Models\Catalogue\Brand::query()->max('sort_order');

                                return is_numeric($maxSort) ? $maxSort + 1 : 0;
                            })
                            ->required()
                            ->minValue(0)
                            ->helperText('Lower numbers appear first. Autofilled, but you can override.'),
                    ]),

                Section::make('Settings')
                    ->schema([
                        Toggle::make('is_active')
                            ->label('Active')
                            ->hiddenOn('create')
                            ->visibleOn('edit')
                            ->required()
                            ->helperText('Inactive brands won\'t be visible'),
                    ]),
            ]);
    }
}
