<?php

namespace app\queue\redis\log;

class Request implements \Webman\RedisQueue\Consumer
{
    /**
     * 全局请求队列
     *
     * @var string
     */
    public $queue = 'webman_log_request';

    /**
     * 连接名
     *
     * @var string
     */
    public $connection = 'default';

    /**
     * 消费
     *
     * @author HSK
     * @date 2022-04-14 22:47:30
     *
     * @param array $data
     *
     * @return void
     */
    public function consume($data)
    {
        try {
            \support\Log::channel('request')->debug('', $data);
        } catch (\Throwable $th) {
            \Hsk99\WebmanException\RunException::report($th);
        }
    }
}
