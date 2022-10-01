<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2018 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------

// +----------------------------------------------------------------------
// | 会话设置
// +----------------------------------------------------------------------

return [
    'id'             => '',
    // SESSION_ID的提交变量,解决flash上传跨域
    'var_session_id' => '',
    // SESSION 前缀
    'prefix'         => 'think',
    // 驱动方式 支持redis memcache memcached
    'type'           => '',
    // 设置缓存过期
    'cache_expire'   => '',
    // 是否自动开启 SESSION
    'auto_start'     => true,
    'expire'         =>  86400,
    'path'           => '../runtime/session'
];
//return [
//    'type' => 'redis',
//    'prefix' => 'module',
//    'auto_start' => true,
//    // redis主机
//    'host' => 's10.z100.vip',
//    // redis端口
//    'port' => 39166,
//    // 密码
//    'password' => '',
//    'expire' => 0
//];
