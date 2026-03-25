<?php

namespace App\Filament\Resources\Transactions\Schemas;

use App\Enums\TransactionStatus;
use App\Enums\TransactionType;
use App\Enums\PaymentMethod;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Form;
use Filament\Schemas\Schema;

class TransactionForm
{
    public static function schema(): array
    {
        return [
            Section::make('Transaction Details')
                ->schema([
                    Grid::make(2)->schema([
                        Select::make('order_id')
                            ->label('Order')
                            ->relationship('order', 'order_number')
                            ->searchable()
                            ->preload()
                            ->required(),
                        Select::make('user_id')
                            ->label('Customer')
                            ->relationship('user', 'name')
                            ->searchable()
                            ->preload(),
                    ]),
                    Grid::make(2)->schema([
                        TextInput::make('transaction_number')
                            ->label('Transaction Number')
                            ->unique(ignoreRecord: true),
                        TextInput::make('amount')
                            ->label('Amount')
                            ->numeric()
                            ->required(),
                    ]),
                    Grid::make(2)->schema([
                        TextInput::make('currency')
                            ->label('Currency')
                            ->default('USD'),
                        Select::make('type')
                            ->label('Type')
                            ->options(TransactionType::class)
                            ->default(TransactionType::PAYMENT->value)
                            ->required(),
                    ]),
                    Grid::make(2)->schema([
                        Select::make('status')
                            ->label('Status')
                            ->options(TransactionStatus::class)
                            ->default(TransactionStatus::PENDING->value)
                            ->required(),
                        Select::make('payment_method')
                            ->label('Payment Method')
                            ->options(PaymentMethod::class),
                    ]),
                ]),

            Section::make('Gateway Details')
                ->schema([
                    Grid::make(2)->schema([
                        Select::make('gateway')
                            ->label('Gateway')
                            ->options([
                                'stripe' => 'Stripe',
                                'paystack' => 'Paystack',
                                'flutterwave' => 'Flutterwave',
                                'paypal' => 'PayPal',
                                'mpesa' => 'M-Pesa',
                                'manual' => 'Manual Payment',
                            ]),
                        TextInput::make('gateway_transaction_id')
                            ->label('Gateway Transaction ID'),
                    ]),
                    // M-Pesa specific fields (visible when gateway is mpesa)
                    Grid::make(2)->schema([
                        TextInput::make('mpesa_transaction_id')
                            ->label('M-Pesa Transaction ID'),
                        TextInput::make('mpesa_phone_number')
                            ->label('M-Pesa Phone Number'),
                    ])
                    ->visible(fn ($get) => $get('gateway') === 'mpesa'),
                    Grid::make(2)->schema([
                        TextInput::make('gateway_response_code')
                            ->label('Response Code'),
                        TextInput::make('gateway_response_message')
                            ->label('Response Message'),
                    ]),
                ]),

            Section::make('Customer Information')
                ->schema([
                    Grid::make(2)->schema([
                        TextInput::make('customer_email')
                            ->label('Customer Email')
                            ->email(),
                        TextInput::make('customer_phone')
                            ->label('Customer Phone'),
                    ]),
                ]),

            Section::make('Additional Information')
                ->schema([
                    Textarea::make('description')
                        ->label('Description')
                        ->rows(3),
                    Textarea::make('metadata')
                        ->label('Metadata (JSON)')
                        ->rows(3)
                        ->hint('Enter as key:value pairs, one per line'),
                ]),
        ];
    }

    public static function form(Form $form): Form
    {
        return $form->schema(self::schema());
    }
}