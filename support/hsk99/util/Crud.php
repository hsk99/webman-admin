<?php

namespace support\hsk99\util;

use think\facade\Db;
use app\common\model\AdminPermission as AdminPermissionModel;

class Crud
{
    // 验证
    protected static $check = ['admin_admin', 'admin_admin_log', 'admin_admin_permission', 'admin_admin_role', 'admin_permission', 'admin_file', 'admin_role', 'admin_role_permission', 'casbin'];

    // 参数
    protected static $data;

    // 获取所有表
    public static function getTable()
    {
        foreach (Db::getTables() as $k => $v) {
            $list[] = ['name' => $v];
        }
        return json(['code' => 0, 'data' => $list]);
    }

    // 添加
    public static function goAdd()
    {
        if ('POST' === request()->method()) {
            $data = request()->post();
            // 数据验证
            if (!preg_match('/^[a-z]+_[a-z]+$/i', $data['name'])) return api([], 400, '表名格式不正确');
            try {
                Db::execute('CREATE TABLE ' . config('thinkorm.connections.mysql.prefix') . $data['name'] . '(
                    `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT "id",
                    `create_time` timestamp NULL DEFAULT NULL COMMENT "创建时间",
                    `update_time` timestamp NULL DEFAULT NULL COMMENT "更新时间",
                    `delete_time` timestamp NULL DEFAULT NULL COMMENT "删除时间",
                    PRIMARY KEY (`id`)
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 COMMENT="' . $data['desc'] . '";
                    ');
            } catch (\Exception $e) {
                return api([], 400, $e->getMessage());
            }
        }

        return api([], 200, '操作成功');
    }

    // 获取Crud变量
    public static function getCrud()
    {
        return [
            'data'        => Db::getFields(request()->input('name')),
            'permissions' => get_tree(AdminPermissionModel::order('sort', 'asc')->select()->toArray()),
            'desc'        => Db::query('SELECT TABLE_COMMENT FROM information_schema.TABLES WHERE TABLE_NAME = "' . request()->input('name') . '"')[0]['TABLE_COMMENT']
        ];
    }

    // 删除CRUD
    public static function goRemove()
    {
        if ('POST' === request()->method()) {
            // 验证
            if (in_array(substr(request()->input('name'), strlen(config('thinkorm.connections.mysql.prefix'))), self::$check)) return api([], 400, '默认字段禁止操作');
            Db::query('drop table ' . request()->input('name'));
            if (request()->input('type')) {
                try {
                    $data['table'] = substr(request()->input('name'), strlen(config('thinkorm.connections.mysql.prefix')));
                    // 表名转驼峰
                    $data['table_hump'] = underline_hump($data['table']);
                    // 左
                    $data['left'] = strstr($data['table'], '_', true);
                    // 右
                    $data['right'] = substr($data['table'], strlen($data['left']) + 1);
                    // 右转驼峰
                    $data['right_hump'] = underline_hump($data['right']);
                    $commom = app_path() . DIRECTORY_SEPARATOR . 'common' . DIRECTORY_SEPARATOR;
                    // 控制器
                    $controller = app_path() . DIRECTORY_SEPARATOR . request()->app . DIRECTORY_SEPARATOR . 'controller' . DIRECTORY_SEPARATOR . $data['left'] . DIRECTORY_SEPARATOR . $data['right_hump'] . '.php';
                    if (file_exists($controller)) unlink($controller);
                    // 模型
                    $model = $commom . DIRECTORY_SEPARATOR . 'model' . DIRECTORY_SEPARATOR . $data['table_hump'] . '.php';
                    if (file_exists($model)) unlink($model);
                    // 删除视图目录
                    $view = app_path() . DIRECTORY_SEPARATOR . request()->app . DIRECTORY_SEPARATOR . 'view' . DIRECTORY_SEPARATOR . $data['left'] . DIRECTORY_SEPARATOR . $data['right'];
                    if (file_exists($view)) remove_dir($view);
                    // 删除菜单
                    AdminPermissionModel::destroy(function ($query) use ($data) {
                        $query->where('href', 'like', "%" . $data['left'] . '/' . $data['right'] . "%");
                    });
                } catch (\Exception $e) {
                    throw new \Exception($e->getMessage(), 400);
                }
            }

            return api([], 200, '操作成功');
        }
    }

    // CRUD生成
    public static function goCrud()
    {
        if ('POST' === request()->method()) {
            // 参数
            self::$data = request()->post();
            // 去除前缀表名
            self::$data['table'] = substr(request()->get('name'), strlen(config('thinkorm.connections.mysql.prefix')));
            // 验证字段
            if (in_array(self::$data['table'], self::$check)) return api([], 400, '默认字段禁止操作');
            // 表名转驼峰
            self::$data['table_hump'] = underline_hump(self::$data['table']);
            // 左
            self::$data['left'] = strstr(self::$data['table'], '_', true);
            // 右
            self::$data['right'] = substr(self::$data['table'], strlen(self::$data['left']) + 1);
            // 右转驼峰
            self::$data['right_hump'] = underline_hump(self::$data['right']);
            // 构造选中参数补全
            for ($i = 0; $i < count(self::$data['name']); $i++) {
                self::getNull($i);
                self::getList($i);
                self::getSearch($i);
                self::getForm($i);
                self::getNull($i);
                if (self::$data['name'][$i] == 'delete_time') {
                    self::$data['model_del'] = 'protected $deleteTime = "delete_time";';
                } else {
                    self::$data['model_del'] = 'protected $deleteTime = false;';
                }
            }

            // 路径
            $tpl    = base_path() . DIRECTORY_SEPARATOR . 'support' . DIRECTORY_SEPARATOR . 'hsk99' . DIRECTORY_SEPARATOR . 'tpl' . DIRECTORY_SEPARATOR;
            $commom = app_path() . DIRECTORY_SEPARATOR . 'common' . DIRECTORY_SEPARATOR;
            $view   = app_path() . DIRECTORY_SEPARATOR . request()->app . DIRECTORY_SEPARATOR . 'view' . DIRECTORY_SEPARATOR . self::$data['left'] . DIRECTORY_SEPARATOR . self::$data['right'] . DIRECTORY_SEPARATOR;
            $crud   = [
                app_path() . DIRECTORY_SEPARATOR . request()->app . DIRECTORY_SEPARATOR . 'controller' . DIRECTORY_SEPARATOR . self::$data['left'] . DIRECTORY_SEPARATOR . self::$data['right_hump'] . '.php' => self::getController($tpl . 'controller.tpl'),
                $commom . 'model' . DIRECTORY_SEPARATOR . self::$data['table_hump'] . '.php' => self::getModel($tpl . 'model.tpl'),
                $view . 'index.html' => self::getIndex($tpl . 'index.tpl'),
                $view . 'add.html' => self::getAdd($tpl . 'add.tpl'),
                $view . 'edit.html' => self::getAdd($tpl . 'edit.tpl'),
                $view . 'recycle.html' => self::getRecycle($tpl . 'recycle.tpl'),
            ];
            foreach ($crud as $k => $v) {
                @mkdir(dirname($k), 0755, true);
                @file_put_contents($k, $v);
            }
            // 添加菜单
            if (self::$data['menu'] != '') self::goMenu();

            return api([], 200, '操作成功');
        }
    }

    // 控制器
    public static function getController($tpl)
    {
        return str_replace(
            ['{{$app}}', '{{$date}}', '{{$ename}}', '{{$left}}', '{{$right}}', '{{$right_hump}}', '{{$table_hump}}', '{{$model_search}}'],
            [request()->app, date('Y-m-d H:i:s'), self::$data['ename'], self::$data['left'], self::$data['right'], self::$data['right_hump'], self::$data['table_hump'], implode("", self::$data['model_search'] ?? [])],
            file_get_contents($tpl)
        );
    }

    // 模型
    public static function getModel($tpl)
    {
        return str_replace(
            ['{{$table_hump}}', '{{$table}}', '{{$model_search}}', '{{$model_del}}'],
            [self::$data['table_hump'], self::$data['table'], implode("", self::$data['model_search'] ?? []), self::$data['model_del'] ?? ''],
            file_get_contents($tpl)
        );
    }

    // 列表页面
    public static function getIndex($tpl)
    {
        return str_replace(
            ['{{$ename}}', '{{$table_hump}}', '{{$left}}', '{{$right_hump}}', '{{$index_search}}', '{{$index_list}}', '{{$index_status}}', '{{$index_status_js}}', '{{$js_search}}'],
            [
                self::$data['ename'], self::$data['table_hump'], self::$data['left'], strtolower(self::$data['right_hump']), implode("", self::$data['index_search'] ?? []), implode("", self::$data['index_list'] ?? []),
                implode("", self::$data['index_status'] ?? []), implode("", self::$data['index_status_js'] ?? []), implode("", self::$data['js_search'] ?? [])
            ],
            file_get_contents($tpl)
        );
    }

    // 添加页面
    public static function getAdd($tpl)
    {
        return str_replace(
            ['{{$html_form}}', '{{$html_js}}', '{{$html_js_data}}'],
            [implode("", self::$data['html_form'] ?? []), implode("", self::$data['html_js'] ?? []), implode("", self::$data['html_js_data'] ?? [])],
            file_get_contents($tpl)
        );
    }

    // 编辑页面
    public static function getEdit($tpl)
    {
        return str_replace(
            ['{{$ename}}', '{{$table_hump}}', '{{$left}}', '{{$right}}', '{{$index_search}}', '{{$index_list}}', '{{$index_status}}', '{{$index_status_js}}'],
            [
                self::$data['ename'], self::$data['table_hump'], self::$data['left'], self::$data['right'], implode("", self::$data['index_search'] ?? []), implode("", self::$data['index_list'] ?? []),
                implode("", self::$data['index_status'] ?? []), implode("", self::$data['index_status_js'] ?? [])
            ],
            file_get_contents($tpl)
        );
    }

    // 回收站页面
    public static function getRecycle($tpl)
    {
        return str_replace(
            ['{{$ename}}', '{{$table_hump}}', '{{$left}}', '{{$right}}', '{{$index_search}}', '{{$index_list}}', '{{$js_search}}'],
            [
                self::$data['ename'], self::$data['table_hump'], self::$data['left'], self::$data['right'], implode("", self::$data['index_search'] ?? []), implode("", self::$data['index_list'] ?? []),
                implode("", self::$data['js_search'] ?? [])
            ],
            file_get_contents($tpl)
        );
    }

    // 列表处理
    public static function getList($i)
    {
        if (isset(self::$data['list'][$i])) {
            // 开关
            if (self::$data['formType'][$i] == "4") {
                self::$data['index_list'][$i] = '{
                       field: "' . self::$data['name'][$i] . '",
                       title: "' . self::$data['desc'][$i] . '",
                       unresize: "true",
                       align: "center",
                       templet:"#' . self::$data['name'][$i] . '"
                   }, ';

                self::$data['index_status'][$i] = '
               <script type="text/html" id="' . self::$data['name'][$i] . '">
                   <input type="checkbox" name="' . self::$data['name'][$i] . '" value="{{d.id}}" lay-skin="switch" lay-text="启用|禁用" lay-filter="' . self::$data['name'][$i] . '" {{# if(d.' . self::$data['name'][$i] . '==1){ }} checked {{# } }}>
               </script>';

                self::$data['index_status_js'][$i] = '
               form.on("switch(' . self::$data['name'][$i] . ')", function(data) {
                   var status = data.elem.checked?1:2;
                   var id = this.value;
                   var load = layer.load();
                   $.post(MODULE_PATH + "status",{' . self::$data['name'][$i] . ':status,id:id},function (res) {
                       layer.close(load);
                       //判断有没有权限
                       if(res && res.code==999){
                           layer.msg(res.msg, {
                               icon: 5,
                               time: 2000, 
                           })
                           return false;
                       }else if (res.code==200){
                           layer.msg(res.msg,{icon:1,time:1500})
                       } else {
                           layer.msg(res.msg,{icon:2,time:1500},function () {
                               $(data.elem).prop("checked",!$(data.elem).prop("checked"));
                               form.render()
                           })
                       }
                   })
               });';
            } else {
                self::$data['index_list'][$i] = '{
                       field: "' . self::$data['name'][$i] . '",
                       title: "' . self::$data['desc'][$i] . '",
                       unresize: "true",
                       align: "center"
                   }, ';
            }
        }
    }

    // 搜索处理
    public static function getSearch($i)
    {
        if (isset(self::$data['search'][$i])) {
            if (strstr(self::$data['name'][$i], "time")) {
                // 模型处理
                self::$data['model_search'][$i] = '
            // 按' . self::$data['desc'][$i] . '查找
            $start = request()->input("get.' . self::$data['name'][$i] . '-start");
            $end = request()->input("get.' . self::$data['name'][$i] . '-end");
            if ($start && $end) {
                $where[]=["' . self::$data['name'][$i] . '","between",[$start,date("Y-m-d",strtotime("$end +1 day"))]];
            }';
                // 页面处理
                self::$data['index_search'][$i] = '   
                    <div class="layui-form-item layui-inline">
                        <label class="layui-form-label">' . self::$data['desc'][$i] . '</label>
                        <div class="layui-input-inline">
                            <input type="text" class="layui-input" id="' . self::$data['name'][$i] . '-start" name="' . self::$data['name'][$i] . '-start" placeholder="开始时间" autocomplete="off">
                        </div>
                        <div class="layui-input-inline">
                            <input type="text" class="layui-input" id="' . self::$data['name'][$i] . '-end" name="' . self::$data['name'][$i] . '-end" placeholder="结束时间" autocomplete="off">
                        </div>
                    </div>';
                // 页面JS处理
                self::$data['js_search'][$i] = 'laydate.render({elem: "#' . self::$data['name'][$i] . '-start"});laydate.render({elem: "#' . self::$data['name'][$i] . '-end"});';
            } else {
                // 模型处理
                self::$data['model_search'][$i] = '
            // 按' . self::$data['desc'][$i] . '查找
            if ($' . self::$data['name'][$i] . ' = request()->input("' . self::$data['name'][$i] . '")) {
                $where[] = ["' . self::$data['name'][$i] . '", "like", "%" . $' . self::$data['name'][$i] . ' . "%"];
            }';
                // 页面处理
                self::$data['index_search'][$i] = '   
                    <div class="layui-form-item layui-inline">
                        <label class="layui-form-label">' . self::$data['desc'][$i] . '</label>
                        <div class="layui-input-inline">
                            <input type="text" name="' . self::$data['name'][$i] . '" placeholder="" class="layui-input">
                        </div>
                    </div>';
            }
        }
    }

    // 表单处理
    public static function getForm($i)
    {
        if (isset(self::$data['form'][$i]) && self::$data['formType'][$i] != '4') {
            $form = '<div class="layui-form-item">
                    <label class="layui-form-label">
                        ' . self::$data['desc'][$i] . '
                    </label>
                    <div class="layui-input-block">
                        ';
            $html_js = '';
            $html_js_data = '';
            $lay_verify = '';
            switch (self::$data['formType'][$i]) {
                case '5':
                    // 文本域
                    if (self::$data['null'][$i] === 'NO') {
                        $lay_verify = ' lay-verify="required ';
                    }
                    $form .= '<textarea class="layui-textarea"' . $lay_verify . ' name="' . self::$data['name'][$i] . '" >{$model[\'' . self::$data['name'][$i] . '\']??""}</textarea>';
                    break;
                case '3':
                    // 上传图片
                    if (self::$data['null'] === 'NO') {
                        $lay_verify = ' lay-verify="uploadimg"';
                    }
                    $form .= '{:opt_image("' .  self::$data['name'][$i]  . '")}
                        <button class="pear-btn pear-btn-primary pear-btn-sm upload-image" type="button">
                            <i class="fa fa-image">
                            </i>
                            上传图片
                        </button>
                        <input' . $lay_verify . ' name="' .  self::$data['name'][$i]  . '" type="hidden" value="{$model[\'' . self::$data['name'][$i] . '\']??""}"/>
                        <div class="upload-image">
                            <span>
                            </span>
                            <img class="upload-image" src="{$model[\'' . self::$data['name'][$i] . '\']??""}"/>
                        </div>';
                    $html_js .= 'layui.link("/static/component/pear/css/module/uploads.css")';
                    break;
                case '2':
                    // 富文本
                    $form .= '<textarea id="' . self::$data['name'][$i] . '" name="' . self::$data['name'][$i] . '" type="text/plain" style="width:100%;margin-bottom:20px;">{$model[\'' . self::$data['name'][$i] . '\']??""}</textarea>';
                    $html_js .= 'var ' . self::$data['name'][$i] . '  = layedit.build("' . self::$data['name'][$i] . '", {height: 400});';
                    $html_js_data .= 'data.field.' . self::$data['name'][$i] . '=layedit.getContent(' . self::$data['name'][$i] . ');';
                    break;
                default:
                    // 文本
                    if (self::$data['null'][$i] === 'NO') {
                        $lay_verify = ' lay-verify="required ';
                        if (in_array(self::$data['type'][$i], ['int', 'decimal', 'float', 'double'])) {
                            $lay_verify .= '|number';
                        }
                        $lay_verify .= '"';
                    }
                    $form .= '<input type="text" class="layui-input layui-form-danger"' . $lay_verify . ' name="' . self::$data['name'][$i] . '" type="text" value="{$model[\'' . self::$data['name'][$i] . '\']??""}"/>';
                    break;
            }
            $form .= '
                    </div>
                </div>';
            self::$data['html_form'][$i] = $form;
            self::$data['html_js'][$i] = $html_js;
            self::$data['html_js_data'][$i] = $html_js_data;
        }
    }

    // 空处理
    public static function getNull($i)
    {
        if (self::$data['null'][$i] == '1' && self::$data['formType'][$i] != "4" && self::$data['name'][$i] != "id") {
            self::$data['validate_rule'][$i] = '\'' . self::$data['name'][$i] . '\' => \'require' . '\',';
            self::$data['validate_msg'][$i] = '\'' . self::$data['name'][$i] . '.require\' => \'' . self::$data['desc'][$i] . '为必填项\',';
            if (strstr(self::$data['type'][$i], 'int') || strstr(self::$data['type'][$i], 'decimal') || strstr(self::$data['type'][$i], 'float') || strstr(self::$data['type'][$i], 'double')) {
                self::$data['validate_rule'][$i] = '\'' . self::$data['name'][$i] . '\' => \'require|number' . '\',';
                self::$data['validate_msg'][$i] = '\'' . self::$data['name'][$i] . '.require\' => \'' . self::$data['desc'][$i] . '为必填项\','
                    . '\'' . self::$data['name'][$i] . '.number\' => \'' . self::$data['desc'][$i] . '需为数字\',';
            }
            self::$data['validate_scene'][$i] = '\'' . self::$data['name'][$i] . '\',';
        }
    }

    // 创建菜单
    public static function goMenu()
    {
        $path = '/' . self::$data['left'] . '/' . strtolower(self::$data['right_hump']) . '/';
        $data = [
            'pid'   => self::$data['menu'],
            'title' => self::$data['ename'],
            'href'  => $path . 'index',
        ];
        $menu = AdminPermissionModel::create(array_merge($data, [
            'icon' => 'layui-icon layui-icon-fire'
        ]));
        $crud = [
            'add'         => "新增",
            'edit'        => "编辑",
            'status'      => "修改状态",
            'remove'      => "删除",
            'batchremove' => "批量删除",
            'recycle'     => "回收站"
        ];
        $data['pid'] = $menu['id'];
        foreach ($crud as $k => $v) {
            $data['title']  = $menu['title'] . $v;
            $data['href']   = $path . $k;
            $data['status'] = 2;
            AdminPermissionModel::create($data);
        }
    }
}
