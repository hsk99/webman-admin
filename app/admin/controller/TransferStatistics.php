<?php

namespace app\admin\controller;

use support\Redis;

/**
 * 应用监控
 *
 * @author HSK
 * @date 2022-04-26 09:09:21
 */
class TransferStatistics
{
    /**
     * 应用监控
     *
     * @author HSK
     * @date 2022-04-26 09:11:10
     *
     * @param \support\Request $request
     *
     * @return \support\Response
     */
    public function index(\support\Request $request)
    {
        $date = (string)request()->input('date', date('Y-m-d', time()));

        // 查看的日期
        $day = date('Ymd', strtotime($date));

        //////
        // 总统计
        //////
        // 总请求数
        $totalStatistic['count'] = Redis::hGet('TransferStatistics:statistic:count', $day) ?: 0;
        // 总成功数
        $totalStatistic['successCount'] = Redis::hGet('TransferStatistics:statistic:success_count', $day) ?: 0;
        // 总失败数
        $totalStatistic['errorCount'] = Redis::hGet('TransferStatistics:statistic:error_count', $day) ?: 0;
        // 总耗时
        $totalStatistic['cost'] = Redis::hGet('TransferStatistics:statistic:cost', $day) ?: 0;
        // 平均耗时
        $totalStatistic['averageCost'] = (0 == $totalStatistic['count']) ? 0 : round($totalStatistic['cost'] / $totalStatistic['count'] * 1000, 2);
        // 收发流量
        $transceivedTraffic = Redis::hGet('TransferStatistics:statistic:transceived_traffic', $day) ?: 0;
        $transceivedTraffic = (0 == $transceivedTraffic) ? 0 : round($transceivedTraffic / 1024 / 1024, 4);
        $totalStatistic['transceivedTraffic'] = $transceivedTraffic;


        //////
        // 获取调用统计数据（当天时间一分钟一统计）
        //////
        $chartCount              = Redis::hGetAll('TransferStatistics:statistic:count:' . $day) ?: [];
        $chartCost               = Redis::hGetAll('TransferStatistics:statistic:cost:' . $day) ?: [];
        $chartSuccessCount       = Redis::hGetAll('TransferStatistics:statistic:success_count:' . $day) ?: [];
        $chartErrorCount         = Redis::hGetAll('TransferStatistics:statistic:error_count:' . $day) ?: [];
        $chartTransceivedTraffic = Redis::hGetAll('TransferStatistics:statistic:transceived_traffic:' . $day) ?: [];

        // 获取间隔
        $intervalList  = [];
        $time          = strtotime($day);
        $intervalCount = (int)(ceil(time() / 60) * 60 - $time) / 60;
        $intervalCount = $intervalCount > 1440 ? 1440 : $intervalCount;
        for ($i = 0; $i < $intervalCount; $i++) {
            $intervalList[] = date('YmdHi', $time + $i * 60);
        }
        $intervalList = array_merge($intervalList, array_keys($chartCount));
        sort($intervalList);
        // 组装数据
        $chartList = [];
        array_map(function ($interval) use (&$chartList, &$chartCount, &$chartCost, &$chartSuccessCount, &$chartErrorCount, &$chartTransceivedTraffic) {
            $chartList['time'][$interval]               = date('y-m-d H:i', strtotime($interval));
            $chartList['count'][$interval]              = $chartCount[$interval] ?? 0;
            $chartList['cost'][$interval]               = $chartCost[$interval] ?? 0;
            $chartList['successCount'][$interval]       = $chartSuccessCount[$interval] ?? 0;
            $chartList['errorCount'][$interval]         = $chartErrorCount[$interval] ?? 0;
            $chartList['averageCost'][$interval]        = (0 == $chartList['count'][$interval]) ? 0 : round($chartList['cost'][$interval] / $chartList['count'][$interval] * 1000, 2);
            $transceivedTraffic = $chartTransceivedTraffic[$interval] ?? 0;
            $transceivedTraffic = (0 == $transceivedTraffic) ? 0 : round($transceivedTraffic / 1024 / 1024, 4);
            $chartList['transceivedTraffic'][$interval] = $transceivedTraffic;
        }, $intervalList);
        $chartList['time']               = array_values($chartList['time'] ?? []);
        $chartList['count']              = array_values($chartList['count'] ?? []);
        $chartList['cost']               = array_values($chartList['cost'] ?? []);
        $chartList['successCount']       = array_values($chartList['successCount'] ?? []);
        $chartList['errorCount']         = array_values($chartList['errorCount'] ?? []);
        $chartList['averageCost']        = array_values($chartList['averageCost'] ?? []);
        $chartList['transceivedTraffic'] = array_values($chartList['transceivedTraffic'] ?? []);


        ///////
        // 获取调用入口
        //////
        $transferList = Redis::hGetAll('TransferStatistics:transfer:' . $day) ?: [];
        $transferList = array_values($transferList);


        //////
        // 获取调用IP
        //////
        $ipList = Redis::hGetAll('TransferStatistics:ip:' . $day) ?: [];
        $ipList = array_values($ipList);


        //////
        // 获取状态码
        //////
        $codeList = Redis::hGetAll('TransferStatistics:code:' . $day) ?: [];
        $codeList = array_values($codeList);


        //////
        // 获取IP统计
        //////
        $ipStatistic = array_map(function ($ip) use (&$day) {
            $ipTemp = str_replace(['::', ':'], '@', $ip);

            // 调用IP次数
            $count = Redis::hGet('TransferStatistics:statistic:ip_count:' . $ipTemp, $day);
            // 调用IP耗时
            $cost = Redis::hGet('TransferStatistics:statistic:ip_cost:' . $ipTemp, $day);
            // 调用IP成功次数
            $success = Redis::hGet('TransferStatistics:statistic:ip_success:' . $ipTemp, $day);
            // 调用IP失败次数
            $error = Redis::hGet('TransferStatistics:statistic:ip_error:' . $ipTemp, $day);
            // 平均耗时
            $averageCost = (0 == $count) ? 0 : round($cost / $count * 1000, 2);
            // 收发流量
            $transceivedTraffic = Redis::hGet('TransferStatistics:statistic:ip_transceived_traffic:' . $ipTemp, $day);
            $transceivedTraffic = (0 == $transceivedTraffic) ? 0 : round($transceivedTraffic / 1024 / 1024, 4);

            return [
                'ip'                 => $ip,
                'count'              => $count,
                'cost'               => $cost,
                'success'            => $success,
                'error'              => $error,
                'averageCost'        => $averageCost,
                'transceivedTraffic' => $transceivedTraffic,
            ];
        }, $ipList);


        //////
        // 获取调用链路统计
        //////
        $transferStatistic = array_map(function ($transfer) use (&$day) {
            $transferTemp = str_replace(['::', ':'], '@', $transfer);

            // 调用链路次数
            $count = Redis::hGet('TransferStatistics:statistic:transfer_count:' . $transferTemp, $day);
            // 调用链路耗时
            $cost = Redis::hGet('TransferStatistics:statistic:transfer_cost:' . $transferTemp, $day);
            // 调用链路成功次数
            $success = Redis::hGet('TransferStatistics:statistic:transfer_success:' . $transferTemp, $day);
            // 调用链路失败次数
            $error = Redis::hGet('TransferStatistics:statistic:transfer_error:' . $transferTemp, $day);
            // 平均耗时
            $averageCost = (0 == $count) ? 0 : round($cost / $count * 1000, 2);

            return [
                'transfer'    => $transfer,
                'count'       => $count,
                'cost'        => $cost,
                'success'     => $success,
                'error'       => $error,
                'averageCost' => $averageCost,
            ];
        }, $transferList);


        return view('transfer_statistics/index', [
            'date'              => $date,
            'totalStatistic'    => $totalStatistic,
            'chartList'         => json_encode($chartList, 320),
            'transferList'      => $transferList,
            'ipList'            => $ipList,
            'codeList'          => $codeList,
            'ipStatistic'       => $ipStatistic,
            'transferStatistic' => $transferStatistic,
        ]);
    }

