<?php

if (is_file(config_path() . '/system.php')) {
    $system = include config_path() . '/system.php';
} else {
    $system = [];
}

if (is_file(config_path() . '/app.php')) {
    $app = include config_path() . '/app.php';
} else {
    $app = [];
}

return [
    'enable'   => true,
    'debug'    => true,
    'notice'   => true,
    'interval' => 30,
    'project'  => $app['project'] ?? 'webman-admin',
    'email'    => !empty($system['exception_email']) ? array_values($system['exception_email']) : [],
];
