<?php
namespace app\security\controller;

use think\Controller;
use app\common\Hash;
use think\Db;

class Index extends Controller
{
    public function login()
    {
        $user = trim(request()->post('user_name'));
        $pwd = request()->post('password');
        if (isset($user)) {
            $search_data = Db::name('user')->where('user_name', $user)->find();
            if ((new Hash())->verify($search_data['passwd'], $pwd, $search_data['salt'])) {
                return json(['state'=>true, 'msg'=>'登录成功']);
            }else {
                return json(['state'=>false, 'msg'=>'登录失败']);
            }
        }else {
            return json(['state'=>false, 'msg'=>'数据为空']);
        }
    }

    public function bind()
    {
        $user_name = request()->param('user_name');
        $passwd = request()->param('passwd');
        $crypt_info = request()->param('uuid');

        $user_info = Db::name('user')->where('user_name', trim($user_name))->find();
        if (empty($user_info['keys']) && empty($user_info['uuid'])) {
            $user_passwd = $user_info["passwd"];
            $salt = $user_info['salt'];

            if ((new Hash())->verify($user_passwd, (string)$passwd, $salt)) {
                Db::name('user')->where('user_id', (int)$user_info['user_id'])
                    ->setField([
                        'keys'  => mt_rand(),
                        'uuid'  => $crypt_info
                    ]);
                return json(['state'=>true, 'msg'=>'绑定成功']);

            }else{
                return json(['state'=>false, 'msg'=>'登录验证失败']);
            }
        }else {
            return json(['state'=>false, 'msg'=>'已绑定']);
        }
    }

    public function key()
    {
        $user_name = request()->post('user_name');
        $instruct = request()->post('instruct');
        if (isset($instruct)) {
            $crypt_str = mt_rand();
            Db::name('user')->where('user_name', trim($user_name))
                ->update([
                    'keys'  => $crypt_str,
                ]);
            return json(["state"=>true, 'data'=>$crypt_str]);
        }else {
            return json(["state"=>false, 'msg'=>'数据异常']);
        }
    }
}
