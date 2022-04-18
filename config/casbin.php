<?php

return [
    'default' => [
        'model' => [
            'config_type'      => 'file',
            'config_file_path' => config_path() . '/casbin-rbac.conf',
            'config_text'      => '',
        ],
        'adapter' => [
            'type'  => 'model',
            'class' => \app\common\model\Casbin::class,
        ],
    ],
];