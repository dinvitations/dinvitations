<x-layouts.app>
    <div
        x-data="qrPrinter({
            pdfUrl: '{{ $pdfUrl }}',
        })"
        class="bg-gray-50 min-h-screen flex flex-col justify-center items-center overflow-hidden font-sans text-gray-800"
    >

        <!-- Hidden iframe for printing -->
        <iframe id="printFrame" class="hidden"></iframe>

        <!-- Centered Content -->
        <div class="bg-white p-6 rounded-xl shadow-xl w-full max-w-md text-center">
            <img src="{{ $imageUrl }}" alt="QR Code"
                 class="mx-auto max-w-full border border-gray-300 rounded-lg shadow-sm" />

            <div class="mt-6 flex flex-col gap-3">
                <x-filament::button
                    type="button"
                    color="primary"
                    x-on:click="printPdf()"
                >
                    Print PDF
                </x-filament::button>

                @if($hasSelfieFeature)
                    <x-filament::button
                        type="button"
                        color="gray"
                        x-on:click="window.location.href = '{{ route('selfie.capture') }}'"
                    >
                        Continue to Selfie
                    </x-filament::button>
                @else
                    <x-filament::button
                        type="button"
                        color="gray"
                        x-on:click="window.location.href = '{{ url()->previous() }}'"
                    >
                        Back
                    </x-filament::button>
                @endif
            </div>
        </div>
    </div>
</x-layouts.app>
