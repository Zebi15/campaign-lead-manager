<x-filament-panels::page>
    <form wire:submit="import">
        {{ $this->form }}
        
        <div class="mt-4">
            <x-filament::button type="submit">
                Import Leads
            </x-filament::button>
        </div>
    </form>
</x-filament-panels::page>