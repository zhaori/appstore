<?php

namespace app\common;

use think\facade\Cookie;
use think\facade\Session;

class LoginCheck
{
    public function check(): bool
    {
        $token = request()->param('token');
        $user = base64_decode(Cookie::get('user_name'));
        if (!empty(Cookie::get('token')) && !empty($user)) {
            //这里的条件需要考虑到客户端禁用cookie的情况，所以只要满足与session一致即可
            if ($token == Session::get('token') || Session::get('token') == Cookie::get('token') &&
                $user == md5(Session::get('user_name'))) {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }
}