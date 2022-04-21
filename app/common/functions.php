<?php

/**
 * 文件选择
 *
 * @author HSK
 * @date 2022-04-21 10:04:01
 *
 * @param string $val
 * @param string $mime
 *
 * @return string
 */
function opt_file(string $val, string $mime = ''): string
{
    return '<button class="pear-btn pear-btn-primary pear-btn-sm" style="margin:4px 5px;vertical-align:top;" id="' . $val . '" type="button">文件选择</button>
    <script>
        layui.use(["jquery"],function() {
            let $ = layui.jquery;
            //弹出窗设置 自己设置弹出百分比
            function screen() {
                if (typeof width !== "number" || width === 0) {
                    width = $(window).width() * 0.8;
                }
                if (typeof height !== "number" || height === 0) {
                    height = $(window).height() - 20;
                }
                return [width + "px", height + "px"];
            }
            $("#' . $val . '").on("click", function () {
                layer.open({
                    type: 2,
                    maxmin: true,
                    title: "文件选择",
                    shade: 0.1,
                    area: screen(),
                    content: "/' . request()->app . '/index/optFile?mime=' . $mime . '",
                    success:function (layero,index) {
                        var iframe = window["layui-layer-iframe" + index];
                        iframe.child("' . $val . '")
                    }
                });
            });
        })
    </script>';
}

/**
 * 下划线转驼峰
 *
 * @author HSK
 * @date 2022-04-17 02:41:16
 *
 * @param string $str
 *
 * @return string
 */
function underline_hump(string $str): string
{
    return ucfirst(
        preg_replace_callback('/_([a-zA-Z])/', function ($match) {
            return strtoupper($match[1]);
        }, $str)
    );
}

/**
 * 递归无限级分类权限
 *
 * @author HSK
 * @date 2022-04-17 02:41:31
 *
 * @param array $data
 * @param integer $pid
 * @param string $field1
 * @param string $field2
 * @param string $field3
 *
 * @return array
 */
function get_tree($data, $pid = 0, $field1 = 'id', $field2 = 'pid', $field3 = 'children'): array
{
    $arr = [];
    foreach ($data as $k => $v) {
        if ($v[$field2] == $pid) {
            $v[$field3] = get_tree($data, $v[$field1]);
            $arr[] = $v;
        }
    }
    return $arr;
}

/**
 * 获取当前时间
 *
 * @author HSK
 * @date 2022-02-22 13:59:48
 *
 * @param boolean $isMicro
 *
 * @return string
 */
function get_date($isMicro = false): string
{
    $time = microtime(true);

    switch ($isMicro) {
        case false:
            $date = date('Y-m-d H:i:s', $time);
            break;
        case true:
            $date = date('Y-m-d H:i:s.', $time) . substr($time, 11);
            break;
    }

    return $date;
}

/**
 * 组装数据，hsk99/webman-gateway-worker
 *
 * @author HSK
 * @date 2022-01-06 11:50:49
 *
 * @param string $event
 * @param integer $code
 * @param string $msg
 * @param array $data
 *
 * @return array
 */
function event($event = '', $code = 200, $msg = 'success', $data = []): array
{
    $result = [
        'code' => $code,
        'msg'  => $msg,
        'data' => $data,
    ];

    if (!empty($event)) {
        $result = ['event' => $event] + $result;
    }

    return $result;
}

/**
 * 字节转化
 *
 * @author HSK
 * @date 2021-11-29 23:46:41
 *
 * @param integer $byte
 *
 * @return string
 */
function byte_size(int $byte): string
{
    $p      = 0;
    $format = 'bytes';

    if ($byte > 0 && $byte < 1024) {
        $p = 0;
        return number_format($byte) . ' ' . $format;
    }

    if ($byte >= 1024 && $byte < pow(1024, 2)) {
        $p      = 1;
        $format = 'KB';
    }

    if ($byte >= pow(1024, 2) && $byte < pow(1024, 3)) {
        $p      = 2;
        $format = 'MB';
    }

    if ($byte >= pow(1024, 3) && $byte < pow(1024, 4)) {
        $p      = 3;
        $format = 'GB';
    }

    if ($byte >= pow(1024, 4) && $byte < pow(1024, 5)) {
        $p      = 3;
        $format = 'TB';
    }

    $byte /= pow(1024, $p);

    return number_format($byte, 3) . ' ' . $format;
}


/**
 * 获取随机UA
 *
 * @author HSK
 * @date 2022-02-18 09:57:46
 *
 * @param string $type
 *
 * @return void
 */