    /**
     * 调用记录
     *
     * @author HSK
     * @date 2022-04-26 09:11:10
     *
     * @param \support\Request $request
     *
     * @return \support\Response
     */
    public function tracingList(\support\Request $request)
    {
        $date  = (string)request()->input('date', date('Y-m-d', time()));
        $page  = (int)request()->input('page', 1);
        $limit = (int)request()->input('limit', 10);

        // 查看的日期
        $day = date('Ymd', strtotime($date));

        // 计算读取范围
        $start = (1 === $page) ? 0 : ($page - 1) * $limit;
        $end   = $page * $limit - 1;

        $trace       = Redis::lRange('TransferStatistics:trace:' . $day, $start, $end);
        $total       = Redis::lLen('TransferStatistics:trace:' . $day);
        $tracingList = [];
        if (!empty($trace)) {
            // 获取链路详细信息
            $tracingList = Redis::hMGet('TransferStatistics:tracing:' . $day, $trace);

            // 处理数据
            $tracingList = array_map(function ($item) {
                $item = json_decode($item, true);

                $item['details']  = json_encode($item, 448);
                $item['costTime'] = round($item['costTime'] * 1000, 2);

                return $item;
            }, $tracingList);
        }

        return api([
            'total'       => $total,
            'tracingList' => $tracingList,
        ]);
    }

