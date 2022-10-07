<?php

namespace app\admin\controller;

use app\common\Hash;
use app\admin\model\AdminModel;
use think\App;
use think\Exception;
use think\facade\Cookie;
use think\facade\Session;
use think\Controller;

class Admin extends Controller
{
    public $admin;
    public $hash;
    public function __construct(App $app = null)
    {
        parent::__construct($app);
        $this->admin = new AdminModel();
        $this->hash = new Hash();
    }

    public function index(){
//        if(!Session::has('admin_name')){
//            $this->error('请登陆！','/admin/admin/login','',2);
//        }
        return $this->fetch('index',['title'=>'Windows AppStore -开源软件分享应用商城']);
    }

    public function login(){
        return $this->fetch('login',["title"=>"后台登录页"]);
    }

    public function add()
    {
        $admin_name = request()->post('admin_name');
        $admin_passwd = request()->post('admin_passwd');
        $validate_data = [
            'user_name' => $admin_name,
            'phone_number' => $admin_passwd
        ];

        $validate = $this->validate($validate_data, '\app\common\validate\info');
        if ($validate !== true) {
            $this->error($validate);
        }

        if ($this->admin->repeat($admin_name)) {
            return ['state'=>false, 'msg'=>'不允许创建存在相同用户名'];
        }

        $hash_data = $this->hash->compute($admin_passwd);
        try {
            $this->admin->insert([
                'admin_name' => $admin_name,
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
        $admin_name = request()->post('user_name');
        $admin_passwd = request()->post('passwd');
        if (isset($admin_name) && isset($admin_passwd)) {
            $get_data = $this->admin->where('admin_name',trim($admin_name))->all();
            if (!empty($get_data['admin_name'])) {
                if ($this->hash->verify($get_data['admin_passwd'], $admin_passwd, $get_data['salt'])) {
                    Session::set('admin_name', $admin_name, 'think');
                    Cookie::set('admin_name', base64_encode(md5($admin_name)), 86400);
                }else {
                    $this->error('登录验证失败,如需要找回密码请联系超级管理员');
                }
            }else {
                return ['state'=>false, 'msg'=>'无此管理员'];
            }
        }else {
            $this->error('非法数据传输');
        }
    }
}