<?php

namespace app\admin\controller\admin;

use app\common\model\AdminAdmin as AdminAdminModel;
use app\common\model\AdminAdminRole as AdminAdminRoleModel;
use app\common\model\AdminAdminPermission as AdminAdminPermissionModel;
use app\common\model\AdminRole as AdminRoleModel;
use app\common\model\AdminPermission as AdminPermissionModel;
use app\common\model\AdminAdminLog as AdminAdminLogModel;

class Admin
{
    /**
     * 列表
     *
     * @author HSK
     * @date 2022-03-24 15:07:28
     *
     * @param \support\Request $request
     *
     * @return \support\Response
     */
    public function index(\support\Request $request)
    {
        if (request()->isAjax()) {
            $page  = (int)request()->input('page', 1);
            $limit = (int)request()->input('limit', 10);

            $where = [];
            if ($keywords = request()->input('keywords', '')) {
                $where[] = ["username|nickname", "like", "%" . $keywords . "%"];
            }

            $list = AdminAdminModel::where($where)
                ->order('id', 'desc')
                ->paginate([
                    'list_rows' => $limit,
                    'page'      => $page,
                ]);
            return api($list);
        }

        return view('admin/admin/index');
    }

    /**
     * 添加
     *
     * @author HSK
     * @date 2022-03-24 15:12:04
     *
     * @param \support\Request $request
     *
     * @return \support\Response
     */
    public function add(\support\Request $request)
    {
        if (request()->isAjax()) {
            try {
                $data = request()->post();

                if (AdminAdminModel::where('username', $data['username'])->count() > 0) {
                    return api([], 400, '用户名已存在');
                }

                $data['password'] = md5($data['password'] . 'hsk99');

                if (AdminAdminModel::create($data)) {
                    return api([], 200, '操作成功');
                } else {
                    return api([], 400, '操作失败');
                }
            } catch (\Throwable $th) {
                \Hsk99\WebmanException\RunException::report($th);
                return api([], 400, '操作失败');
            }
        }

        return view('admin/admin/add');
    }

    /**
     * 编辑
     *
     * @author HSK
     * @date 2022-03-24 15:13:48
     *
     * @param \support\Request $request
     *
     * @return \support\Response
     */
    public function edit(\support\Request $request)
    {
        if (request()->isAjax()) {
            try {
                $data = request()->post();

                if (AdminAdminModel::where('username', $data['username'])->where('id', '<>', request()->input('id'))->count() > 0) {
                    return api([], 400, '用户名已存在');
                }

                if (!empty($data['password'])) {
                    $data['password'] = md5($data['password'] . 'hsk99');
                } else {
                    unset($data['password']);
                }

                if (AdminAdminModel::update($data, ['id' => request()->input('id')])) {
                    return api([], 200, '操作成功');
                } else {
                    return api([], 400, '操作失败');
                }
            } catch (\Throwable $th) {
                \Hsk99\WebmanException\RunException::report($th);
                return api([], 400, '操作失败');
            }
        }

        return view('admin/admin/edit', [
            'model' => AdminAdminModel::find(request()->input('id'))
        ]);
    }

    /**
     * 状态
     *
     * @author HSK
     * @date 2022-03-24 15:15:03
     *
     * @param \support\Request $request
     *
     * @return \support\Response
     */
    public function status(\support\Request $request)
    {
        try {
            if (AdminAdminModel::update(request()->post(), ['id' => request()->input('id')])) {
                return api([], 200, '操作成功');
            } else {
                return api([], 400, '操作失败');
            }
        } catch (\Throwable $th) {
            \Hsk99\WebmanException\RunException::report($th);
            return api([], 400, '操作失败');
        }
    }

    /**
     * 删除
     *
     * @author HSK
     * @date 2022-03-24 15:15:47
     *
     * @param \support\Request $request
     *
     * @return \support\Response
     */
    public function remove(\support\Request $request)
    {
        AdminAdminModel::startTrans();

        try {
            $adminDelete = AdminAdminModel::destroy(request()->input('id'));
            $adminRoleDelete = AdminAdminRoleModel::destroy(function ($query) {
                $query->where('admin_id', request()->input('id'));
            });
            $adminPermissionDelete = AdminAdminPermissionModel::destroy(function ($query) {
                $query->where('admin_id', request()->input('id'));
            });

            if ($adminDelete && $adminRoleDelete && $adminPermissionDelete) {
                AdminAdminModel::commit();
                return api([], 200, '操作成功');
            } else {
                AdminAdminModel::rollback();
                return api([], 400, '操作失败');
            }
        } catch (\Throwable $th) {
            \Hsk99\WebmanException\RunException::report($th);
            AdminAdminModel::rollback();
            return api([], 400, '操作失败');
        }
    }

