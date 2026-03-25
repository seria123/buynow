<?php

namespace App\Filament\Resources\WarrantyResource\Tables;

use App\Models\Sales\Warranty;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Actions\ActionGroup;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class WarrantiesTable
{
    public static function getColumns(): array
    {
        return [
            TextColumn::make('warranty_number')
                ->label('Warranty #')
                ->searchable()
                ->sortable()
                ->fontFamily('mono')
                ->copyable()
                ->badge()
                ->color('info'),

            TextColumn::make('product.name')
                ->label('Product')
                ->searchable()
                ->sortable()
                ->weight('medium')
                ->limit(30),

            TextColumn::make('user.name')
                ->label('Customer')
                ->searchable()
                ->sortable()
                ->limit(25),

            TextColumn::make('warranty_type')
                ->label('Type')
                ->formatStateUsing(fn ($state) => match ($state) {
                    'standard' => 'Standard',
                    'extended' => 'Extended',
                    'lifetime' => 'Lifetime',
                    'manufacturer' => 'Manufacturer',
                    default => ucfirst($state),
                })
                ->badge()
                ->color(fn ($state) => match ($state) {
                    'standard' => 'info',
                    'extended' => 'warning',
                    'lifetime' => 'success',
                    'manufacturer' => 'purple',
                    default => 'gray',
                }),

            TextColumn::make('start_date')
                ->label('Start Date')
                ->date('M j, Y')
                ->sortable(),

            TextColumn::make('end_date')
                ->label('End Date')
                ->date('M j, Y')
                ->sortable(),

            TextColumn::make('remaining_days')
                ->label('Remaining')
                ->formatStateUsing(fn ($record) => $record->remaining_days . ' days')
                ->color(fn ($record) => $record->remaining_days <= 30 ? 'danger' : ($record->remaining_days <= 90 ? 'warning' : 'success')),

            TextColumn::make('status_label')
                ->label('Status')
                ->badge()
                ->color(fn ($state) => match ($state) {
                    'Active' => 'success',
                    'Expired' => 'danger',
                    'Claimed' => 'warning',
                    'Cancelled' => 'gray',
                    default => 'gray',
                }),
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
                    'claimed' => 'Claimed',
                    'cancelled' => 'Cancelled',
                ])
                ->query(function (Builder $query, array $data) {
                    return match ($data['value']) {
                        'active' => $query->where('status', 'active')->where('end_date', '>=', now()->toDateString()),
                        'expired' => $query->where(function ($q) {
                            $q->where('status', 'expired')
                                ->orWhere(function ($q2) {
                                    $q2->where('status', 'active')
                                        ->where('end_date', '<', now()->toDateString());
                                });
                        }),
                        'claimed' => $query->where('status', 'claimed'),
                        'cancelled' => $query->where('status', 'cancelled'),
                        default => $query,
                    };
                }),

            SelectFilter::make('warranty_type')
                ->label('Type')
                ->options([
                    'standard' => 'Standard',
                    'extended' => 'Extended',
                    'lifetime' => 'Lifetime',
                    'manufacturer' => 'Manufacturer',
                ]),

            Filter::make('expiring_soon')
                ->label('Expiring Soon (30 days)')
                ->query(function (Builder $query) {
                    return $query->where('status', 'active')
                        ->whereBetween('end_date', [now(), now()->addDays(30)]);
                })
                ->toggle(),

            Filter::make('expired')
                ->label('Expired')
                ->query(function (Builder $query) {
                    return $query->where('end_date', '<', now()->toDateString());
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
                    ->modalHeading('Delete Warranty')
                    ->modalDescription('Are you sure you want to delete this warranty? This action cannot be undone.'),
            ])
                ->icon('heroicon-m-ellipsis-horizontal')
                ->tooltip('Actions'),
        ];
    }

    public static function getBulkActions(): array
    {
        return [
            BulkActionGroup::make([
                BulkAction::make('mark_active')
                    ->label('Mark as Active')
                    ->action(function ($records) {
                        $records->each(fn (Warranty $record) => $record->update(['status' => 'active']));
                    })
                    ->icon('heroicon-m-check')
                    ->requiresConfirmation(),

                BulkAction::make('mark_expired')
                    ->label('Mark as Expired')
                    ->action(function ($records) {
                        $records->each(fn (Warranty $record) => $record->update(['status' => 'expired']));
                    })
                    ->icon('heroicon-m-x-mark')
                    ->requiresConfirmation(),

                BulkAction::make('mark_claimed')
                    ->label('Mark as Claimed')
                    ->action(function ($records) {
                        $records->each(fn (Warranty $record) => $record->update(['status' => 'claimed']));
                    })
                    ->icon('heroicon-m-check-badge')
                    ->requiresConfirmation(),
            ]),
        ];
    }
}