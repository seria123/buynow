<?php

namespace App\Filament\Resources\PromotionResource\Tables;

use App\Models\Sales\Promotion;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Actions\ActionGroup;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class PromotionsTable
{
    public static function getColumns(): array
    {
        return [
            TextColumn::make('name')
                ->label('Name')
                ->searchable()
                ->sortable()
                ->weight('medium'),

            TextColumn::make('code')
                ->label('Code')
                ->searchable()
                ->fontFamily('mono')
                ->copyable()
                ->badge()
                ->color('info')
                ->placeholder('Automatic'),

            TextColumn::make('promotion_type')
                ->label('Type')
                ->formatStateUsing(fn ($state) => match($state) {
                    'percentage' => '% Discount',
                    'fixed' => 'Fixed Amount',
                    'buy_one_get_one' => 'BOGO',
                    'free_shipping' => 'Free Shipping',
                    'bundle' => 'Bundle',
                    'flash_sale' => 'Flash Sale',
                    default => $state,
                })
                ->badge()
                ->color(fn ($state) => match($state) {
                    'percentage', 'fixed' => 'info',
                    'buy_one_get_one' => 'warning',
                    'free_shipping' => 'success',
                    'flash_sale' => 'danger',
                    'bundle' => 'purple',
                    default => 'gray',
                }),

            TextColumn::make('value')
                ->label('Value')
                ->formatStateUsing(function ($state, $record) {
                    if (!$state && $record->promotion_type !== 'free_shipping') {
                        return '-';
                    }
                    if ($record->promotion_type === 'percentage') {
                        return $state . '%';
                    }
                    if ($record->promotion_type === 'fixed') {
                        return 'KSh ' . number_format($state, 2);
                    }
                    if ($record->promotion_type === 'buy_one_get_one') {
                        return 'Buy ' . ($record->buy_quantity ?? 1) . ' Get ' . ($record->get_quantity ?? 1);
                    }
                    return $state;
                })
                ->sortable(),

            TextColumn::make('apply_to')
                ->label('Applies To')
                ->formatStateUsing(fn ($state) => match($state) {
                    'all' => 'All Products',
                    'category' => 'Category',
                    'products' => 'Specific Products',
                    'customer_group' => 'Customer Group',
                    default => $state,
                })
                ->badge()
                ->color(fn ($state) => $state === 'all' ? 'gray' : 'info'),

            TextColumn::make('minimum_order_amount')
                ->label('Min. Order')
                ->formatStateUsing(fn ($state) => $state ? 'KSh ' . number_format($state, 2) : '-')
                ->sortable(),

            TextColumn::make('priority')
                ->label('Priority')
                ->sortable()
                ->badge()
                ->color(fn ($state) => $state > 50 ? 'warning' : 'gray'),

            TextColumn::make('usage_limit')
                ->label('Usage')
                ->formatStateUsing(function ($state, $record) {
                    if (!$state) {
                        return '∞';
                    }
                    return $record->used_count . ' / ' . $state;
                })
                ->badge()
                ->color(function ($record) {
                    if (!$record->usage_limit) {
                        return 'gray';
                    }
                    $percentage = ($record->used_count / $record->usage_limit) * 100;
                    if ($percentage >= 100) {
                        return 'danger';
                    }
                    if ($percentage >= 80) {
                        return 'warning';
                    }
                    return 'success';
                }),

            TextColumn::make('expires_at')
                ->label('Expires')
                ->date('M j, Y')
                ->sortable()
                ->placeholder('No expiry'),

            IconColumn::make('is_active')
                ->label('Active')
                ->boolean()
                ->sortable(),

            IconColumn::make('is_flash_sale')
                ->label('Flash')
                ->boolean()
                ->sortable()
                ->visible(fn () => false), // Hidden by default, can be enabled
        ];
    }

    public static function getFilters(): array
    {
        return [
            SelectFilter::make('promotion_type')
                ->label('Promotion Type')
                ->options([
                    'percentage' => 'Percentage Discount',
                    'fixed' => 'Fixed Amount',
                    'buy_one_get_one' => 'Buy 1 Get 1',
                    'free_shipping' => 'Free Shipping',
                    'bundle' => 'Bundle Deal',
                    'flash_sale' => 'Flash Sale',
                ]),

            SelectFilter::make('apply_to')
                ->label('Applies To')
                ->options([
                    'all' => 'All Products',
                    'category' => 'Specific Category',
                    'products' => 'Specific Products',
                    'customer_group' => 'Customer Group',
                ]),

            SelectFilter::make('status')
                ->label('Status')
                ->options([
                    'active' => 'Active',
                    'expired' => 'Expired',
                    'upcoming' => 'Upcoming',
                    'inactive' => 'Inactive',
                ])
                ->query(function (Builder $query, array $data) {
                    return match ($data['value']) {
                        'active' => $query->active()->valid(),
                        'expired' => $query->whereNotNull('expires_at')->where('expires_at', '<', now()),
                        'upcoming' => $query->whereNotNull('starts_at')->where('starts_at', '>', now()),
                        'inactive' => $query->where('is_active', false),
                        default => $query,
                    };
                }),

            Filter::make('valid')
                ->label('Currently Valid')
                ->query(function (Builder $query) {
                    return $query->valid();
                })
                ->toggle(),

            Filter::make('automatic')
                ->label('Automatic Promotions')
                ->query(function (Builder $query) {
                    return $query->where(function ($q) {
                        $q->whereNull('code')->orWhere('code', '');
                    });
                })
                ->toggle(),

            Filter::make('coupon_based')
                ->label('Coupon-based Promotions')
                ->query(function (Builder $query) {
                    return $query->whereNotNull('code')->where('code', '!=', '');
                })
                ->toggle(),
        ];
    }

    public static function getActions(): array
    {
        return [
            ActionGroup::make([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make()
                    ->requiresConfirmation()
                    ->modalHeading('Delete Promotion')
                    ->modalDescription('Are you sure you want to delete this promotion? This action cannot be undone.'),
            ])
                ->icon('heroicon-m-ellipsis-horizontal')
                ->tooltip('Actions'),
        ];
    }

    public static function getBulkActions(): array
    {
        return [
            BulkActionGroup::make([
                BulkAction::make('activate')
                    ->label('Activate')
                    ->action(function ($records) {
                        $records->each(fn (Promotion $record) => $record->update(['is_active' => true]));
                    })
                    ->icon('heroicon-m-check')
                    ->requiresConfirmation(),

                BulkAction::make('deactivate')
                    ->label('Deactivate')
                    ->action(function ($records) {
                        $records->each(fn (Promotion $record) => $record->update(['is_active' => false]));
                    })
                    ->icon('heroicon-m-x-mark')
                    ->requiresConfirmation(),
            ]),
        ];
    }
}
