<?php

namespace App\Filament\Resources\Categories\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Enums\IconSize;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Str;

class CategoryForm
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

                        Select::make('icon')
                            ->label('Icon')
                            ->options(self::heroiconOptions())
                            ->searchable()
                            ->allowHtml()
                            ->optionsLimit(1000)
                            ->nullable()
                            ->placeholder('No icon')
                            ->native(false)
                            ->helperText('Browse the full Heroicons library with inline previews.')
                            ->columnSpanFull(),
                    ]),
                // ->columns(2),

                Section::make('Hierarchy & Settings')
                    ->schema([
                        Select::make('parent_id')
                            ->label('Parent Category')
                            ->relationship('parent', 'name', fn ($query) => $query->where('is_active', true))
                            ->searchable()
                            ->preload()
                            ->placeholder('None (Top Level)')
                            ->helperText('Select a parent category to create a subcategory (only active categories shown)'),

                        TextInput::make('sort_order')
                            ->label('Sort Order')
                            ->numeric()
                            ->default(function ($get) {
                                $parentId = $get('parent_id');
                                $query = \App\Models\Catalogue\Category::query();
                                if (! empty($parentId)) {
                                    $query->where('parent_id', $parentId);
                                } else {
                                    $query->whereNull('parent_id');
                                }
                                $maxSort = $query->max('sort_order');

                                return is_numeric($maxSort) ? $maxSort + 1 : 0;
                            })
                            ->required()
                            ->minValue(0)
                            ->helperText('Lower numbers appear first. Autofilled, but you can override.'),

                        Toggle::make('is_active')
                            ->label('Active')
                            // ->default(true)
                            ->hiddenOn('create')
                            ->visibleOn('edit')
                            ->required()
                            ->helperText('Inactive categories won\'t be visible'),
                    ]),
            ]);
    }

    /**
     * Build the available Heroicon options.
     *
     * @return array<string, string>
     */
    private static function heroiconOptions(): array
    {
        return collect(Heroicon::cases())
            ->groupBy(fn (Heroicon $icon) => str_starts_with($icon->value, 'o-') ? 'Outlined' : 'Solid')
            ->map(fn ($icons) => $icons
                ->sortBy(fn (Heroicon $icon) => Str::headline(str_replace('Outlined', '', $icon->name)))
                ->mapWithKeys(fn (Heroicon $icon) => [
                    $icon->value => self::renderIconOption($icon),
                ])
                ->toArray()
            )
            ->toArray();
    }

    private static function renderIconOption(Heroicon $icon): string
    {
        $variantLabel = str_starts_with($icon->value, 'o-') ? 'Outlined' : 'Solid';
        $label = Str::headline(str_replace('Outlined', '', $icon->name));
        $iconIdentifier = $icon->getIconForSize(IconSize::Medium);
        $iconSvg = Blade::render(
            '<x-filament::icon :icon="$icon" class="w-4 h-4 text-gray-600" />',
            ['icon' => $iconIdentifier]
        );

        return sprintf(
            '<span class="flex items-center gap-2">
                <span class="p-1.5 rounded-lg bg-gray-50 ring-1 ring-gray-200">%s</span>
                <span class="flex flex-col text-left">
                    <span class="text-sm font-medium text-gray-900">%s</span>
                    <span class="text-xs text-gray-500">%s</span>
                </span>
            </span>',
            trim($iconSvg),
            e($label),
            e($variantLabel)
        );
    }
}
