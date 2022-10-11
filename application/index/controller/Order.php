<?php

namespace app\index\controller;

use Redis;
use think\App;
use think\Controller;
use think\Db;
use think\Exception;
use think\facade\Session;
use app\common\LoginCheck;
use app\common\ReserveCheck;
use app\common\PayKeyCheck;
use think\facade\Validate;

class Order extends Controller
{
    public $user_name;
    public $redis;
    public $total;

    public function __construct(App $app = null)
    {
        parent::__construct($app);
        if (!(new LoginCheck())->check()) {
            $this->error('请登录后操作', '/index/index/login');
        }else{
            $this->redis = new Redis();
            $this->redis->connect(config('queue.host'), config('queue.port'));
            $this->redis->select(config('queue.db'));
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

    public function deleteTempOrder(): array
    {
        $del_state = request()->post('time_state');
        // 删除临时订单表数据，但只是标记为0，删除订单将在某个时间段统一删除或者做其它操作
        if (isset($del_state) && $del_state) {
            $temp_id = request()->post('temp_id');
            Db::name("tempcart")->where([
                'temp_id'   => (int)$temp_id,
                'user_id'   => (int)Session::get('user_id')
            ])->update(['state'=>-1]);
            return ["state" => true, 'msg' => '删除成功'];
        }else{
            return ["state" => false, 'msg' => '删除失败'];
        }
    }

    public function buyorder()
    {
        //生成购物订单
        $temp_id = request()->get('id');
        $get_buy = Db::name('tempcart')->where([
            'temp_id'       => (int)$temp_id,
            'state'         => 1
        ])->lock(true)->find();
        if (is_null($get_buy)) {
            $this->error('无效的订单参数');
        }
        $begin_time = $get_buy['create_time'];

        return $this->fetch("index/buy", [
            "title"         => '订单',
            'temp_id'       => $temp_id,
            'buy_name'      => $get_buy['goods_name'],
            'buy_num'       => $get_buy['quantity'],
            "buy_price"     => $get_buy['unit_price'],
            "total_price"   => $get_buy['total'],
            "end_time"      => date('Y-m-d H:i:s', strtotime("$begin_time + 30 minute"))
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
            'user_id'       => (int)$user_id,
            'goods_name'    => $buy_name,
            'state'         => 1
        ])->find();

        if (!empty($repeat_data)) {
            return json(['state' => false, 'msg' => '不要重复提交']);
        }
        $buy_temp_id = Db::name('tempcart')->insertGetId([
            'user_id'       => $user_id,
            "goods_name"    => $buy_name,
            "quantity"      => $buy_num,
            "unit_price"    => $buy_price,
            "total"         => $total_price,
            "state"         => 1
        ]);
        return json(['state' => true, 'msg' => $buy_temp_id]);
    }

    public function isJob($data)
        //直接从购物车表里读取数据,然后排队处理
    {
        $user_id = (int)Session::get("user_id");
        $unrepeat_order_id = array(); //不重复订单id数组
        $read_queue = $this->redis->keys("task_queue");
     
        if (!empty($read_queue)){
            //判断查询是否为空的情况，当不为空的时候执行
            foreach ($read_queue as $key=>$value) {
                //当请求的订单id不存在任务队列中的时候，认定为新订单
                if (!in_array($value, $data)){
                    array_push($unrepeat_order_id, $value);
                }
            }
        }else {
            $unrepeat_order_id = $data;
        }

        if (!empty($unrepeat_order_id)){
            foreach ($unrepeat_order_id as $key=>$value){
                //这里的$value就是与购物车表的cart_id
                $cart_data = Db::name("shopcart")->where("cart_id", (int)$value)->find();
                $total = Db::name('commodity')->where('comm_reserve', $cart_data["quantity"])->find();
                
                if($cart_data['quantity'] > $total["comm_reserve"]){
                    // 如果购买数量大于了库存，则存放至等待队列中,否则，进入处理队列
                    $this->redis->lPush('await_queue',(int)$value);
                }elseif($cart_data['quantity'] <= $total["comm_reserve"]){
                    $this->redis->lPush("task_queue", (int)$value);
                }else{
                    $this->error('库存不足');
                }
            }
        }else {
            $this->error('异常的提交');
        }
        
        return json(['state'=> true, 'data' =>$user_id]);
    }

    public function isOrder($page=1)
    {
        //购物车提交后，显示订单
        $user = $this->request->param("user_name");
        if(!empty($user) && Session::get("user_name")==$user){
            $user_id =  Db::name("user")->where("user_name", $user)->value("user_id");
            $get_cart = Db::name("shopcart")->where("user_id", $user_id)->paginate(10, false, ["page"=>$page]);
            $max_page = $get_cart->lastPage();
            if($page>$max_page){
                return ["error_info" => "超出最大页"];
            }
            $rule = ['page'=>'integer'];
            $msg  = ['page.integer'=>'页码必须是数字,整数'];
            $validate = Validate::make($rule,$msg);
            $result = $validate->check(['page'=>$page]);

            if(!$result) {
                $this->error($validate->getError());
            }
            $n = 0;
            foreach ($get_cart as $key=>$value){
                $n+=$value["total"];
            }
            return $this->fetch("index/order",[
                "title"         => "订单页",
                "maxPage"       => $max_page,
                "data"          => $get_cart,
                "total"         => $n,
                "user_name"     => $user
            ]);
        }else{
            $this->error("未登录,返回登录页", "/index/index/login");
        }
    }

    public function pay(): array
    {     
        if ((new LoginCheck())->check()) {
            $user_id = Session::get('user_id');
            $get_pwd = request()->post('passwd');
            $get_num = request()->post('num');
            $get_pay_sum = request()->post('pay_sum');
            //验证支付密码
            $get_temp = Db::name("tempcart");
            if ((new PayKeyCheck())->check($user_id, $get_pwd)){
                $get_id =  request()->post('submit_order_id');
                $get_good_name = $get_temp->where([
                    "user_id"   => (int)$user_id,
                    "temp_id"   => (int)$get_id,
                    "state"     => 1
                ])->value('goods_name');
                $this->redis->lPush('task_queue',json_encode([
                    "user_id"       => $user_id,
                    "temp_id"       => $get_id,
                    "get_goods_name"    => $get_good_name,
                    "get_num"       => $get_num,
                    "get_pay_sum"   => $get_pay_sum
                ]));
                self::queue();
            }else{
                return ["state"=>false, "msg"=>"支付验证失败"];
            }
        }
        return ['state'=>true, "msg"=>"等待支付成功"];
    }

    public function await_queue(){
        // 处理等待队列
        return $this->redis->rpop("await_queue");
    }

    public function task_queue(){
        //处理订单队列
        return $this->redis->rpop("task_queue");
    }

    public function queue()
    {
        $queue_data = json_decode(self::task_queue(), true);
        var_dump($queue_data['get_goods_name']);
        var_dump($queue_data['get_num']);
        if (!is_null($queue_data)) {
            if ((new ReserveCheck())->check($queue_data['get_goods_name'], $queue_data['get_num'])) {
                $get_temp = Db::name("tempcart");
                $get_temp->where([
                    'user_id'       => (int)$queue_data['user_id'],
                    'goods_name'    => $queue_data['get_goods_name'],
                    'state'         => 1
                ])->update(['total'=>$queue_data['get_pay_sum']]);

                Db::name("commodity")->where("comm_name", $queue_data['get_goods_name'])
                    ->setDec('comm_reserve', (int)$queue_data['get_num']);

                $pay_bank = Db::name('bank')->where('user_id', (int)$queue_data['user_id']);
                $now_money = $pay_bank->value('money_num');
                if ($now_money >= $queue_data['get_pay_sum']) {
                    $pay_bank->setField("money_num", $now_money-$queue_data['get_pay_sum']);

                }else{
                    return ['state'=>false, 'msg'=>'银行资金不足'];
                }
            }else{
                $this->redis->lPush('await_queue', json_encode($queue_data));
                return ["state"=>false, "msg"=>"支付失败，库存不足，订单位于等候中，可在我的订单里找到处理结果"];
            }

        }
        return true;
    }

    public function test()
    {
        // $this->redis->lPush('test', \json_encode(['id'=>1, 'state'=>\true]));
        self::queue();
    }
}

