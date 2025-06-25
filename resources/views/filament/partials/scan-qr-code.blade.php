<div 
    x-data="{
        scanner: null,
        initScanner() {
            try {
                this.scanner = new Html5QrcodeScanner('reader', { fps: 10, qrbox: 250 }, false);
                this.scanner.render(
                    decodedText => alert(`Scanned: ${decodedText}`)
                );
            } catch (err) {
                alert('Camera error: ' + err);
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
    <div class="mx-auto max-w-lg p-4">
        <div id="reader" class="w-full min-h-[320px] h-auto bg-gray-100"></div>
    </div>
</div>
