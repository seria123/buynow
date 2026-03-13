<?php

namespace App\Filament\Pages;

use App\Settings\GeneralSettings;
use Filament\Actions\Action;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section as SectionComponent;
use Filament\Schemas\Schema;

class GeneralSettingsPage extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $slug = 'settings/general';

    protected static ?string $title = 'General Settings';

    protected string $view = 'filament.pages.settings-form';

    public ?array $data = [];

    public function mount(): void
    {
        $settings = app(GeneralSettings::class);
        
        $this->form->fill([
            'site_name' => $settings->site_name,
            'site_email' => $settings->site_email,
            'site_logo' => $settings->site_logo,
            'site_favicon' => $settings->site_favicon,
            'currency' => $settings->currency,
            'currency_symbol' => $settings->currency_symbol,
            'timezone' => $settings->timezone,
            'locale' => $settings->locale,
            'date_format' => $settings->date_format,
            'time_format' => $settings->time_format,
            'maintenance_mode' => $settings->maintenance_mode,
            'maintenance_message' => $settings->maintenance_message,
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                SectionComponent::make('Site Information')
                    ->schema([
                        TextInput::make('site_name')
                            ->label('Site Name')
                            ->required(),
                        TextInput::make('site_email')
                            ->label('Site Email')
                            ->email()
                            ->required(),
                        TextInput::make('site_logo')
                            ->label('Site Logo URL')
                            ->url(),
                        TextInput::make('site_favicon')
                            ->label('Site Favicon URL')
                            ->url(),
                    ])->columns(2),

                SectionComponent::make('Currency & Locale')
                    ->schema([
                        TextInput::make('currency')
                            ->label('Currency Code')
                            ->placeholder('USD')
                            ->required(),
                        TextInput::make('currency_symbol')
                            ->label('Currency Symbol')
                            ->placeholder('$')
                            ->required(),
                        Select::make('timezone')
                            ->label('Timezone')
                            ->options([
                                'Africa/Nairobi' => 'Africa/Nairobi (EAT)',
                                'Africa/Lagos' => 'Africa/Lagos (WAT)',
                                'Africa/Cairo' => 'Africa/Cairo (EET)',
                                'Africa/Johannesburg' => 'Africa/Johannesburg (SAST)',
                                'UTC' => 'UTC',
                                'Europe/London' => 'Europe/London (GMT)',
                                'America/New_York' => 'America/New_York (EST)',
                                'America/Los_Angeles' => 'America/Los_Angeles (PST)',
                            ])
                            ->required(),
                        Select::make('locale')
                            ->label('Locale')
                            ->options([
                                'en' => 'English',
                                'sw' => 'Swahili',
                            ])
                            ->required(),
                    ])->columns(2),

                SectionComponent::make('Date & Time Format')
                    ->schema([
                        TextInput::make('date_format')
                            ->label('Date Format')
                            ->placeholder('Y-m-d')
                            ->required(),
                        TextInput::make('time_format')
                            ->label('Time Format')
                            ->placeholder('H:i:s')
                            ->required(),
                    ])->columns(2),

                SectionComponent::make('Maintenance Mode')
                    ->schema([
                        Toggle::make('maintenance_mode')
                            ->label('Enable Maintenance Mode'),
                        Textarea::make('maintenance_message')
                            ->label('Maintenance Message')
                            ->rows(3),
                    ]),
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
        
        $settings = app(GeneralSettings::class);
        $settings->site_name = $data['site_name'];
        $settings->site_email = $data['site_email'];
        $settings->site_logo = $data['site_logo'];
        $settings->site_favicon = $data['site_favicon'];
        $settings->currency = $data['currency'];
        $settings->currency_symbol = $data['currency_symbol'];
        $settings->timezone = $data['timezone'];
        $settings->locale = $data['locale'];
        $settings->date_format = $data['date_format'];
        $settings->time_format = $data['time_format'];
        $settings->maintenance_mode = $data['maintenance_mode'];
        $settings->maintenance_message = $data['maintenance_message'];
        
        $settings->save();

        Notification::make()
            ->title('Settings saved successfully')
            ->success()
            ->send();
    }
}
