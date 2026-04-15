<?php

namespace App\Filament\Resources\Orders\Tables;

use Filament\Tables\Table;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\Action;
use Filament\Actions\ViewAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;

class OrdersTable
{
    public static function make(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('order_number')
                    ->label('Order #')
                    ->searchable()
                    ->sortable()
                    ->copyable(),

                TextColumn::make('user.name')
                    ->label('Customer')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('total_amount')
                    ->label('Total')
                    ->money('KES')
                    ->sortable(),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending'    => 'warning',
                        'processing' => 'info',
                        'shipped'    => 'primary',
                        'delivered'  => 'success',
                        'cancelled'  => 'danger',
                        default      => 'gray',
                    }),

                TextColumn::make('payment_status')
                    ->label('Payment')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'paid'    => 'success',
                        'unpaid'  => 'warning',
                        'refunded' => 'info',
                        'pending' => 'warning',
                        'processing' => 'info',
                        'failed' => 'danger',
                        'cancelled' => 'danger', // Changed from 'danger' to 'danger' (was already danger but ensure consistency)
                        'expired' => 'secondary',
                        default   => 'gray',
                    })
                    ->icon(fn (string $state): string => match ($state) {
                        'paid'    => 'heroicon-o-check-circle',
                        'unpaid'  => 'heroicon-o-currency-dollar',
                        'refunded' => 'heroicon-o-arrow-path',
                        'pending'  => 'heroicon-o-clock',
                        'processing' => 'heroicon-o-arrow-path',
                        'failed'   => 'heroicon-o-x-circle',
                        'cancelled' => 'heroicon-o-x-mark',
                        'expired'  => 'heroicon-o-calendar',
                        default    => 'heroicon-o-credit-card',
                    }),

                TextColumn::make('payment_method')
                    ->label('Method')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('promotion_code')
                    ->label('Promo Code')
                    ->badge()
                    ->color('info')
                    ->placeholder('No promo')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('discount_amount')
                    ->label('Discount')
                    ->money('KES')
                    ->placeholder('KES 0.00')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('created_at')
                    ->label('Placed At')
                    ->dateTime('M d, Y H:i')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'pending'    => 'Pending',
                        'processing' => 'Processing',
                        'shipped'    => 'Shipped',
                        'delivered'  => 'Delivered',
                        'cancelled'  => 'Cancelled',
                    ]),

                SelectFilter::make('payment_status')
                    ->options([
                        'paid'    => 'Paid',
                        'unpaid'  => 'Unpaid',
                        'refunded' => 'Refunded',
                        'pending' => 'Pending',
                        'processing' => 'Processing',
                        'failed' => 'Failed',
                        'cancelled' => 'Cancelled',
                        'expired' => 'Expired',
                    ]),
            ])
            ->actions([
                ViewAction::make(),
                Action::make('updateStatus')
                    ->label('Update Status')
                    ->icon('heroicon-o-arrow-path')
                    ->color('warning')
                    ->form([
                        Select::make('status')
                            ->label('Order Status')
                            ->options([
                                'pending'    => 'Pending',
                                'processing' => 'Processing',
                                'shipped'    => 'Shipped',
                                'delivered'  => 'Delivered',
                                'cancelled'  => 'Cancelled',
                            ])
                            ->required(),
                    ])
                    ->action(function ($record, array $data): void {
                        $record->update(['status' => $data['status']]);
                    })
                    ->successNotificationTitle('Order status updated'),

                Action::make('updatePaymentStatus')
                    ->label('Update Payment')
                    ->icon('heroicon-o-credit-card')
                    ->color('success')
                    ->form([
                        Select::make('payment_status')
                            ->label('Payment Status')
                            ->options([
                                'paid'    => 'Paid',
                                'unpaid'  => 'Unpaid',
                                'pending' => 'Pending',
                                'processing' => 'Processing',
                                'refunded' => 'Refunded',
                                'failed' => 'Failed',
                                'cancelled' => 'Cancelled',
                            ])
                            ->required(),
                    ])
                    ->action(function ($record, array $data): void {
                        $record->updatePaymentStatus($data['payment_status']);
                    })
                    ->successNotificationTitle('Payment status updated'),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    BulkAction::make('markShipped')
                        ->label('Mark as Shipped')
                        ->icon('heroicon-o-truck')
                        ->color('primary')
                        ->action(fn ($records) => $records->each->update(['status' => 'shipped']))
                        ->requiresConfirmation(),

                    BulkAction::make('markDelivered')
                        ->label('Mark as Delivered')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->action(fn ($records) => $records->each->update(['status' => 'delivered']))
                        ->requiresConfirmation(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
