import preset from './vendor/filament/support/tailwind.config.preset';

const safelist = require('./tailwind-safelist.json');

export default {
    presets: [preset],
    content: [
        './app/Filament/**/*.php',
        './resources/views/filament/**/*.blade.php',
        './vendor/filament/**/*.blade.php',
    ],
    safelist
}