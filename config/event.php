<?php

return [
    // 后台请求记录
    'admin.request.log' => [
        [app\admin\event\Request::class, 'log'],
    ]
];
