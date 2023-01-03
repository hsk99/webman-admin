<?php

if (is_phar() && is_file(base_path(false) . '/system.php')) {
    $system = include base_path(false) . '/system.php';
} else if (is_file(config_path() . '/system.php')) {
    $system = include config_path() . '/system.php';
} else {
    $system = [];
}

return [
    'smtp_host'   => $system['smtp_host'] ?? '',
    'smtp_user'   => $system['smtp_user'] ?? '',
    'smtp_pass'   => $system['smtp_pass'] ?? '',
    'smtp_secure' => $system['smtp_secure'] ?? '',
    'smtp_port'   => $system['smtp_port'] ?? '',
];
