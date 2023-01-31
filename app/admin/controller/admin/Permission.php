<?php

namespace app\admin\controller\admin;

use app\admin\model\AdminPermission as AdminPermissionModel;
use app\admin\model\AdminAdminPermission as AdminAdminPermissionModel;
use app\admin\model\AdminRolePermission as AdminRolePermissionModel;

class Permission
{
    /**
     * 列表
     *
     * @author HSK
     * @date 2022-03-23 17:34:38
     *
     * @param \support\Request $request
     *
     * @return \support\Response
     */
    public function index(\support\Request $request)
    {
        if (request()->isAjax()) {
            $list = AdminPermissionModel::order('sort', 'desc')->select();
            return api($list);
        }

        return view('admin/permission/index');
    }

    /**
     * 添加
     *
     * @author HSK
     * @date 2022-03-23 18:12:27
     *
     * @param \support\Request $request
     *
     * @return \support\Response
     */
    public function add(\support\Request $request)
    {
        if (request()->isAjax()) {
            try {
                if (AdminPermissionModel::create(request()->post())) {
                    return api([], 200, '操作成功');
                } else {
                    return api([], 400, '操作失败');
                }
            } catch (\Throwable $th) {
                return api([], 400, '操作失败');
            }
        }

        return view('admin/permission/add', [
            'permissions' => get_tree(AdminPermissionModel::order('sort', 'asc')->select()->toArray())
        ]);
    }

    /**
     * 编辑
     *
     * @author HSK
     * @date 2022-03-23 18:34:43
     *
     * @param \support\Request $request
     *
     * @return \support\Response
     */
    public function edit(\support\Request $request)
    {
        if (request()->isAjax()) {
            try {
                if (AdminPermissionModel::update(request()->post(), ['id' => request()->input('id')])) {
                    return api([], 200, '操作成功');
                } else {
                    return api([], 400, '操作失败');
                }
            } catch (\Throwable $th) {
                \Hsk99\WebmanException\RunException::report($th);
                return api([], 400, '操作失败');
            }
        }

        return view('admin/permission/edit', [
            'model'       => AdminPermissionModel::find(request()->input('id')),
            'permissions' => get_tree(AdminPermissionModel::order('sort', 'asc')->select()->toArray()),
        ]);
    }

    /**
     * 状态
     *
     * @author HSK
     * @date 2022-03-23 20:42:03
     *
     * @param \support\Request $request
     *
     * @return \support\Response
     */
    public function status(\support\Request $request)
    {
        try {
            if (AdminPermissionModel::update(request()->post(), ['id' => request()->input('id')])) {
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
     * @date 2022-03-23 20:43:43
     *
     * @param \support\Request $request
     *
     * @return \support\Response
     */
    public function remove(\support\Request $request)
    {
        try {
            AdminPermissionModel::startTrans();

            if (request()->input('type')) {
                $ids = AdminPermissionModel::where('pid', request()->input('id'))->column('id');

                $pidAdminPermission = AdminPermissionModel::destroy(function ($query) {
                    $query->where('pid', '=', request()->input('id'));
                });
                $pidAdminAdminPermission = AdminAdminPermissionModel::destroy(function ($query) use ($ids) {
                    $query->where('permission_id', 'in', $ids);
                });
                $pidAdminRolePermission = AdminRolePermissionModel::destroy(function ($query) use ($ids) {
                    $query->where('permission_id', 'in', $ids);
                });
            } else {
                if (AdminPermissionModel::where('pid', request()->input('id'))->count() > 0) {
                    AdminPermissionModel::commit();
                    return api([], 201, '存在子权限，确认删除后不可恢复');
                }

                $pidAdminPermission = $pidAdminAdminPermission = $pidAdminRolePermission = true;
            }

            $adminPermission = AdminPermissionModel::destroy(function ($query) {
                $query->where('id', '=', request()->input('id'));
            });
            $adminAdminPermission = AdminAdminPermissionModel::destroy(function ($query) {
                $query->where('permission_id', '=', request()->input('id'));
            });
            $adminRolePermission = AdminRolePermissionModel::destroy(function ($query) {
                $query->where('permission_id', '=', request()->input('id'));
            });

            if (
                $pidAdminPermission &&
                $pidAdminAdminPermission &&
                $pidAdminRolePermission &&
                $adminPermission &&
                $adminAdminPermission &&
                $adminRolePermission
            ) {
                AdminPermissionModel::commit();
                return api([], 200, '操作成功');
            } else {
                AdminPermissionModel::rollback();
                return api([], 400, '操作失败');
            }
        } catch (\Throwable $th) {
            \Hsk99\WebmanException\RunException::report($th);
            AdminPermissionModel::rollback();
            return api([], 400, '操作失败');
        }
    }

    /**
     * 生成菜单
     *
     * @author HSK
     * @date 2022-04-12 17:26:31
     *
     * @param \support\Request $request
     *
     * @return \support\Response
     */
    public function generate(\support\Request $request)
    {
        try {
            $routeList = [];
            foreach (\Webman\Route::getRoutes() as $route) {
                if (
                    "/" === substr($route->getPath(), -1, 1) ||
                    "/" . request()->app === $route->getPath() ||
                    request()->app !== strtolower(substr($route->getPath(), 1, strlen(request()->app))) ||
                    request()->app . '/crud' === strtolower(substr($route->getPath(), 1, strlen(request()->app . '/crud'))) ||
                    request()->app . '/index' === strtolower(substr($route->getPath(), 1, strlen(request()->app . '/index'))) ||
                    request()->app . '/login' === strtolower(substr($route->getPath(), 1, strlen(request()->app . '/login')))
                ) {
                    continue;
                }

                $class  = $route->getCallback()[0];
                $method = $route->getCallback()[1];

                $reflectionClass = new \ReflectionClass($class);

                $path = $route->getPath();
                $path = substr($path, 1 + strlen(request()->app));

                $routeList[$class . '::' . $method] = [
                    'class'       => preg_replace('/(.*)\/{1}([^\/]*)/i', '$1', $path),
                    'href'        => $path,
                    'controller'  => $class,
                    'action'      => $method,
                    'class_desc'  => annotation_scan($reflectionClass->getDocComment()),
                    'method_desc' => annotation_scan($reflectionClass->getMethod($method)->getDocComment()),
                ];
            }
            $routeList = array_column($routeList, NULL, 'href');

            $permissionList = AdminPermissionModel::select()->toArray();
            $permissionList = array_column($permissionList, NULL, 'href');

            $createPermissionKey = array_diff(array_keys($routeList), array_keys($permissionList));
            $createPermissionKey = array_values($createPermissionKey);

            if (0 === count($createPermissionKey)) {
                return api([], 200, '操作成功');
            }

            array_map(function ($href) use ($routeList, $permissionList) {
                $route = $routeList[$href];

                if (!empty($permissionList[$route['class'] . '/index'])) {
                    $pid = $permissionList[$route['class'] . '/index']['id'];
                }

                AdminPermissionModel::create([
                    'pid'    => $pid ?? 0,
                    'title'  => $route['class_desc'] . $route['method_desc'],
                    'href'   => $route['href'],
                    'status' => strtolower($route['action']) === 'index' ? 1 : 2,
                ]);
            }, $createPermissionKey);

            return api([], 200, '操作成功');
        } catch (\Throwable $th) {
            \Hsk99\WebmanException\RunException::report($th);
            return api([], 400, '操作失败');
        }
    }
}
