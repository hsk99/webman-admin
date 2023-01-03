<?php

namespace app\common\middleware;

class RequestMonitoring implements \Webman\MiddlewareInterface
{
    /**
     * @var array
     */
    public $sqlLogs = [];

    /**
     * @var array
     */
    public $redisLogs = [];

    /**
     * @author HSK
     * @date 2022-12-30 11:48:04
     *
     * @param \Webman\Http\Request $request
     * @param callable $next
     *
     * @return \Webman\Http\Response
     */
    public function process(\Webman\Http\Request $request, callable $next): \Webman\Http\Response
    {
        $start_time = microtime(true);

        $response = $next($request);

        $runTime = (microtime(true) - $start_time) ?? 0;

        switch (true) {
            case method_exists($response, 'exception') && $exception = $response->exception():
                $body = (string)$exception;
                break;
            case 'application/json' === strtolower($response->getHeader('Content-Type')):
                $body = json_decode($response->rawBody(), true);
                break;
            default:
                $body = 'Non Json data';
                break;
        }

        if (!empty($request->header('content-length'))) {
            $requestLen = $request->header('content-length');
        } else {
            $requestLen = strlen($request->rawBuffer());
        }

        if (null !== $response->file) {
            $fileLen = (0 === $response->file['length']) ? filesize($response->file['file']) : $response->file['length'];
        } else {
            $fileLen = 0;
        }

        static $initialized;
        if (!$initialized) {
            if (class_exists(\think\facade\Db::class)) {
                \think\facade\Db::listen(function ($sql, $runtime, $master) {
                    if ($sql === 'select 1' || !is_numeric($runtime)) {
                        return;
                    }

                    // 兼容 webman/log 插件记录Sql日志
                    if (
                        class_exists(\Webman\Log\Middleware::class) &&
                        config('plugin.webman.log.app.enable', false) &&
                        !class_exists(\Hsk99\WebmanStatistic\Middleware::class) &&
                        !config('plugin.hsk99.statistic.app.enable', false)
                    ) {
                        \think\facade\Db::log($sql . " [$master RunTime: " . $runtime * 1000 . " ms]");
                    }

                    $this->sqlLogs[] = "[SQL] " . trim($sql) . " [$master RunTime: " . $runtime * 1000 . " ms]" . PHP_EOL;
                });
            }

            if (class_exists(\Illuminate\Redis\Events\CommandExecuted::class)) {
                foreach (config('redis', []) as $key => $config) {
                    if (strpos($key, 'redis-queue') !== false) {
                        continue;
                    }
                    try {
                        \support\Redis::connection($key)->listen(function (\Illuminate\Redis\Events\CommandExecuted $command) {
                            foreach ($command->parameters as &$item) {
                                if (is_array($item)) {
                                    $item = implode('\', \'', $item);
                                }
                            }
                            $this->redisLogs[] = "[Redis]\t[connection:{$command->connectionName}] Redis::{$command->command}('" . implode('\', \'', $command->parameters) . "') ({$command->time} ms)" . PHP_EOL;
                        });
                    } catch (\Throwable $e) {
                    }
                }
            }

            $initialized = true;
        }

        $data = [
            'time'                => date('Y-m-d H:i:s.', $start_time) . substr($start_time, 11),   // 请求时间（包含毫秒时间）
            'message'             => 'http request',                                                // 描述
            'transceived_traffic' => $requestLen + strlen($response) + $fileLen,                    // 收发流量
            'run_time'            => $runTime,                                                      // 运行时长
            'ip'                  => $request->getRealIp($safe_mode = true) ?? '',                  // 请求客户端IP
            'url'                 => $request->fullUrl() ?? '',                                     // 请求URL
            'method'              => $request->method() ?? '',                                      // 请求方法
            'request_param'       => $request->all() ?? [],                                         // 请求参数
            'request_header'      => $request->header() ?? [],                                      // 请求头
            'cookie'              => $request->cookie() ?? [],                                      // 请求cookie
            'session'             => $request->session()->all() ?? [],                              // 请求session
            'response_code'       => $response->getStatusCode() ?? '',                              // 响应码
            'response_header'     => $response->getHeaders() ?? [],                                 // 响应头
            'response_body'       => $body ?? [],                                                   // 响应数据
            'sql'                 => $this->sqlLogs,                                                // 运行SQL
            'redis'               => $this->redisLogs,                                              // 运行Redis
        ];
        $this->sqlLogs   = [];
        $this->redisLogs = [];

        // 记录详细请求日志
        \support\Log::channel('request')->debug('', $data);

        // 应用监控
        if (config('app.monitor') && "app\\" . $request->app . "\controller\TransferStatistics" !== $request->controller) {
            $transfer = $request->controller . '::' . $request->action;
            if ('::' === $transfer) {
                $transfer = $request->path();
            }
            \Webman\RedisQueue\Client::send('webman_TransferStatistics', ['transfer' => $transfer] + $data);
        }

        return $response;
    }
}
