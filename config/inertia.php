<?php

/*
|--------------------------------------------------------------------------
| Inertia
|--------------------------------------------------------------------------
|
| ページは resources/js/pages（小文字）に置いている（resources/js/app.ts を参照）。
| パッケージの既定は resources/js/Pages（大文字）で、大文字小文字を区別しない
| macOS では通るが、Linux の CI ではページが見つからずテストが落ちるため、
| ページの場所だけを上書きする。ほかの項目はパッケージの既定を使う。
|
*/

return [

    'page_paths' => [

        resource_path('js/pages'),

    ],

    'testing' => [

        'ensure_pages_exist' => true,

        'page_paths' => [

            resource_path('js/pages'),

        ],

        'page_extensions' => [

            'js',
            'jsx',
            'svelte',
            'ts',
            'tsx',
            'vue',

        ],

    ],

];
