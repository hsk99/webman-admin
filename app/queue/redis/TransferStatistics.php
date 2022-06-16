<?php

namespace app\queue\redis;

use support\Redis;

class TransferStatistics implements \Webman\RedisQueue\Consumer
{
    /**
     * 应用监控队列
     *
     * @var string
     */
    public $queue = 'webman_TransferStatistics';

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
     * @date 2022-04-25 23:52:33
     *
     * @param array $data
     *
     * @return void
     */
    public function consume($data)
    {
        if (!empty($data['event']) && 'transceivedTraffic' === $data['event']) {
            $this->transceivedTraffic($data);
        } else {
            $this->transfer($data);
        }
    }

    /**
     * 调用统计
     *
     * @author HSK
     * @date 2022-05-25 21:56:57
     *
     * @param array $data
     *
     * @return void
     */
    protected function transfer(array $data)
    {
        try {
            $trace              = uniqid();                      // 追踪标识
            $ip                 = $data['ip'];                   // IP
            $transfer           = $data['transfer'];             // 调用（控制器::方法 或 url）
            $costTime           = $data['run_time'];             // 消耗时长（运行时长）
            $success            = $data['response_code'] < 400;  // 状态
            $time               = $data['time'];                 // 产生时间
            $code               = $data['response_code'];        // 状态码
            $transceivedTraffic = $data['transceived_traffic'];  // 收发流量

            // 调用、IP 替换掉“:”，防止redis存储分类层级混乱，兼容IPv6
            $ipTemp       = str_replace(['::', ':'], '@', $ip);
            $transferTemp = str_replace(['::', ':'], '@', $transfer);

            // 产生日期
            $day = date('Ymd', strtotime($time));

            // 产生时间间隔（一分钟）
            $interval = date('YmdHi', ceil(strtotime($time) / 60) * 60);

            // 记录调用（按天存储）
            Redis::hSet('TransferStatistics:tracing:' . $day, $trace, json_encode([
                'trace'    => $trace,
                'success'  => $success,
                'costTime' => $costTime,
                'code'     => $code,
            ] + $data, 320));

            // 记录追踪标识（按天存储）
            Redis::lPush('TransferStatistics:trace:' . $day, $trace);


            //////
            // 记录整体统计（按一分钟分钟统计，用于图表展示）
            //////
            // 次数
            Redis::hIncrBy('TransferStatistics:statistic:count:' . $day, $interval, 1);
            // 耗时
            Redis::hIncrByFloat('TransferStatistics:statistic:cost:' . $day, $interval, $costTime);
            // 成功次数
            Redis::hIncrBy('TransferStatistics:statistic:success_count:' . $day, $interval, $success ? 1 : 0);
            // 失败次数
            Redis::hIncrBy('TransferStatistics:statistic:error_count:' . $day, $interval, $success ? 0 : 1);
            // 收发流量
            Redis::hIncrBy('TransferStatistics:statistic:transceived_traffic:' . $day, $interval, $transceivedTraffic);

            //////
            // 记录“transfer”统计（按一分钟分钟统计，用于图表展示）
            //////
            // 调用次数
            Redis::hIncrBy('TransferStatistics:statistic:transfer_count:' . $transferTemp . ':' . $day, $interval, 1);
            // 调用耗时
            Redis::hIncrByFloat('TransferStatistics:statistic:transfer_cost:' . $transferTemp . ':' . $day, $interval, $costTime);
            // 调用成功次数
            Redis::hIncrBy('TransferStatistics:statistic:transfer_success:' . $transferTemp . ':' . $day, $interval, $success ? 1 : 0);
            // 调用失败次数
            Redis::hIncrBy('TransferStatistics:statistic:transfer_error:' . $transferTemp . ':' . $day, $interval, $success ? 0 : 1);

            //////
            // 记录“ip统计（按一分钟分钟统计，用于图表展示）
            //////
            // 调用次数
            Redis::hIncrBy('TransferStatistics:statistic:ip_count:' . $ipTemp . ':' . $day, $interval, 1);
            // 调用耗时
            Redis::hIncrByFloat('TransferStatistics:statistic:ip_cost:' . $ipTemp . ':' . $day, $interval, $costTime);
            // 调用成功次数
            Redis::hIncrBy('TransferStatistics:statistic:ip_success:' . $ipTemp . ':' . $day, $interval, $success ? 1 : 0);
            // 调用失败次数
            Redis::hIncrBy('TransferStatistics:statistic:ip_error:' . $ipTemp . ':' . $day, $interval, $success ? 0 : 1);
            // IP收发流量
            Redis::hIncrBy('TransferStatistics:statistic:ip_transceived_traffic:' . $ipTemp . ':' . $day, $interval, $transceivedTraffic);

            //////
            // 记录“code”统计（按一分钟分钟统计，用于图表展示）
            //////
            // 状态码次数
            Redis::hIncrBy('TransferStatistics:statistic:code_count:' . $code . ':' . $day, $interval, 1);
            // 状态码耗时
            Redis::hIncrByFloat('TransferStatistics:statistic:code_cost:' . $code . ':' . $day, $interval, $costTime);


            //////
            // 记录整体统计（按天统计）
            //////
            // 次数
            Redis::hIncrBy('TransferStatistics:statistic:count', $day, 1);
            // 耗时
            Redis::hIncrByFloat('TransferStatistics:statistic:cost', $day, $costTime);
            // 成功次数
            Redis::hIncrBy('TransferStatistics:statistic:success_count', $day, $success ? 1 : 0);
            // 失败次数
            Redis::hIncrBy('TransferStatistics:statistic:error_count', $day, $success ? 0 : 1);
            // 收发流量
            Redis::hIncrBy('TransferStatistics:statistic:transceived_traffic', $day, $transceivedTraffic);

            //////
            // 记录“transfer”统计（按天统计）
            //////
            // 调用次数
            Redis::hIncrBy('TransferStatistics:statistic:transfer_count:' . $transferTemp, $day, 1);
            // 调用耗时
            Redis::hIncrByFloat('TransferStatistics:statistic:transfer_cost:' . $transferTemp, $day, $costTime);
            // 调用成功次数
            Redis::hIncrBy('TransferStatistics:statistic:transfer_success:' . $transferTemp, $day, $success ? 1 : 0);
            // 调用失败次数
            Redis::hIncrBy('TransferStatistics:statistic:transfer_error:' . $transferTemp, $day, $success ? 0 : 1);

            //////
            // 记录“IP”统计（按天统计）
            //////
            // IP次数
            Redis::hIncrBy('TransferStatistics:statistic:ip_count:' . $ipTemp, $day, 1);
            // IP耗时
            Redis::hIncrByFloat('TransferStatistics:statistic:ip_cost:' . $ipTemp, $day, $costTime);
            // IP成功次数
            Redis::hIncrBy('TransferStatistics:statistic:ip_success:' . $ipTemp, $day, $success ? 1 : 0);
            // IP失败次数
            Redis::hIncrBy('TransferStatistics:statistic:ip_error:' . $ipTemp, $day, $success ? 0 : 1);
            // IP收发流量
            Redis::hIncrBy('TransferStatistics:statistic:ip_transceived_traffic:' . $ipTemp, $day, $transceivedTraffic);

            //////
            // 记录“code”统计（按天统计）
            //////
            // 状态码次数
            Redis::hIncrBy('TransferStatistics:statistic:code_count:' . $code, $day, 1);
            // 状态码耗时
            Redis::hIncrByFloat('TransferStatistics:statistic:code_cost:' . $code, $day, $costTime);


            // 记录IP分布（按天记录）
            Redis::hSetNx('TransferStatistics:ip:' . $day, $ip, $ip);
            // 记录IP链路标识（按天记录）
            Redis::lPush('TransferStatistics:ip_trace:' . $ipTemp . ':' . $day, $trace);

            // 记录状态码分布（按天记录）
            Redis::hSetNx('TransferStatistics:code:' . $day, $code, $code);
            // 记录单状态码链路标识（按天记录）
            Redis::lPush('TransferStatistics:code_trace:' . $code . ':' . $day, $trace);

            // 记录调用分布（按天记录）
            Redis::hSetNx('TransferStatistics:transfer:' . $day, $transfer, $transfer);
            // 记录单调用标识（按天记录）
            Redis::lPush('TransferStatistics:transfer_trace:' . $transferTemp . ':' . $day, $trace);


            // 过期时间
            $expireAt = get_system('transfer_storage', 0) ? strtotime($day) + 86400 * get_system('transfer_storage', 0) : 0;
            // 设置过期时间
            if ($expireAt > 0) {
                Redis::expireAt('TransferStatistics:tracing:' . $day, $expireAt);
                Redis::expireAt('TransferStatistics:trace:' . $day, $expireAt);
                Redis::expireAt('TransferStatistics:statistic:count:' . $day, $expireAt);
                Redis::expireAt('TransferStatistics:statistic:cost:' . $day, $expireAt);
                Redis::expireAt('TransferStatistics:statistic:success_count:' . $day, $expireAt);
                Redis::expireAt('TransferStatistics:statistic:error_count:' . $day, $expireAt);
                Redis::expireAt('TransferStatistics:statistic:transceived_traffic:' . $day, $expireAt);
                Redis::expireAt('TransferStatistics:statistic:transfer_count:' . $transferTemp . ':' . $day, $expireAt);
                Redis::expireAt('TransferStatistics:statistic:transfer_cost:' . $transferTemp . ':' . $day, $expireAt);
                Redis::expireAt('TransferStatistics:statistic:transfer_success:' . $transferTemp . ':' . $day, $expireAt);
                Redis::expireAt('TransferStatistics:statistic:transfer_error:' . $transferTemp . ':' . $day, $expireAt);
                Redis::expireAt('TransferStatistics:statistic:ip_count:' . $ipTemp . ':' . $day, $expireAt);
                Redis::expireAt('TransferStatistics:statistic:ip_cost:' . $ipTemp . ':' . $day, $expireAt);
                Redis::expireAt('TransferStatistics:statistic:ip_success:' . $ipTemp . ':' . $day, $expireAt);
                Redis::expireAt('TransferStatistics:statistic:ip_error:' . $ipTemp . ':' . $day, $expireAt);
                Redis::expireAt('TransferStatistics:statistic:ip_transceived_traffic:' . $ipTemp . ':' . $day, $expireAt);
                Redis::expireAt('TransferStatistics:statistic:code_count:' . $code . ':' . $day, $expireAt);
                Redis::expireAt('TransferStatistics:statistic:code_cost:' . $code . ':' . $day, $expireAt);
                // Redis::expireAt('TransferStatistics:statistic:count', $expireAt);
                // Redis::expireAt('TransferStatistics:statistic:cost', $expireAt);
                // Redis::expireAt('TransferStatistics:statistic:success_count', $expireAt);
                // Redis::expireAt('TransferStatistics:statistic:error_count', $expireAt);
                // Redis::expireAt('TransferStatistics:statistic:transfer_count:' . $transferTemp, $expireAt);
                // Redis::expireAt('TransferStatistics:statistic:transfer_cost:' . $transferTemp, $expireAt);
                // Redis::expireAt('TransferStatistics:statistic:transfer_success:' . $transferTemp, $expireAt);
                // Redis::expireAt('TransferStatistics:statistic:transfer_error:' . $transferTemp, $expireAt);
                // Redis::expireAt('TransferStatistics:statistic:ip_count:' . $ipTemp, $expireAt);
                // Redis::expireAt('TransferStatistics:statistic:ip_cost:' . $ipTemp, $expireAt);
                // Redis::expireAt('TransferStatistics:statistic:ip_success:' . $ipTemp, $expireAt);
                // Redis::expireAt('TransferStatistics:statistic:ip_error:' . $ipTemp, $expireAt);
                // Redis::expireAt('TransferStatistics:statistic:code_count:' . $code, $expireAt);
                // Redis::expireAt('TransferStatistics:statistic:code_cost:' . $code, $expireAt);
                Redis::expireAt('TransferStatistics:ip:' . $day, $expireAt);
                Redis::expireAt('TransferStatistics:ip_trace:' . $ipTemp . ':' . $day, $expireAt);
                Redis::expireAt('TransferStatistics:code:' . $day, $expireAt);
                Redis::expireAt('TransferStatistics:code_trace:' . $code . ':' . $day, $expireAt);
                Redis::expireAt('TransferStatistics:transfer:' . $day, $expireAt);
                Redis::expireAt('TransferStatistics:transfer_trace:' . $transferTemp . ':' . $day, $expireAt);
            }
            // 去除过期时间
            else {
                Redis::persist('TransferStatistics:tracing:' . $day);
                Redis::persist('TransferStatistics:trace:' . $day);
                Redis::persist('TransferStatistics:statistic:count:' . $day);
                Redis::persist('TransferStatistics:statistic:cost:' . $day);
                Redis::persist('TransferStatistics:statistic:success_count:' . $day);
                Redis::persist('TransferStatistics:statistic:error_count:' . $day);
                Redis::persist('TransferStatistics:statistic:transceived_traffic:' . $day);
                Redis::persist('TransferStatistics:statistic:transfer_count:' . $transferTemp . ':' . $day);
                Redis::persist('TransferStatistics:statistic:transfer_cost:' . $transferTemp . ':' . $day);
                Redis::persist('TransferStatistics:statistic:transfer_success:' . $transferTemp . ':' . $day);
                Redis::persist('TransferStatistics:statistic:transfer_error:' . $transferTemp . ':' . $day);
                Redis::persist('TransferStatistics:statistic:ip_count:' . $ipTemp . ':' . $day);
                Redis::persist('TransferStatistics:statistic:ip_cost:' . $ipTemp . ':' . $day);
                Redis::persist('TransferStatistics:statistic:ip_success:' . $ipTemp . ':' . $day);
                Redis::persist('TransferStatistics:statistic:ip_error:' . $ipTemp . ':' . $day);
                Redis::persist('TransferStatistics:statistic:ip_transceived_traffic:' . $ipTemp . ':' . $day);
                Redis::persist('TransferStatistics:statistic:code_count:' . $code . ':' . $day);
                Redis::persist('TransferStatistics:statistic:code_cost:' . $code . ':' . $day);
                // Redis::persist('TransferStatistics:statistic:count');
                // Redis::persist('TransferStatistics:statistic:cost');
                // Redis::persist('TransferStatistics:statistic:success_count');
                // Redis::persist('TransferStatistics:statistic:error_count');
                // Redis::persist('TransferStatistics:statistic:transfer_count:' . $transferTemp);
                // Redis::persist('TransferStatistics:statistic:transfer_cost:' . $transferTemp);
                // Redis::persist('TransferStatistics:statistic:transfer_success:' . $transferTemp);
                // Redis::persist('TransferStatistics:statistic:transfer_error:' . $transferTemp);
                // Redis::persist('TransferStatistics:statistic:ip_count:' . $ipTemp);
                // Redis::persist('TransferStatistics:statistic:ip_cost:' . $ipTemp);
                // Redis::persist('TransferStatistics:statistic:ip_success:' . $ipTemp);
                // Redis::persist('TransferStatistics:statistic:ip_error:' . $ipTemp);
                // Redis::persist('TransferStatistics:statistic:code_count:' . $code);
                // Redis::persist('TransferStatistics:statistic:code_cost:' . $code);
                Redis::persist('TransferStatistics:ip:' . $day);
                Redis::persist('TransferStatistics:ip_trace:' . $ipTemp . ':' . $day);
                Redis::persist('TransferStatistics:code:' . $day);
                Redis::persist('TransferStatistics:code_trace:' . $code . ':' . $day);
                Redis::persist('TransferStatistics:transfer:' . $day);
                Redis::persist('TransferStatistics:transfer_trace:' . $transferTemp . ':' . $day);
            }
        } catch (\Throwable $th) {
            \Hsk99\WebmanException\RunException::report($th);
        }
    }

