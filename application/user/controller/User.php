<?php

namespace app\user\controller;

use think\Controller;
use think\Db;
use think\Exception;
use think\facade\Cookie;
use think\facade\Session;

class User extends Controller
{
    public function index()
    {
        $login_info = request()->param('token');
        if(isset($login_info) && Session::get("session_id")==$login_info || Cookie::get('session_id')){
            return $this->fetch("index", [
                "title"         => "正品软件代理商城",
                "logo"          => '/static/store.ico',
                "user"          => Cookie::get("user_name")
            ]);
        }else{
            $this->error("用户不存在请重新登录", "/user/user/login");
        }
    }

    public function login()
    {
        return $this->fetch('login', ["title" => "登录页"]);
    }

    public function register()
    {
        return $this->fetch('register', ["title" => "注册"]);
    }

    public function addUser()
    {
        $user = request()->post('user_name');
        $pwd = request()->post('password');
        $email = request()->post('email');
        $phone_number = request()->post('phone_number');

        $validate_data = [
            'user_name' => $user,
            'email' => $email,
            'phone_number' => $phone_number
        ];

        $validate = $this->validate($validate_data, '\app\user\common\validate\info');
        if ($validate !== true) {
            $this->error($validate);
        }

        if ($user == Db::name('user')->where('user_name', $user)->value('user_name')) {
            $this->error('不允许创建存在相同用户名');
        }

        try {
            $get_id = Db::name('user')->insertGetId(['user_name' => $user, 'email' => $email, 'phone_number' => $phone_number]);
            Db::name('password')->insert(['user_id' => $get_id, 'password' => $pwd]);
            $this->success('注册成功，自动返回登录界面', 'user/user/login');
        } catch (Exception $e) {
            $this->error($e);
        }
    }

    public function verifyLogin()
    {
        $user = request()->post('user_name');
        trim($user);
        $pwd = request()->post('password');
        $user_id = Db::name('user')->where('user_name', $user)->value('user_id');
        $login_state = request()->post("check_state");

        if (!is_null($user_id)) {
            $search_pwd = Db::name('password')->where('user_id', $user_id)->value('password');
            if ($pwd == $search_pwd) {
                Session::set('name', $user, 'think');
                Session::set('session_id', session_id(), 'think');
                if($login_state){
                    //login_state为true，即代表永久存储，但事实上设置一个月有效期
                    Session::set('state', true, 'think');
                    return ["state"=>"true", "session_id" =>session_id(), "user_name"=>hash('md5',$user),"expire"=>86400*30];
                }else{
                    Session::set('state', false, 'think');
                    return ["state"=>false, "session_id" =>session_id(),'user_name'=>$user];
                }
            }else {
               $this->error("密码错误");
            }
        }else{
            $this->error('用户不存在');
        }
    }

    public function test()
    {
        echo hash('md5',Cookie::get('user_name'));
        var_dump(Cookie::get("session_id"));
//        $this->success('注册成功，自动返回登录界面', 'user/user/login');
    }

}
