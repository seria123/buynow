<?php

namespace App\Filament\Pages;

use BackedEnum;
use Filament\Pages\Page;

class Settings extends Page
{
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected string $view = 'filament.pages.settings';

    protected static ?string $title = 'Settings';

    protected static bool $shouldRegisterNavigation = false;

    public function getHeading(): string
    {
        return 'Settings';
    }

    public function getSubheading(): ?string
    {
        return 'Manage your application settings';
    }

    public function getSettingsSections(): array
    {
        return [
            [
                'title' => 'General Settings',
                'description' => 'Configure site name, currency, timezone, and other general options.',
                'icon' => 'heroicon-o-globe-alt',
                'url' => GeneralSettingsPage::getUrl(),
                'color' => 'primary',
                'badge' => 'Site',
            ],
            [
                'title' => 'Email Settings',
                'description' => 'Configure SMTP settings, mailgun, or SES for sending emails.',
                'icon' => 'heroicon-o-envelope',
                'url' => EmailSettingsPage::getUrl(),
                'color' => 'success',
                'badge' => 'Mail',
            ],
            [
                'title' => 'Payment Settings',
                'description' => 'Configure M-Pesa, Stripe, PayPal, and Cash on Delivery options.',
                'icon' => 'heroicon-o-credit-card',
                'url' => PaymentSettingsPage::getUrl(),
                'color' => 'warning',
                'badge' => 'Finance',
            ],
            [
                'title' => 'Social Authentication',
                'description' => 'Configure Google, Facebook, and Twitter OAuth login settings.',
                'icon' => 'heroicon-o-users',
                'url' => SocialAuthSettingsPage::getUrl(),
                'color' => 'info',
                'badge' => 'Auth',
            ],
            [
                'title' => 'Tax Settings',
                'description' => 'Configure tax rates, VAT settings, and tax-inclusive pricing.',
                'icon' => 'heroicon-o-receipt-percent',
                'url' => TaxSettingsPage::getUrl(),
                'color' => 'danger',
                'badge' => 'Tax',
            ],
            [
                'title' => 'Product SKU Settings',
                'description' => 'Configure SKU patterns and prefixes for product categories.',
                'icon' => 'heroicon-o-hashtag',
                'url' => ProductSkuSettings::getUrl(),
                'color' => 'gray',
                'badge' => 'Product',
            ],
        ];
    }
}
