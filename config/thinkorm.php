<?php

return [
    'default' => 'mysql',

    'connections' => [

        'mysql' => [
            'type'            => 'mysql',
            'hostname'        => '127.0.0.1',
            'database'        => 'webmanadmin',
            'username'        => 'webmanadmin',
            'password'        => 'webmanadmin',
            'hostport'        => 3306,
            'params'          => [
                PDO::ATTR_ERRMODE,
                PDO::ERRMODE_EXCEPTION
            ],
            'charset'         => 'utf8mb4',
            'prefix'          => '',
            'break_reconnect' => true,
            'trigger_sql'     => true,
            'debug'           => true,
            'auto_timestamp'  => true
        ],
    ],
];
