<?php

namespace app\index\controller;

use app\common\Hash;
use app\common\LoginCheck;
use think\App;
use think\Controller;
use think\Db;
use think\Exception;
use think\facade\Cookie;
use think\facade\Session;

class Index extends Controller
{
    private $hash;
    private $user;

    public function __construct(App $app = null)
    {
        parent::__construct($app);
        $this->hash = new Hash();
    }

    public function index()
    {
        $v = new LoginCheck();
        if ($v->check()) {
            if (password_verify(Session::get('user_name'), base64_decode(Cookie::get('token')))) {
                $this->user = Session::get('user_name');
            } elseif (password_verify(Session::get('user_name'), base64_decode(request()->param('token')))) {
                $this->user = Session::get('user_name');
            } else {
                $this->user = null;
            }
        }
        return $this->fetch("index", [
            "title" => "电子书商城",
            "logo"  => '/static/store.ico',
            "user"  => $this->user,
            'class' => Db::name('classify')->column('classify_name'),
            'data'  => Db::name('commodity')->column('comm_id, comm_name,comm_quantity, photo, column_synopsis'),
        ]);
    }

    public function login()
    {
        return $this->fetch('login', ["title" => "登录页"]);
    }

    public function register()
    {
        return $this->fetch('register', ["title" => "注册"]);
    }

    public function quit()
    {
        $get_data = request()->post('value');
        if (isset($get_data )){
            Session::delete('user_name');
            Session::delete('user_id');
            Session::delete('token');
            Cookie::delete('user_name');
            Cookie::delete('token');
            return ['state'=>true, 'msg'=>'退出成功'];
        }
    }

    public function addUser()
    {
        $user = trim(request()->post('user_name'));
        $pwd = request()->post('password');
        $email = trim(request()->post('email'));
        $phone_number = request()->post('phone_number');

        $validate_data = [
            'user_name' => $user,
            'email' => $email,
            'phone_number' => $phone_number
        ];

        $validate = $this->validate($validate_data, '\app\common\validate\info');
        if ($validate !== true) {
            $this->error($validate);
        }

        if (Db::name('user')->where('user_name', $user)->value('user_name')) {
            $this->error('不允许创建存在相同用户名');
        }

        $hash_data = $this->hash->compute($pwd);
        try {
            Db::name('user')->insert([
                'user_name' => $user,
                'email' => $email,
                'phone_number' => $phone_number,
                'passwd' => $hash_data['passwd'],
                'salt' => $hash_data['salt']
            ]);
            $this->success('注册成功，自动返回登录界面', '/index/index/login');
        } catch (Exception $e) {
            $this->error($e);
        }
    }

    public function verifyLogin()
    {
        $user = trim(request()->post('user_name'));
        $pwd = request()->post('password');
        $login_state = request()->post("check_state");
        if (isset($user)) {
            $search_data = Db::name('user')->where('user_name', $user)->find();
            if ($this->hash->verify($search_data['passwd'], $pwd, $search_data['salt'])) {
                $uuid = base64_encode(password_hash($user, PASSWORD_BCRYPT));
                Session::set('user_name', $user, 'think');
                Session::set('user_id', $search_data['user_id']);
                Session::set('token', $uuid, 'think');
                if ($login_state) {
                    //login_state为true，即代表永久存储，但事实上设置一个月有效期
                    Session::set('state', true, 'think');
                    Cookie::set('user_name', base64_encode(md5($user)), 86400 * 30);
                    Cookie::set('token', $uuid, 86400 * 30);
                } else {
                    Session::set('state', false, 'think');
                    Cookie::set('user_name', base64_encode(md5($user)), 86400);
                    Cookie::set('token', $uuid, 86400);
                }
                $this->success(true, '/index/index', $uuid);
            }else {
                $this->error("密码错误");
            }
        }
    }

    public function test()
    {
        var_dump(Db::name('commodity')->column('comm_name, photo, column_synopsis'));
    }
}
