<?php

namespace app\index\controller;

class Index
{
    /**
     * 首页
     *
     * @author HSK
     * @date 2022-04-13 14:14:37
     *
     * @param \support\Request $request
     *
     * @return \support\Response
     */
    public function index(\support\Request $request)
    {
        return view('index/index');
    }
}
