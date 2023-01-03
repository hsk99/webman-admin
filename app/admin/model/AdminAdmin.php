<?php

namespace app\admin\model;

use support\hsk99\Model;
use think\model\concern\SoftDelete;

class AdminAdmin extends Model
{
    use SoftDelete;

    protected $table      = 'admin_admin';
    protected $deleteTime = 'delete_time';
}
