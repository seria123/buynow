<?php

namespace App\Services\ImportExport;

use App\Models\ImportExport\ImportExport;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

abstract class BaseImportExportService
{
    protected ?ImportExport $importExport = null;
    protected User $user;

    abstract protected function getModelType(): string;

    public function setUser(User $user): self
    {
        $this->user = $user;
        return $this;
    }

    public function createImportRecord(UploadedFile $file): ImportExport
    {
        $fileName = $file->getClientOriginalName();
        $filePath = $file->store('imports', 'local');

        $this->importExport = ImportExport::create([
            'type' => ImportExport::TYPE_IMPORT,
            'model' => $this->getModelType(),
            'status' => ImportExport::STATUS_PENDING,
            'file_path' => $filePath,
            'file_name' => $fileName,
            'user_id' => $this->user->id,
        ]);

        return $this->importExport;
    }

    public function createExportRecord(): ImportExport
    {
        $this->importExport = ImportExport::create([
            'type' => ImportExport::TYPE_EXPORT,
            'model' => $this->getModelType(),
            'status' => ImportExport::STATUS_PENDING,
            'user_id' => $this->user->id,
        ]);

        return $this->importExport;
    }

    protected function getImportExport(): ImportExport
    {
        return $this->importExport;
    }

    protected function logActivity(string $action, array $context = []): void
    {
        $context['model_type'] = $this->getModelType();
        $context['import_export_id'] = $this->importExport?->id;
        $context['user_id'] = $this->user->id ?? null;

        Log::channel('import_export')->info(
            "Import/Export: {$action}",
            $context
        );
    }

    protected function getFilePath(): ?string
    {
        return $this->importExport?->file_path;
    }

    protected function deleteFile(?string $filePath): void
    {
        if ($filePath && Storage::disk('local')->exists($filePath)) {
            Storage::disk('local')->delete($filePath);
        }
    }

    public function shouldQueue(int $rowCount): bool
    {
        // Queue imports with more than 100 rows
        return $rowCount > 100;
    }
}
