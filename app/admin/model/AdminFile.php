<?php

namespace app\admin\model;

use support\hsk99\Model;
use think\model\concern\SoftDelete;

class AdminFile extends Model
{
    use SoftDelete;

    protected $table      = 'admin_file';
    protected $deleteTime = false;
}
