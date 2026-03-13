<?php

namespace App\Filament\Pages;

use App\Settings\PaymentSettings;
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

class PaymentSettingsPage extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $slug = 'settings/payment';

    protected static ?string $title = 'Payment Settings';

    protected string $view = 'filament.pages.settings-form';

    public ?array $data = [];

    public function mount(): void
    {
        $settings = app(PaymentSettings::class);
        
        $this->form->fill([
            'mpesa_enabled' => $settings->mpesa_enabled,
            'mpesa_environment' => $settings->mpesa_environment,
            'mpesa_consumer_key' => $settings->mpesa_consumer_key,
            'mpesa_consumer_secret' => $settings->mpesa_consumer_secret,
            'mpesa_shortcode' => $settings->mpesa_shortcode,
            'mpesa_passkey' => $settings->mpesa_passkey,
            'mpesa_callback_url' => $settings->mpesa_callback_url,
            'stripe_enabled' => $settings->stripe_enabled,
            'stripe_key' => $settings->stripe_key,
            'stripe_secret' => $settings->stripe_secret,
            'stripe_webhook_secret' => $settings->stripe_webhook_secret,
            'paypal_enabled' => $settings->paypal_enabled,
            'paypal_client_id' => $settings->paypal_client_id,
            'paypal_client_secret' => $settings->paypal_client_secret,
            'paypal_mode' => $settings->paypal_mode,
            'cod_enabled' => $settings->cod_enabled,
            'cod_instructions' => $settings->cod_instructions,
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                SectionComponent::make('M-Pesa Settings')
                    ->schema([
                        Toggle::make('mpesa_enabled')
                            ->label('Enable M-Pesa'),
                        Select::make('mpesa_environment')
                            ->label('Environment')
                            ->options([
                                'sandbox' => 'Sandbox',
                                'production' => 'Production',
                            ]),
                        TextInput::make('mpesa_consumer_key')
                            ->label('Consumer Key'),
                        TextInput::make('mpesa_consumer_secret')
                            ->label('Consumer Secret')
                            ->password(),
                        TextInput::make('mpesa_shortcode')
                            ->label('Shortcode'),
                        TextInput::make('mpesa_passkey')
                            ->label('Passkey')
                            ->password(),
                        TextInput::make('mpesa_callback_url')
                            ->label('Callback URL')
                            ->url(),
                    ])->columns(2),

                SectionComponent::make('Stripe Settings')
                    ->schema([
                        Toggle::make('stripe_enabled')
                            ->label('Enable Stripe'),
                        TextInput::make('stripe_key')
                            ->label('Publishable Key'),
                        TextInput::make('stripe_secret')
                            ->label('Secret Key')
                            ->password(),
                        TextInput::make('stripe_webhook_secret')
                            ->label('Webhook Secret')
                            ->password(),
                    ])->columns(2),

                SectionComponent::make('PayPal Settings')
                    ->schema([
                        Toggle::make('paypal_enabled')
                            ->label('Enable PayPal'),
                        TextInput::make('paypal_client_id')
                            ->label('Client ID'),
                        TextInput::make('paypal_client_secret')
                            ->label('Client Secret')
                            ->password(),
                        Select::make('paypal_mode')
                            ->label('Mode')
                            ->options([
                                'sandbox' => 'Sandbox',
                                'live' => 'Live',
                            ]),
                    ])->columns(2),

                SectionComponent::make('Cash on Delivery')
                    ->schema([
                        Toggle::make('cod_enabled')
                            ->label('Enable Cash on Delivery'),
                        Textarea::make('cod_instructions')
                            ->label('COD Instructions')
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
        
        $settings = app(PaymentSettings::class);
        $settings->mpesa_enabled = $data['mpesa_enabled'];
        $settings->mpesa_environment = $data['mpesa_environment'];
        $settings->mpesa_consumer_key = $data['mpesa_consumer_key'];
        $settings->mpesa_consumer_secret = $data['mpesa_consumer_secret'];
        $settings->mpesa_shortcode = $data['mpesa_shortcode'];
        $settings->mpesa_passkey = $data['mpesa_passkey'];
        $settings->mpesa_callback_url = $data['mpesa_callback_url'];
        $settings->stripe_enabled = $data['stripe_enabled'];
        $settings->stripe_key = $data['stripe_key'];
        $settings->stripe_secret = $data['stripe_secret'];
        $settings->stripe_webhook_secret = $data['stripe_webhook_secret'];
        $settings->paypal_enabled = $data['paypal_enabled'];
        $settings->paypal_client_id = $data['paypal_client_id'];
        $settings->paypal_client_secret = $data['paypal_client_secret'];
        $settings->paypal_mode = $data['paypal_mode'];
        $settings->cod_enabled = $data['cod_enabled'];
        $settings->cod_instructions = $data['cod_instructions'];
        
        $settings->save();

        Notification::make()
            ->title('Payment settings saved successfully')
            ->success()
            ->send();
    }
}
