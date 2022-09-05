<?php
// 需要广播两条信息，当库存充足与不充足的情况
class ReserveService
{
    public $mysql;
    public $await_redis;    //库存不满足的等待任务
    public $task_redis;     //可直接处理的任务
    public $redis_config = [
            'host'     => 's5.z100.vip',
            'port'     => 39166,
            'select'   => 2
        ];
    // 定义redis rdb2 为等待队列
    // rdb3为订单处理队列
    public $task_redis_config;

    public $mysql_config = [
            'host'  => 's5.z100.vip',
            'port'  => 24085,
            'db'    => 'softwarestore',
            'user'  => 'root',
            'password'  => 123456
    ];

    function __construct() {
       $this->await_redis = new Redis();
       $this->await_redis->connect($this->redis_config['host'], $this->redis_config['port']);
       $this->await_redis->select($this->redis_config['select']);

       $this->task_redis_config = $this->redis_config;
       $this->task_redis_config["select"] = 3;  // 设置rdb3
       $this->task_redis = new Redis();
       $this->task_redis->connect($this->redis_config['host'], $this->redis_config['port']);
       $this->task_redis->select($this->task_redis_config['select']);

        try {
            $this->mysql = new mysqli(  $this->mysql_config['host'],
                                        $this->mysql_config['user'],
                                        $this->mysql_config['password'],
                                        $this->mysql_config['db']
                                    );
        }catch (mysqli_sql_exception $e){
            die("Error!:".$e->getMessage()."<br>");
        }
    }

    public function await_queue(){
        // 处理等待队列
        $queue_data = null;
        $await_queue = $this->task_redis->get("await_queue");
        if(!is_null($await_queue)){
            $queue_data = $this->task_redis->Rpop("await_queue");
        }
        return $queue_data;
    }

    public function task_queue(){
        //处理订单队列
        $queue_data = null;
        $task_queue = $this->task_redis->get("task_queue");
        if(!is_null($task_queue)){
            $queue_data = $this->task_redis->Rpop("task_queue");
        }
        return $queue_data;
    }

    public function test(){

    }
}
