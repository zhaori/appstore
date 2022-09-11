<?php
$datetime = new DateTime();
$now_time = $datetime->format('Y-m-d H:i:s');
echo date('Y-m-d H:i:s', time());
echo date('Y-m-d H:i:s', time() + 60 * 30);
