<?php

namespace support\hsk99\bootstrap;

class ThinkOrmLog implements \Webman\Bootstrap
{
    /**
     * 进程启动时调用
     *
     * @author HSK
     * @date 2022-04-15 10:43:25
     *
     * @param \Workerman\Worker $worker
     *
     * @return void
     */
    public static function start($worker)
    {
        if ($worker) {
            \think\facade\Db::listen(function ($sql, $runtime, $master) use ($worker) {
                $time = microtime(true);

                if ($sql === 'select 1' || !is_numeric($runtime)) {
                    return;
                }

                $sqlLog = [
                    'worker'   => $worker->name,                                     // 运行进程
                    'time'     => date('Y-m-d H:i:s.', $time) . substr($time, 11),   // 请求时间（包含毫秒时间）
                    'message'  => 'sql log',                                         // 描述
                    'sql'      => trim($sql),                                        // SQL语句
                    'run_time' => $runtime * 1000 . 'ms',                             // 运行时长
                    'master'   => $master,                                           // 主从标识
                ];

                \support\Log::channel('sql')->debug('', $sqlLog);
            });
        }
    }
}
