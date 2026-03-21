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
                                    'unpaid'   => 'danger',
                                    'refunded' => 'warning',
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
            ]);
    }
}