    /**
     * 批量删除
     *
     * @author HSK
     * @date 2022-03-24 15:19:57
     *
     * @param \support\Request $request
     *
     * @return \support\Response
     */
    public function batchRemove(\support\Request $request)
    {
        if (!is_array(request()->input('ids'))) return api([], 400, '数据不存在');

        AdminAdminModel::startTrans();

        try {
            $adminDelete = AdminAdminModel::destroy(request()->input('ids'));
            $adminRoleDelete = AdminAdminRoleModel::destroy(function ($query) {
                $query->where('admin_id', 'in', request()->input('ids'));
            });
            $adminPermissionDelete = AdminAdminPermissionModel::destroy(function ($query) {
                $query->where('admin_id', 'in', request()->input('ids'));
            });

            if ($adminDelete && $adminRoleDelete && $adminPermissionDelete) {
                AdminAdminModel::commit();
                return api([], 200, '操作成功');
            } else {
                AdminAdminModel::rollback();
                return api([], 400, '操作失败');
            }
        } catch (\Throwable $th) {
            \Hsk99\WebmanException\RunException::report($th);
            AdminAdminModel::rollback();
            return api([], 400, '操作失败');
        }
    }

    /**
     * 用户分配角色
     *
     * @author HSK
     * @date 2022-03-24 15:22:13
     *
     * @param \support\Request $request
     *
     * @return \support\Response
     */
    public function role(\support\Request $request)
    {
        if (request()->isAjax()) {

            AdminAdminModel::startTrans();

            try {
                $adminRoleDelete = AdminAdminRoleModel::destroy(function ($query) {
                    $query->where('admin_id', request()->input('id'));
                });
                if (request()->input('roles')) {
                    $roles = array_map(function ($item) {
                        return [
                            'admin_id' => request()->input('id'),
                            'role_id'  => $item,
                        ];
                    }, request()->input('roles'));
                    $adminRoleCreate = (new AdminAdminRoleModel())->saveAll($roles);
                } else {
                    $adminRoleCreate = true;
                }

                if ($adminRoleDelete && $adminRoleCreate) {
                    AdminAdminModel::commit();

                    \teamones\casbin\Enforcer::deleteRolesForUser('admin_admin_' . request()->input('id'));
                    if (request()->input('roles')) {
                        array_map(function ($item) {
                            \teamones\casbin\Enforcer::addRoleForUser('admin_admin_' . request()->input('id'), 'admin_role_' . $item);
                        }, request()->input('roles'));
                    }
                    \teamones\casbin\Enforcer::loadPolicy();
                    for ($i = 0; $i < config('server.count'); $i++) {
                        \support\Redis::set('CasbinLoadPolicy:' . $i, 1);
                    }

                    return api([], 200, '操作成功');
                } else {
                    AdminAdminModel::rollback();
                    return api([], 400, '操作失败');
                }
            } catch (\Throwable $th) {
                \Hsk99\WebmanException\RunException::report($th);
                AdminAdminModel::rollback();
                return api([], 400, '操作失败');
            }
        }

        $admin = AdminAdminModel::find(request()->input('id'));
        $adminRoleIds = AdminAdminRoleModel::where('admin_id', request()->input('id'))->column('role_id');
        $roles = AdminRoleModel::order('id', 'asc')->select();
        foreach ($roles as $role) {
            if (!empty($adminRoleIds) && in_array($role->id, $adminRoleIds)) {
                $role->own = true;
            }
        }

        return view('admin/admin/role', [
            'admin' => $admin,
            'roles' => $roles
        ]);
    }

