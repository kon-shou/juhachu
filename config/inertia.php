<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Testing
    |--------------------------------------------------------------------------
    |
    | ページコンポーネントは resources/js/pages（小文字）に置いている。
    | パッケージの既定値は js/Pages（大文字）で、大文字小文字を区別する
    | Linux（CI）ではページが見つからずテストが落ちるため、実際の場所を指定する。
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
