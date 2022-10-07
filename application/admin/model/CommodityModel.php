<?php

namespace app\admin\model;

use think\Model;

class CommodityModel extends Model
{
    protected $pk = 'comm_id';

    public function clear()
    {
        //查询商品库存为空的情况
        return self::where('comm_reserve', 0)->field('comm_id');
    }
}