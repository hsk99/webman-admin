<?php

/**
 * This file is part of webman.
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the MIT-LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @author    walkor<walkor@workerman.net>
 * @copyright walkor<walkor@workerman.net>
 * @link      http://www.workerman.net/
 * @license   http://www.opensource.org/licenses/mit-license.php MIT License
 */

if (is_file(config_path() . '/app.php')) {
    $app = include config_path() . '/app.php';
} else {
    $app = [];
}

return [
    'default' => [
        'host'     => '127.0.0.1',
        'password' => 'hsk99',
        'port'     => 6379,
        'database' => 0,
        'prefix'   => ($app['project'] ?? 'webman-admin') . ':',
    ],
];
