<?php

/*
|--------------------------------------------------------------------------
| Inertia のページの場所
|--------------------------------------------------------------------------
|
| パッケージの既定は resources/js/Pages（P が大文字）だが、このアプリの
| ページは resources/js/pages にある。macOS のように大文字小文字を区別しない
| ファイルシステムでは既定のままでも見つかるが、Linux（CI）では見つからず
| assertInertia の component() が失敗するため、実際のパスを指定する。
|
| パッケージの設定とは最上位のキーごとにマージされるので、ここに書いた
| キー（page_paths・testing）だけがパッケージの既定を置き換える。
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
