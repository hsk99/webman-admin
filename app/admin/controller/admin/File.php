<?php

namespace app\admin\controller\admin;

use app\common\model\AdminFile as AdminFileModel;

class File
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

            $where = [];


            $list = AdminFileModel::where($where)
                ->order('id', 'desc')
                ->paginate([
                    'list_rows' => $limit,
                    'page'      => $page,
                ]);
            return api($list);
        }

        return view('admin/file/index');
    }

    /**
     * 添加单文件
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
        return view('admin/file/add');
    }

    /**
     * 添加多文件
     *
     * @author HSK
     * @date 2022-04-10 01:12:23
     *
     * @param \support\Request $request
     *
     * @return \support\Response
     */
    public function adds(\support\Request $request)
    {
        return view('admin/file/adds');
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
        AdminFileModel::startTrans();

        try {
            $fileInfo = AdminFileModel::field('href,type')->find(request()->input('id'));

            if (AdminFileModel::destroy(request()->input('id'))) {
                switch ($fileInfo['type']) {
                    case 1:
                        $path = public_path() . $fileInfo['href'];
                        if (file_exists($path)) unlink($path);
                        break;
                    case 2:
                        \support\hsk99\util\Oss::delete($fileInfo['href']);
                        break;
                    case 3:
                        \support\hsk99\util\Qiniu::delete($fileInfo['href']);
                        break;
                }

                AdminFileModel::commit();
                return api([], 200, '操作成功');
            } else {
                AdminFileModel::rollback();
                return api([], 400, '操作失败');
            }
        } catch (\Throwable $th) {
            \Hsk99\WebmanException\RunException::report($th);
            AdminFileModel::rollback();
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

        AdminFileModel::startTrans();

        try {
            $fileList = AdminFileModel::field('href,type')->where('id', 'in', request()->input('ids'))->select();

            if (AdminFileModel::destroy(request()->input('ids'))) {
                foreach ($fileList as $item) {
                    switch ($item['type']) {
                        case 1:
                            $path = public_path() . $item['href'];
                            if (file_exists($path)) unlink($path);
                            break;
                        case 2:
                            \support\hsk99\util\Oss::delete($item['href']);
                            break;
                        case 3:
                            \support\hsk99\util\Qiniu::delete($item['href']);
                            break;
                    }
                }

                AdminFileModel::commit();
                return api([], 200, '操作成功');
            } else {
                AdminFileModel::rollback();
                return api([], 400, '操作失败');
            }
        } catch (\Throwable $th) {
            \Hsk99\WebmanException\RunException::report($th);
            AdminFileModel::rollback();
            return api([], 400, '操作失败');
        }
    }
}
