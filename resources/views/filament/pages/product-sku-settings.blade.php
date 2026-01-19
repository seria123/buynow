<x-filament-panels::page>
    <form wire:submit.prevent="save" class="space-y-6">
        {{ $this->form }}

        <div class="flex justify-end mt-6">
            <x-filament::button type="submit">
                Save settings
            </x-filament::button>
        </div>
    </form>
</x-filament-panels::page>
