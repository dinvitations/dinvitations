import preset from './vendor/filament/support/tailwind.config.preset';

export default {
    presets: [preset],
    content: [
        './app/Filament/**/*.php',
        './resources/views/filament/**/*.blade.php',
        './vendor/filament/**/*.blade.php',
        './resources/views/livewire/scan-qr-code.blade.php',
        './resources/views/livewire/selfie-station.blade.php',
        './resources/views/livewire/selfie-display.blade.php',
        './resources/views/livewire/greeting-display.blade.php',
        './resources/views/livewire/view-qr-code.blade.php',
    ]
}
