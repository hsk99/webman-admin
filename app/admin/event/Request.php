<?php

namespace app\admin\event;

class Request
{
    /**
     * 后台请求记录
     *
     * @author HSK
     * @date 2022-12-31 23:30:12
     *
     * @return void
     */
    function log() 
    {
        try {
            \app\admin\model\AdminAdminLog::create([
                'uid'         => session('adminId'),
                'url'         => substr(request()->path(), 1 + strlen(request()->app)) ?: "/",
                'desc'        => json_encode([
                    'method' => request()->method() ?? '',   // 请求方法
                    'param'  => request()->all() ?? [],      // 请求参数
                ]),
                'ip'          => request()->getRealIp($safe_mode = true),
                'user_agent'  => request()->header('user-agent'),
                'create_time' => date('Y-m-d H:i:s')
            ]);
        } catch (\Throwable $th) {
            \Hsk99\WebmanException\RunException::report($th);
        }
    }
}
