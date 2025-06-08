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
            'grapesjs-rte-extensions' => 'css/grapesjs-rte-extensions.min.css',
            'grapesjs-uppy' => 'css/grapesjs-uppy.min.css',
            // 'quill-bubble' => 'css/quill.bubble.css',
            // 'grapesjs-component-code-editor' => 'css/grapesjs-component-code-editor.min.css',
            // 'grapesjs-plugin-toolbox' => 'css/grapesjs-plugin-toolbox.min.css',
            // 'grapesjs-rulers' => 'css/grapesjs-rulers.min.css',
            // 'grapesjs-undraw' => 'css/grapesjs-undraw.min.css',
        ],

        'js' => [
            // slug => path to js file in your resources directory
            // 'slug' => 'path/to/js/file.js',
            'gjs-blocks-basic' => 'js/gjs-blocks-basic.min.js',
            'grapesjs-dinvitations' => 'js/grapesjs-dinvitations.min.js',
            'grapesjs-rte-extensions' => 'js/grapesjs-rte-extensions.min.js',
            'grapesjs-uppy' => 'js/grapesjs-uppy.min.js',
            'grapesjs-navbar' => 'js/grapesjs-navbar.min.js',
            'grapesjs-parser-postcss' => 'js/grapesjs-parser-postcss.min.js',
            'grapesjs-tabs' => 'js/grapesjs-tabs.min.js',
            'grapesjs-tooltip' => 'js/grapesjs-tooltip.min.js',
            'grapesjs-component-countdown' => 'js/grapesjs-component-countdown.min.js',
            'grapesjs-plugin-forms' => 'js/grapesjs-plugin-forms.min.js',
            'grapesjs-typed' => 'js/grapesjs-typed.min.js',
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
