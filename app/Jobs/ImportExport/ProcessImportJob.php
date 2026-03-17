<?php

namespace App\Jobs\ImportExport;

use App\Models\ImportExport\ImportExport;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Throwable;

class ProcessImportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $backoff = 60;

    public function __construct(
        public ImportExport $importExport
    ) {
        $this->onQueue('imports');
    }

    public function handle(): void
    {
        try {
            $this->importExport->markAsProcessing();

            Log::channel('import_export')->info('Starting import processing', [
                'import_export_id' => $this->importExport->id,
                'model' => $this->importExport->model,
                'file_path' => $this->importExport->file_path,
            ]);

            $importer = $this->getImporter();
            $importer->import($this->importExport);

            Log::channel('import_export')->info('Import processing completed', [
                'import_export_id' => $this->importExport->id,
                'successful_rows' => $this->importExport->successful_rows,
                'failed_rows' => $this->importExport->failed_rows,
            ]);

        } catch (Throwable $e) {
            Log::channel('import_export')->error('Import processing failed', [
                'import_export_id' => $this->importExport->id,
                'error' => $e->getMessage(),
            ]);

            $this->importExport->markAsFailed($e->getMessage());
            throw $e;
        }
    }

    protected function getImporter()
    {
        $model = $this->importExport->model;
        $importerClass = match ($model) {
            'product' => \App\Imports\ProductsImport::class,
            'customer' => \App\Imports\CustomersImport::class,
            default => throw new \InvalidArgumentException("Unknown import model: {$model}"),
        };

        return new $importerClass($this->importExport);
    }

    public function failed(Throwable $exception): void
    {
        Log::channel('import_export')->error('Import job failed permanently', [
            'import_export_id' => $this->importExport->id,
            'error' => $exception->getMessage(),
        ]);

        $this->importExport->markAsFailed($exception->getMessage());
    }
}
