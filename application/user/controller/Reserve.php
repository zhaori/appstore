<?php
// 对商品库存进行轮询，报告库存情况，将库存正常和为空的排入对应的队列
namespace app\user\controller;

use think\App;
use think\cache\driver\Redis;
use think\Controller;
use think\Db;
use think\Exception;

class Reserve extends Controller{
    public $selectData;
    public $await_data = array();
    public $task_data = array();
    public $await_redis;    //库存为空的等待任务
    public $task_redis;     //可直接处理的任务
    public $task_option;

    protected $redis_options = [
        'host'       => 's5.z100.vip',
        'port'       => 39166,
        'password'   => '',
        'select'     => 2,
        'timeout'    => 3600,
        'expire'     => 0,
        'persistent' => false,
        'prefix'     => '',
        'serialize'  => true,
    ];


    public function __construct(App $app = null)
    {
        parent::__construct($app);
        $this->await_redis = new Redis($this->redis_options);
        $this->task_option = $this->redis_options;
        $this->task_option['select'] = 3;
        $this->task_redis = new Redis($this->task_option);
    }

    public function index(){
        $sql = "select comm_id, comm_name, comm_reserve from tp_commodity order by comm_reserve ASC";
        try {
            $this->selectData = Db::query($sql);
        } catch (Exception $e) {
            trace($e);
        }

        $data_length = count($this->selectData);

        for($int_num=0; $int_num < $data_length; $int_num++){
            if($this->selectData[$int_num]["comm_reserve"]==0){
                // 把库存为空的商品排入等待数据库
                $this->await_data[] = $this->selectData[$int_num]["comm_id"];
            }else{
                // 把库存不为空的商品排入可执行任务队列
                $this->task_data[] = $this->selectData[$int_num]["comm_id"];
            }
        }
        if(isset($this->await_data)){
            $this->await_redis->set("await_data", $this->await_data);
        }
        if(isset($this->task_data)){
            $this->task_redis->set("task_data", $this->task_data);
        }
    }

    public function expired(){
        //处理到期订单
    }

    public function test(){
        print_r($this->await_redis->get("await_data"));
    }
}
