<?php

namespace app\admin\controller;

class Config
{
    /**
     * 列表
     *
     * @author HSK
     * @date 2022-03-24 16:10:38
     *
     * @param \support\Request $request
     *
     * @return \support\Response
     */
    public function index(\support\Request $request)
    {
        if (request()->isAjax()) {
            try {
                $data = request()->post();

                $str = "<?php\r\n\r\nreturn [\r\n";
                foreach ($data as $key => $value) {
                    if (empty($value)) {
                        continue;
                    }
                    if (is_array($value)) {
                        $str .= $this->getArrTree($key, $value);
                    } else {
                        $str .= "\t'$key' => '$value',";
                        $str .= "\r\n";
                    }
                }
                $str .= '];';
                @file_put_contents(config_path() . '/system.php', $str);

                \Webman\Config::reload(config_path(), ['route', 'container']);;

                return api([], 200, '操作成功');
            } catch (\Throwable $th) {
                \Hsk99\WebmanException\RunException::report($th);
                return api([], 400, '操作失败');
            }
        }

        return view('config/index', [
            'data' => config('system', [
                'login_captcha' => '1',
                'file_type'     => '1',
            ])
        ]);
    }

    /**
     * 递归配置数组
     *
     * @author HSK
     * @date 2022-04-17 21:42:31
     *
     * @param string $key
     * @param array $data
     * @param string $level
     *
     * @return string
     */
    protected function getArrTree(string $key, array $data, string $level = "\t"): string
    {
        $i = "$level'$key' => [\r\n";
        foreach ($data as $k => $v) {
            if (is_array($v)) {
                $i .= $this->getArrTree($k, $v, $level . "\t");
            } else {
                $i .= "$level\t'$k' => '$v',";
                $i .= "\r\n";
            }
        }
        return  $i . "$level" . '],' . "\r\n";
    }
}