    /**
     * 收发流量统计
     *
     * @author HSK
     * @date 2022-05-25 21:57:49
     *
     * @param array $data
     *
     * @return void
     */
    protected function transceivedTraffic(array $data)
    {
        try {
            $ip                 = $data['ip'];                   // IP
            $time               = $data['time'];                 // 产生时间
            $transceivedTraffic = $data['transceived_traffic'];  // 收发流量

            // IP 替换掉“:”，防止redis存储分类层级混乱，兼容IPv6
            $ipTemp = str_replace(['::', ':'], '@', $ip);

            // 产生日期
            $day = date('Ymd', strtotime($time));

            // 产生时间间隔（一分钟）
            $interval = date('YmdHi', ceil(strtotime($time) / 60) * 60);


            //////
            // 记录整体统计（按一分钟分钟统计，用于图表展示）
            //////
            // 收发流量
            Redis::hIncrBy('TransferStatistics:statistic:transceived_traffic:' . $day, $interval, $transceivedTraffic);


            //////
            // 记录“ip统计（按一分钟分钟统计，用于图表展示）
            //////
            // IP收发流量
            Redis::hIncrBy('TransferStatistics:statistic:ip_transceived_traffic:' . $ipTemp . ':' . $day, $interval, $transceivedTraffic);


            //////
            // 记录整体统计（按天统计）
            //////
            // 收发流量
            Redis::hIncrBy('TransferStatistics:statistic:transceived_traffic', $day, $transceivedTraffic);


            //////
            // 记录“IP”统计（按天统计）
            //////
            // IP收发流量
            Redis::hIncrBy('TransferStatistics:statistic:ip_transceived_traffic:' . $ipTemp, $day, $transceivedTraffic);


            // 记录IP分布（按天记录）
            Redis::hSetNx('TransferStatistics:ip:' . $day, $ip, $ip);


            // 过期时间
            $expireAt = get_system('transfer_storage', 0) ? strtotime($day) + 86400 * get_system('transfer_storage', 0) : 0;
            // 设置过期时间
            if ($expireAt > 0) {
                Redis::expireAt('TransferStatistics:statistic:transceived_traffic:' . $day, $expireAt);
                Redis::expireAt('TransferStatistics:statistic:ip_transceived_traffic:' . $ipTemp . ':' . $day, $expireAt);
                Redis::expireAt('TransferStatistics:ip:' . $day, $expireAt);
            }
            // 去除过期时间
            else {
                Redis::persist('TransferStatistics:statistic:transceived_traffic:' . $day);
                Redis::persist('TransferStatistics:statistic:ip_transceived_traffic:' . $ipTemp . ':' . $day);
                Redis::persist('TransferStatistics:ip:' . $day);
            }
        } catch (\Throwable $th) {
            \Hsk99\WebmanException\RunException::report($th);
        }
    }
}
