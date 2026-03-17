<?php

namespace App\Exports;

use App\Models\Catalogue\Product;
use App\Models\ImportExport\ImportExport;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class ProductsExport implements FromCollection, WithHeadings, WithMapping
{
    use Exportable;

    protected ImportExport $importExport;

    public function __construct(ImportExport $importExport)
    {
        $this->importExport = $importExport;
    }

    public function collection(): Collection
    {
        return Product::with(['category', 'brand'])
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function headings(): array
    {
        return [
            'ID',
            'Name',
            'SKU',
            'Slug',
            'Price',
            'Compare Price',
            'Cost',
            'Quantity',
            'Low Stock Threshold',
            'Short Description',
            'Description',
            'Status',
            'Published',
            'Featured',
            'Category',
            'Brand',
            'Created At',
            'Updated At',
        ];
    }

    public function map($product): array
    {
        return [
            $product->id,
            $product->name,
            $product->sku,
            $product->slug,
            $product->price,
            $product->compare_price,
            $product->cost,
            $product->quantity,
            $product->low_stock_threshold,
            $product->short_description,
            strip_tags($product->description),
            $product->status?->value ?? $product->status,
            $product->published ? 'Yes' : 'No',
            $product->is_featured ? 'Yes' : 'No',
            $product->category?->name,
            $product->brand?->name,
            $product->created_at?->format('Y-m-d H:i:s'),
            $product->updated_at?->format('Y-m-d H:i:s'),
        ];
    }
}
