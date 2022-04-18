<?php

namespace app\common\model;

use support\hsk99\Model;
use think\model\concern\SoftDelete;

class AdminRole extends Model
{
    use SoftDelete;

    protected $table      = 'admin_role';
    protected $deleteTime = 'delete_time';
}