    /**
     * 调用入口
     *
     * @author HSK
     * @date 2022-04-26 09:11:10
     *
     * @param \support\Request $request
     *
     * @return \support\Response
     */
    public function transfer(\support\Request $request)
    {
        $transfer = (string)request()->input('transfer', '');
        $date     = (string)request()->input('date', date('Y-m-d', time()));

        $transferTemp = str_replace(['::', ':'], '@', $transfer);

        // 查看的日期
        $day = date('Ymd', strtotime($date));

        //////
        // 获取调用入口统计数据（当天时间一分钟一统计）
        //////
        $chartCount        = Redis::hGetAll('TransferStatistics:statistic:transfer_count:' . $transferTemp . ':' . $day) ?: [];
        $chartCost         = Redis::hGetAll('TransferStatistics:statistic:transfer_cost:' . $transferTemp . ':' . $day) ?: [];
        $chartSuccessCount = Redis::hGetAll('TransferStatistics:statistic:transfer_success:' . $transferTemp . ':' . $day) ?: [];
        $chartErrorCount   = Redis::hGetAll('TransferStatistics:statistic:transfer_error:' . $transferTemp . ':' . $day) ?: [];
        // 获取间隔
        $intervalList  = [];
        $time          = strtotime($day);
        $intervalCount = (int)(ceil(time() / 60) * 60 - $time) / 60;
        $intervalCount = $intervalCount > 1440 ? 1440 : $intervalCount;
        for ($i = 0; $i < $intervalCount; $i++) {
            $intervalList[] = date('YmdHi', $time + $i * 60);
        }
        $intervalList = array_merge($intervalList, array_keys($chartCount));
        sort($intervalList);
        // 组装数据
        $chartList = [];
        array_map(function ($interval) use (&$chartList, &$chartCount, &$chartCost, &$chartSuccessCount, &$chartErrorCount) {
            $chartList['time'][$interval]         = date('y-m-d H:i', strtotime($interval));
            $chartList['count'][$interval]        = $chartCount[$interval] ?? 0;
            $chartList['cost'][$interval]         = $chartCost[$interval] ?? 0;
            $chartList['successCount'][$interval] = $chartSuccessCount[$interval] ?? 0;
            $chartList['errorCount'][$interval]   = $chartErrorCount[$interval] ?? 0;
            $chartList['averageCost'][$interval]  = (0 == $chartList['count'][$interval]) ? 0 : round($chartList['cost'][$interval] / $chartList['count'][$interval] * 1000, 2);
        }, $intervalList);
        $chartList['time']         = array_values($chartList['time'] ?? []);
        $chartList['count']        = array_values($chartList['count'] ?? []);
        $chartList['cost']         = array_values($chartList['cost'] ?? []);
        $chartList['successCount'] = array_values($chartList['successCount'] ?? []);
        $chartList['errorCount']   = array_values($chartList['errorCount'] ?? []);
        $chartList['averageCost']  = array_values($chartList['averageCost'] ?? []);

        return view('transfer_statistics/transfer', [
            'date'      => $date,
            'transfer'  => $transfer,
            'chartList' => json_encode($chartList, 320),
        ]);
    }

