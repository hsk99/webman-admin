<?php

namespace app\api\controller\{{$left}};

use app\common\model\{{$table_hump}} as {{$table_hump}}Model;

/**
 * {{$ename}}API
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
    public function list(\support\Request $request)
    {
        try {
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
        } catch (\Throwable $th) {
            \Hsk99\WebmanException\RunException::report($th);
            return api([], 400, 'error');
        }
    }

    /**
     * 详情
     *
     * @author HSK
     * @date {{$date}}
     *
     * @param \support\Request $request
     *
     * @return \support\Response
     */
    public function info(\support\Request $request)
    {
        try {
            $id = request()->input('id');
            if (empty($id)) return api([], 400, '参数错误');

            $info = {{$table_hump}}Model::find($id);

            return api($info);
        } catch (\Throwable $th) {
            \Hsk99\WebmanException\RunException::report($th);
            return api([], 400, 'error');
        }
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
            $id = request()->input('id');
            if (empty($id)) return api([], 400, '参数错误');

            if ({{$table_hump}}Model::destroy($id)) {
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
        try {
            $ids = request()->input('ids');
            if (!is_array($ids)) return api([], 400, '参数错误');

            if ({{$table_hump}}Model::destroy($ids)) {
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
        try {
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
        } catch (\Throwable $th) {
            \Hsk99\WebmanException\RunException::report($th);
            return api([], 400, 'error');
        }
    }

    /**
     * 回收站操作
     *
     * @author HSK
     * @date {{$date}}
     *
     * @param \support\Request $request
     *
     * @return \support\Response
     */
    public function recycleOperate(\support\Request $request)
    {
        try {
            $ids = request()->input('ids');
            if (!is_array($ids)) return api([], 400, '参数错误');

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
}
