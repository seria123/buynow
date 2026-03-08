<?php

namespace App\Filament\Resources\Orders\Schemas;

use Filament\Infolists\Components\TextEntry;
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

                Section::make('Customer')
                    ->schema([
                        Grid::make(2)->schema([
                            TextEntry::make('user.name')->label('Name'),
                            TextEntry::make('user.email')->label('Email'),
                        ]),
                    ]),
            ]);
    }
}
