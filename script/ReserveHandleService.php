<?php
// 出队列
class ReserveHandleService
{
    public $redis;

    function __construct() {
        $this->redis = new Redis();
        $this->redis->connect('s5.z100.vip', 39166);
        $this->redis->select(2);

    }

    public function await_queue(){
        // 处理等待队列
        $queue_data = null;
        $await_queue = $this->redis->get("await_queue");
        if(!is_null($await_queue)){
            $queue_data = $this->redis->lpop("await_queue");
        }
        return $queue_data;
    }

    public function task_queue(){
        //处理订单队列
        $queue_data = null;
        $task_queue = $this->redis->get("task_queue");
        if(!is_null($task_queue)){
            $queue_data = $this->redis->lpop("task_queue");
        }
        return $queue_data;
    }

}

$queue_list = new ReserveHandleService();
while (true){
   echo 123;
   print_r($queue_list->task_queue());
}