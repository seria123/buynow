<?php

namespace App\Filament\Pages;

use App\Data\ProductSkuPatternData;
use App\Models\Catalogue\Category;
use App\Settings\ProductSkuSettings as ProductSkuSettingsConfig;
use BackedEnum;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Collection;
use UnitEnum;

class ProductSkuSettings extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-hashtag';

    protected static ?string $navigationLabel = 'SKU Patterns';

    protected static ?string $title = 'Product SKU Settings';

    protected static string|UnitEnum|null $navigationGroup = 'Settings';

    protected static bool $shouldRegisterNavigation = false;

    protected static ?int $navigationSort = 90;

    protected string $view = 'filament.pages.product-sku-settings';

    public ?array $data = [];

    public function mount(): void
    {
        /** @var ProductSkuSettingsConfig $settings */
        $settings = app(ProductSkuSettingsConfig::class);

        $this->form->fill([
            'category_patterns' => collect($settings->category_patterns)
                ->map(function ($pattern) {
                    if ($pattern instanceof ProductSkuPatternData) {
                        return $pattern->toArray();
                    }

                    return $pattern;
                })
                ->all(),
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Category Patterns')
                    ->description('Assign a SKU prefix and separator per category. A UUID suffix will be appended automatically.')
                    ->schema([
                        Repeater::make('category_patterns')
                            ->label('Patterns')
                            ->schema([
                                Select::make('category_id')
                                    ->label('Category')
                                    ->options(fn () => $this->getCategoryOptions())
                                    ->required()
                                    ->searchable()
                                    ->preload()
                                    ->native(false)
                                    ->helperText('Select the category this pattern applies to.'),
                                TextInput::make('prefix')
                                    ->label('Prefix')
                                    ->required()
                                    ->maxLength(12)
                                    ->rule('alpha_dash')
                                    ->helperText('Letters, numbers, dashes and underscores only, e.g. ELEC')
                                    ->extraInputAttributes(['style' => 'text-transform: uppercase;']),
                                TextInput::make('separator')
                                    ->label('Separator')
                                    ->maxLength(4)
                                    ->default('-')
                                    ->helperText('Defaults to a dash if left blank.'),
                            ])
                            ->columns(3)
                            ->addActionLabel('Add Pattern')
                            ->reorderable(false)
                            ->cloneable(false),
                    ]),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $state = $this->form->getState();

        $patterns = collect($state['category_patterns'] ?? [])
            ->filter(fn (array $pattern) => filled($pattern['category_id'] ?? null) && filled($pattern['prefix'] ?? null))
            ->map(function (array $pattern) {
                $separator = $pattern['separator'] ?? '-';

                return [
                    'category_id' => (string) $pattern['category_id'],
                    'prefix' => strtoupper(trim($pattern['prefix'])),
                    'separator' => filled($separator) ? $separator : '-',
                ];
            })
            ->values();

        if ($this->hasDuplicateCategories($patterns)) {
            $this->addError('data.category_patterns', 'Each category can only have one pattern.');

            return;
        }

        /** @var ProductSkuSettingsConfig $settings */
        $settings = app(ProductSkuSettingsConfig::class);
        $settings->category_patterns = $patterns->all();
        $settings->save();

        Notification::make()
            ->title('SKU settings saved')
            ->success()
            ->send();
    }

    protected function hasDuplicateCategories(Collection $patterns): bool
    {
        $ids = $patterns->pluck('category_id');

        return $ids->count() !== $ids->unique()->count();
    }

    protected function getCategoryOptions(): array
    {
        return Category::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->pluck('name', 'id')
            ->toArray();
    }
}
