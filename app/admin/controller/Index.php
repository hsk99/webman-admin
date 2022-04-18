<?php

namespace app\admin\controller;

use app\common\model\AdminPermission as AdminPermissionModel;
use app\common\model\AdminAdmin as AdminAdminModel;
use app\common\model\AdminAdminRole as AdminAdminRoleModel;
use app\common\model\AdminRolePermission as AdminRolePermissionModel;
use app\common\model\AdminAdminPermission as AdminAdminPermissionModel;
use app\common\model\AdminFile as AdminFileModel;

class Index
{
    /**
     * 首页
     *
     * @author HSK
     * @date 2022-03-23 13:24:10
     *
     * @param \support\Request $request
     *
     * @return \support\Response
     */
    public function index(\support\Request $request)
    {
        return view('index/index');
    }

    /**
     * 获取菜单
     *
     * @author HSK
     * @date 2022-03-23 13:44:02
     *
     * @param \support\Request $request
     *
     * @return \support\Response
     */
    public function menu(\support\Request $request)
    {
        $adminId = session('adminId');

        $permissionList = [];
        if (1 === $adminId) {
            $permissionList = AdminPermissionModel::where('status', 1)
                ->order(['sort' => 'DESC'])
                ->select()
                ->toArray();
        } else {
            $roleIds            = AdminAdminRoleModel::where('admin_id', $adminId)->column('role_id');
            $rolePermissionIds  = AdminRolePermissionModel::where('role_id', 'in', $roleIds)->column('permission_id');
            $adminPermissionIds = AdminAdminPermissionModel::where('admin_id', $adminId)->column('permission_id');
            $permissionIds      = array_merge($rolePermissionIds, $adminPermissionIds);
            $permissionIds      = array_unique($permissionIds);

            $permissionList = AdminPermissionModel::where('id', 'in', $permissionIds)
                ->where('status', 1)
                ->order(['sort' => 'DESC'])
                ->select()
                ->toArray();
        }

        $permissionList = array_map(function ($item) {
            $item['href'] =  '/' . request()->app . $item['href'];
            return $item;
        }, $permissionList);

        if (config('app.debug')) {
            $permissionList[-1] = [
                "id"    => -1,
                "pid"   => 0,
                "title" => "自动生成",
                "icon"  => "layui-icon layui-icon-util",
                "type"  => 0,
                "href"  => "",
            ];
            $permissionList[-2] = [
                "id"       => -2,
                "pid"      => -1,
                "title"    => "CRUD管理",
                "icon"     => "layui-icon layui-icon-console",
                "type"     => 1,
                "openType" => "_iframe",
                'href'     => "/" . request()->app . "/crud/index",
            ];
            $permissionList[-3] = [
                "id"       => -3,
                "pid"      => -1,
                "title"    => "表单生成",
                "icon"     => "layui-icon layui-icon-console",
                "type"     => 1,
                "openType" => "_iframe",
                'href'     => "/" . request()->app . "/crud/form",
            ];
        }

        return json(get_tree($permissionList));
    }

    /**
     * 主页
     *
     * @author HSK
     * @date 2022-03-23 15:01:50
     *
     * @param \support\Request $request
     *
     * @return \support\Response
     */
    public function home(\support\Request $request)
    {
        return view('index/home', [
            'os'             => PHP_OS,
            'php'            => PHP_VERSION,
            'webman_version' => 'v' . WEBMAN_VERSION,
            'mysql'          => \think\facade\Db::query('SELECT VERSION() as mysql_version')[0]['mysql_version'],
            'upload'         => config('server.max_package_size') / (1024 * 1024) . 'M',
        ]);
    }

