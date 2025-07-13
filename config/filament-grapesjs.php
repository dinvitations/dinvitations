<?php

declare(strict_types=1);

// config for Dotswan/FilamentGrapesjs
return [

    /**
     * If adding custom assets, you can add them here.
     * The files must be located in your application's resources directory and should be a relative path
     * ( the resource_path() function is used to locate the file )
     * After modifying, you must run composer dump-autoload to regenerate the minified files
     */
    'assets' => [

        'css' => [
            // slug => path to css file in your resources directory
            // 'slug' => 'path/to/css/file.css',
            'grapesjs-component-code-editor' => 'https://unpkg.com/grapesjs-component-code-editor/dist/grapesjs-component-code-editor.min.css',
            'grapesjs-rte-extensions' => 'https://unpkg.com/grapesjs-rte-extensions/dist/grapesjs-rte-extensions.min.css',
            'grapesjs-uppy' => 'https://unpkg.com/grapesjs-uppy/dist/grapesjs-uppy.min.css',
            // 'grapesjs-plugin-toolbox' => 'css/grapesjs-plugin-toolbox.min.css',
            // 'grapesjs-rulers' => 'css/grapesjs-rulers.min.css',
            // 'grapesjs-undraw' => 'css/grapesjs-undraw.min.css',
            // 'quill-bubble' => 'css/quill.bubble.css',
        ],

        'js' => [
            // slug => path to js file in your resources directory
            // 'slug' => 'path/to/js/file.js',
            'gjs-blocks-basic' => 'https://unpkg.com/grapesjs-blocks-basic',
            'grapesjs-component-code-editor' => 'https://unpkg.com/grapesjs-component-code-editor',
            'grapesjs-component-countdown' => 'https://unpkg.com/grapesjs-component-countdown',
            'grapesjs-custom-code' => 'https://unpkg.com/grapesjs-custom-code',
            'grapesjs-dinvitations' => 'js/grapesjs-dinvitations.min.js',
            'grapesjs-navbar' => 'https://unpkg.com/grapesjs-navbar',
            'grapesjs-parser-postcss' => 'https://unpkg.com/grapesjs-parser-postcss',
            'grapesjs-plugin-export' => 'https://unpkg.com/grapesjs-plugin-export',
            'grapesjs-plugin-forms' => 'https://unpkg.com/grapesjs-plugin-forms',
            'grapesjs-preset-webpage' => 'https://unpkg.com/grapesjs-preset-webpage',
            'grapesjs-rte-extensions' => 'https://unpkg.com/grapesjs-rte-extensions',
            'grapesjs-style-bg' => 'https://unpkg.com/grapesjs-style-bg',
            'grapesjs-tabs' => 'https://unpkg.com/grapesjs-tabs',
            'grapesjs-tooltip' => 'https://unpkg.com/grapesjs-tooltip',
            'grapesjs-touch' => 'https://unpkg.com/grapesjs-touch',
            'grapesjs-tui-image-editor' => 'https://unpkg.com/grapesjs-tui-image-editor',
            'grapesjs-typed' => 'https://unpkg.com/grapesjs-typed',
            // 'gjs-quill' => 'js/gjs-quill.min.js',
            // 'grapesjs-calendly' => 'js/grapesjs-calendly.min.js',
            // 'grapesjs-plugin-toolbox' => 'js/grapesjs-plugin-toolbox.min.js',
            // 'grapesjs-rulers' => 'js/grapesjs-rulers.min.js',
            // 'grapesjs-script-editor' => 'https://unpkg.com/grapesjs-script-editor',
            // 'grapesjs-style-border' => 'js/grapesjs-style-border.min.js',
            // 'grapesjs-style-easing' => 'js/grapesjs-style-easing.min.js',
            // 'grapesjs-style-filter' => 'js/grapesjs-style-filter.min.js',
            // 'grapesjs-style-gpickr' => 'js/grapesjs-style-gpickr.min.js',
            // 'grapesjs-undraw' => 'js/grapesjs-undraw.min.js',
            // 'grapesjs-uppy' => 'https://unpkg.com/grapesjs-uppy',
        ]
    ]
];
