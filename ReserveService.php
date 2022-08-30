<?php
// 需要广播两条信息，当库存充足与不充足的情况
class ReserveService
{
    public $mysql;
    public $selectData;
    public $await_list;  //库存为空的情况
    public $task_list; //库存不为空的时候
    public $await_redis;    //库存为空的等待任务
    public $task_redis;     //可直接处理的任务
    public $redis_config = [
            'host'     => '127.0.0.1',
            'port'     => 6379,
            'select'   => 2,
            'password' => '',
            'expire'   => ''
        ];
    // 定义redis rdb2 为等待队列
    // rdb3为订单处理队列
    public $task_redis_config;

    public $mysql_config = [
            'host'  => '127.0.0.1',
            'port'  => 3306,
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

    public function getData(): array
    {
        //读取数据库库存信息
        $array_data = array();
        $sql = "select comm_id, comm_name, comm_reserve from tp_commodity";
        $data = $this->mysql->query($sql);
        foreach ($data as $key=>$value){
            $array_data[] = $value;
        }
        return $array_data;
    }

    public function unrepeat(){
        $get_len = $this->await_redis->lLen("await");
        $repeat_data = $this->await_redis->lRange("await", 0, $get_len);
        var_dump($repeat_data);
    }

    public function await_queue($queue_list){
        // 把库存为空的商品id插入等待队列中
        if(isset($queue_list) && is_array($queue_list)){
            foreach ($queue_list as $key=> $value){
                $this->await_redis->lPush("await", $value);
            }
        }
    }

    public function task_queue($queue_list){
        // 把库存不为空的商品id插入处理队列中
        if(isset($queue_list) && is_array($queue_list)){
            foreach ($queue_list as $key=> $value){
                $this->task_redis->lPush("task", $value);
            }
        }
    }

    public function listen()
    {
        //监听库存信息
        foreach ($this->getData() as $key=>$value){
            if($value['comm_reserve']==0){
                $this->await_list[] = $value["comm_id"];
            }else{
                $this->task_list[] = $value["comm_id"];
            }
        }

        if(!isset($this->await_list)){
            $this->await_queue($this->await_list);

        }elseif (!isset($this->task_list)){
            $this->task_queue($this->task_list);
        }
    }

    public function test(){
        $get_len = $this->await_redis->lLen("await");
        $this->await_redis->lRange("await", 0, $get_len);
    }
}

$service = new ReserveService();
$service->listen();
$service->unrepeat();
//$service->test();
//while (true){
//    try {
//        $service->listen();
//        sleep(3);
//    }catch (Exception $e){
//        die("Error:".$e->getMessage()."\n");
//    }
//
//}
//$service->queue($service->listen());
//$service->test();
