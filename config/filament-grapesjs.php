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
            'grapesjs-rte-extensions' => 'https://unpkg.com/grapesjs-rte-extensions/dist/grapesjs-rte-extensions.min.css',
            'grapesjs-uppy' => 'https://unpkg.com/grapesjs-uppy/dist/grapesjs-uppy.min.css',
            // 'quill-bubble' => 'css/quill.bubble.css',
            // 'grapesjs-component-code-editor' => 'css/grapesjs-component-code-editor.min.css',
            // 'grapesjs-plugin-toolbox' => 'css/grapesjs-plugin-toolbox.min.css',
            // 'grapesjs-rulers' => 'css/grapesjs-rulers.min.css',
            // 'grapesjs-undraw' => 'css/grapesjs-undraw.min.css',
        ],

        'js' => [
            // slug => path to js file in your resources directory
            // 'slug' => 'path/to/js/file.js',
            'gjs-blocks-basic' => 'https://unpkg.com/grapesjs-blocks-basic',
            'grapesjs-dinvitations' => 'js/grapesjs-dinvitations.min.js',
            'grapesjs-rte-extensions' => 'https://unpkg.com/grapesjs-rte-extensions',
            'grapesjs-uppy' => 'https://unpkg.com/grapesjs-uppy',
            'grapesjs-navbar' => 'https://unpkg.com/grapesjs-navbar',
            'grapesjs-parser-postcss' => 'https://unpkg.com/grapesjs-parser-postcss',
            'grapesjs-tabs' => 'https://unpkg.com/grapesjs-tabs',
            'grapesjs-tooltip' => 'https://unpkg.com/grapesjs-tooltip',
            'grapesjs-component-countdown' => 'https://unpkg.com/grapesjs-component-countdown',
            'grapesjs-plugin-forms' => 'https://unpkg.com/grapesjs-plugin-forms',
            'grapesjs-typed' => 'https://unpkg.com/grapesjs-typed',
            // 'gjs-quill' => 'js/gjs-quill.min.js',
            // 'grapesjs-calendly' => 'js/grapesjs-calendly.min.js',
            // 'grapesjs-component-code-editor' => 'js/grapesjs-component-code-editor.min.js',
            // 'grapesjs-custom-code' => 'js/grapesjs-custom-code.min.js',
            // 'grapesjs-style-bg' => 'js/grapesjs-style-bg.min.js',
            // 'grapesjs-style-border' => 'js/grapesjs-style-border.min.js',
            // 'grapesjs-style-easing' => 'js/grapesjs-style-easing.min.js',
            // 'grapesjs-style-filter' => 'js/grapesjs-style-filter.min.js',
            // 'grapesjs-style-gpickr' => 'js/grapesjs-style-gpickr.min.js',
            // 'grapesjs-plugin-export' => 'js/grapesjs-plugin-export.min.js',
            // 'grapesjs-plugin-toolbox' => 'js/grapesjs-plugin-toolbox.min.js',
            // 'grapesjs-preset-webpage' => 'js/grapesjs-preset-webpage.min.js',
            // 'grapesjs-rulers' => 'js/grapesjs-rulers.min.js',
            // 'grapesjs-script-editor' => 'js/grapesjs-script-editor.min.js',
            // 'grapesjs-undraw' => 'js/grapesjs-undraw.min.js',
        ]
    ]
];
