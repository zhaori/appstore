<?php
    $conn = new mysqli("127.0.0.1", "root", 123456, "softwarestore");
    $data =  $conn->query('select * from tp_user');
    print_r($data->fetch_all());