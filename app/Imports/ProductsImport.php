<?php

namespace App\Imports;

use App\Models\Catalogue\Product;
use App\Models\ImportExport\ImportExport;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\OnEachRow;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Row;

class ProductsImport implements OnEachRow, WithBatchInserts, WithChunkReading, WithValidation
{
    use Importable;

    protected ImportExport $importExport;
    protected int $rowIndex = 0;
    protected int $successfulRows = 0;
    protected int $failedRows = 0;

    public function __construct(ImportExport $importExport)
    {
        $this->importExport = $importExport;
    }

    public function onRow(Row $row)
    {
        $this->rowIndex++;

        // Skip header row
        if ($this->rowIndex === 1) {
            return;
        }

        $data = $row->toArray();

        try {
            $product = $this->createOrUpdateProduct($data);
            $this->successfulRows++;
            
            Log::channel('import_export')->debug('Product imported successfully', [
                'row' => $this->rowIndex,
                'product_id' => $product->id,
                'sku' => $product->sku,
            ]);
        } catch (\Exception $e) {
            $this->failedRows++;
            $this->importExport->addValidationError(
                $this->rowIndex,
                'general',
                $e->getMessage()
            );

            Log::channel('import_export')->warning('Product import failed', [
                'row' => $this->rowIndex,
                'error' => $e->getMessage(),
                'data' => $data,
            ]);
        }
    }

    public function batchSize(): int
    {
        return 100;
    }

    public function chunkSize(): int
    {
        return 100;
    }

    public function rules(): array
    {
        return [
            '*.0' => 'required|string|max:255', // name
            '*.1' => 'nullable|string|max:255|unique:products,sku', // sku
            '*.2' => 'nullable|string|max:255', // slug
            '*.3' => 'nullable|numeric|min:0', // price
            '*.4' => 'nullable|numeric|min:0', // compare_price
            '*.5' => 'nullable|numeric|min:0', // cost
            '*.6' => 'nullable|integer|min:0', // quantity
            '*.7' => 'nullable|integer|min:0', // low_stock_threshold
            '*.8' => 'nullable|string|max:1000', // short_description
            '*.9' => 'nullable|string', // description
            '*.10' => 'nullable|in:pending,approved,rejected', // status
            '*.11' => 'nullable|boolean', // published
            '*.12' => 'nullable|boolean', // is_featured
            '*.13' => 'nullable|exists:categories,id', // category_id
            '*.14' => 'nullable|exists:brands,id', // brand_id
        ];
    }

    public function customValidationMessages(): array
    {
        return [
            '*.0.required' => 'Product name is required',
            '*.1.unique' => 'SKU already exists',
            '*.3.numeric' => 'Price must be a number',
            '*.13.exists' => 'Category does not exist',
            '*.14.exists' => 'Brand does not exist',
        ];
    }

    protected function createOrUpdateProduct(array $data): Product
    {
        // Map CSV columns to product fields
        $productData = [
            'name' => $data[0] ?? null,
            'sku' => $data[1] ?? null,
            'slug' => $data[2] ?? null,
            'price' => $data[3] ?? 0,
            'compare_price' => $data[4] ?? null,
            'cost' => $data[5] ?? null,
            'quantity' => $data[6] ?? 0,
            'low_stock_threshold' => $data[7] ?? 10,
            'short_description' => $data[8] ?? null,
            'description' => $data[9] ?? null,
            'status' => $data[10] ?? 'pending',
            'published' => $data[11] ?? false,
            'is_featured' => $data[12] ?? false,
            'category_id' => $data[13] ?? null,
            'brand_id' => $data[14] ?? null,
        ];

        // Generate slug if not provided
        if (empty($productData['slug']) && $productData['name']) {
            $productData['slug'] = \Str::slug($productData['name']);
        }

        // Find existing product by SKU or create new one
        $product = Product::where('sku', $productData['sku'])->first();

        if ($product) {
            $product->update(array_filter($productData));
        } else {
            $product = Product::create(array_filter($productData));
        }

        return $product;
    }

    public function import(ImportExport $importExport): void
    {
        // Get total rows from the import file
        $totalRows = $this->getTotalRows($importExport->file_path);
        $importExport->update(['total_rows' => $totalRows]);

        // Process the import
        $this->importToDatabase();

        // Mark as completed
        $importExport->markAsCompleted($this->successfulRows, $this->failedRows);
    }

    protected function getTotalRows(string $filePath): int
    {
        $file = storage_path('app/' . $filePath);
        $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($file);
        return $spreadsheet->getActiveSheet()->getHighestRow();
    }

    protected function importToDatabase(): void
    {
        $filePath = storage_path('app/' . $this->importExport->file_path);
        
        $this->import($filePath);
    }
}
