<?php

namespace app\common\middleware;

use Webman\MiddlewareInterface;
use Webman\Http\Response;
use Webman\Http\Request;

class StaticFile implements MiddlewareInterface
{
    public function process(Request $request, callable $next): Response
    {
        // 禁止访问.开头的隐藏文件
        if (strpos($request->path(), '/.') !== false) {
            $response = response('<h1>403 forbidden</h1>', 403);
        } else {
            /** @var Response $response */
            $response = $next($request);
        }

        // 增加跨域http头
        // $response->withHeaders([
        //     'Access-Control-Allow-Origin'      => '*',
        //     'Access-Control-Allow-Credentials' => 'true',
        // ]);

        $response->withHeaders([
            'Server' => 'hsk99'
        ]);

        if (config('app.monitor')) {
            $this->recordTransceivedTraffic($request, $response);
        }

        return $response;
    }

    /**
     * 记录收发流量
     *
     * @author HSK
     * @date 2022-05-25 01:13:50
     *
     * @param Request $request
     *
     * @return void
     */
    protected function recordTransceivedTraffic(Request $request, Response $response)
    {
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

        $data = [
            'event'               => 'transceivedTraffic',
            'time'                => date('Y-m-d H:i:s', time()),                    // 请求时间
            'transceived_traffic' => $requestLen + strlen($response) + $fileLen,     // 收发流量
            'ip'                  => $request->getRealIp($safe_mode = true) ?? '',   // 请求客户端IP
        ];

        \Webman\RedisQueue\Client::send('webman_TransferStatistics', $data);
    }
}
