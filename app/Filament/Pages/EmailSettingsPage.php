<?php

namespace App\Filament\Pages;

use App\Settings\EmailSettings;
use Filament\Actions\Action;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section as SectionComponent;
use Filament\Schemas\Schema;

class EmailSettingsPage extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $slug = 'settings/email';

    protected static ?string $title = 'Email Settings';

    protected string $view = 'filament.pages.settings-form';

    public ?array $data = [];

    public function mount(): void
    {
        $settings = app(EmailSettings::class);
        
        $this->form->fill([
            'mailer' => $settings->mailer,
            'host' => $settings->host,
            'port' => $settings->port,
            'username' => $settings->username,
            'password' => $settings->password,
            'encryption' => $settings->encryption,
            'from_address' => $settings->from_address,
            'from_name' => $settings->from_name,
            'mailgun_domain' => $settings->mailgun_domain,
            'mailgun_secret' => $settings->mailgun_secret,
            'ses_key' => $settings->ses_key,
            'ses_secret' => $settings->ses_secret,
            'ses_region' => $settings->ses_region,
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                SectionComponent::make('SMTP Settings')
                    ->schema([
                        Select::make('mailer')
                            ->label('Mail Mailer')
                            ->options([
                                'smtp' => 'SMTP',
                                'mailgun' => 'Mailgun',
                                'ses' => 'Amazon SES',
                                'sendmail' => 'Sendmail',
                                'log' => 'Log',
                            ])
                            ->required(),
                        TextInput::make('host')
                            ->label('SMTP Host')
                            ->placeholder('smtp.mailgun.org'),
                        TextInput::make('port')
                            ->label('SMTP Port')
                            ->numeric()
                            ->placeholder('587'),
                        TextInput::make('username')
                            ->label('SMTP Username'),
                        TextInput::make('password')
                            ->label('SMTP Password')
                            ->password()
                            ->revealable(),
                        Select::make('encryption')
                            ->label('SMTP Encryption')
                            ->options([
                                'tls' => 'TLS',
                                'ssl' => 'SSL',
                            ]),
                    ])->columns(2),

                SectionComponent::make('Sender Information')
                    ->schema([
                        TextInput::make('from_address')
                            ->label('From Address')
                            ->email()
                            ->required(),
                        TextInput::make('from_name')
                            ->label('From Name')
                            ->required(),
                    ])->columns(2),

                SectionComponent::make('Mailgun Settings')
                    ->schema([
                        TextInput::make('mailgun_domain')
                            ->label('Mailgun Domain'),
                        TextInput::make('mailgun_secret')
                            ->label('Mailgun Secret')
                            ->password()
                            ->revealable(),
                    ])->columns(2),

                SectionComponent::make('AWS SES Settings')
                    ->schema([
                        TextInput::make('ses_key')
                            ->label('SES Access Key'),
                        TextInput::make('ses_secret')
                            ->label('SES Secret Key')
                            ->password()
                            ->revealable(),
                        TextInput::make('ses_region')
                            ->label('SES Region')
                            ->placeholder('us-east-1'),
                    ])->columns(3),
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
        
        $settings = app(EmailSettings::class);
        $settings->mailer = $data['mailer'];
        $settings->host = $data['host'];
        $settings->port = $data['port'];
        $settings->username = $data['username'];
        $settings->password = $data['password'];
        $settings->encryption = $data['encryption'];
        $settings->from_address = $data['from_address'];
        $settings->from_name = $data['from_name'];
        $settings->mailgun_domain = $data['mailgun_domain'];
        $settings->mailgun_secret = $data['mailgun_secret'];
        $settings->ses_key = $data['ses_key'];
        $settings->ses_secret = $data['ses_secret'];
        $settings->ses_region = $data['ses_region'];
        
        $settings->save();

        Notification::make()
            ->title('Email settings saved successfully')
            ->success()
            ->send();
    }
}
