<?php

namespace App\Filament\Resources\Orders\Schemas;

use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;

class OrderForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Update Order Status')
                    ->schema([
                        Grid::make(2)->schema([
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

                            Select::make('payment_status')
                                ->label('Payment Status')
                                ->options([
                                    'paid'     => 'Paid',
                                    'unpaid'   => 'Unpaid',
                                    'refunded' => 'Refunded',
                                ])
                                ->required(),
                        ]),
                    ]),
            ]);
    }
}
