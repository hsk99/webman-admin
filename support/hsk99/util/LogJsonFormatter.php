<?php

namespace support\hsk99\util;

class LogJsonFormatter extends \Monolog\Formatter\JsonFormatter
{
    public function format(array $record): string
    {
        return json_encode($record['context'], 320) . PHP_EOL;
    }
}
