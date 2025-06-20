<x-filament::page>
    {{ $this->table }}

    @if ($selectedGroup)
        <hr class="my-6" />
        <div>
            @livewire('guest-table', ['groupId' => $selectedGroup->id])
        </div>
    @endif
</x-filament::page>
