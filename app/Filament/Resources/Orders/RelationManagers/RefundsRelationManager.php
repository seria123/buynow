<?php

namespace App\Filament\Resources\Orders\RelationManagers;

use App\Models\Sales\Refund;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Table;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Actions;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;

class RefundsRelationManager extends RelationManager
{
    protected static string $relationship = 'refunds';

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('amount')
                    ->label('Amount')
                    ->money('KES')
                    ->sortable(),

                BadgeColumn::make('refund_type')
                    ->label('Type')
                    ->colors([
                        'primary' => 'full',
                        'warning' => 'partial',
                    ]),

                BadgeColumn::make('status')
                    ->label('Status')
                    ->colors([
                        'warning' => 'pending',
                        'info' => 'processing',
                        'success' => 'completed',
                        'danger' => ['failed', 'rejected'],
                    ]),

                TextColumn::make('reason')
                    ->label('Reason')
                    ->limit(50),

                TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime('M j, Y H:i')
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('Issue Refund')
                    ->icon('heroicon-o-plus')
                    ->color('success')
                    ->form([
                        Select::make('refund_type')
                            ->label('Refund Type')
                            ->options([
                                'full' => 'Full Refund',
                                'partial' => 'Partial Refund',
                            ])
                            ->default('full')
                            ->required()
                            ->reactive(),
                        TextInput::make('amount')
                            ->label('Refund Amount')
                            ->prefix('KES')
                            ->numeric()
                            ->required()
                            ->visible(fn ($get) => $get('refund_type') === 'partial'),
                        Textarea::make('reason')
                            ->label('Reason for Refund')
                            ->rows(3)
                            ->required(),
                    ])
                    ->action(function (array $data, $ownerRecord): void {
                        $amount = $data['refund_type'] === 'full' 
                            ? $ownerRecord->total_amount 
                            : $data['amount'];

                        Refund::issueRefund(
                            $ownerRecord,
                            $amount,
                            $data['reason'],
                            $data['refund_type']
                        );
                    })
                    ->successNotificationTitle('Refund issued successfully'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->label('View'),
                Tables\Actions\ActionGroup::make([
                    Actions\Action::make('markProcessing')
                        ->label('Mark as Processing')
                        ->icon('heroicon-o-arrow-path')
                        ->color('info')
                        ->visible(fn (Refund $record) => $record->status === 'pending')
                        ->action(fn (Refund $record) => $record->markAsProcessing()),
                    Actions\Action::make('markCompleted')
                        ->label('Mark as Completed')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->visible(fn (Refund $record) => in_array($record->status, ['pending', 'processing']))
                        ->form([
                            TextInput::make('mpesa_transaction_id')
                                ->label('M-Pesa Transaction ID')
                                ->placeholder('e.g., RGX1234567'),
                        ])
                        ->action(function (Refund $record, array $data) {
                            $record->markAsCompleted($data['mpesa_transaction_id'] ?? null);
                        }),
                    Actions\Action::make('markFailed')
                        ->label('Mark as Failed')
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->visible(fn (Refund $record) => in_array($record->status, ['pending', 'processing']))
                        ->form([
                            Textarea::make('notes')
                                ->label('Reason for Failure')
                                ->rows(3),
                        ])
                        ->action(function (Refund $record, array $data) {
                            $record->markAsFailed($data['notes'] ?? null);
                        }),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
