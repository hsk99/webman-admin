<?php

namespace app\queue\redis\log;

class Admin implements \Webman\RedisQueue\Consumer
{
    /**
     * 后台访问队列
     *
     * @var string
     */
    public $queue = 'webman_log_admin';

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
     * @date 2022-04-14 22:39:20
     *
     * @param array $data
     *
     * @return void
     */
    public function consume($data)
    {
        try {
            \app\common\model\AdminAdminLog::create($data);
        } catch (\Throwable $th) {
            \Hsk99\WebmanException\RunException::report($th);
        }
    }
}
