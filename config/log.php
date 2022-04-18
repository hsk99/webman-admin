<?php

return [
    'default' => [
        'handlers' => [
            [
                'class' => \Monolog\Handler\RotatingFileHandler::class,
                'constructor' => [
                    runtime_path() . '/logs/emergency/emergency.log', 7, \Monolog\Logger::EMERGENCY, false
                ],
                'formatter' => [
                    'class' => \Monolog\Formatter\LineFormatter::class,
                    'constructor' => ["[%datetime%] %message% %context% %extra%\n", 'Y-m-d H:i:s', true],
                ],
            ],
            [
                'class' => \Monolog\Handler\RotatingFileHandler::class,
                'constructor' => [
                    runtime_path() . '/logs/alert/alert.log', 7, \Monolog\Logger::ALERT, false
                ],
                'formatter' => [
                    'class' => \Monolog\Formatter\LineFormatter::class,
                    'constructor' => ["[%datetime%] %message% %context% %extra%\n", 'Y-m-d H:i:s', true],
                ],
            ],
            [
                'class' => \Monolog\Handler\RotatingFileHandler::class,
                'constructor' => [
                    runtime_path() . '/logs/critical/critical.log', 7, \Monolog\Logger::CRITICAL, false
                ],
                'formatter' => [
                    'class' => \Monolog\Formatter\LineFormatter::class,
                    'constructor' => ["[%datetime%] %message% %context% %extra%\n", 'Y-m-d H:i:s', true],
                ],
            ],
            [
                'class' => \Monolog\Handler\RotatingFileHandler::class,
                'constructor' => [
                    runtime_path() . '/logs/error/error.log', 7, \Monolog\Logger::ERROR, false
                ],
                'formatter' => [
                    'class' => \Monolog\Formatter\LineFormatter::class,
                    'constructor' => ["[%datetime%] %message% %context% %extra%\n", 'Y-m-d H:i:s', true],
                ],
            ],
            [
                'class' => \Monolog\Handler\RotatingFileHandler::class,
                'constructor' => [
                    runtime_path() . '/logs/warning/warning.log', 7, \Monolog\Logger::WARNING, false
                ],
                'formatter' => [
                    'class' => \Monolog\Formatter\LineFormatter::class,
                    'constructor' => ["[%datetime%] %message% %context% %extra%\n", 'Y-m-d H:i:s', true],
                ],
            ],
            [
                'class' => \Monolog\Handler\RotatingFileHandler::class,
                'constructor' => [
                    runtime_path() . '/logs/notice/notice.log', 7, \Monolog\Logger::NOTICE, false
                ],
                'formatter' => [
                    'class' => \Monolog\Formatter\LineFormatter::class,
                    'constructor' => ["[%datetime%] %message% %context% %extra%\n", 'Y-m-d H:i:s', true],
                ],
            ],
            [
                'class' => \Monolog\Handler\RotatingFileHandler::class,
                'constructor' => [
                    runtime_path() . '/logs/info/info.log', 7, \Monolog\Logger::INFO, false
                ],
                'formatter' => [
                    'class' => \Monolog\Formatter\LineFormatter::class,
                    'constructor' => ["[%datetime%] %message% %context% %extra%\n", 'Y-m-d H:i:s', true],
                ],
            ],
            [
                'class' => \Monolog\Handler\RotatingFileHandler::class,
                'constructor' => [
                    runtime_path() . '/logs/debug/debug.log', 7, \Monolog\Logger::DEBUG, false
                ],
                'formatter' => [
                    'class' => \Monolog\Formatter\LineFormatter::class,
                    'constructor' => ["[%datetime%] %message% %context% %extra%\n", 'Y-m-d H:i:s', true],
                ],
            ],
        ],
    ],
    'request' => [
        'handlers' => [
            [
                'class' => \Monolog\Handler\RotatingFileHandler::class,
                'constructor' => [
                    runtime_path() . '/logs/request/request.log', 7, \Monolog\Logger::DEBUG
                ],
                'formatter' => [
                    'class' => \support\hsk99\util\LogJsonFormatter::class,
                    'constructor' => []
                ],
            ]
        ],
    ],
    'sql' => [
        'handlers' => [
            [
                'class' => \Monolog\Handler\RotatingFileHandler::class,
                'constructor' => [
                    runtime_path() . '/logs/sql/sql.log', 7, \Monolog\Logger::DEBUG
                ],
                'formatter' => [
                    'class' => \support\hsk99\util\LogJsonFormatter::class,
                    'constructor' => []
                ],
            ]
        ],
    ],
];
