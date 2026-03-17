<?php

namespace App\Filament\Exporters;

use App\Models\Catalogue\Product;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;

class ProductExporter extends Exporter
{
    public static function getColumns(): array
    {
        return [
            ExportColumn::make('id')
                ->label('ID'),
            ExportColumn::make('name')
                ->label('Name'),
            ExportColumn::make('sku')
                ->label('SKU'),
            ExportColumn::make('slug')
                ->label('Slug'),
            ExportColumn::make('price')
                ->label('Price'),
            ExportColumn::make('compare_price')
                ->label('Compare Price'),
            ExportColumn::make('cost')
                ->label('Cost'),
            ExportColumn::make('quantity')
                ->label('Quantity'),
            ExportColumn::make('low_stock_threshold')
                ->label('Low Stock Threshold'),
            ExportColumn::make('short_description')
                ->label('Short Description'),
            ExportColumn::make('status')
                ->label('Status')
                ->getStateUsing(fn (Product $record): string => $record->status?->value ?? 'pending'),
            ExportColumn::make('published')
                ->label('Published')
                ->getStateUsing(fn (Product $record): string => $record->published ? 'Yes' : 'No'),
            ExportColumn::make('is_featured')
                ->label('Featured')
                ->getStateUsing(fn (Product $record): string => $record->is_featured ? 'Yes' : 'No'),
            ExportColumn::make('category.name')
                ->label('Category')
                ->default('Uncategorized'),
            ExportColumn::make('brand.name')
                ->label('Brand')
                ->default('No Brand'),
            ExportColumn::make('created_at')
                ->label('Created At')
                ->format('Y-m-d H:i:s'),
            ExportColumn::make('updated_at')
                ->label('Updated At')
                ->format('Y-m-d H:i:s'),
        ];
    }

    public static function getModel(): string
    {
        return Product::class;
    }

    public function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Your product export has been completed and ' . number_format($export->successful_rows) . ' ' . str('row')->plural($export->successful_rows) . ' exported.';

        if ($export->failed_rows_count > 0) {
            $body .= ' ' . number_format($export->failed_rows_count) . ' ' . str('row')->plural($export->failed_rows_count) . ' failed to export.';
        }

        return $body;
    }
}
