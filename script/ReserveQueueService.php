<?php

class ReserveQueueService
{
    public $mysql;

    public $mysql_config = [
        'host' => 's5.z100.vip',
        'port' => 24085,
        'db' => 'softwarestore',
        'user' => 'root',
        'password' => 123456
    ];

    function __construct()
    {
        try {
            $this->mysql = new mysqli($this->mysql_config['host'],
                $this->mysql_config['user'],
                $this->mysql_config['password'],
                $this->mysql_config['db']
            );
        } catch (mysqli_sql_exception $e) {
            die("Error!:" . $e->getMessage() . "<br>");
        }
    }


}