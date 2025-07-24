// resources/js/qrScanner.js
if (!window.qrScanner) {
    window.qrScanner =  function(options = {}) {
        return {
            scanner: null,
            isProcessing: false,
            lastScan: null,
            errorMessage: '',
            successMessage: '',
            pendingPayload: null,
            guestCount: 1,
            apiKey: options.apiKey || '',
            scanRoute: options.scanRoute || '',
            csrfToken: options.csrfToken || '',

            initScanner() {
                try {
                    if (this.scanner) this.scanner.clear();

                    this.scanner = new Html5QrcodeScanner('reader', {
                        fps: 10,
                        qrbox: 250,
                    }, false);

                    this.scanner.render(this.onScanSuccess.bind(this));
                } catch (err) {
                    console.error('Scanner init failed:', err);
                }
            },

            async onScanSuccess(decodedText) {
                if (this.isProcessing || decodedText === this.lastScan) return;

                this.isProcessing = true;
                this.lastScan = decodedText;
                this.errorMessage = '';

                let qrPayload;

                try {
                    qrPayload = JSON.parse(decodedText);
                } catch {
                    this.showError('Invalid QR format. Please try scanning again.');
                    return;
                }

                if (!this.isValidPayload(qrPayload)) {
                    this.showError('This QR code is not valid. Please scan a valid QR code.');
                    return;
                }

                if (qrPayload.type === 'attendance') {
                    this.pendingPayload = qrPayload;
                    this.isProcessing = false;
                    this.$dispatch('open-modal', { id: 'guest-count-modal' });
                } else {
                    await this.submitPayload(qrPayload);
                }
            },

            isValidPayload(payload) {
                return (
                    payload &&
                    typeof payload === 'object' &&
                    payload.id &&
                    ['attendance', 'souvenir'].includes(payload.type)
                );
            },

            async submitPayload(payload, guestCount = 1) {
                try {
                    const response = await fetch(this.scanRoute, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': this.csrfToken,
                        },
                        body: JSON.stringify({
                            qrPayload: payload,
                            guestCount
                        }),
                    });

                    const data = await response.json();

                    if (!response.ok) throw new Error(data.message || 'Unable to process this QR code.');

                    this.successMessage = data.message || 'Success!';

                    if (data.qrcode_view_url) {
                        window.open(data.qrcode_view_url, '_blank');
                    }
                } catch (err) {
                    this.showError(err.message || 'An unexpected error occurred.');
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
            },

            showError(message) {
                this.errorMessage = message;
                this.isProcessing = false;
                this.lastScan = null;
            },

            resetScan() {
                this.lastScan = null;
                this.errorMessage = '';
            }
        };
    }
}