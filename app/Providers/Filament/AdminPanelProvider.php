<?php

namespace App\Providers\Filament;

use App\Filament\Pages\BulkData;
use App\Filament\Pages\Settings;
use App\Filament\Resources\RefundResource;
use App\Filament\Resources\PromotionResource;
use App\Filament\Resources\InventorySources\InventorySourceResource;
use App\Filament\Resources\Transactions\TransactionResource;
use App\Filament\Widgets\LowStockProductsWidget;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\NavigationItem;
use Filament\Navigation\MenuItem;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets\AccountWidget;
use Filament\Widgets\FilamentInfoWidget;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login()
            ->colors([
                'primary' => Color::Amber,
            ])
            ->maxContentWidth('full')
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->resources([
                RefundResource::class,
                TransactionResource::class,
                \App\Filament\Resources\AdResource\AdResource::class,
            ])
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->pages([
                Dashboard::class,
                BulkData::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
            ->widgets([
                AccountWidget::class,
                FilamentInfoWidget::class,
                LowStockProductsWidget::class,
            ])
            ->databaseNotifications()
            ->userMenuItems([
                'profile' => MenuItem::make()
                    ->label('Profile')
                    ->url('#')
                    ->icon('heroicon-o-user-circle'),
                'settings' => MenuItem::make()
                    ->label('Settings')
                    ->url(fn (): string => Settings::getUrl())
                    ->icon('heroicon-o-cog-6-tooth'),
            ])
            ->navigationItems([
                NavigationItem::make('Transactions')
                    ->url(fn (): string => TransactionResource::getUrl('index', panel: 'admin'))
                    ->icon('heroicon-o-credit-card')
                    ->isActiveWhen(fn (): bool => request()->routeIs('filament.admin.resources.transactions.*')),
                NavigationItem::make('Invoices')
                    ->url(fn (): string => route('admin.invoices.index'))
                    ->icon('heroicon-o-document-text')
                    ->isActiveWhen(fn (): bool => request()->routeIs('admin.invoices.*')),
                NavigationItem::make('Refunds')
                    ->url(fn (): string => RefundResource::getUrl('index'))
                    ->icon('heroicon-o-receipt-refund')
                    ->isActiveWhen(fn (): bool => request()->routeIs('filament.admin.resources.refunds.*')),
                NavigationItem::make('Promotions')
                    ->url(fn (): string => PromotionResource::getUrl('index'))
                    ->icon('heroicon-o-tag')
                    ->isActiveWhen(fn (): bool => request()->routeIs('filament.admin.resources.promotions.*')),
                NavigationItem::make('Inventory Sources')
                    ->url(fn (): string => InventorySourceResource::getUrl('index'))
                    ->icon('heroicon-o-building-office-2')
                    ->isActiveWhen(fn (): bool => request()->routeIs('filament.admin.resources.inventory-sources.*')),
                NavigationItem::make('Warranties')
                    ->url(fn (): string => \App\Filament\Resources\WarrantyResource::getUrl('index'))
                    ->icon('heroicon-o-shield-check')
                    ->isActiveWhen(fn (): bool => request()->routeIs('filament.admin.resources.warranties.*')),
                NavigationItem::make('Ads')
                    ->url(fn (): string => \App\Filament\Resources\AdResource\AdResource::getUrl('index'))
                    ->icon('heroicon-o-megaphone')
                    ->isActiveWhen(fn (): bool => request()->routeIs('filament.admin.resources.ad-resource.ads.*')),
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
