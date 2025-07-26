<div
    x-data="qrScanner({
        apiKey: '{{ config('app.qr_api_key') }}',
        scanRoute: '{{ route('qrcode.scan') }}',
        csrfToken: '{{ csrf_token() }}'
    })"
    x-init="
        initScanner();
        const observer = new MutationObserver(mutations => {
            for (const mutation of mutations) {
                for (const removedNode of mutation.removedNodes) {
                    if (removedNode.contains($el)) {
                        stopScanner();
                        observer.disconnect();
                    }
                }
            }
        });
        observer.observe(document.body, { childList: true, subtree: true });
    "
>
    <div class="w-full max-w-md space-y-4 text-center px-4">
        <template x-if="errorMessage">
            <div class="text-red-600 text-sm font-medium" x-text="errorMessage"></div>
        </template>

        <template x-if="successMessage">
            <div class="text-green-600 text-sm font-medium" x-text="successMessage"></div>
        </template>

        <div id="reader" class="w-full min-h-[320px] bg-white shadow-md border rounded-md"></div>

        <x-filament::modal
            id="guest-count-modal"
            icon="heroicon-o-users"
            icon-color="primary"
            width="md"
            alignment="center"
            x-on:close-modal="resetScan()"
        >
            <x-slot name="heading">
                {{ __('How many guests?') }}
            </x-slot>

            <x-slot name="description">
                {{ __('Please enter the number of guests attending.') }}
            </x-slot>

            <x-slot name="footer">
                <div class="space-y-4 w-full">
                    <x-filament::input.wrapper>
                        <x-filament::input
                            type="number"
                            min="1"
                            x-model="guestCount"
                            placeholder="Number of guests"
                        />
                    </x-filament::input.wrapper>

                    <div class="flex justify-end gap-2">
                        <x-filament::button
                            color="gray"
                            x-on:click="$dispatch('close-modal', { id: 'guest-count-modal' })"
                        >
                            {{ __('Cancel') }}
                        </x-filament::button>

                        <x-filament::button
                            color="primary"
                            x-on:click="
                                $dispatch('close-modal', { id: 'guest-count-modal' });
                                submitPayload(pendingPayload, guestCount);
                            "
                        >
                            {{ __('Submit') }}
                        </x-filament::button>
                    </div>
                </div>
            </x-slot>
        </x-filament::modal>
    </div>
</div>