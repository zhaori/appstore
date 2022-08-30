<?php
    //数据查询函数

    $conn = new mysqli("127.0.0.1", "root", 123456, "test");
    
//$query_sql = "SELECT $sql_field FROM `$table` WHERE `$key` = '" . $value . "'";
//try {
//    return $conn->query($query_sql);
    // var_dump($number);
    for($n=1;$n<=1000000;$n++){
        // print($n+"\n");
        $conn->query('insert into `test` (goods_name, goods_classify, goods_reserve) value("商品名称'.$n.'","商品分类'.$n.'","商品库存'.$n.'");');
        // echo 'insert into `test` (goods_name, goods_classify, goods_reserve) value("商品名称'.$n.'","商品分类'.$n.'","商品库存'.$n.'")';

    }
    // foreach($number as $key=>$value){
    //     print($value[1]);
    // }
    // $conn->close();