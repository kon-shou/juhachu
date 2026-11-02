<?php

/*
|--------------------------------------------------------------------------
| Inertia
|--------------------------------------------------------------------------
|
| ページは resources/js/pages（小文字）にある。パッケージの既定は
| resources/js/Pages（大文字）で、大文字小文字を区別する環境（Linux の CI）では
| assertInertia がページを見つけられない。ここでは置き場所だけを上書きし、
| ほかの値はパッケージの既定（vendor/inertiajs/inertia-laravel/config/inertia.php）を使う。
| mergeConfigFrom は最上位のキーごとに上書きするため、testing は中身を全部書く。
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
