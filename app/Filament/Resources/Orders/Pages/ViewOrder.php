<?php

namespace App\Filament\Resources\Orders\Pages;

use App\Filament\Resources\Orders\OrderResource;
use Filament\Resources\Pages\ViewRecord;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;

class ViewOrder extends ViewRecord
{
    protected static string $resource = OrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
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
