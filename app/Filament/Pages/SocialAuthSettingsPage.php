<?php

namespace App\Filament\Pages;

use App\Settings\SocialAuthSettings;
use Filament\Actions\Action;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section as SectionComponent;
use Filament\Schemas\Schema;

class SocialAuthSettingsPage extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $slug = 'settings/social-auth';

    protected static ?string $title = 'Social Authentication';

    protected string $view = 'filament.pages.settings-form';

    public ?array $data = [];

    public function mount(): void
    {
        $settings = app(SocialAuthSettings::class);
        
        $this->form->fill([
            'google_enabled' => $settings->google_enabled,
            'google_client_id' => $settings->google_client_id,
            'google_client_secret' => $settings->google_client_secret,
            'google_redirect' => $settings->google_redirect,
            'facebook_enabled' => $settings->facebook_enabled,
            'facebook_client_id' => $settings->facebook_client_id,
            'facebook_client_secret' => $settings->facebook_client_secret,
            'facebook_redirect' => $settings->facebook_redirect,
            'twitter_enabled' => $settings->twitter_enabled,
            'twitter_client_id' => $settings->twitter_client_id,
            'twitter_client_secret' => $settings->twitter_client_secret,
            'twitter_redirect' => $settings->twitter_redirect,
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                SectionComponent::make('Google OAuth')
                    ->schema([
                        Toggle::make('google_enabled')
                            ->label('Enable Google Login'),
                        TextInput::make('google_client_id')
                            ->label('Client ID'),
                        TextInput::make('google_client_secret')
                            ->label('Client Secret')
                            ->password(),
                        TextInput::make('google_redirect')
                            ->label('Redirect URL')
                            ->url(),
                    ])->columns(2),

                SectionComponent::make('Facebook OAuth')
                    ->schema([
                        Toggle::make('facebook_enabled')
                            ->label('Enable Facebook Login'),
                        TextInput::make('facebook_client_id')
                            ->label('App ID'),
                        TextInput::make('facebook_client_secret')
                            ->label('App Secret')
                            ->password(),
                        TextInput::make('facebook_redirect')
                            ->label('Redirect URL')
                            ->url(),
                    ])->columns(2),

                SectionComponent::make('Twitter OAuth')
                    ->schema([
                        Toggle::make('twitter_enabled')
                            ->label('Enable Twitter Login'),
                        TextInput::make('twitter_client_id')
                            ->label('Client ID'),
                        TextInput::make('twitter_client_secret')
                            ->label('Client Secret')
                            ->password(),
                        TextInput::make('twitter_redirect')
                            ->label('Redirect URL')
                            ->url(),
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
        
        $settings = app(SocialAuthSettings::class);
        $settings->google_enabled = $data['google_enabled'];
        $settings->google_client_id = $data['google_client_id'];
        $settings->google_client_secret = $data['google_client_secret'];
        $settings->google_redirect = $data['google_redirect'];
        $settings->facebook_enabled = $data['facebook_enabled'];
        $settings->facebook_client_id = $data['facebook_client_id'];
        $settings->facebook_client_secret = $data['facebook_client_secret'];
        $settings->facebook_redirect = $data['facebook_redirect'];
        $settings->twitter_enabled = $data['twitter_enabled'];
        $settings->twitter_client_id = $data['twitter_client_id'];
        $settings->twitter_client_secret = $data['twitter_client_secret'];
        $settings->twitter_redirect = $data['twitter_redirect'];
        
        $settings->save();

        Notification::make()
            ->title('Social auth settings saved successfully')
            ->success()
            ->send();
    }
}