    /**
     * 入口调用记录
     *
     * @author HSK
     * @date 2022-04-26 09:11:10
     *
     * @param \support\Request $request
     *
     * @return \support\Response
     */
    public function transferTracingList(\support\Request $request)
    {
        $transfer = (string)request()->input('transfer', '');
        $date     = (string)request()->input('date', date('Y-m-d', time()));
        $page     = (int)request()->input('page', 1);
        $limit    = (int)request()->input('limit', 10);

        $transferTemp = str_replace(['::', ':'], '@', $transfer);

        // 查看的日期
        $day = date('Ymd', strtotime($date));

        // 计算读取范围
        $start = (1 === $page) ? 0 : ($page - 1) * $limit;
        $end   = $page * $limit - 1;

        $trace       = Redis::lRange('TransferStatistics:transfer_trace:' . $transferTemp . ':' . $day, $start, $end);
        $total       = Redis::lLen('TransferStatistics:transfer_trace:' . $transferTemp . ':' . $day);
        $tracingList = [];
        if (!empty($trace)) {
            // 获取链路详细信息
            $tracingList = Redis::hMGet('TransferStatistics:tracing:' . $day, $trace);

            // 处理数据
            $tracingList = array_map(function ($item) {
                $item = json_decode($item, true);

                $item['details']  = json_encode($item, 448);
                $item['costTime'] = round($item['costTime'] * 1000, 2);

                return $item;
            }, $tracingList);
        }

        return api([
            'total'       => $total,
            'tracingList' => $tracingList,
        ]);
    }

