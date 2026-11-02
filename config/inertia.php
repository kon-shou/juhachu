<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Pages
    |--------------------------------------------------------------------------
    |
    | パッケージの既定は js/Pages（大文字）なので、置き場所に合わせて
    | resources/js/pages（小文字）を明示する。ensure_pages_exist は既定の
    | false のまま（本番の挙動は変えない）。
    |
    */

    'page_paths' => [

        resource_path('js/pages'),

    ],

    /*
    |--------------------------------------------------------------------------
    | Testing
    |--------------------------------------------------------------------------
    |
    | ページは resources/js/pages（小文字）に置いている（resources/js/app.ts の
    | resolve と同じ）。パッケージの既定は js/Pages（大文字）で、大文字小文字を
    | 区別するファイルシステム（CI の Linux）では assertInertia の component()
    | がページを見つけられないため、ここで明示する。
    |
    */

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