    /**
     * 用户直接分配权限
     *
     * @author HSK
     * @date 2022-03-24 15:32:23
     *
     * @param \support\Request $request
     *
     * @return \support\Response
     */
    public function permission(\support\Request $request)
    {
        if (request()->isAjax()) {

            AdminAdminModel::startTrans();

            try {
                $adminPermissionDelete = AdminAdminPermissionModel::destroy(function ($query) {
                    $query->where('admin_id', request()->input('id'));
                });
                if (request()->input('permissions')) {
                    $permissions = array_map(function ($item) {
                        return [
                            'admin_id'      => request()->input('id'),
                            'permission_id' => $item,
                        ];
                    }, request()->input('permissions'));
                    $adminPermissionCreate = (new AdminAdminPermissionModel())->saveAll($permissions);
                } else {
                    $adminPermissionCreate = true;
                }

                if ($adminPermissionDelete && $adminPermissionCreate) {
                    AdminAdminModel::commit();

                    \teamones\casbin\Enforcer::deletePermissionsForUser('admin_admin_' . request()->input('id'));
                    if (request()->input('permissions')) {
                        $permissionIds  = array_column($permissions, 'permission_id');
                        $permissionList = AdminPermissionModel::where('id', 'in', $permissionIds)->select()->toArray();
                        $permissionList = array_column($permissionList, null, 'href');
                        foreach (\Webman\Route::getRoutes() as $route) {
                            $href   = $route->getPath();
                            if (request()->app !== substr($href, 1, strlen(request()->app))) {
                                continue;
                            }
                            $href   = substr($href, 1 + strlen(request()->app));
                            $class  = $route->getCallback()[0];
                            $method = $route->getCallback()[1];

                            if (empty($permissionList[$href])) {
                                continue;
                            }

                            $permissionList[$href]['class']  = $class;
                            $permissionList[$href]['method'] = $method;
                        }
                        array_map(function ($item) {
                            if (!empty($item['class']) && !empty($item['method'])) {
                                \teamones\casbin\Enforcer::addPermissionForUser('admin_admin_' . request()->input('id'), substr($item['class'], strlen('app\\' . request()->app)), $item['method']);
                            }
                        }, $permissionList);
                    }
                    \teamones\casbin\Enforcer::loadPolicy();
                    for ($i = 0; $i < config('server.count'); $i++) {
                        \support\Redis::set('CasbinLoadPolicy:' . $i, 1);
                    }

                    return api([], 200, '操作成功');
                } else {
                    AdminAdminModel::rollback();
                    return api([], 400, '操作失败');
                }
            } catch (\Throwable $th) {
                \Hsk99\WebmanException\RunException::report($th);
                AdminAdminModel::rollback();
                return api([], 400, '操作失败');
            }
        }

        $admin = AdminAdminModel::find(request()->input('id'));
        $adminPermissionsIds = AdminAdminPermissionModel::where('admin_id', request()->input('id'))->column('permission_id');
        $permissions = AdminPermissionModel::order('sort', 'asc')->select();
        foreach ($permissions as $permission) {
            if (!empty($adminPermissionsIds) && in_array($permission->id, $adminPermissionsIds)) {
                $permission->own = true;
            }
        }
        $permissions = get_tree($permissions->toArray());

        return view('admin/admin/permission', [
            'admin'       => $admin,
            'permissions' => $permissions
        ]);
    }

    /**
     * 回收站
     *
     * @author HSK
     * @date 2022-03-24 15:57:56
     *
     * @param \support\Request $request
     *
     * @return \support\Response
     */
    public function recycle(\support\Request $request)
    {
        if (request()->isAjax()) {
            if ('POST' === request()->method()) {
                $ids = request()->input('ids');
                if (!is_array($ids)) return api([], 400, '参数错误');
                try {
                    if (request()->input('type')) {
                        $result = AdminAdminModel::onlyTrashed()->where('id', 'in', $ids)->select()->each(function ($item) {
                            $item->restore();
                        });
                    } else {
                        $result = AdminAdminModel::destroy($ids, true);

                        array_map(function ($id) {
                            \teamones\casbin\Enforcer::deletePermissionsForUser('admin_admin_' . $id);
                        }, $ids);
                    }

                    if ($result) {
                        return api([], 200, '操作成功');
                    } else {
                        return api([], 400, '操作失败');
                    }
                } catch (\Throwable $th) {
                    \Hsk99\WebmanException\RunException::report($th);
                    return api([], 400, '操作失败');
                }
            }

            $page     = (int)request()->input('page', 1);
            $limit    = (int)request()->input('limit', 10);
            $username = request()->input('username', '');

            $where = [];
            if ($username) {
                $where[] = ['username', 'like', "%" . $username . "%"];
            }

            $list = AdminAdminModel::onlyTrashed()
                ->where($where)
                ->order('id', 'desc')
                ->paginate([
                    'list_rows' => $limit,
                    'page'      => $page,
                ]);
            return api($list);
        }

        return view('admin/admin/recycle');
    }

    /**
     * 用户日志
     *
     * @author HSK
     * @date 2022-03-24 16:02:03
     *
     * @param \support\Request $request
     *
     * @return \support\Response
     */
    public function log(\support\Request $request)
    {
        if (request()->isAjax()) {
            $page  = (int)request()->input('page', 1);
            $limit = (int)request()->input('limit', 10);

            $where = [];
            if ($uid   = (int)request()->input('uid', '')) {
                $where[] = ['uid', '=', $uid];
            }
            if ($ip = request()->input('ip', '')) {
                $where[] = ["ip", "=", $ip];
            }
            if ($keywords = request()->input('keywords', '')) {
                $where[] = ["url|desc", "like", "%" . $keywords . "%"];
            }

            $list = AdminAdminLogModel::where($where)
                ->order('id', 'desc')
                ->paginate([
                    'list_rows' => $limit,
                    'page'      => $page,
                ]);
            return api($list);
        }

        return view('admin/admin/log');
    }

    /**
     * 清空日志
     *
     * @author HSK
     * @date 2022-03-24 16:05:35
     *
     * @param \support\Request $request
     *
     * @return \support\Response
     */
    public function removeLog(\support\Request $request)
    {
        try {
            if (\think\facade\Db::name('admin_admin_log')->delete(true)) {
                return api([], 200, '操作成功');
            } else {
                return api([], 400, '操作失败');
            }
        } catch (\Throwable $th) {
            \Hsk99\WebmanException\RunException::report($th);
            return api([], 400, '操作失败');
        }
    }
}
