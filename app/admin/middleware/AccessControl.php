<?php

namespace app\admin\middleware;

/**
 * 访问控制
 *
 * @author HSK
 * @date 2022-04-18 10:15:07
 */
class AccessControl implements \Webman\MiddlewareInterface
{
    public function process(\Webman\Http\Request $request, callable $next): \Webman\Http\Response
    {
        $token        = request()->cookie('authorization') ?? request()->header('authorization');
        $refreshToken = request()->cookie('refresh_auth') ?? request()->header('refresh_auth');

        // 已登录，跳出页面
        if (
            strtolower(request()->controller) === "app\\request()->app\controller\login"
            && strtolower(request()->action) === 'index'
            && 'GET' === request()->method()
            && session()->has('adminId')
        ) {
            return redirect("/" . request()->app);
        }

        // 执行登录，跳出校验
        if (strtolower(request()->controller) === "app\\" . request()->app . "\\controller\\login") {
            return $next($request);
        }

        // 不存在登录信息
        if (!session()->has('adminId')) {
            // 存在Token，执行自动登录
            if (!empty($token)) {
                try {
                    $tokenResult = \Tinywan\Jwt\JwtToken::verify(1, $token);

                    session()->set('adminId', $tokenResult['extend']['id']);
                    session()->set('adminName', $tokenResult['extend']['username']);
                    session()->set('nickname', $tokenResult['extend']['nickname']);

                    return redirect("/" . request()->app);
                } catch (\Throwable $th) {
                    switch ($th->getMessage()) {
                        case '获取的扩展字段不存在':
                        case '身份验证会话已过期，请重新登录！':
                        case '身份验证令牌尚未生效':
                        case '身份验证令牌无效':
                            break;
                        default:
                            \Hsk99\WebmanException\RunException::report($th);
                            break;
                    }
                }
            }

            // 存在刷新Token，执行自动登录
            if (!empty($refreshToken)) {
                try {
                    $refreshTokenResult = \Tinywan\Jwt\JwtToken::verify(2, $refreshToken);

                    $token = \Tinywan\Jwt\JwtToken::generateToken($refreshTokenResult['extend']);

                    session()->set('adminId', $refreshTokenResult['extend']['id']);
                    session()->set('adminName', $refreshTokenResult['extend']['username']);
                    session()->set('nickname', $refreshTokenResult['extend']['nickname']);

                    return redirect("/" . request()->app)
                        ->cookie('authorization', $token['access_token'], $token['expires_in'], '/');
                } catch (\Throwable $th) {
                    switch ($th->getMessage()) {
                        case '获取的扩展字段不存在':
                        case '身份验证会话已过期，请重新登录！':
                        case '身份验证令牌尚未生效':
                        case '身份验证令牌无效':
                            break;
                        default:
                            \Hsk99\WebmanException\RunException::report($th);
                            break;
                    }
                }
            }

            // 不存在Token、刷新Token，跳转登录页面
            return redirect("/" . request()->app . "/login/index");
        }

        // 记录后台访问日志
        \Webman\RedisQueue\Client::send('webman_log_admin', [
            'uid'        => session('adminId'),
            'url'        => substr(request()->path(), 1 + strlen(request()->app)) ?: "/",
            'desc'       => json_encode([
                'method' => request()->method() ?? '',   // 请求方法
                'param'  => request()->all() ?? [],      // 请求参数
            ]),
            'ip'         => request()->getRealIp($safe_mode = true),
            'user_agent' => request()->header('user-agent')
        ]);

        // 跳出权限校验
        if (
            1 === session('adminId') ||
            "/" === substr(request()->path(), -1, 1) ||
            "/" . request()->app === request()->path() ||
            request()->app . '/crud' === substr(request()->path(), 1, strlen(request()->app . '/crud')) ||
            request()->app . '/index' === substr(request()->path(), 1, strlen(request()->app . '/index')) ||
            request()->app . '/login' === substr(request()->path(), 1, strlen(request()->app . '/login'))
        ) {
            return $next($request);
        }

        // 权限校验
        if (\support\Redis::exists('CasbinLoadPolicy:' . request()->connection->worker->id)) {
            \teamones\casbin\Enforcer::loadPolicy();
            \support\Redis::del('CasbinLoadPolicy:' . request()->connection->worker->id);
        }
        if (!\teamones\casbin\Enforcer::enforce('admin_admin_' . session('adminId'), substr(request()->controller, strlen('app\\' . request()->app)), request()->action)) {
            return api([], 999, '没有权限');
        }

        return $next($request);
    }
}
