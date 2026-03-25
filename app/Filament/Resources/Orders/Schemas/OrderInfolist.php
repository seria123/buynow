<?php

namespace App\Filament\Resources\Orders\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\BadgeEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;

class OrderInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Order Details')
                    ->schema([
                        Grid::make(3)->schema([
                            TextEntry::make('order_number')->label('Order Number')->copyable(),
                            TextEntry::make('status')->label('Status')->badge()
                                ->color(fn (string $state): string => match ($state) {
                                    'pending'    => 'warning',
                                    'processing' => 'info',
                                    'shipped'    => 'primary',
                                    'delivered'  => 'success',
                                    'cancelled'  => 'danger',
                                    default      => 'gray',
                                }),
                            TextEntry::make('payment_status')->label('Payment Status')->badge()
                                ->color(fn (string $state): string => match ($state) {
                                    'paid'     => 'success',
                                    'unpaid'   => 'warning',
                                    'refunded' => 'info',
                                    'pending'  => 'warning',
                                    'processing' => 'info',
                                    'failed'   => 'danger',
                                    'cancelled' => 'danger',
                                    'expired'  => 'secondary',
                                    default    => 'gray',
                                }),
                            TextEntry::make('total_amount')->label('Total Amount')->money('KES'),
                            TextEntry::make('payment_method')->label('Payment Method'),
                            TextEntry::make('created_at')->label('Placed At')->dateTime('M d, Y H:i'),
                        ]),
                    ]),

                Section::make('Promotion Applied')
                    ->visible(fn ($record) => $record->promotion_code !== null)
                    ->schema([
                        Grid::make(3)->schema([
                            TextEntry::make('promotion_code')->label('Promo Code')
                                ->badge()
                                ->color('info'),
                            TextEntry::make('discount_amount')->label('Discount')
                                ->money('KES'),
                            TextEntry::make('promotion.name')->label('Promotion Name'),
                        ]),
                    ]),

                Section::make('Customer')
                    ->schema([
                        Grid::make(2)->schema([
                            TextEntry::make('user.name')->label('Name'),
                            TextEntry::make('user.email')->label('Email'),
                        ]),
                    ]),

                Section::make('Refund Information')
                    ->visible(fn ($record) => $record->hasRefunds())
                    ->schema([
                        Grid::make(3)->schema([
                            TextEntry::make('total_refunded')
                                ->label('Total Refunded')
                                ->money('KES')
                                ->getStateUsing(fn ($record) => $record->total_refunded),
                            BadgeEntry::make('refund_status')
                                ->label('Refund Status')
                                ->badge()
                                ->color(fn ($record): string => match(true) {
                                    $record->isFullyRefunded() => 'success',
                                    $record->hasCompletedRefunds() => 'info',
                                    $record->hasRefunds() => 'warning',
                                    default => 'gray',
                                })
                                ->getStateUsing(fn ($record): string => match(true) {
                                    $record->isFullyRefunded() => 'Fully Refunded',
                                    $record->hasCompletedRefunds() => 'Partially Refunded',
                                    $record->hasRefunds() => 'Refund in Progress',
                                    default => 'No Refunds',
                                }),
                            TextEntry::make('refunds_count')
                                ->label('Refund Count')
                                ->getStateUsing(fn ($record) => $record->refunds()->count()),
                        ]),
                    ]),

                Section::make('Transaction Information')
                    ->schema([
                        Grid::make(3)->schema([
                            TextEntry::make('primaryTransaction.transaction_number')
                                ->label('Transaction No.')
                                ->copyable()
                                ->placeholder('No transaction created yet')
                                ->url(fn ($record) => $record->primaryTransaction ? route('filament.admin.resources.transactions.view', $record->primaryTransaction) : null),
                            BadgeEntry::make('primaryTransaction.status')
                                ->label('Transaction Status')
                                ->badge()
                                ->placeholder('No transaction')
                                ->color(fn (string $state): string => match ($state) {
                                    'pending' => 'warning',
                                    'processing' => 'info',
                                    'completed' => 'success',
                                    'failed' => 'danger',
                                    'cancelled' => 'secondary',
                                    'refunded' => 'info',
                                    'expired' => 'secondary',
                                    default => 'gray',
                                }),
                            TextEntry::make('primaryTransaction.gateway')
                                ->label('Gateway')
                                ->placeholder('-'),
                        ]),
                        Grid::make(3)->schema([
                            TextEntry::make('primaryTransaction.gateway_transaction_id')
                                ->label('Gateway Transaction ID')
                                ->copyable()
                                ->placeholder('-'),
                            TextEntry::make('primaryTransaction.mpesa_transaction_id')
                                ->label('M-Pesa Transaction ID')
                                ->copyable()
                                ->placeholder('-')
                                ->visible(fn ($record) => $record->primaryTransaction?->gateway === 'mpesa'),
                            TextEntry::make('primaryTransaction.mpesa_phone_number')
                                ->label('M-Pesa Phone')
                                ->placeholder('-')
                                ->visible(fn ($record) => $record->primaryTransaction?->gateway === 'mpesa'),
                        ]),
                        Grid::make(3)->schema([
                            TextEntry::make('primaryTransaction.gateway_response_code')
                                ->label('Response Code')
                                ->placeholder('-'),
                            TextEntry::make('primaryTransaction.gateway_response_message')
                                ->label('Response Message')
                                ->placeholder('-'),
                            TextEntry::make('primaryTransaction.processed_at')
                                ->label('Processed At')
                                ->dateTime()
                                ->placeholder('-'),
                        ]),
                    ]),
            ]);
    }
}