function user_agent($type = 'pc')
{
    $userAgent = [
        'pc' => [
            'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_11_1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/51.0.2704.103 Safari/537.36',
            'Mozilla/5.0 (X11; Linux x86_64; rv:29.0) Gecko/20100101 Firefox/29.0',
            'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/34.0.1847.137 Safari/537.36',
            'Mozilla/5.0 (Macintosh; Intel Mac OS X 10.9; rv:29.0) Gecko/20100101 Firefox/29.0',
            'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_9_3) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/34.0.1847.137 Safari/537.36',
            'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_9_3) AppleWebKit/537.75.14 (KHTML, like Gecko) Version/7.0.3 Safari/537.75.14',
            'Mozilla/5.0 (Windows NT 6.1; WOW64; rv:29.0) Gecko/20100101 Firefox/29.0',
            'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/34.0.1847.137 Safari/537.36',
            'Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1)',
            'Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 5.1)',
            'Mozilla/4.0 (compatible; MSIE 8.0; Windows NT 6.1; WOW64; Trident/4.0)',
            'Mozilla/5.0 (compatible; MSIE 9.0; Windows NT 6.1; WOW64; Trident/5.0)',
            'Mozilla/5.0 (compatible; MSIE 10.0; Windows NT 6.1; WOW64; Trident/6.0)',
            'Mozilla/5.0 (Windows NT 6.1; WOW64; Trident/7.0; rv:11.0) like Gecko',
        ],
        'android' => [
            'Mozilla/5.0 (Android; Mobile; rv:29.0) Gecko/29.0 Firefox/29.0',
            'Mozilla/5.0 (Linux; Android 4.4.2; Nexus 4 Build/KOT49H) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/34.0.1847.114 Mobile Safari/537.36',
        ],
        'ios' => [
            'Mozilla/5.0 (iPad; CPU OS 7_0_4 like Mac OS X) AppleWebKit/537.51.1 (KHTML, like Gecko) CriOS/34.0.1847.18 Mobile/11B554a Safari/9537.53',
            'Mozilla/5.0 (iPad; CPU OS 7_0_4 like Mac OS X) AppleWebKit/537.51.1 (KHTML, like Gecko) Version/7.0 Mobile/11B554a Safari/9537.53',
            'Mozilla/5.0 (iPhone; CPU iPhone OS 8_0_2 like Mac OS X) AppleWebKit/600.1.4 (KHTML, like Gecko) Version/8.0 Mobile/12A366 Safari/600.1.4',
            'Mozilla/5.0 (iPhone; CPU iPhone OS 8_0 like Mac OS X) AppleWebKit/600.1.4 (KHTML, like Gecko) Version/8.0 Mobile/12A366 Safari/600.1.4',
        ],
    ];

    $type        = strtolower($type);
    $limit_types = array_keys($userAgent);
    !in_array($type, $limit_types) && $type = 'pc';
    $rand_key   = array_rand($userAgent[$type]);
    $user_agent = $userAgent[$type][$rand_key] . rand(0, 10000);

    return $user_agent;
}

/**
 * API响应
 *
 * @author HSK
 * @date 2021-11-18 10:40:39
 *
 * @param array $data
 * @param integer $code
 * @param string $msg
 *
 * @return \Webman\Http\Response
 */
function api($data = [], $code = 200, $msg = 'success')
{
    return json([
        'code' => $code,
        'msg'  => $msg,
        'data' => $data,
    ], 320);
}

/**
 * 生成URL，带有参数
 *
 * @author HSK
 * @date 2021-11-17 16:22:55
 *
 * @param string $name
 * @param array $parameters
 *
 * @return string
 */
function url($name, $parameters = []): string
{
    $route = route($name);
    if (!$route) {
        return '';
    }

    if (!empty($parameters)) {
        if ('/' === substr($route, -1, 1)) {
            $route = substr($route, 0, -1);
        }

        return $route . '?' . http_build_query($parameters);
    }

    return $route;
}

/**
 * 注释解析
 *
 * @author HSK
 * @date 2021-11-16 13:33:32
 *
 * @param string $doc
 *
 * @return string
 */
function annotation_scan(string $doc): string
{
    if (empty($doc)) {
        return '';
    }

    // 获取注释信息有效参数的起始位置，并截取
    $pos = strpos($doc, '/**', 0);
    if ($pos === false) {
        return '';
    }
    $doc = substr($doc, $pos);

    // 处理数据
    $data = [];
    foreach (explode("\n", $doc) as $item) {
        if ($item = trim($item, "\r\n * /")) {
            if (false === strpos($item, '@')) {
                return $item;
            }
        }
    }
}
