<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\Products\ProductResource;
use App\Models\Catalogue\Product;
use App\Services\InventoryService;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class LowStockProductsWidget extends BaseWidget
{
    protected int | string | array $columnSpan = 'full';

    protected static ?int $sort = 2;

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Product::query()
                    ->where('published', true)
                    ->where(function ($query) {
                        // Products with inventory sources - use subquery to calculate available stock
                        $query->whereHas('inventorySources', function ($subQuery) {
                            $subQuery->select('product_id')
                                ->groupBy('product_id')
                                ->havingRaw(
                                    'SUM(quantity - reserved_quantity) <= COALESCE(
                                        (SELECT low_stock_threshold FROM products WHERE products.id = product_inventory_source.product_id),
                                        10)'
                                );
                        })
                            ->orWhere(function ($query) {
                                $query->where('published', true)
                                    ->whereDoesntHave('inventorySources')
                                    ->whereRaw('COALESCE(quantity, 0) <= COALESCE(low_stock_threshold, 10)');
                            });
                    })
            )
            ->columns([
                TextColumn::make('name')
                    ->label('Product')
                    ->searchable()
                    ->url(fn (Product $record): string => ProductResource::getUrl('edit', ['record' => $record]))
                    ->openUrlInNewTab(),

                TextColumn::make('sku')
                    ->label('SKU')
                    ->searchable(),

                TextColumn::make('quantity')
                    ->label('Default Stock')
                    ->getStateUsing(function (Product $record) {
                        // If using inventory sources, sum up from all sources
                        if ($record->inventorySources()->exists()) {
                            return $record->inventorySources()
                                ->get()
                                ->sum(fn($source) => $source->pivot->quantity - $source->pivot->reserved_quantity);
                        }
                        return $record->quantity ?? 0;
                    }),

                TextColumn::make('low_stock_threshold')
                    ->label('Threshold')
                    ->getStateUsing(fn (Product $record) => $record->low_stock_threshold ?? 10),

                BadgeColumn::make('status')
                    ->label('Status')
                    ->getStateUsing(function (Product $record) {
                        $stock = 0;
                        if ($record->inventorySources()->exists()) {
                            $stock = $record->inventorySources()
                                ->get()
                                ->sum(fn($source) => $source->pivot->quantity - $source->pivot->reserved_quantity);
                        } else {
                            $stock = $record->quantity ?? 0;
                        }
                        
                        if ($stock <= 0) {
                            return 'Out of Stock';
                        }
                        
                        $threshold = $record->low_stock_threshold ?? 10;
                        if ($stock <= $threshold) {
                            return 'Low Stock';
                        }
                        
                        return 'In Stock';
                    })
                    ->colors([
                        'danger' => 'Out of Stock',
                        'warning' => 'Low Stock',
                        'success' => 'In Stock',
                    ]),
            ])
            ->heading('Low Stock Alerts')
            ->description('Products that need restocking');
    }
}
