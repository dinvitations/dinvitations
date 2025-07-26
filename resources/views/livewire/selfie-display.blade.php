@php
    $bgUrl = $background_url
        ? Storage::disk('minio')->temporaryUrl($background_url, now()->addMinutes(5))
        : null;
@endphp

<div
    wire:poll.30s
    class="flex flex-col text-white text-center px-6 py-10 h-screen bg-cover bg-center relative"
    style="background-image: url('{{ $bgUrl }}');"
>
    <div class="absolute inset-0 bg-black bg-opacity-50"></div>

    {{-- Center Section --}}
    <div class="relative z-10 flex-1 flex items-center justify-center">
        <div>
            <h2 class="text-lg md:text-xl font-medium mb-4">
                Welcome to {{ $event_name }}
            </h2>

            <h1 class="text-4xl md:text-6xl font-extrabold tracking-tight leading-tight">
                {{ $guest_name }}
            </h1>
        </div>
    </div>

    {{-- Bottom Section --}}
    <div class="relative z-10 mb-6">
        <div class="text-sm md:text-base font-medium mb-1">
            {{ $event_date }}
        </div>
        <div class="text-sm md:text-base font-medium">
            {{ $address }}
        </div>
    </div>
</div>
