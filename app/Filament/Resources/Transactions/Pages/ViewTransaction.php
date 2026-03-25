<?php

namespace App\Filament\Resources\Transactions\Pages;

use App\Filament\Resources\Transactions\TransactionResource;
use App\Models\Sales\Transaction;
use Filament\Actions;
use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Resources\Pages\ViewRecord;

class ViewTransaction extends ViewRecord
{
    protected static string $resource = TransactionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),

            // Mark as Completed
            Action::make('markCompleted')
                ->label('Mark as Completed')
                ->color('success')
                ->visible(fn ($record) => in_array($record->status, ['pending', 'processing']))
                ->requiresConfirmation()
                ->action(function (Transaction $record) {
                    $record->markAsCompleted();
                    $this->refreshFormData(['status']);
                }),

            // Mark as Failed
            Action::make('markFailed')
                ->label('Mark as Failed')
                ->color('danger')
                ->visible(fn ($record) => in_array($record->status, ['pending', 'processing']))
                ->form([
                    Textarea::make('message')
                        ->label('Failure Reason')
                        ->placeholder('Enter the reason for failure'),
                ])
                ->action(function (Transaction $record, array $data) {
                    $record->markAsFailed($data['message'] ?? 'Manual failure');
                }),

            // Mark as Cancelled
            Action::make('markCancelled')
                ->label('Mark as Cancelled')
                ->color('warning')
                ->visible(fn ($record) => in_array($record->status, ['pending', 'processing']))
                ->form([
                    Textarea::make('message')
                        ->label('Cancellation Reason')
                        ->placeholder('Enter the reason for cancellation'),
                ])
                ->action(function (Transaction $record, array $data) {
                    $record->markAsCancelled($data['message'] ?? 'Manual cancellation');
                }),
        ];
    }
}