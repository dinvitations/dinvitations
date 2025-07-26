// resources/js/qrPrinter.js
if (!window.qrPrinter) {
    window.qrPrinter = function (options = {}) {
        return {
            pdfUrl: options.pdfUrl || '',

            printPdf() {
                if (!this.pdfUrl) {
                    alert("No PDF URL provided for printing.");
                    return;
                }

                const frame = document.getElementById('printFrame');
                if (!frame) {
                    alert("Print frame not found.");
                    return;
                }

                frame.src = this.pdfUrl;

                frame.onload = () => {
                    try {
                        frame.contentWindow.focus();
                        frame.contentWindow.print();
                    } catch (e) {
                        console.error("Failed to print:", e);
                    }
                };
            }
        };
    }
}
