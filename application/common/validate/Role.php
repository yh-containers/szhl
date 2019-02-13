<?php
namespace app\common\validate;

use think\Validate;

class Role extends Validate
{
    protected $rule = [
        'name'      =>  'require|min:1',
    ];

    protected $message = [
        'name.require'      => '角色名必须输入',
        'name.min'          => '角色名字符长度必须超过:rule位',
    ];

}