<?php

namespace App\Filament\Resources\Orders\RelationManagers;

use App\Enums\TransactionStatus;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Resources\RelationManagers\RelationManager;

class TransactionsRelationManager extends RelationManager
{
    protected static string $relationship = 'transactions';

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('transaction_number')
                    ->label('Transaction No.')
                    ->searchable()
                    ->copyable()
                    ->url(fn ($record) => route('filament.admin.resources.transactions.view', $record)),

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
                    ->label('M-Pesa ID')
                    ->searchable()
                    ->visible(fn ($record) => $record->gateway === 'mpesa'),

                TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                \Filament\Tables\Filters\SelectFilter::make('status')
                    ->label('Status')
                    ->options(TransactionStatus::class),
                \Filament\Tables\Filters\SelectFilter::make('type')
                    ->label('Type')
                    ->options([
                        'payment' => 'Payment',
                        'refund' => 'Refund',
                    ]),
            ])
            ->actions([
                \Filament\Tables\Actions\ViewAction::make(),
            ])
            ->defaultSort('created_at', 'desc');
    }
}