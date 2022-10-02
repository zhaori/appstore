<?php
namespace app\security\controller;

use think\Controller;
use app\common\Hash;
use think\Db;

class Index extends Controller
{
    public function key()
    {
        $user_name = request()->param('user_name');
        $passwd = request()->param('passwd');
        $crypt_info = request()->param('uuid');

        $get_user = Db::name('user');
        $user_info = $get_user->where('user_name', trim($user_name))->find();
        $user_passwd = $user_info["passwd"];
        $salt = $user_info['salt'];

        if ((new Hash())->verify($user_passwd, $passwd, $salt)) {
            $crypt_str = mt_rand(0, 10);
            $get_user->where('user_id', (int)$user_info['user_id'])
                ->setField([
                    'keys'=>$crypt_str,
                    'uuid'=>$crypt_info
                ]);
            return $crypt_str;

        }else{
            return json(['state'=>false, 'msg'=>'登录验证失败']);
        }



//        exec("openssl genrsa -out ../runtime/pemkey/private.pem");
    }
}