    /**
     * 修改密码
     *
     * @author HSK
     * @date 2022-03-23 15:13:25
     *
     * @param \support\Request $request
     *
     * @return \support\Response
     */
    public function pass(\support\Request $request)
    {
        if (request()->isAjax()) {
            try {
                $data = request()->post();

                // 校验数据
                $validate = new \think\Validate();
                $validate->rule([
                    'old_password'     => 'require',
                    'password'         => 'require',
                    'confirm_password' => 'require|confirm:password',
                ]);
                $validate->message([
                    'old_password.require'     => '原密码不能为空',
                    'password.require'         => '新密码不能为空',
                    'confirm_password.require' => '确认密码不能为空',
                    'confirm_password.confirm' => '两次输入新密码不一致',
                ]);
                if (!$validate->check($data)) {
                    return api([], 400, $validate->getError());
                }

                $where['id']       = session('adminId');
                $where['password'] = md5($data['old_password'] . 'hsk99');
                $adminInfo = AdminAdminModel::field('id,username,status')->where($where)->find();
                if (empty($adminInfo)) {
                    return api([], 400, '原密码错误');
                }

                if (AdminAdminModel::update(['password' => md5($data['password'] . 'hsk99')], ['id' => session('adminId')])) {
                    session()->delete('adminId');
                    session()->delete('adminName');

                    return api([], 200, '修改成功')
                        ->cookie('authorization', '', 1, '/')
                        ->cookie('refresh_auth', '', 1, '/');
                } else {
                    return api([], 400, '修改失败');
                }
            } catch (\Throwable $th) {
                \Hsk99\WebmanException\RunException::report($th);
                return api([], 400, '修改失败');
            }
        }

        return view('index/pass');
    }

    /**
     * 清除缓存
     *
     * @author HSK
     * @date 2022-04-01 16:08:22
     *
     * @param \support\Request $request
     *
     * @return \support\Response
     */
    public function cache(\support\Request $request)
    {
        remove_dir(runtime_path() . '/views/');

        return api([], 200, '清除缓存成功');
    }

    /**
     * 通用上传
     *
     * @author HSK
     * @date 2022-04-06 15:47:27
     *
     * @param \support\Request $request
     *
     * @return \support\Response
     */
    public function upload(\support\Request $request)
    {
        try {
            /**
             * @var \Webman\Http\UploadFile
             */
            $file = request()->file('file');
            if ($file && $file->isValid()) {
                switch (config('system.file_type')) {
                    case 3:
                        $data = \support\hsk99\util\Qiniu::upload($file);
                        break;
                    case 2:
                        $data = \support\hsk99\util\Oss::upload($file);
                        break;
                    case 1:
                    default:
                        $path = public_path() . '/upload/' . $file->getUploadExtension() . '/' . date('Ymd') . '/' . uniqid() . '.' . $file->getUploadExtension();
                        $file->move($path);

                        $data = [
                            'name' => $file->getUploadName(),
                            'href' => str_replace(public_path(), '', $path),
                            'mime' => $file->getUploadMineType(),
                            'size' => byte_size(filesize($path) ?? 0),
                            'type' => 1,
                            'ext'  => $file->getUploadExtension(),
                        ];

                        break;
                }
            } else {
                return api([], 400, '上传失败');
            }

            AdminFileModel::create($data);

            return api([
                'src' => $data['href']
            ], 0, '上传成功');
        } catch (\Throwable $th) {
            \Hsk99\WebmanException\RunException::report($th);
            return api([], 400, '上传失败');
        }
    }

    /**
     * 选择图片
     *
     * @author HSK
     * @date 2022-04-06 23:20:37
     *
     * @param \support\Request $request
     *
     * @return \support\Response
     */
    public function optImage(\support\Request $request)
    {
        if (request()->isAjax()) {
            $page  = (int)request()->input('page', 1);
            $limit = (int)request()->input('limit', 10);

            $where = [];
            if ($keywords = request()->input('keywords', '')) {
                $where[] = ["name|mime", "like", "%" . $keywords . "%"];
            }
            $where[] = ["mime", "like", "%image/%"];

            $list = AdminFileModel::where($where)
                ->order('id', 'desc')
                ->paginate([
                    'list_rows' => $limit,
                    'page'      => $page,
                ]);
            return api($list);
        }

        return view('index/opt_image');
    }
}
