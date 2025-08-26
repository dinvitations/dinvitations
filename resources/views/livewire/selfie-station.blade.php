<div
    x-data="selfieStation({
        apiKey: '{{ config('app.selfie_api_key') }}',
        uploadRoute: '{{ route('selfie.upload') }}',
        csrfToken: '{{ csrf_token() }}',
        guestId: '{{ $guestId }}'
    })"
    x-init="init()"
    class="relative w-screen h-screen bg-black">

    <!-- Video Feed -->
    <video id="webcam" autoplay playsinline class="absolute top-0 left-0 w-full h-full object-cover z-0"></video>

    <!-- Selfie Frame Corners -->
    <div class="absolute inset-4 pointer-events-none z-10">
        <template x-for="corner in [
            { pos: 'top-0 left-0', borders: 'border-t-4 border-l-4' },
            { pos: 'top-0 right-0', borders: 'border-t-4 border-r-4' },
            { pos: 'bottom-0 left-0', borders: 'border-b-4 border-l-4' },
            { pos: 'bottom-0 right-0', borders: 'border-b-4 border-r-4' }
        ]" :key="corner.pos">
            <div
                :class="`absolute ${corner.pos} w-10 h-10 border-white ${corner.borders}`"
            ></div>
        </template>
    </div>

    <!-- Capture + Stop Buttons -->
    <div class="z-10 absolute bottom-10 left-1/2 transform -translate-x-1/2 flex items-center gap-4">
        <!-- Capture -->
        <button
            @click="capture"
            class="bg-white p-6 rounded-full shadow-lg hover:bg-gray-200"
            aria-label="Capture Selfie"
        >
            <!-- Heroicon: Camera -->
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="size-6">
                <path d="M12 9a3.75 3.75 0 1 0 0 7.5A3.75 3.75 0 0 0 12 9Z" />
                <path fill-rule="evenodd" d="M9.344 3.071a49.52 49.52 0 0 1 5.312 0c.967.052 1.83.585 2.332 1.39l.821 1.317c.24.383.645.643 1.11.71.386.054.77.113 1.152.177 1.432.239 2.429 1.493 2.429 2.909V18a3 3 0 0 1-3 3h-15a3 3 0 0 1-3-3V9.574c0-1.416.997-2.67 2.429-2.909.382-.064.766-.123 1.151-.178a1.56 1.56 0 0 0 1.11-.71l.822-1.315a2.942 2.942 0 0 1 2.332-1.39ZM6.75 12.75a5.25 5.25 0 1 1 10.5 0 5.25 5.25 0 0 1-10.5 0Zm12-1.5a.75.75 0 1 0 0-1.5.75.75 0 0 0 0 1.5Z" clip-rule="evenodd" />
            </svg>

        </button>

        <!-- Toggle Camera Button -->
        <button
            @click="toggleCamera"
            :class="isCameraOn ? 'bg-red-600 hover:bg-red-700 text-white' : 'bg-green-600 hover:bg-green-700 text-white'"
            class="p-3 rounded-full shadow-lg"
            x-tooltip="isCameraOn ? 'Stop Camera' : 'Start Camera'"
            aria-label="Toggle Camera"
        >
            <template x-if="isCameraOn">
                <!-- Stop Icon -->
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="size-6">
                    <path fill-rule="evenodd" d="M4.5 7.5a3 3 0 0 1 3-3h9a3 3 0 0 1 3 3v9a3 3 0 0 1-3 3h-9a3 3 0 0 1-3-3v-9Z" clip-rule="evenodd" />
                </svg>
            </template>
            <template x-if="!isCameraOn">
                <!-- Play Icon -->
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="size-6">
                    <path fill-rule="evenodd" d="M4.5 5.653c0-1.427 1.529-2.33 2.779-1.643l11.54 6.347c1.295.712 1.295 2.573 0 3.286L7.28 19.99c-1.25.687-2.779-.217-2.779-1.643V5.653Z" clip-rule="evenodd" />
                </svg>
            </template>
        </button>
    </div>

    <!-- Hidden Canvas -->
    <canvas id="canvas" class="hidden"></canvas>

    <!-- Preview Modal -->
    <x-filament::modal
        id="selfie-preview-modal"
        icon="heroicon-o-camera"
        icon-color="primary"
        width="md"
        alignment="center"
    >
        <x-slot name="heading">
            {{ __('Preview Selfie') }}
        </x-slot>

        <x-slot name="description">
            {{ __('Make sure you’re happy with your photo before continuing.') }}
        </x-slot>

        <div class="w-full">
            <img :src="preview" alt="Selfie preview" class="w-full rounded border mb-4" />
        </div>

        <x-slot name="footer">
            <div class="flex justify-center gap-4">
                <x-filament::button
                    color="gray"
                    x-on:click="$dispatch('close-modal', { id: 'selfie-preview-modal' })"
                >
                    {{ __('Retake') }}
                </x-filament::button>

                <x-filament::button
                    color="primary"
                    x-on:click="confirm"
                >
                    {{ __('Continue') }}
                </x-filament::button>
            </div>
        </x-slot>
    </x-filament::modal>
</div>