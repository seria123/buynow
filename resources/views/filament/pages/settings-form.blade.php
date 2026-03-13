<x-filament-panels::page>
    <div class="fi-form-scrollable">
        <form
            wire:submit.prevent="saveSettings"
            class="fi-form-form"
        >
            {{ $this->form }}

            <div class="mt-6 flex justify-end gap-3">
                <x-filament::button type="submit" color="primary">
                    Save
                </x-filament::button>
            </div>
        </form>
    </div>
</x-filament-panels::page>
