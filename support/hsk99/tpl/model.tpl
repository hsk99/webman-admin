<?php

namespace app\common\model;

use support\hsk99\Model;
use think\model\concern\SoftDelete;

class {{$table_hump}} extends Model
{
    use SoftDelete;

    protected $table = '{{$table}}';
    {{$model_del}}
}
