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
                ->color('info'),

            TextColumn::make('type')
                ->label('Type')
                ->formatStateUsing(fn ($state) => $state === 'percentage' ? '%' : '$')
                ->badge()
                ->color(fn ($state) => $state === 'percentage' ? 'info' : 'success'),

            TextColumn::make('value')
                ->label('Value')
                ->formatStateUsing(function ($state, $record) {
                    if ($record->type === 'percentage') {
                        return $state . '%';
                    }
                    return '$' . number_format($state, 2);
                })
                ->sortable(),

            TextColumn::make('minimum_order_amount')
                ->label('Min. Order')
                ->formatStateUsing(fn ($state) => $state ? '$' . number_format($state, 2) : '-')
                ->sortable(),

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
        ];
    }

    public static function getFilters(): array
    {
        return [
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
