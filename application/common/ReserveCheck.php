<?php
# 库存验证
namespace app\common;

use think\Db;
use think\Exception;

class ReserveCheck
{
    public function check($goods_name, $buy_num): bool
    {
        try{
            $reserve = Db::name('commodity')->where('comm_name', $goods_name)->value('comm_reserve');
            if ((int)$buy_num > (int)$reserve) {
                return false;
            } else {
                return true;
            }
        }catch(Exception $e){
           \trace($e, 'error');
        }
    }
}