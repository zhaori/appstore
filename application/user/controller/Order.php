<?php

namespace app\user\controller;

use Redis;
use think\App;
use think\Controller;
use think\Db;
use think\Exception;
use think\facade\Session;
use app\common\LoginCheck;
use app\common\ReserveCheck;

class Order extends Controller
{
    public $user_name;
    public $redis;
    public $total;

    public function __construct(App $app = null)
    {
        parent::__construct($app);
        $this->redis = new Redis();
        $this->redis->connect('s5.z100.vip', 39166);
        $this->redis->select(2);
        if (!(new LoginCheck())->check()) {
            $this->error('请登录后操作', '/user/user/login');
        }
    }

    public function index()
    {
        //提交订单支付
        $data = request()->post('data');
        if (!empty($data)) {
            self::isJob($data);
        }
    }

    public function deleteTempOrder()
    {
        $temp_id = request()->post('temp_id');
        $del_state = request()->post('time_state');
        if (isset($del_state) && $del_state == true) {
            try {
                Db::name("tempcart")->where('temp_id', (int)$temp_id)->delete();
            } catch (Exception $e) {
                var_dump($e);
            }
        }
        return ["state" => true, 'msg' => '删除成功'];
    }

    public function buyorder()
    {
        $temp_id = request()->get('id');
        $get_buy = Db::name('tempcart')->where('temp_id', (int)$temp_id)->find();
        if (!(new ReserveCheck())->check($get_buy['goods_name'], $get_buy['quantity'])) {
            $this->error('异常的数据提交');
        }
        $begin_time = $get_buy['create_time'];

        return $this->fetch("/user/buy", [
            "title" => '订单',
            'temp_id' => $temp_id,
            'buy_name' => $get_buy['goods_name'],
            'buy_num' => $get_buy['quantity'],
            "buy_price" => $get_buy['unit_price'],
            "total_price" => $get_buy['total'],
            "end_time" => date('Y-m-d H:i:s', strtotime("$begin_time + 30 minute"))
        ]);

    }

    public function buy()
    {
        //直接购买
        $buy_name = request()->post('buy_name');
        $buy_num = request()->post('buy_num');
        $buy_price = request()->post('buy_price');
        $total_price = request()->post('total_price');
        $user_id = Db::name('user')->where('user_name', Session::get('user_name'))->value('user_id');
        $repeat_data = Db::name('tempcart')->where([
            'user_id' => (int)$user_id,
            'goods_name' => $buy_name
        ])->find();

        if (!empty($repeat_data)) {
            return json(['state' => false, 'msg' => '不要重复提交']);
        }
        $buy_temp_id = Db::name('tempcart')->insertGetId([
            'user_id' => $user_id,
            "goods_name" => $buy_name,
            "quantity" => $buy_num,
            "unit_price" => $buy_price,
            "total" => $total_price
        ]);
        return json(['state' => true, 'msg' => $buy_temp_id]);
    }

    public function isJob($data)
        //直接从购物车表里读取数据,然后排队处理
    {
        foreach($data as $key=>$value){
            //这里的$value就是与购物车表的cart_id
            $cart_data = Db::name("shopcart")->where("cart_id", (int)$value)->find();
            $total = Db::name('commodity')->where('comm_reserve', $cart_data["quantity"])->find();

            if($cart_data['quantity'] > $total["comm_reserve"]){
                // 如果购买数量大于了库存，则存放至等待队列中,否则，进入处理队列
                $this->redis->lPush('await_queue',$value);
            }elseif($cart_data['quantity'] <= $total["comm_reserve"]){
                $this->redis->lpush("task_queue", $value);
            }else{
                $this->error('库存不足');
            }
        }
    }

//    public function orderService($queue_data){
//        if(count($queue_data) > 0 || !empty($queue_data)){
//            $this->task_redis->lpush("task_queue", $queue_data);
//        }
//    }
//
//    public function awaitTask($queue_data){
//        $await_redis = $this->await_redis->get("await_queue");
//        //当redis缓存中存在值就纳入订单处理队列
//        if(!is_null($await_redis)){
//            $this->isJob($queue_data);
//        }else{
//            // 如果库存依然不足，就从队列头部删除，插入队列尾部
//            $await_data = array_shift($this->await_array);
//            $this->await_array[] = $await_data;
//        }
//    }


//    public function isOrder($page=1){
//        //购物车提交后，显示订单
//        $user = $this->request->param("user_name");
//        if(!empty($user) && Session::get("user_name")==$user){
//            $user_id =  Db::name("user")->where("user_name", $user)->value("user_id");
//            $get_cart = Db::name("shopcart")->where("user_id", $user_id)->paginate(10, false, ["page"=>$page]);
//            $max_page = $get_cart->lastPage();
//            if($page>$max_page){
//                return ["error_info" => "超出最大页"];
//            }
//            $rule = ['page'=>'integer'];
//            $msg  = ['page.integer'=>'页码必须是数字,整数'];
//            $validate = Validate::make($rule,$msg);
//            $result = $validate->check(['page'=>$page]);
//
//            if(!$result) {
//                $this->error($validate->getError());
//            }
//            $n = 0;
//            foreach ($get_cart as $key=>$value){
//                $n+=$value["total"];
//            }
//            return $this->fetch("user/order",[
//                "title"         => "订单页",
//                "maxPage"       => $max_page,
//                "data"          => $get_cart,
//                "total"         => $n,
//                "user_name"     => $user
//            ]);
//        }else{
//            $this->error("未登录,返回登录页", "/user/user/login");
//        }
//    }


    public function test()
    {
//        $get_buy = Db::name('shopcart_temp')->where('temp_id', 21)->find();
//        var_dump($get_buy);
        var_dump(Db::name('commodity')->where('comm_name', "chrome浏览器")->value('comm_reserve'));

    }
}

