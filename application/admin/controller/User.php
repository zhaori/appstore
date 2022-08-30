<?php

namespace app\admin\controller;

use think\facade\Session;
use think\Controller;

class User extends Controller
{
    public function index(){
//        if(!Session::has('admin_name')){
//            $this->error('请登陆！','/admin/user/login','',2);
//        }
        return $this->fetch('index',['title'=>'Windows AppStore -开源软件分享应用商城']);
    }

    public function login(){
        return $this->fetch('login',["title"=>"后台登录页"]);
    }
}