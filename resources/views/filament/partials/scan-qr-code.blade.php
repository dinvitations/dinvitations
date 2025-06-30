<div
    x-data="{
        scanner: null,
        isProcessing: false,
        lastScan: null,
        errorMessage: '',
        apiKey: '{{ config('app.qr_api_key') }}',
        userId: '{{ auth()->user()->id }}',

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

                    if (!qrPayload || typeof qrPayload !== 'object' || !qrPayload.id || qrPayload.type !== 'attendance') {
                        this.errorMessage = 'This QR code is not valid for check-in. Please scan a valid attendance QR code.';
                        this.isProcessing = false;
                        this.lastScan = null;
                        return;
                    }

                    try {
                        const response = await fetch('/api/scan-qrcode', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Authorization': 'Bearer ' + this.apiKey
                            },
                            body: JSON.stringify({
                                qrPayload,
                                userId: this.userId
                            })
                        });

                        const data = await response.json();

                        if (!response.ok || !data.pdf_url) {
                            throw new Error(data.message || 'Unable to process this QR code. Please try again.');
                        }

                        window.open(data.pdf_url, '_blank');

                    } catch (err) {
                        console.error(err);
                        this.errorMessage = err.message || 'An unexpected error occurred. Please try again.';
                    } finally {
                        this.isProcessing = false;
                        setTimeout(() => {
                            this.lastScan = null;
                        }, 1500);
                    }
                });

            } catch (err) {
                console.error('Scanner init failed:', err);
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

        <div id="reader" class="w-full min-h-[320px] h-auto bg-white shadow-md border rounded-md"></div>
    </div>
</div>
