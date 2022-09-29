<?php
// 支付验证
namespace app\common;

use think\Db;

class PayKeyCheck
{
    public function check($user_id, $key) :bool
    {
        /**
         * @param mixed $user_id 传入用户id
         * @param mixed $key 传入用户支付密码
         * @return mixed 返回bool，密码正确为true，反之为false
         */
        $get_pay_key = Db::name('user')->where('user_id', (int)$user_id)->value('keys');
        if ($key == $get_pay_key){
            return true;
        }else{
            return false;
        }
    }
}