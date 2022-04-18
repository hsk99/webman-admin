<?php

namespace app\admin\controller;

use think\facade\Db;

class Crud
{
    /**
     * 系统配置
     *
     * @author HSK
     * @date 2022-03-24 18:09:01
     *
     * @param \support\Request $request
     *
     * @return \support\Response
     */
    public function index(\support\Request $request)
    {
        if (request()->isAjax()) {
            return \support\hsk99\util\Crud::getTable();
        }

        return view('crud/index', [
            'prefix' => config('thinkorm.connections.mysql.prefix')
        ]);
    }

    /**
     * 列表
     *
     * @author HSK
     * @date 2022-03-24 18:13:24
     *
     * @param \support\Request $request
     *
     * @return \support\Response
     */
    public function list(\support\Request $request)
    {
        return json(['code' => 0, 'data' => Db::getFields(request()->input('name'))]);
    }

    /**
     * 新增
     *
     * @author HSK
     * @date 2022-03-25 13:13:33
     *
     * @param \support\Request $request
     *
     * @return \support\Response
     */
    public function add(\support\Request $request)
    {
        if (request()->isAjax()) {
            return \support\hsk99\util\Crud::goAdd();
        }

        return view('crud/add', [
            'prefix' => config('thinkorm.connections.mysql.prefix')
        ]);
    }

    /**
     * CRUD
     *
     * @author HSK
     * @date 2022-03-25 13:18:24
     *
     * @param \support\Request $request
     *
     * @return \support\Response
     */
    public function crud(\support\Request $request)
    {
        if (request()->isAjax()) {
            return \support\hsk99\util\Crud::goCrud();
        }

        return view('crud/crud', \support\hsk99\util\Crud::getCrud());
    }

    /**
     * 删除
     *
     * @author HSK
     * @date 2022-03-25 14:25:18
     *
     * @param \support\Request $request
     *
     * @return \support\Response
     */
    public function remove(\support\Request $request)
    {
        return \support\hsk99\util\Crud::goRemove();
    }

    /**
     * 表单构建
     *
     * @author HSK
     * @date 2022-04-06 16:14:10
     *
     * @param \support\Request $request
     *
     * @return \support\Response
     */
    public function form(\support\Request $request)
    {
        return view('crud/form');
    }
}
