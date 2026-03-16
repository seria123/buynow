<?php

namespace App\Filament\Exporters;

use App\Models\User;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;

class CustomerExporter extends Exporter
{
    public static function getColumns(): array
    {
        return [
            ExportColumn::make('name')
                ->label('Name'),
            ExportColumn::make('email')
                ->label('Email'),
            ExportColumn::make('phone')
                ->label('Phone'),
            ExportColumn::make('customerGroup.name')
                ->label('Customer Group'),
            ExportColumn::make('total_orders')
                ->label('Total Orders'),
            ExportColumn::make('lifetime_value')
                ->label('Lifetime Value'),
            ExportColumn::make('average_order_value')
                ->label('Average Order Value'),
            ExportColumn::make('first_order_date')
                ->label('First Order Date'),
            ExportColumn::make('last_order_date')
                ->label('Last Order Date'),
            ExportColumn::make('is_active')
                ->label('Active')
                ->formatStateUsing(fn (bool $state): string => $state ? 'Yes' : 'No'),
            ExportColumn::make('created_at')
                ->label('Registered Date'),
        ];
    }

    public static function getModel(): string
    {
        return User::class;
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
