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
                'title' => 'Product SKU Settings',
                'description' => 'Configure SKU patterns and prefixes for product categories. Define how product SKUs are generated automatically.',
                'icon' => 'heroicon-o-hashtag',
                'url' => ProductSkuSettings::getUrl(),
                'color' => 'primary',
                'badge' => 'Product',
            ],
            // Future settings sections will be added here
            // Example:
            // [
            //     'title' => 'Email Settings',
            //     'description' => 'Configure email templates and SMTP settings',
            //     'icon' => 'heroicon-o-envelope',
            //     'url' => EmailSettings::getUrl(),
            //     'color' => 'success',
            //     'badge' => 'Communication',
            // ],
        ];
    }
}
