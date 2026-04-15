<?php

namespace App\Filament\Resources\Orders\Pages;

use App\Filament\Resources\Orders\OrderResource;
use App\Models\Sales\Refund;
use Filament\Resources\Pages\ViewRecord;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;

class ViewOrder extends ViewRecord
{
    protected static string $resource = OrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('issueRefund')
                ->label('Issue Refund')
                ->icon('heroicon-o-currency-dollar')
                ->color('success')
                ->visible(fn ($record) => in_array($record->payment_status, ['paid']) && !$record->isFullyRefunded())
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
                        ->visible(fn ($get) => $get('refund_type') === 'partial')
                        ->helperText(fn ($record) => 'Max: KES ' . number_format($record->total_amount - $record->total_refunded, 2)),
                    Textarea::make('reason')
                        ->label('Reason for Refund')
                        ->rows(3)
                        ->required(),
                ])
                ->action(function (array $data, $record): void {
                    $amount = $data['refund_type'] === 'full' 
                        ? $record->total_amount 
                        : $data['amount'];

                    // Validate partial refund amount
                    $maxRefundable = $record->total_amount - $record->total_refunded;
                    if ($amount > $maxRefundable) {
                        Notification::make()
                            ->title('Invalid Refund Amount')
                            ->body('Refund amount cannot exceed the remaining refundable amount: KES ' . number_format($maxRefundable, 2))
                            ->danger()
                            ->send();
                        return;
                    }

                    Refund::issueRefund(
                        $record,
                        $amount,
                        $data['reason'],
                        $data['refund_type']
                    );

                    Notification::make()
                        ->title('Refund Issued Successfully')
                        ->body('A refund of KES ' . number_format($amount, 2) . ' has been issued for order #' . $record->order_number)
                        ->success()
                        ->send();
                }),

            Action::make('updateStatus')
                ->label('Update Status')
                ->icon('heroicon-o-arrow-path')
                ->color('warning')
                ->form([
                    Select::make('status')
                        ->label('Order Status')
                        ->options([
                            'pending'    => 'Pending',
                            'processing' => 'Processing',
                            'shipped'    => 'Shipped',
                            'out_for_delivery' => 'Out for Delivery',
                            'delivered'  => 'Delivered',
                            'cancelled'  => 'Cancelled',
                        ])
                        ->default(fn () => $this->record->status)
                        ->required(),

                    Select::make('payment_status')
                        ->label('Payment Status')
                        ->options([
                            'paid'     => 'Paid',
                            'unpaid'   => 'Unpaid',
                            'pending'  => 'Pending',
                            'refunded' => 'Refunded',
                            'failed'   => 'Failed',
                            'cancelled' => 'Cancelled',
                        ])
                        ->default(fn () => $this->record->payment_status)
                        ->required(),
                ])
                ->action(function (array $data): void {
                    $oldStatus = $this->record->status;
                    $oldPaymentStatus = $this->record->payment_status;
                    
                    // Update status if changed
                    if ($data['status'] !== $oldStatus) {
                        $this->record->updateStatus($data['status']);
                    }
                    
                    // Update payment status if changed
                    if ($data['payment_status'] !== $oldPaymentStatus) {
                        $this->record->updatePaymentStatus($data['payment_status']);
                    }
                    
                    $this->refreshFormData(['status', 'payment_status']);
                })
                ->successNotificationTitle('Order updated successfully'),

            // Action to manually record a cancelled payment (when callback fails)
            Action::make('recordCancelledPayment')
                ->label('Record Cancelled Payment')
                ->icon('heroicon-o-x-mark')
                ->color('danger')
                ->visible(fn ($record) => 
                    in_array($record->payment_status, ['unpaid', 'pending']) && 
                    $record->checkout_request_id &&
                    !$record->transactions()->where('status', 'cancelled')->exists()
                )
                ->requiresConfirmation()
                ->modalDescription('This will record the payment as cancelled and create a transaction record. Use this when the M-Pesa callback was not received.')
                ->action(function ($record): void {
                    // Create a cancelled transaction
                    $transaction = \App\Models\Sales\Transaction::create([
                        'order_id' => $record->id,
                        'user_id' => $record->user_id,
                        'amount' => $record->total_amount,
                        'currency' => 'KES',
                        'type' => 'payment',
                        'status' => 'cancelled',
                        'payment_method' => 'mpesa',
                        'gateway' => 'mpesa',
                        'gateway_transaction_id' => $record->checkout_request_id,
                        'gateway_response_message' => 'Manually marked as cancelled (callback not received)',
                        'customer_email' => $record->user?->email,
                        'customer_phone' => $record->user?->phone,
                    ]);

                    // Update order payment status
                    $record->updatePaymentStatus('cancelled');

                    Notification::make()
                        ->title('Payment Recorded as Cancelled')
                        ->body('Transaction ' . $transaction->transaction_number . ' has been created with cancelled status.')
                        ->success()
                        ->send();

                    $this->refreshFormData(['payment_status']);
                }),

            // Action to manually record a SUCCESS payment (when callback fails)
            Action::make('recordPaymentReceived')
                ->label('Record Payment Received')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->visible(fn ($record) => 
                    in_array($record->payment_status, ['unpaid', 'pending']) && 
                    $record->checkout_request_id &&
                    !$record->transactions()->where('status', 'completed')->exists()
                )
                ->requiresConfirmation()
                ->modalDescription('This will record the payment as completed. Use this when the M-Pesa callback was not received but you confirmed payment was made.')
                ->form([
                    TextInput::make('mpesa_receipt')
                        ->label('M-Pesa Receipt Number (optional)')
                        ->placeholder('e.g., RGX1234567'),
                ])
                ->action(function (array $data, $record): void {
                    // Create a completed transaction
                    $transaction = \App\Models\Sales\Transaction::create([
                        'order_id' => $record->id,
                        'user_id' => $record->user_id,
                        'amount' => $record->total_amount,
                        'currency' => 'KES',
                        'type' => 'payment',
                        'status' => 'completed',
                        'payment_method' => 'mpesa',
                        'gateway' => 'mpesa',
                        'gateway_transaction_id' => $record->checkout_request_id,
                        'mpesa_transaction_id' => $data['mpesa_receipt'] ?? null,
                        'mpesa_phone_number' => $record->user?->phone,
                        'gateway_response_message' => 'Manually marked as paid (callback not received)',
                        'customer_email' => $record->user?->email,
                        'customer_phone' => $record->user?->phone,
                        'processed_at' => now(),
                    ]);

                    // Update order payment status
                    $record->updatePaymentStatus('paid');
                    $record->updateStatus('processing');
                    $record->payment_method = 'mpesa';
                    $record->save();

                    Notification::make()
                        ->title('Payment Recorded as Received')
                        ->body('Transaction ' . $transaction->transaction_number . ' has been created with completed status.')
                        ->success()
                        ->send();

                    $this->refreshFormData(['payment_status', 'status']);
                }),
        ];
    }
}
