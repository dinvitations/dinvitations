@php
    $bgUrl = $background_url
        ? Storage::disk('minio')->temporaryUrl($background_url, now()->addMinutes(5))
        : null;
@endphp

<head>
    <style>
        body, html {
            margin: 0;
            padding: 0;
            background: black;
            font-family: 'Inria+Serif', serif;
        }
    </style>
</head>
<body>
<form method="POST" action="{{ route('greeting.upload') }}">
    @csrf
    <input type="hidden" name="greeting" id="greetingCanvas">
    <input type="hidden" name="guest_id" id="guestIdInput" value="{{ $guest_id }}">

    <div class="w-screen h-screen flex items-center justify-center bg-black overflow-hidden">
        @if ($bgUrl)
            <div wire:poll.5s class="relative h-screen" x-data="signaturePadComponent({{ json_encode($guest_id) }})" x-init="init()">
                <img
                    src="{{ $bgUrl }}"
                    alt="Background"
                    class="h-screen w-auto object-contain z-0"
                />

                <div class="absolute inset-0 z-20 flex flex-col text-white text-center px-8 py-6 space-y-6">
                    {{-- Text --}}
                    <div class="pt-12">
                        <h2 class="text-lg md:text-xl font-medium py-6">
                            Welcome to <br>{{ $event_name }}
                        </h2>

                        <h1 class="text-5xl font-extrabold tracking-tight leading-tight py-6">
                            {{ $guest_name }}
                        </h1>

                        <h2 class="text-lg md:text-xl font-medium py-6">
                            At {{ $address }}
                        </h2>

                        <h2 class="text-lg md:text-xl font-medium pt-6">
                            Write down your love, hopes, and prayers <br> right here.
                        </h2>
                    </div>

                    {{-- Canvas --}}
                    <div class="flex justify-center px-6">
                        <div class="w-full overflow-hidden">
                            <canvas
                                x-ref="canvas"
                                class="w-full touch-none rounded-[45px]"
                                style="touch-action: none; height: 40vh;"
                            ></canvas>
                        </div>
                    </div>

                    {{-- Buttons --}}
                    <div class="flex justify-between gap-8 px-6 pb-6">
                        <button
                            type="button"
                            x-on:click="clear()"
                            class="flex-1 border border-white text-white font-semibold py-2 rounded-lg transition hover:bg-white hover:text-black"
                        >
                            Refresh
                        </button>

                        <button
                            type="submit"
                            x-on:click="submit"
                            class="flex-1 bg-white text-black font-semibold py-2 rounded-lg transition hover:opacity-90 disabled:opacity-30 disabled:cursor-not-allowed"
                        >
                            Submit
                        </button>
                    </div>
                </div>

                <div class="absolute inset-0 bg-black bg-opacity-50 z-10"></div>
            </div>
        @endif
    </div>
</form>

<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('signaturePadComponent', (guestId) => ({
            signaturePad: null,

            init() {
                const canvas = this.$refs.canvas;
                const ratio = Math.max(window.devicePixelRatio || 1, 1);
                canvas.width = canvas.offsetWidth * ratio;
                canvas.height = canvas.offsetHeight * ratio;
                canvas.getContext('2d').scale(ratio, ratio);

                this.signaturePad = new SignaturePad(canvas, {
                    penColor: 'black',
                    backgroundColor: 'rgba(255,255,255,0.8)',
                });
            },

            clear() {
                this.signaturePad.clear();
            },

            submit() {
                if (this.signaturePad.isEmpty()) {
                    if (confirm('Greeting is empty. Are you sure you want to submit without writing anything?')) {
                        document.getElementById('greetingCanvas').value = '';
                    } else {
                        event.preventDefault();
                    }
                } else {
                    document.getElementById('greetingCanvas').value = this.signaturePad.toDataURL();
                }
            }
        }));
    });
</script>
