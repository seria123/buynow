<?php

namespace App\Imports;

use App\Models\User;
use App\Models\ImportExport\ImportExport;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\OnEachRow;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Row;

class CustomersImport implements OnEachRow, WithBatchInserts, WithChunkReading, WithValidation
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
            $customer = $this->createOrUpdateCustomer($data);
            $this->successfulRows++;
            
            Log::channel('import_export')->debug('Customer imported successfully', [
                'row' => $this->rowIndex,
                'customer_id' => $customer->id,
                'email' => $customer->email,
            ]);
        } catch (\Exception $e) {
            $this->failedRows++;
            $this->importExport->addValidationError(
                $this->rowIndex,
                'general',
                $e->getMessage()
            );

            Log::channel('import_export')->warning('Customer import failed', [
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
            '*.1' => 'required|email|max:255|unique:users,email', // email
            '*.2' => 'nullable|string|max:20', // phone
            '*.3' => 'nullable|string|max:255', // username
            '*.4' => 'nullable|exists:customer_groups,id', // customer_group_id
            '*.5' => 'nullable|boolean', // marketing_opt_in
        ];
    }

    public function customValidationMessages(): array
    {
        return [
            '*.0.required' => 'Customer name is required',
            '*.1.required' => 'Email is required',
            '*.1.email' => 'Invalid email format',
            '*.1.unique' => 'Email already exists',
            '*.4.exists' => 'Customer group does not exist',
        ];
    }

    protected function createOrUpdateCustomer(array $data): User
    {
        // Map CSV columns to user fields
        $customerData = [
            'name' => $data[0] ?? null,
            'email' => $data[1] ?? null,
            'phone' => $data[2] ?? null,
            'username' => $data[3] ?? null,
            'customer_group_id' => $data[4] ?? null,
            'marketing_opt_in' => $data[5] ?? false,
        ];

        // Find existing customer by email or create new one
        $customer = User::where('email', $customerData['email'])->first();

        if ($customer) {
            // Update existing customer (exclude password for existing users)
            $customer->update(array_filter($customerData));
        } else {
            // Create new customer with a temporary password
            $customerData['password'] = Hash::make(config('app.default_customer_password', 'password'));
            $customer = User::create(array_filter($customerData));
        }

        return $customer;
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
