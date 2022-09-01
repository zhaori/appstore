<?php

namespace app\common\validate;

use think\Validate;

class info extends Validate
{
    /**
     * 定义验证规则
     * 格式：'字段名'    =>    ['规则1','规则2'...]
     *
     * @var array
     */
    protected $rule = [
        'user_name' => 'require|max:25',
        'email' => 'email',
        'phone_number' => 'number'
    ];

    /**
     * 定义错误信息
     * 格式：'字段名.规则名'    =>    '错误信息'
     *
     * @var array
     */
    protected $message = [
        'user_name.require' => '名称必须存在',
        'user_name.max' => '最大25个字符长度',
        'email' => 'email必须是邮箱格式',
        'phone_number.number' => '必须是电话号码'
    ];
}