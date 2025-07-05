<div
    x-data="{
        scanner: null,
        isProcessing: false,
        lastScan: null,
        errorMessage: '',
        successMessage: '',
        apiKey: '{{ config('app.qr_api_key') }}',
        userId: '{{ auth()->user()->id }}',
        pendingPayload: null,
        guestCount: 1,

        initScanner() {
            try {
                if (this.scanner) {
                    this.scanner.clear();
                }

                this.scanner = new Html5QrcodeScanner('reader', { fps: 10, qrbox: 250 }, false);

                this.scanner.render(async decodedText => {
                    if (this.isProcessing || decodedText === this.lastScan) return;

                    this.isProcessing = true;
                    this.lastScan = decodedText;
                    this.errorMessage = '';

                    let qrPayload = null;

                    try {
                        qrPayload = JSON.parse(decodedText);
                    } catch (e) {
                        this.errorMessage = 'Invalid QR format. Please try scanning again.';
                        this.isProcessing = false;
                        this.lastScan = null;
                        return;
                    }

                    if (!qrPayload || typeof qrPayload !== 'object' || !qrPayload.id || !['attendance', 'souvenir'].includes(qrPayload.type)) {
                        this.errorMessage = 'This QR code is not valid. Please scan a valid QR code.';
                        this.isProcessing = false;
                        this.lastScan = null;
                        return;
                    }

                    if (qrPayload.type === 'attendance') {
                        this.pendingPayload = qrPayload;
                        this.isProcessing = false;
                        this.$dispatch('open-modal', { id: 'guest-count-modal' });
                        return;
                    }

                    await this.submitPayload(qrPayload);
                });

            } catch (err) {
                console.error('Scanner init failed:', err);
            }
        },

        async submitPayload(payload, guestCount = 1) {
            try {
                const response = await fetch('/api/scan-qrcode', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Authorization': 'Bearer ' + this.apiKey
                    },
                    body: JSON.stringify({
                        qrPayload: payload,
                        userId: this.userId,
                        guestCount: guestCount
                    })
                });

                const data = await response.json();

                if (!response.ok) {
                    throw new Error(data.message || 'Unable to process this QR code. Please try again.');
                }

                this.successMessage = data.message || 'Success!';

                if (data.pdf_url) {
                    window.open(data.pdf_url, '_blank');
                }
            } catch (err) {
                console.error(err);
                this.errorMessage = err.message || 'An unexpected error occurred. Please try again.';
            } finally {
                this.isProcessing = false;
                this.pendingPayload = null;
                setTimeout(() => {
                    this.lastScan = null;
                    this.successMessage = '';
                }, 1500);
            }
        },

        async stopScanner() {
            if (this.scanner) {
                try {
                    await this.scanner.clear();
                    this.scanner = null;
                    console.log('Scanner stopped');
                } catch (e) {
                    console.error('Error stopping scanner:', e);
                }
            }
        }
    }"
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

        <div id="reader" class="w-full min-h-[320px] h-auto bg-white shadow-md border rounded-md"></div>

        <x-filament::modal
            id="guest-count-modal"
            icon="heroicon-o-users"
            icon-color="primary"
            width="md"
            alignment="center"
            x-on:close-modal="lastScan = null"
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
