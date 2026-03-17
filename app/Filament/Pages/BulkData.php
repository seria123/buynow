<?php

namespace App\Filament\Pages;

use App\Exports\CustomersExport;
use App\Exports\ProductsExport;
use App\Jobs\ImportExport\ProcessImportJob;
use App\Models\ImportExport\ImportExport;
use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;

class BulkData extends Page implements HasActions, HasForms
{
    use InteractsWithActions;
    use InteractsWithForms;

    protected string $view = 'filament.pages.bulk-data';

    public static function canAccess(): bool
    {
        return true;
    }

    public function getTitle(): string
    {
        return 'Bulk Data';
    }

    public function importProductsAction(): Action
    {
        return Action::make('importProducts')
            ->label('Import Products')
            ->form([
                FileUpload::make('file')
                    ->label('Upload File')
                    ->acceptedFileTypes([
                        'text/csv',
                        'application/vnd.ms-excel',
                        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                    ])
                    ->required()
                    ->maxSize(10240),
                Select::make('update_existing')
                    ->label('Update Existing Records')
                    ->options([
                        'yes' => 'Yes, update existing products',
                        'no' => 'No, skip existing products',
                    ])
                    ->default('yes'),
            ])
            ->action(function (array $data) {
                $this->processImport('product', $data['file']);
            })
            ->successNotification(
                fn () => Notification::make()
                    ->title('Import Started')
                    ->body('Your product import has been queued for processing.')
                    ->success()
            );
    }

    public function importCustomersAction(): Action
    {
        return Action::make('importCustomers')
            ->label('Import Customers')
            ->form([
                FileUpload::make('file')
                    ->label('Upload File')
                    ->acceptedFileTypes([
                        'text/csv',
                        'application/vnd.ms-excel',
                        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                    ])
                    ->required()
                    ->maxSize(10240),
                Select::make('update_existing')
                    ->label('Update Existing Records')
                    ->options([
                        'yes' => 'Yes, update existing customers',
                        'no' => 'No, skip existing customers',
                    ])
                    ->default('yes'),
            ])
            ->action(function (array $data) {
                $this->processImport('customer', $data['file']);
            })
            ->successNotification(
                fn () => Notification::make()
                    ->title('Import Started')
                    ->body('Your customer import has been queued for processing.')
                    ->success()
            );
    }

    public function exportProductsAction(): Action
    {
        return Action::make('exportProducts')
            ->label('Export Products')
            ->form([
                Select::make('format')
                    ->label('Export Format')
                    ->options([
                        'csv' => 'CSV',
                        'xlsx' => 'Excel',
                    ])
                    ->default('csv'),
            ])
            ->action(function (array $data) {
                $this->processExport('product', $data['format']);
            })
            ->successNotification(
                fn () => Notification::make()
                    ->title('Export Started')
                    ->body('Your product export is being generated.')
                    ->success()
            );
    }

    public function exportCustomersAction(): Action
    {
        return Action::make('exportCustomers')
            ->label('Export Customers')
            ->form([
                Select::make('format')
                    ->label('Export Format')
                    ->options([
                        'csv' => 'CSV',
                        'xlsx' => 'Excel',
                    ])
                    ->default('csv'),
            ])
            ->action(function (array $data) {
                $this->processExport('customer', $data['format']);
            })
            ->successNotification(
                fn () => Notification::make()
                    ->title('Export Started')
                    ->body('Your customer export is being generated.')
                    ->success()
            );
    }

    protected function processImport(string $model, string $filePath): void
    {
        $user = Auth::user();

        $importExport = ImportExport::create([
            'type' => ImportExport::TYPE_IMPORT,
            'model' => $model,
            'status' => ImportExport::STATUS_PENDING,
            'file_path' => $filePath,
            'file_name' => basename($filePath),
            'user_id' => $user->id,
        ]);

        ProcessImportJob::dispatch($importExport);
    }

    protected function processExport(string $model, string $format): void
    {
        $user = Auth::user();

        $importExport = ImportExport::create([
            'type' => ImportExport::TYPE_EXPORT,
            'model' => $model,
            'status' => ImportExport::STATUS_PROCESSING,
            'user_id' => $user->id,
        ]);

        try {
            $exportClass = match ($model) {
                'product' => ProductsExport::class,
                'customer' => CustomersExport::class,
            };

            $fileName = $model . '_export_' . now()->format('Y_m_d_His');

            $export = new $exportClass($importExport);

            $filePath = Excel::store($export, "exports/{$fileName}.{$format}", 'local');

            $importExport->update([
                'status' => ImportExport::STATUS_COMPLETED,
                'file_path' => "exports/{$fileName}.{$format}",
                'file_name' => "{$fileName}.{$format}",
                'total_rows' => $this->getExportRowCount($model),
                'processed_rows' => $this->getExportRowCount($model),
                'successful_rows' => $this->getExportRowCount($model),
            ]);

            Notification::make()
                ->title('Export Completed')
                ->body('Your export has been generated successfully.')
                ->success()
                ->send();

        } catch (\Exception $e) {
            $importExport->update([
                'status' => ImportExport::STATUS_FAILED,
                'error_message' => $e->getMessage(),
            ]);

            Notification::make()
                ->title('Export Failed')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

    protected function getExportRowCount(string $model): int
    {
        return match ($model) {
            'product' => \App\Models\Catalogue\Product::count(),
            'customer' => \App\Models\User::count(),
            default => 0,
        };
    }
}