    /**
     * 调用IP
     *
     * @author HSK
     * @date 2022-04-26 09:11:10
     *
     * @param \support\Request $request
     *
     * @return \support\Response
     */
    public function ip(\support\Request $request)
    {
        $ip   = (string)request()->input('ip', '');
        $date = (string)request()->input('date', date('Y-m-d', time()));

        $ipTemp = str_replace(['::', ':'], '@', $ip);

        // 查看的日期
        $day = date('Ymd', strtotime($date));

        //////
        // 获取调用IP统计数据（当天时间一分钟一统计）
        //////
        $chartCount              = Redis::hGetAll('TransferStatistics:statistic:ip_count:' . $ipTemp . ':' . $day) ?: [];
        $chartCost               = Redis::hGetAll('TransferStatistics:statistic:ip_cost:' . $ipTemp . ':' . $day) ?: [];
        $chartSuccessCount       = Redis::hGetAll('TransferStatistics:statistic:ip_success:' . $ipTemp . ':' . $day) ?: [];
        $chartErrorCount         = Redis::hGetAll('TransferStatistics:statistic:ip_error:' . $ipTemp . ':' . $day) ?: [];
        $chartTransceivedTraffic = Redis::hGetAll('TransferStatistics:statistic:ip_transceived_traffic:' . $ipTemp . ':' . $day) ?: [];

        // 获取间隔
        $intervalList  = [];
        $time          = strtotime($day);
        $intervalCount = (int)(ceil(time() / 60) * 60 - $time) / 60;
        $intervalCount = $intervalCount > 1440 ? 1440 : $intervalCount;
        for ($i = 0; $i < $intervalCount; $i++) {
            $intervalList[] = date('YmdHi', $time + $i * 60);
        }
        $intervalList = array_merge($intervalList, array_keys($chartCount));
        sort($intervalList);
        // 组装数据
        $chartList = [];
        array_map(function ($interval) use (&$chartList, &$chartCount, &$chartCost, &$chartSuccessCount, &$chartErrorCount, &$chartTransceivedTraffic) {
            $chartList['time'][$interval]               = date('y-m-d H:i', strtotime($interval));
            $chartList['count'][$interval]              = $chartCount[$interval] ?? 0;
            $chartList['cost'][$interval]               = $chartCost[$interval] ?? 0;
            $chartList['successCount'][$interval]       = $chartSuccessCount[$interval] ?? 0;
            $chartList['errorCount'][$interval]         = $chartErrorCount[$interval] ?? 0;
            $chartList['averageCost'][$interval]        = (0 == $chartList['count'][$interval]) ? 0 : round($chartList['cost'][$interval] / $chartList['count'][$interval] * 1000, 2);
            $transceivedTraffic = $chartTransceivedTraffic[$interval] ?? 0;
            $transceivedTraffic = (0 == $transceivedTraffic) ? 0 : round($transceivedTraffic / 1024 / 1024, 4);
            $chartList['transceivedTraffic'][$interval] = $transceivedTraffic;
        }, $intervalList);
        $chartList['time']               = array_values($chartList['time'] ?? []);
        $chartList['count']              = array_values($chartList['count'] ?? []);
        $chartList['cost']               = array_values($chartList['cost'] ?? []);
        $chartList['successCount']       = array_values($chartList['successCount'] ?? []);
        $chartList['errorCount']         = array_values($chartList['errorCount'] ?? []);
        $chartList['averageCost']        = array_values($chartList['averageCost'] ?? []);
        $chartList['transceivedTraffic'] = array_values($chartList['transceivedTraffic'] ?? []);

        return view('transfer_statistics/ip', [
            'date'      => $date,
            'ip'        => $ip,
            'chartList' => json_encode($chartList, 320),
        ]);
    }

    /**
     * IP调用记录
     *
     * @author HSK
     * @date 2022-04-26 09:11:10
     *
     * @param \support\Request $request
     *
     * @return \support\Response
     */
    public function ipTracingList(\support\Request $request)
    {
        $ip    = (string)request()->input('ip', '');
        $date  = (string)request()->input('date', date('Y-m-d', time()));
        $page  = (int)request()->input('page', 1);
        $limit = (int)request()->input('limit', 10);

        $ipTemp = str_replace(['::', ':'], '@', $ip);

        // 查看的日期
        $day = date('Ymd', strtotime($date));

        // 计算读取范围
        $start = (1 === $page) ? 0 : ($page - 1) * $limit;
        $end   = $page * $limit - 1;

        $trace       = Redis::lRange('TransferStatistics:ip_trace:' . $ipTemp . ':' . $day, $start, $end);
        $total       = Redis::lLen('TransferStatistics:ip_trace:' . $ipTemp . ':' . $day);
        $tracingList = [];
        if (!empty($trace)) {
            // 获取链路详细信息
            $tracingList = Redis::hMGet('TransferStatistics:tracing:' . $day, $trace);

            // 处理数据
            $tracingList = array_map(function ($item) {
                $item = json_decode($item, true);

                $item['details']  = json_encode($item, 448);
                $item['costTime'] = round($item['costTime'] * 1000, 2);

                return $item;
            }, $tracingList);
        }

        return api([
            'total'       => $total,
            'tracingList' => $tracingList,
        ]);
    }

