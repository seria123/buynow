<?php

use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;

?>

{{-- filament/pages/import-export.blade.php --}}

<x-filament-panels::page>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        {{-- Import Section --}}
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <h2 class="text-lg font-semibold mb-4">Import Data</h2>
            
            <div class="space-y-4">
                {{ $this->importProductsAction }}
                {{ $this->importCustomersAction }}
            </div>
        </div>

        {{-- Export Section --}}
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <h2 class="text-lg font-semibold mb-4">Export Data</h2>
            
            <div class="space-y-4">
                {{ $this->exportProductsAction }}
                {{ $this->exportCustomersAction }}
            </div>
        </div>
    </div>

    <div class="mt-6 bg-white dark:bg-gray-800 rounded-lg shadow p-6">
        <h2 class="text-lg font-semibold mb-4">Import/Export History</h2>
        <p class="text-gray-600 dark:text-gray-400">
            View your import/export history in the 
            <a href="{{ route('filament.admin.resources.import-exports.index') }}" class="text-primary-600 hover:underline">
                Import/Export Logs
            </a>
            section.
        </p>
    </div>
</x-filament-panels::page>
