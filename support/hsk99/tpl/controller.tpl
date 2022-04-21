<?php

namespace app\{{$app}}\controller\{{$left}};

use app\common\model\{{$table_hump}} as {{$table_hump}}Model;

/**
 * {{$ename}}
 *
 * @author HSK
 * @date {{$date}}
 */
class {{$right_hump}}
{
    /**
     * 列表
     *
     * @author HSK
     * @date {{$date}}
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
            {{$model_search}}

            $list = {{$table_hump}}Model::where($where)
                ->order('id', 'desc')
                ->paginate([
                    'list_rows' => $limit,
                    'page'      => $page,
                ]);
            return api($list);
        }

        return view('{{$left}}/{{$right}}/index');
    }

    /**
     * 添加
     *
     * @author HSK
     * @date {{$date}}
     *
     * @param \support\Request $request
     *
     * @return \support\Response
     */
    public function add(\support\Request $request)
    {
        if (request()->isAjax()) {
            try {
                $fields = {{$fields}};
                $data   = array_filter(request()->post(), function ($k) use ($fields) {
                    return in_array($k, $fields);
                }, ARRAY_FILTER_USE_KEY);
                if ({{$table_hump}}Model::create($data)) {
                    return api([], 200, '操作成功');
                } else {
                    return api([], 400, '操作失败');
                }
            } catch (\Throwable $th) {
                \Hsk99\WebmanException\RunException::report($th);
                return api([], 400, '操作失败');
            }
        }

        return view('{{$left}}/{{$right}}/add');
    }

    /**
     * 编辑
     *
     * @author HSK
     * @date {{$date}}
     *
     * @param \support\Request $request
     *
     * @return \support\Response
     */
    public function edit(\support\Request $request)
    {
        if (request()->isAjax()) {
            try {
                $fields = {{$fields}};
                $data   = array_filter(request()->post(), function ($k) use ($fields) {
                    return in_array($k, $fields);
                }, ARRAY_FILTER_USE_KEY);
                if ({{$table_hump}}Model::update($data, ['id' => request()->input('id')])) {
                    return api([], 200, '操作成功');
                } else {
                    return api([], 400, '操作失败');
                }
            } catch (\Throwable $th) {
                \Hsk99\WebmanException\RunException::report($th);
                return api([], 400, '操作失败');
            }
        }

        return view('{{$left}}/{{$right}}/edit', [
            'model' => {{$table_hump}}Model::find(request()->input('id')),
        ]);
    }

    /**
     * 修改状态
     *
     * @author HSK
     * @date {{$date}}
     *
     * @param \support\Request $request
     *
     * @return \support\Response
     */
    public function status(\support\Request $request)
    {
        try {
            $fields = {{$fields}};
            $data   = array_filter(request()->post(), function ($k) use ($fields) {
                return in_array($k, $fields);
            }, ARRAY_FILTER_USE_KEY);
            if ({{$table_hump}}Model::update($data, ['id' => request()->input('id')])) {
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
     * @date {{$date}}
     *
     * @param \support\Request $request
     *
     * @return \support\Response
     */
    public function remove(\support\Request $request)
    {
        try {
            if ({{$table_hump}}Model::destroy(request()->input('id'))) {
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
     * 批量删除
     *
     * @author HSK
     * @date {{$date}}
     *
     * @param \support\Request $request
     *
     * @return \support\Response
     */
    public function batchRemove(\support\Request $request)
    {
        if (!is_array(request()->input('ids'))) return api([], 400, '数据不存在');

        try {
            if ({{$table_hump}}Model::destroy(request()->input('ids'))) {
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
     * 回收站
     *
     * @author HSK
     * @date {{$date}}
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
                        $result = {{$table_hump}}Model::onlyTrashed()->where('id', 'in', $ids)->select()->each(function ($item) {
                            $item->restore();
                        });
                    } else {
                        $result = {{$table_hump}}Model::destroy($ids, true);
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

            $where = [];
            {{$model_search}}

            $list = {{$table_hump}}Model::onlyTrashed()
                ->where($where)
                ->order('id', 'desc')
                ->paginate([
                    'list_rows' => $limit,
                    'page'      => $page,
                ]);
            return api($list);
        }

        return view('{{$left}}/{{$right}}/recycle');
    }
}
