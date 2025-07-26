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
            'grapesjs-dinvitations' => 'css/grapesjs-dinvitations.min.css',
            'grapick' => 'https://unpkg.com/grapick/dist/grapick.min.css',
            'grapesjs-component-code-editor' => 'https://unpkg.com/grapesjs-component-code-editor/dist/grapesjs-component-code-editor.min.css',
            'grapesjs-rte-extensions' => 'https://unpkg.com/grapesjs-rte-extensions/dist/grapesjs-rte-extensions.min.css',
            // 'grapesjs-uppy' => 'https://unpkg.com/grapesjs-uppy/dist/grapesjs-uppy.min.css',
            // 'grapesjs-plugin-toolbox' => 'css/grapesjs-plugin-toolbox.min.css',
            // 'grapesjs-rulers' => 'css/grapesjs-rulers.min.css',
            // 'grapesjs-undraw' => 'css/grapesjs-undraw.min.css',
            // 'quill-bubble' => 'css/quill.bubble.css',
        ],

        'js' => [
            // slug => path to js file in your resources directory
            // 'slug' => 'path/to/js/file.js',
            'grapesjs-dinvitations' => 'js/grapesjs-dinvitations.min.js',
            'gjs-blocks-basic' => 'https://unpkg.com/grapesjs-blocks-basic',
            'grapesjs-component-code-editor' => 'https://unpkg.com/grapesjs-component-code-editor',
            'grapesjs-component-countdown' => 'https://unpkg.com/grapesjs-component-countdown',
            'grapesjs-custom-code' => 'https://unpkg.com/grapesjs-custom-code',
            'grapesjs-navbar' => 'https://unpkg.com/grapesjs-navbar',
            'grapesjs-parser-postcss' => 'https://unpkg.com/grapesjs-parser-postcss',
            'grapesjs-plugin-forms' => 'https://unpkg.com/grapesjs-plugin-forms',
            'grapesjs-plugin-export' => 'https://unpkg.com/grapesjs-plugin-export',
            'grapesjs-rte-extensions' => 'https://unpkg.com/grapesjs-rte-extensions',
            'grapesjs-style-bg' => 'https://unpkg.com/grapesjs-style-bg',
            'grapesjs-tabs' => 'https://unpkg.com/grapesjs-tabs',
            'grapesjs-tooltip' => 'https://unpkg.com/grapesjs-tooltip',
            'grapesjs-touch' => 'https://unpkg.com/grapesjs-touch',
            'grapesjs-tui-image-editor' => 'https://unpkg.com/grapesjs-tui-image-editor',
            'grapesjs-typed' => 'https://unpkg.com/grapesjs-typed',
            // 'grapesjs-preset-webpage' => 'https://unpkg.com/grapesjs-preset-webpage',
            // 'gjs-quill' => 'https://unpkg.com/gjs-quill',
            // 'grapesjs-calendly' => 'https://unpkg.com/grapesjs-calendly',
            // 'grapesjs-plugin-toolbox' => 'https://unpkg.com/grapesjs-plugin-toolbox',
            // 'grapesjs-rulers' => 'https://unpkg.com/grapesjs-rulers',
            // 'grapesjs-script-editor' => 'https://unpkg.com/grapesjs-script-editor',
            // 'grapesjs-style-border' => 'https://unpkg.com/grapesjs-style-border',
            // 'grapesjs-style-easing' => 'https://unpkg.com/grapesjs-style-easing',
            // 'grapesjs-style-filter' => 'https://unpkg.com/grapesjs-style-filter',
            // 'grapesjs-style-gpickr' => 'https://unpkg.com/grapesjs-style-gpickr',
            // 'grapesjs-undraw' => 'https://unpkg.com/grapesjs-undraw',
            // 'grapesjs-uppy' => 'https://unpkg.com/grapesjs-uppy',
        ],
    ],

    'settings' => [
        'i18n' => [
            'en' => [
                'styleManager' => [
                    'properties' => [
                        'background-repeat' => 'Repeat',
                        'background-position' => 'Position',
                        'background-attachment' => 'Attachment',
                        'background-size' => 'Size',
                    ]
                ]
            ]
        ],
        'pluginOpts' => [
            'gjs-blocks-basic' => [
                'flexGrid' => true
            ],
            'grapesjs-tabs' => [
                'tabsBlock' => [
                    'category' => 'Extra'
                ],
            ],
            'grapesjs-tui-image-editor' => [
                'script' => [
                    // 'https://cdnjs.cloudflare.com/ajax/libs/fabric.js/1.6.7/fabric.min.js',
                    'https://uicdn.toast.com/tui.code-snippet/v1.5.2/tui-code-snippet.min.js',
                    'https://uicdn.toast.com/tui-color-picker/v2.2.7/tui-color-picker.min.js',
                    'https://uicdn.toast.com/tui-image-editor/v3.15.2/tui-image-editor.min.js',
                ],
                'style' => [
                    'https://uicdn.toast.com/tui-color-picker/v2.2.7/tui-color-picker.min.css',
                    'https://uicdn.toast.com/tui-image-editor/v3.15.2/tui-image-editor.min.css',
                ],
            ],
            'grapesjs-typed' => [
                'block' => [
                    'category' => 'Extra',
                    'content' => [
                        'type' => 'typed',
                        'type-speed' => 40,
                        'strings' => [
                            "Text row one",
                            "Text row two",
                            "Text row three"
                        ],
                    ],
                ],
            ]
        ],
        'selectorManager' => [
            'componentFirst' => true
        ],
        'styleManager' => [
            'sectors' => [
                [
                    'name' => 'General',
                    'properties' => [
                        [
                            'extend' => 'float',
                            'type' => 'radio',
                            'default' => 'none',
                            'options' => [
                                ['value' => 'none', 'className' => 'fa fa-times'],
                                ['value' => 'left', 'className' => 'fa fa-align-left'],
                                ['value' => 'right', 'className' => 'fa fa-align-right'],
                            ],
                        ],
                        'display',
                        ['extend' => 'position', 'type' => 'select'],
                        'top',
                        'right',
                        'left',
                        'bottom',
                    ],
                ],
                [
                    'name' => 'Dimension',
                    'open' => false,
                    'properties' => [
                        'width',
                        [
                            'id' => 'flex-width',
                            'type' => 'integer',
                            'name' => 'Width',
                            'units' => ['px', '%'],
                            'property' => 'flex-basis',
                            'toRequire' => 1,
                        ],
                        'height',
                        'max-width',
                        'min-height',
                        'margin',
                        'padding',
                    ],
                ],
                [
                    'name' => 'Typography',
                    'open' => false,
                    'properties' => [
                        'font-family',
                        'font-size',
                        'font-weight',
                        'letter-spacing',
                        'color',
                        'line-height',
                        [
                            'extend' => 'text-align',
                            'options' => [
                                ['id' => 'left', 'label' => 'Left', 'className' => 'fa fa-align-left'],
                                ['id' => 'center', 'label' => 'Center', 'className' => 'fa fa-align-center'],
                                ['id' => 'right', 'label' => 'Right', 'className' => 'fa fa-align-right'],
                                ['id' => 'justify', 'label' => 'Justify', 'className' => 'fa fa-align-justify'],
                            ],
                        ],
                        [
                            'property' => 'text-decoration',
                            'type' => 'radio',
                            'default' => 'none',
                            'options' => [
                                ['id' => 'none', 'label' => 'None', 'className' => 'fa fa-times'],
                                ['id' => 'underline', 'label' => 'underline', 'className' => 'fa fa-underline'],
                                ['id' => 'line-through', 'label' => 'Line-through', 'className' => 'fa fa-strikethrough'],
                            ],
                        ],
                        'text-shadow',
                    ],
                ],
                [
                    'name' => 'Decorations',
                    'open' => false,
                    'properties' => [
                        'opacity',
                        'border-radius',
                        'border',
                        'box-shadow',
                        'background',
                    ],
                ],
                [
                    'name' => 'Extra',
                    'open' => false,
                    'buildProps' => ['transition', 'perspective', 'transform'],
                ],
                [
                    'name' => 'Flex',
                    'open' => false,
                    'properties' => [
                        [
                            'name' => 'Flex Container',
                            'property' => 'display',
                            'type' => 'select',
                            'defaults' => 'block',
                            'list' => [
                                ['value' => 'block', 'name' => 'Disable'],
                                ['value' => 'flex', 'name' => 'Enable'],
                            ],
                        ],
                        [
                            'name' => 'Flex Parent',
                            'property' => 'label-parent-flex',
                            'type' => 'integer',
                        ],
                        [
                            'name' => 'Direction',
                            'property' => 'flex-direction',
                            'type' => 'radio',
                            'defaults' => 'row',
                            'list' => [
                                ['value' => 'row', 'name' => 'Row', 'className' => 'icons-flex icon-dir-row', 'title' => 'Row'],
                                ['value' => 'row-reverse', 'name' => 'Row reverse', 'className' => 'icons-flex icon-dir-row-rev', 'title' => 'Row reverse'],
                                ['value' => 'column', 'name' => 'Column', 'title' => 'Column', 'className' => 'icons-flex icon-dir-col'],
                                ['value' => 'column-reverse', 'name' => 'Column reverse', 'title' => 'Column reverse', 'className' => 'icons-flex icon-dir-col-rev'],
                            ],
                        ],
                        [
                            'name' => 'Justify',
                            'property' => 'justify-content',
                            'type' => 'radio',
                            'defaults' => 'flex-start',
                            'list' => [
                                ['value' => 'flex-start', 'className' => 'icons-flex icon-just-start', 'title' => 'Start'],
                                ['value' => 'flex-end', 'title' => 'End', 'className' => 'icons-flex icon-just-end'],
                                ['value' => 'space-between', 'title' => 'Space between', 'className' => 'icons-flex icon-just-sp-bet'],
                                ['value' => 'space-around', 'title' => 'Space around', 'className' => 'icons-flex icon-just-sp-ar'],
                                ['value' => 'center', 'title' => 'Center', 'className' => 'icons-flex icon-just-sp-cent'],
                            ],
                        ],
                        [
                            'name' => 'Align',
                            'property' => 'align-items',
                            'type' => 'radio',
                            'defaults' => 'center',
                            'list' => [
                                ['value' => 'flex-start', 'title' => 'Start', 'className' => 'icons-flex icon-al-start'],
                                ['value' => 'flex-end', 'title' => 'End', 'className' => 'icons-flex icon-al-end'],
                                ['value' => 'stretch', 'title' => 'Stretch', 'className' => 'icons-flex icon-al-str'],
                                ['value' => 'center', 'title' => 'Center', 'className' => 'icons-flex icon-al-center'],
                            ],
                        ],
                        [
                            'name' => 'Flex Children',
                            'property' => 'label-parent-flex',
                            'type' => 'integer',
                        ],
                        [
                            'name' => 'Order',
                            'property' => 'order',
                            'type' => 'integer',
                            'defaults' => 0,
                            'min' => 0,
                        ],
                        [
                            'name' => 'Flex',
                            'property' => 'flex',
                            'type' => 'composite',
                            'properties' => [
                                [
                                    'name' => 'Grow',
                                    'property' => 'flex-grow',
                                    'type' => 'integer',
                                    'defaults' => 0,
                                    'min' => 0,
                                ],
                                [
                                    'name' => 'Shrink',
                                    'property' => 'flex-shrink',
                                    'type' => 'integer',
                                    'defaults' => 0,
                                    'min' => 0,
                                ],
                                [
                                    'name' => 'Basis',
                                    'property' => 'flex-basis',
                                    'type' => 'integer',
                                    'units' => ['px', '%', ''],
                                    'unit' => '',
                                    'defaults' => 'auto',
                                ],
                            ],
                        ],
                        [
                            'name' => 'Align',
                            'property' => 'align-self',
                            'type' => 'radio',
                            'defaults' => 'auto',
                            'list' => [
                                ['value' => 'auto', 'name' => 'Auto'],
                                ['value' => 'flex-start', 'title' => 'Start', 'className' => 'icons-flex icon-al-start'],
                                ['value' => 'flex-end', 'title' => 'End', 'className' => 'icons-flex icon-al-end'],
                                ['value' => 'stretch', 'title' => 'Stretch', 'className' => 'icons-flex icon-al-str'],
                                ['value' => 'center', 'title' => 'Center', 'className' => 'icons-flex icon-al-center'],
                            ],
                        ],
                    ],
                ],
            ]
        ],
        'deviceManager' => [
            'default' => 'mobilePortrait',
        ]
    ]
];
