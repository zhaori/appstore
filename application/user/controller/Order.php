<?php

namespace app\user\controller;

use think\App;
use think\cache\driver\Redis;
use think\facade\Session;
use think\validate;
use think\Controller;
use think\Db;

Class Order extends Controller{
    protected $redis_options = [
        'host' => 's5.z100.vip',
        'port' => 39166,
        'password' => '',
        'select' => 2,
        'timeout' => 3600,
        'expire' => 0,
        'persistent' => false,
        'prefix' => '',
        'serialize' => true,
    ];
    private $queueMaxLength = 2;  //队列最大长度
    public $user_name;
    public $await_redis;
    public $task_redis;
    public $total;
    public $task_array = array();         //正在处理队列
    public $await_array = array();        //等待处理队列
    protected $task_option;


    public function __construct(App $app = null)
    {
        parent::__construct($app);
        $this->await_redis = new Redis($this->redis_options);
        $this->task_option = $this->redis_options;
        $this->task_option["select"] = 3;
        $this->task_redis = new Redis($this->task_option);
    }

    public function index(){
        //提交订单支付
        $data = $this->request->post();
        if ((new \app\user\common\LoginCheck)->check()) {
            $this->isJob($data);
        } else {
            $this->error("未登录,返回登录页", "/user/user/login");
        }
    }

    public function isJob($data)
    {
        if (!empty($data) && is_array($data)) {
            if (!is_null($this->task_redis->get("task_data"))) {
                //当库存不为空的时候才开始查询具体某件商品
                $this->total = Db::name("commodity")->where("comm_reserve", (int)$data["buy_num"])->value("comm_reserve");
            } else {
                // 如果库存为0直接排入等待处理队列中
                $this->await_array[] = $data;
            }

            if($data["buy_num"] > $this->total){
                // 如果购买数量大于了库存，则存放至等待队列中,否则，进入处理队列
                $this->await_array[] = $data;
                $this->awaitTask($this->await_array);
            }else{
                $this->task_array[] = $data;
                $this->orderService($this->task_array);
            }
        }
    }

    public function orderService($queue_data){
        if(count($queue_data) > 0){
            //当任务存在时，获取队列第一个数据处理，并从队列中删除
            $task_data = array_shift($this->task_array);
            var_dump($task_data);
        }
    }

    public function awaitTask($queue_data){
        $await_redis = $this->await_redis->get("await_data");
        //当redis缓存中存在值就纳入订单处理队列
        if(!is_null($await_redis)){
            $this->isJob($queue_data);
        }else{
            // 如果库存依然不足，就从队列头部删除，插入队列尾部
            $await_data = array_shift($this->await_array);
            $this->await_array[] = $await_data;
        }
    }


    public function isOrder($page=1){
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
            return $this->fetch("user/order",[
                "title"         => "订单页",
                "maxPage"       => $max_page,
                "data"          => $get_cart,
                "total"         => $n,
                "user_name"     => $user
            ]);
        }else{
            $this->error("未登录,返回登录页", "/user/user/login");
        }
    }


    public function test(){
        $data = $this->request->post();
        foreach ($data["order"] as $key=>$value){
            var_dump($value["quantity"]);
        }

    }
    public function test1(){
       var_dump(config("order.port")) ;

    }
}

