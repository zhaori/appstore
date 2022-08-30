<?php

namespace app\user\common;

use think\Controller;
use think\facade\Cookie;
use think\facade\Session;

class LoginCheck extends Controller
{
    public function check(): bool
    {
        $user_name = $this->request->param("user_name");
        $login_info = request()->param('token');
        if(!isset($login_info)||!isset($user_name)){
            return false;
        }
        if(Session::get("session_id")==$login_info && Cookie::get('session_id')==$login_info &&
            Cookie::get('user_name')==$user_name && Session::get('user_name'==$user_name)
        ){
            return true;
        }else{
            return false;
        }
    }
}