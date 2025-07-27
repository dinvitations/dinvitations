@php
    $bgUrl = $background_url
        ? Storage::disk('minio')->temporaryUrl($background_url, now()->addMinutes(5))
        : null;
@endphp

<div class="w-screen h-screen flex items-center justify-center bg-black overflow-hidden">
    @if ($bgUrl)
        <div class="relative h-screen">
            {{-- Background image --}}
            <img
                src="{{ $bgUrl }}"
                alt="Background"
                class="h-screen w-auto object-contain z-0"
            />

            {{-- Canvas for drawing --}}
            <canvas
                id="drawingCanvas"
                class="absolute inset-0 z-30 touch-none"
                style="width: 100%; height: 100%;"
            ></canvas>

            {{-- Black translucent overlay --}}
            <div class="absolute inset-0 bg-black bg-opacity-50 z-10"></div>

            {{-- Text + Buttons --}}
            <div class="absolute inset-0 z-20 flex flex-col justify-between text-white text-center px-6 py-6 space-y-6">
                {{-- Top Text Section --}}
                <div class="pt-6">
                    <h2 class="text-lg md:text-xl font-medium mb-2">
                        Welcome to <br>{{ $event_name }}
                    </h2>

                    <h1 class="text-lg md:text-xl font-extrabold tracking-tight leading-tight mb-2">
                        {{ $guest_name }}
                    </h1>

                    <h2 class="text-lg md:text-xl font-medium mb-2">
                        At {{ $address }}
                    </h2>

                    <h2 class="text-lg md:text-xl font-medium">
                        Write down your love, hopes, and prayers right here.
                    </h2>
                </div>

                {{-- Buttons --}}
                <div class="flex justify-between gap-4 px-2 pb-6">
                    <button
                        type="button"
                        wire:click="$refresh"
                        class="flex-1 border border-white text-white font-semibold py-2 rounded-lg transition hover:bg-white hover:text-black"
                    >
                        Refresh
                    </button>

                    <button
                        type="submit"
                        class="flex-1 bg-white text-black font-semibold py-2 rounded-lg transition hover:opacity-90"
                    >
                        Submit
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>

<script>
    const canvas = document.getElementById('drawingCanvas');
    const ctx = canvas.getContext('2d');

    let isDrawing = false;

    // Resize canvas to actual pixel size
    function resizeCanvas() {
        const rect = canvas.getBoundingClientRect();
        canvas.width = rect.width;
        canvas.height = rect.height;
    }

    window.addEventListener('load', resizeCanvas);
    window.addEventListener('resize', resizeCanvas);

    function getPosition(e) {
        const rect = canvas.getBoundingClientRect();
        return {
            x: (e.touches ? e.touches[0].clientX : e.clientX) - rect.left,
            y: (e.touches ? e.touches[0].clientY : e.clientY) - rect.top
        };
    }

    function startDraw(e) {
        e.preventDefault();
        isDrawing = true;
        const pos = getPosition(e);
        ctx.beginPath();
        ctx.moveTo(pos.x, pos.y);
    }

    function draw(e) {
        if (!isDrawing) return;
        e.preventDefault();
        const pos = getPosition(e);
        ctx.lineTo(pos.x, pos.y);
        ctx.strokeStyle = '#ffffff';
        ctx.lineWidth = 2;
        ctx.lineCap = 'round';
        ctx.stroke();
    }

    function endDraw() {
        isDrawing = false;
        ctx.closePath();
    }

    // Mouse
    canvas.addEventListener('mousedown', startDraw);
    canvas.addEventListener('mousemove', draw);
    canvas.addEventListener('mouseup', endDraw);
    canvas.addEventListener('mouseout', endDraw);

    // Touch
    canvas.addEventListener('touchstart', startDraw, { passive: false });
    canvas.addEventListener('touchmove', draw, { passive: false });
    canvas.addEventListener('touchend', endDraw);
</script>
