<?php

namespace app\admin\model;

use think\Model;

class AdminModel extends Model
{
    protected $pk = 'admin_id';

    public function repeat($admin_name): bool
    {
        $get_admin_info = self::where('admin_name', trim($admin_name))->value('admin_name');
        if (!empty($get_admin_info)) {
            return false;
        }else {
            return true;
        }
    }
}