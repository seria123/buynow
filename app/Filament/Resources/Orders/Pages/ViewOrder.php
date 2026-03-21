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
        ];
    }
}
