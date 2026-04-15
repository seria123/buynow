<?php

namespace App\Filament\Resources\Transactions\Schemas;

use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\TextEntry;
use Filament\Schemas\Components\BadgeEntry;
use Filament\Schemas\Components\KeyValueEntry;
use Filament\Schemas\Schema;

class TransactionInfolist
{
    public static function schema(): array
    {
        return [
            Section::make('Transaction Details')
                ->schema([
                    Grid::make(3)->schema([
                        TextEntry::make('transaction_number')
                            ->label('Transaction No.')
                            ->copyable(),
                        TextEntry::make('order.order_number')
                            ->label('Order')
                            ->url(fn ($record) => $record->order ? route('filament.admin.resources.orders.view', $record->order) : null),
                        TextEntry::make('user.name')
                            ->label('Customer'),
                    ]),
                    Grid::make(3)->schema([
                        TextEntry::make('amount')
                            ->label('Amount')
                            ->money(fn ($record) => $record->currency ?? 'USD'),
                        BadgeEntry::make('status')
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
                        BadgeEntry::make('type')
                            ->label('Type')
                            ->color(fn (string $state): string => match ($state) {
                                'payment' => 'success',
                                'refund' => 'warning',
                                default => 'gray',
                            }),
                    ]),
                ]),

            Section::make('Order Information')
                ->schema([
                    Grid::make(3)->schema([
                        TextEntry::make('order.status')
                            ->label('Order Status')
                            ->badge()
                            ->color(fn (string $state): string => match ($state) {
                                'pending' => 'warning',
                                'confirmed' => 'info',
                                'processing' => 'info',
                                'shipped' => 'info',
                                'delivered' => 'success',
                                'cancelled' => 'danger',
                                'refunded' => 'warning',
                                default => 'gray',
                            }),
                        TextEntry::make('order.payment_status')
                            ->label('Payment Status')
                            ->badge()
                            ->color(fn (string $state): string => match ($state) {
                                'pending' => 'warning',
                                'paid' => 'success',
                                'partially_paid' => 'info',
                                'refunded' => 'warning',
                                'failed' => 'danger',
                                'cancelled' => 'danger',
                                default => 'gray',
                            }),
                        TextEntry::make('order.total_amount')
                            ->label('Order Total')
                            ->money(fn ($record) => $record->order?->currency ?? 'USD'),
                    ]),
                ])
                ->visible(fn ($record) => $record->order !== null),

            Section::make('Order Items')
                ->schema([
                    \Filament\Infolists\Components\RepeatableEntry::make('order.items')
                        ->schema([
                            Grid::make(4)->schema([
                                \Filament\Infolists\Components\TextEntry::make('product_name')
                                    ->label('Product'),
                                \Filament\Infolists\Components\TextEntry::make('quantity')
                                    ->label('Qty'),
                                \Filament\Infolists\Components\TextEntry::make('price')
                                    ->label('Unit Price')
                                    ->money('USD'),
                                \Filament\Infolists\Components\TextEntry::make('subtotal')
                                    ->label('Total')
                                    ->money('USD'),
                            ]),
                        ])
                        ->visible(fn ($record) => $record->order && $record->order->items->count() > 0),
                ]),

            Section::make('Payment Information')
                ->schema([
                    Grid::make(2)->schema([
                        TextEntry::make('payment_method')
                            ->label('Payment Method'),
                        TextEntry::make('gateway')
                            ->label('Gateway'),
                    ]),
                    Grid::make(2)->schema([
                        TextEntry::make('gateway_transaction_id')
                            ->label('Gateway Transaction ID')
                            ->copyable(),
                        TextEntry::make('gateway_response_code')
                            ->label('Response Code'),
                    ]),
                    // M-Pesa specific fields
                    Grid::make(2)->schema([
                        TextEntry::make('mpesa_transaction_id')
                            ->label('M-Pesa Transaction ID')
                            ->copyable()
                            ->visible(fn ($record) => $record->gateway === 'mpesa'),
                        TextEntry::make('mpesa_phone_number')
                            ->label('M-Pesa Phone Number')
                            ->visible(fn ($record) => $record->gateway === 'mpesa'),
                    ]),
                    TextEntry::make('gateway_response_message')
                        ->label('Response Message'),
                    TextEntry::make('customer_email')
                        ->label('Customer Email'),
                    TextEntry::make('customer_phone')
                        ->label('Customer Phone'),
                ]),

            Section::make('Additional Information')
                ->schema([
                    TextEntry::make('description')
                        ->label('Description'),
                    KeyValueEntry::make('metadata')
                        ->label('Metadata'),
                    KeyValueEntry::make('gateway_response_data')
                        ->label('Gateway Response Data'),
                ]),

            Section::make('Timestamps')
                ->schema([
                    Grid::make(3)->schema([
                        TextEntry::make('processed_at')
                            ->label('Processed At')
                            ->dateTime(),
                        TextEntry::make('created_at')
                            ->label('Created At')
                            ->dateTime(),
                        TextEntry::make('updated_at')
                            ->label('Updated At')
                            ->dateTime(),
                    ]),
                ]),
        ];
    }
}