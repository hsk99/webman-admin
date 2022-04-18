<?php

namespace app\admin\controller\admin;

use app\common\model\AdminRole as AdminRoleModel;
use app\common\model\AdminAdminRole as AdminAdminRoleModel;
use app\common\model\AdminRolePermission as AdminRolePermissionModel;
use app\common\model\AdminPermission as AdminPermissionModel;

class Role
{
    /**
     * 列表
     *
     * @author HSK
     * @date 2022-03-23 21:13:09
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

            $list = AdminRoleModel::order('id', 'desc')
                ->paginate([
                    'list_rows' => $limit,
                    'page'      => $page,
                ]);
            return api($list);
        }

        return view('admin/role/index');
    }

    /**
     * 添加
     *
     * @author HSK
     * @date 2022-03-23 21:13:09
     *
     * @param \support\Request $request
     *
     * @return \support\Response
     */
    public function add(\support\Request $request)
    {
        if (request()->isAjax()) {
            try {
                if (AdminRoleModel::create(request()->post())) {
                    return api([], 200, '操作成功');
                } else {
                    return api([], 400, '操作失败');
                }
            } catch (\Throwable $th) {
                \Hsk99\WebmanException\RunException::report($th);
                return api([], 400, '操作失败');
            }
        }

        return view('admin/role/add');
    }

    /**
     * 编辑
     *
     * @author HSK
     * @date 2022-03-23 21:13:09
     *
     * @param \support\Request $request
     *
     * @return \support\Response
     */
    public function edit(\support\Request $request)
    {
        if (request()->isAjax()) {
            try {
                if (AdminRoleModel::update(request()->post(), ['id' => request()->input('id')])) {
                    return api([], 200, '操作成功');
                } else {
                    return api([], 400, '操作失败');
                }
            } catch (\Throwable $th) {
                \Hsk99\WebmanException\RunException::report($th);
                return api([], 400, '操作失败');
            }
        }

        return view('admin/role/edit', [
            'model' => AdminRoleModel::find(request()->input('id')),
        ]);
    }

    /**
     * 删除
     *
     * @author HSK
     * @date 2022-03-23 21:13:09
     *
     * @param \support\Request $request
     *
     * @return \support\Response
     */
    public function remove(\support\Request $request)
    {
        AdminRoleModel::startTrans();

        try {
            $adminRoleDelete = AdminRoleModel::destroy(request()->input('id'));
            $adminAdminRoleDelete = AdminAdminRoleModel::destroy(function ($query) {
                $query->where('role_id', '=', request()->input('id'));
            });
            $adminRolePermissionDelete = AdminRolePermissionModel::destroy(function ($query) {
                $query->where('role_id', '=', request()->input('id'));
            });

            if ($adminRoleDelete && $adminAdminRoleDelete && $adminRolePermissionDelete) {
                AdminRoleModel::commit();
                return api([], 200, '操作成功');
            } else {
                AdminRoleModel::rollback();
                return api([], 400, '操作失败');
            }
        } catch (\Throwable $th) {
            \Hsk99\WebmanException\RunException::report($th);
            AdminRoleModel::rollback();
            return api([], 400, '操作失败');
        }
    }

    /**
     * 用户分配直接权限
     *
     * @author HSK
     * @date 2022-03-23 21:22:45
     *
     * @param \support\Request $request
     *
     * @return \support\Response
     */
    public function permission(\support\Request $request)
    {
        if (request()->isAjax()) {
            AdminRoleModel::startTrans();

            try {
                $adminRolePermissionDelete = AdminRolePermissionModel::destroy(function ($query) {
                    $query->where('role_id', '=', request()->input('id'));
                });
                if (request()->input('permissions')) {
                    $permissions = array_map(function ($item) {
                        return [
                            'role_id'       => request()->input('id'),
                            'permission_id' => $item,
                        ];
                    }, request()->input('permissions'));
                    $adminRolePermissionCreate = (new AdminRolePermissionModel())->saveAll($permissions);
                } else {
                    $adminRolePermissionCreate = true;
                }

                $adminRoleUpdate = AdminRoleModel::update(request()->except(['permissions'], ['id' => request()->input('id')]));

                if ($adminRolePermissionDelete && $adminRolePermissionCreate && $adminRoleUpdate) {
                    AdminRoleModel::commit();

                    if (request()->input('permissions')) {
                        $permissionIds  = array_column($permissions, 'permission_id');
                        $permissionList = AdminPermissionModel::where('id', 'in', $permissionIds)->select()->toArray();
                        $permissionList = array_column($permissionList, null, 'href');

                        foreach (\Webman\Route::getRoutes() as $route) {
                            $href   = $route->getPath();
                            $href   = substr($href, 1 + strlen(request()->app));
                            $class  = $route->getCallback()[0];
                            $method = $route->getCallback()[1];

                            if (!empty($href) && empty($permissionList[$href])) {
                                continue;
                            }

                            $permissionList[$href]['class']  = $class;
                            $permissionList[$href]['method'] = $method;
                        }
                        unset($permissionList[""]);

                        \teamones\casbin\Enforcer::deletePermissionsForUser('admin_role_' . request()->input('id'));
                        array_map(function ($item) {
                            if (!empty($item['class']) && !empty($item['method'])) {
                                \teamones\casbin\Enforcer::addPermissionForUser('admin_role_' . request()->input('id'), substr($item['class'], strlen('app\\' . request()->app)), $item['method']);
                            }
                        }, $permissionList);
                    }

                    return api([], 200, '操作成功');
                } else {
                    AdminRoleModel::rollback();
                    return api([], 400, '操作失败');
                }
            } catch (\Throwable $th) {
                \Hsk99\WebmanException\RunException::report($th);
                AdminRoleModel::rollback();
                return api([], 400, '操作失败');
            }
        }

        $rolePermissionsIds = AdminRolePermissionModel::where('role_id', request()->input('id'))->column('permission_id');
        $permissions = AdminPermissionModel::order('sort', 'asc')->select();
        foreach ($permissions as $permission) {
            if (!empty($rolePermissionsIds) && in_array($permission->id, $rolePermissionsIds)) {
                $permission->own = true;
            }
        }
        $permissions = get_tree($permissions->toArray());

        return view('admin/role/permission', [
            'role'        => AdminRoleModel::find(request()->input('id')),
            'permissions' => $permissions,
        ]);
    }

    /**
     * 回收站
     *
     * @author HSK
     * @date 2022-03-23 21:47:07
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
                        $result = AdminRoleModel::onlyTrashed()->where('id', 'in', $ids)->select()->each(function ($item) {
                            $item->restore();
                        });
                    } else {
                        $result = AdminRoleModel::destroy($ids, true);
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

            $page  = (int)request()->input('page', 1);
            $limit = (int)request()->input('limit', 10);

            $list = AdminRoleModel::onlyTrashed()
                ->order('id', 'desc')
                ->paginate([
                    'list_rows' => $limit,
                    'page'      => $page,
                ]);
            return api($list);
        }

        return view('admin/role/recycle');
    }
}