    /**
     * 状态码
     *
     * @author HSK
     * @date 2022-04-26 09:11:10
     *
     * @param \support\Request $request
     *
     * @return \support\Response
     */
    public function code(\support\Request $request)
    {
        $code = request()->input('code', '');
        $date = (string)request()->input('date', date('Y-m-d', time()));

        // 查看的日期
        $day = date('Ymd', strtotime($date));

        //////
        // 获取状态码统计数据（当天时间一分钟一统计）
        //////
        $chartCount = Redis::hGetAll('TransferStatistics:statistic:code_count:' . $code . ':' . $day) ?: [];
        $chartCost  = Redis::hGetAll('TransferStatistics:statistic:code_cost:' . $code . ':' . $day) ?: [];

        // 获取间隔
        $intervalList  = [];
        $time          = strtotime($day);
        $intervalCount = (int)(ceil(time() / 60) * 60 - $time) / 60;
        $intervalCount = $intervalCount > 1440 ? 1440 : $intervalCount;
        for ($i = 0; $i < $intervalCount; $i++) {
            $intervalList[] = date('YmdHi', $time + $i * 60);
        }
        $intervalList = array_merge($intervalList, array_keys($chartCount));
        sort($intervalList);
        // 组装数据
        $chartList = [];
        array_map(function ($interval) use (&$chartList, &$chartCount, &$chartCost, &$chartSuccessCount, &$chartErrorCount) {
            $chartList['time'][$interval]        = date('y-m-d H:i', strtotime($interval));
            $chartList['count'][$interval]       = $chartCount[$interval] ?? 0;
            $chartList['cost'][$interval]        = $chartCost[$interval] ?? 0;
            $chartList['averageCost'][$interval] = (0 == $chartList['count'][$interval]) ? 0 : round($chartList['cost'][$interval] / $chartList['count'][$interval] * 1000, 2);
        }, $intervalList);
        $chartList['time']        = array_values($chartList['time'] ?? []);
        $chartList['count']       = array_values($chartList['count'] ?? []);
        $chartList['cost']        = array_values($chartList['cost'] ?? []);
        $chartList['averageCost'] = array_values($chartList['averageCost'] ?? []);

        return view('transfer_statistics/code', [
            'date'      => $date,
            'code'      => $code,
            'chartList' => json_encode($chartList, 320),
        ]);
    }

    /**
     * 状态码调用记录
     *
     * @author HSK
     * @date 2022-04-26 09:11:10
     *
     * @param \support\Request $request
     *
     * @return \support\Response
     */
    public function codeTracingList(\support\Request $request)
    {
        $code  = request()->input('code', '');
        $date  = (string)request()->input('date', date('Y-m-d', time()));
        $page  = (int)request()->input('page', 1);
        $limit = (int)request()->input('limit', 10);

        // 查看的日期
        $day = date('Ymd', strtotime($date));

        // 计算读取范围
        $start = (1 === $page) ? 0 : ($page - 1) * $limit;
        $end   = $page * $limit - 1;

        $trace       = Redis::lRange('TransferStatistics:code_trace:' . $code . ':' . $day, $start, $end);
        $total       = Redis::lLen('TransferStatistics:code_trace:' . $code . ':' . $day);
        $tracingList = [];
        if (!empty($trace)) {
            // 获取链路详细信息
            $tracingList = Redis::hMGet('TransferStatistics:tracing:' . $day, $trace);

            // 处理数据
            $tracingList = array_map(function ($item) {
                $item = json_decode($item, true);

                $item['details']  = json_encode($item, 448);
                $item['costTime'] = round($item['costTime'] * 1000, 2);

                return $item;
            }, $tracingList);
        }

        return api([
            'total'       => $total,
            'tracingList' => $tracingList,
        ]);
    }
}
