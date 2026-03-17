<?php

namespace App\Models\ImportExport;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\User;

class ImportExport extends Model
{
    use HasUuids;

    protected $table = 'import_exports';

    protected $fillable = [
        'type',
        'model',
        'status',
        'file_path',
        'file_name',
        'total_rows',
        'processed_rows',
        'successful_rows',
        'failed_rows',
        'error_message',
        'validation_errors',
        'user_id',
    ];

    protected $casts = [
        'validation_errors' => 'array',
        'total_rows' => 'integer',
        'processed_rows' => 'integer',
        'successful_rows' => 'integer',
        'failed_rows' => 'integer',
    ];

    public const TYPE_IMPORT = 'import';
    public const TYPE_EXPORT = 'export';

    public const MODEL_PRODUCT = 'product';
    public const MODEL_CUSTOMER = 'customer';

    public const STATUS_PENDING = 'pending';
    public const STATUS_PROCESSING = 'processing';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_FAILED = 'failed';

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isCompleted(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    public function isFailed(): bool
    {
        return $this->status === self::STATUS_FAILED;
    }

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function isProcessing(): bool
    {
        return $this->status === self::STATUS_PROCESSING;
    }

    public function markAsProcessing(): self
    {
        $this->update(['status' => self::STATUS_PROCESSING]);
        return $this;
    }

    public function markAsCompleted(int $successfulRows = 0, int $failedRows = 0): self
    {
        $this->update([
            'status' => self::STATUS_COMPLETED,
            'processed_rows' => $this->total_rows,
            'successful_rows' => $successfulRows,
            'failed_rows' => $failedRows,
        ]);
        return $this;
    }

    public function markAsFailed(string $errorMessage): self
    {
        $this->update([
            'status' => self::STATUS_FAILED,
            'error_message' => $errorMessage,
        ]);
        return $this;
    }

    public function updateProgress(int $processedRows, int $successfulRows = 0, int $failedRows = 0): self
    {
        $this->update([
            'processed_rows' => $processedRows,
            'successful_rows' => $successfulRows,
            'failed_rows' => $failedRows,
        ]);
        return $this;
    }

    public function addValidationError(int $row, string $field, string $message): self
    {
        $errors = $this->validation_errors ?? [];
        $errors[] = [
            'row' => $row,
            'field' => $field,
            'message' => $message,
        ];
        $this->update(['validation_errors' => $errors]);
        return $this;
    }
}
