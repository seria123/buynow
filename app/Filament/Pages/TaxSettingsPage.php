<?php

namespace App\Filament\Pages;

use App\Settings\TaxSettings;
use Filament\Actions\Action;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section as SectionComponent;
use Filament\Schemas\Schema;

class TaxSettingsPage extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $slug = 'settings/tax';

    protected static ?string $title = 'Tax Settings';

    protected string $view = 'filament.pages.settings-form';

    public ?array $data = [];

    public function mount(): void
    {
        $settings = app(TaxSettings::class);
        
        $this->form->fill([
            'tax_enabled' => $settings->tax_enabled,
            'tax_type' => $settings->tax_type,
            'default_tax_rate' => $settings->default_tax_rate,
            'tax_number' => $settings->tax_number,
            'price_includes_tax' => $settings->price_includes_tax,
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                SectionComponent::make('Tax Configuration')
                    ->schema([
                        Toggle::make('tax_enabled')
                            ->label('Enable Tax'),
                        Select::make('tax_type')
                            ->label('Tax Type')
                            ->options([
                                'exclusive' => 'Tax Exclusive',
                                'inclusive' => 'Tax Inclusive',
                            ]),
                        Toggle::make('price_includes_tax')
                            ->label('Prices Include Tax'),
                    ])->columns(3),

                SectionComponent::make('Tax Rates')
                    ->schema([
                        TextInput::make('default_tax_rate')
                            ->label('Default Tax Rate (%)')
                            ->numeric()
                            ->step(0.01),
                        TextInput::make('tax_number')
                            ->label('Tax/VAT Number'),
                    ])->columns(2),
            ])
            ->statePath('data');
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('save')
                ->label('Save')
                ->submit('saveSettings')
                ->color('primary'),
        ];
    }

    public function saveSettings(): void
    {
        $data = $this->form->getState();
        
        $settings = app(TaxSettings::class);
        $settings->tax_enabled = $data['tax_enabled'];
        $settings->tax_type = $data['tax_type'];
        $settings->default_tax_rate = $data['default_tax_rate'];
        $settings->tax_number = $data['tax_number'];
        $settings->price_includes_tax = $data['price_includes_tax'];
        
        $settings->save();

        Notification::make()
            ->title('Tax settings saved successfully')
            ->success()
            ->send();
    }
}
