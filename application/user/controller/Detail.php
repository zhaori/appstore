<?php

namespace app\user\controller;
use think\App;
use think\Controller;
use think\Db;
use think\Exception;
use think\exception\DbException;
use app\user\common\LoginCheck;

class Detail extends Controller
{
    public string $get_user;
    public bool $login_verify;

    public function __construct(App $app = null)
    {
        parent::__construct($app);
        $login = new LoginCheck();
        $this->login_verify = $login->check();
    }

    public function index()
    {
        $get_id = $this->request->param("id");
        $this->get_user = $this->request->get("user_name");
        $get_comm = Db::name('commodity')->where('comm_id', (int)$get_id)->select();

        try {
            $get_data = $get_comm[0];
        } catch (Exception $e) {
            $this->error("数据不存在","/user/user");
        }


        $class_name = DB::name("classify")->where("classify_id", (int)$get_data["classify_id"])->value("classify_name");

        return $this->fetch('user/view',[
            "title"         => "商品详情",
            "user"          => $this->get_user,
            "name"          => $get_data["comm_name"],
            "classify"      => $class_name,
            "unit_price"    => $get_data["comm_quantity"],
            "synopsis"      => $get_data["column_synopsis"],
            "reserve"       => $get_data["comm_reserve"]
        ]);

    }

    public function submitCart()
    {
        $data = $this->request->post();
        if ($this->login_verify) {
            $user_id = Db::name('user')->where('user_name', (string)$this->request->param('user_name'))->value("user_id");
            try {
                $select_cart_data = Db::name("shopcart")->where("user_id", (int)$user_id)->select();
            } catch (DbException $e) {
                $this->error($e);
            }
            // 判断同一个用户是否重复添加购物车
            $select_cart_data_size = count($select_cart_data);
            for ($a = 0; $a < $select_cart_data_size; $a++) {
                array_shift($select_cart_data[$a]);
                if ($user_id == $select_cart_data[$a]["user_id"]) {
                    if(!empty(array_diff_assoc($data, $select_cart_data[$a]))){
                        return ["error_info"=>"重复添加到购物车"];
                    }
                }
            }
            return Db::name("shopcart")->insertGetId([
                "user_id"       => $user_id,
                "goods_name"    => $data["name"],
                "quantity"      => $data["buy_num"],
                "unit_price"    => $data["buy_price"],
                "total"         => $data["total_price"]
            ]);
        }else{
            $this->error('未提交信息，错误页面', "/user/Detail");
        }
    }

    public function shoppCart(){
        $user_name = $this->request->param("user_name");
        $login_info = request()->param('token');
        if (!isset($login_info) || !isset($user_name)) {
            $this->error('登录信息不存在', '/user/user/login');
        }
        if ($this->login_verify) {
            try {
                $get_user = Db::name('user')->where("user_name", $user_name)->select()[0];
                $get_shopcart_data = Db::name('shopcart')->where("user_id", (int)$get_user["user_id"])->select();
            } catch (DbException $e) {
                $this->error($e);
            }
            return $this->fetch("user/shopcart", [
                "title" => '购物车',
                "data" => $get_shopcart_data,
                "user_name" => $user_name
            ]);
        }else{
            $this->error("未登录,返回登录页", "/user/user/login");
        }

    }

    public function test(){

    }
}