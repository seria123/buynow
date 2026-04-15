<?php

namespace App\Filament\Resources\Transactions\Tables;

use App\Enums\TransactionStatus;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class TransactionsTable
{
    public static function schema(): array
    {
        return [
            TextColumn::make('transaction_number')
                ->label('Transaction No.')
                ->searchable()
                ->sortable()
                ->copyable(),

            TextColumn::make('order.order_number')
                ->label('Order')
                ->searchable()
                ->sortable()
                ->url(fn ($record) => $record->order ? route('filament.admin.resources.orders.view', $record->order) : null),

            TextColumn::make('user.name')
                ->label('Customer')
                ->searchable()
                ->sortable(),

            TextColumn::make('amount')
                ->label('Amount')
                ->money(fn ($record) => $record->currency ?? 'USD')
                ->sortable(),

            BadgeColumn::make('status')
                ->label('Status')
                ->color(fn (string $state): string => match ($state) {
                    'pending' => 'warning',
                    'processing' => 'info',
                    'completed' => 'success',
                    'failed' => 'danger',
                    'cancelled' => 'danger', // Changed from 'secondary' to 'danger' for visibility
                    'refunded' => 'info',
                    'expired' => 'secondary',
                    default => 'gray',
                })
                ->icon(fn (string $state): string => match ($state) {
                    'pending' => 'heroicon-o-clock',
                    'processing' => 'heroicon-o-arrow-path',
                    'completed' => 'heroicon-o-check-circle',
                    'failed' => 'heroicon-o-x-circle',
                    'cancelled' => 'heroicon-o-x-mark',
                    'refunded' => 'heroicon-o-arrow-path',
                    'expired' => 'heroicon-o-calendar',
                    default => 'heroicon-o-credit-card',
                }),

            BadgeColumn::make('type')
                ->label('Type')
                ->color(fn (string $state): string => match ($state) {
                    'payment' => 'success',
                    'refund' => 'warning',
                    default => 'gray',
                }),

            TextColumn::make('payment_method')
                ->label('Method')
                ->searchable(),

            TextColumn::make('gateway')
                ->label('Gateway')
                ->searchable(),

            TextColumn::make('mpesa_transaction_id')
                ->label('M-Pesa Receipt')
                ->searchable()
                ->copyable()
                ->toggleable(),

            TextColumn::make('customer_phone')
                ->label('Phone')
                ->searchable()
                ->toggleable(),

            TextColumn::make('created_at')
                ->label('Created')
                ->dateTime()
                ->sortable(),
        ];
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns(self::schema())
            ->filters([
                // Status filter
                \Filament\Tables\Filters\SelectFilter::make('status')
                    ->label('Status')
                    ->options(TransactionStatus::class),

                // Type filter
                \Filament\Tables\Filters\SelectFilter::make('type')
                    ->label('Type')
                    ->options([
                        'payment' => 'Payment',
                        'refund' => 'Refund',
                    ]),

                // Gateway filter
                \Filament\Tables\Filters\SelectFilter::make('gateway')
                    ->label('Gateway')
                    ->options([
                        'stripe' => 'Stripe',
                        'paystack' => 'Paystack',
                        'flutterwave' => 'Flutterwave',
                        'paypal' => 'PayPal',
                        'mpesa' => 'M-Pesa',
                        'manual' => 'Manual',
                    ]),
            ])
            ->actions([
                \Filament\Actions\ViewAction::make(),
                \Filament\Actions\EditAction::make(),
            ])
            ->bulkActions([
                \Filament\Actions\BulkActionGroup::make([
                    \Filament\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }
}