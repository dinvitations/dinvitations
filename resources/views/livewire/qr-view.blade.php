<div style="text-align: center; margin-top: 2rem;">
    <p>
        <img src="{{ $imageUrl }}" alt="QR Code" style="max-width: 100%; height: auto; border: 2px solid #333;" />
    </p>

    <p>
        <button onclick="printPdf()" class="button" style="padding: 0.5rem 1rem; font-size: 1rem; cursor: pointer;">
            Print PDF
        </button>
    </p>
</div>

<script>
    function printPdf() {
        const pdfUrl = @json($pdfUrl);
        window.open(pdfUrl, '_blank');
    }
</script>
