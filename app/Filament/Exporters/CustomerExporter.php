<?php

namespace App\Filament\Exporters;

use App\Models\User;
use App\Models\Sales\Order;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;

class CustomerExporter extends Exporter
{
    public static function getColumns(): array
    {
        return [
            ExportColumn::make('id')
                ->label('ID'),
            ExportColumn::make('name')
                ->label('Name'),
            ExportColumn::make('email')
                ->label('Email'),
            ExportColumn::make('phone')
                ->label('Phone'),
            ExportColumn::make('customerGroup.name')
                ->label('Customer Group')
                ->default('Default'),
            ExportColumn::make('total_orders')
                ->label('Total Orders')
                ->getStateUsing(fn (User $record): int => $record->orders()->count()),
            ExportColumn::make('lifetime_value')
                ->label('Lifetime Value (KES)')
                ->getStateUsing(fn (User $record): float => (float) $record->orders()->sum('total_amount')),
            ExportColumn::make('average_order_value')
                ->label('Average Order Value (KES)')
                ->getStateUsing(fn (User $record): float => (float) $this->calculateAverageOrderValue($record)),
            ExportColumn::make('first_order_date')
                ->label('First Order Date')
                ->getStateUsing(fn (User $record): ?string => $record->orders()->min('created_at')?->format('Y-m-d H:i:s')),
            ExportColumn::make('last_order_date')
                ->label('Last Order Date')
                ->getStateUsing(fn (User $record): ?string => $record->orders()->max('created_at')?->format('Y-m-d H:i:s')),
            ExportColumn::make('is_active')
                ->label('Active')
                ->getStateUsing(fn (User $record): string => $record->is_active ? 'Yes' : 'No'),
            ExportColumn::make('created_at')
                ->label('Registered Date')
                ->format('Y-m-d H:i:s'),
        ];
    }

    public static function getModel(): string
    {
        return User::class;
    }

    protected function calculateAverageOrderValue(User $record): float
    {
        $totalOrders = $record->orders()->count();
        if ($totalOrders === 0) {
            return 0;
        }
        $totalAmount = $record->orders()->sum('total_amount');
        return $totalAmount / $totalOrders;
    }

    public function resolveRecords(): \Illuminate\Database\Eloquent\Collection
    {
        return User::query()
            ->with(['orders', 'customerGroup'])
            ->get();
    }

    public function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Your customer export has been completed and ' . number_format($export->successful_rows) . ' ' . str('row')->plural($export->successful_rows) . ' exported.';

        if ($export->failed_rows_count > 0) {
            $body .= ' ' . number_format($export->failed_rows_count) . ' ' . str('row')->plural($export->failed_rows_count) . ' failed to export.';
        }

        return $body;
    }
}
