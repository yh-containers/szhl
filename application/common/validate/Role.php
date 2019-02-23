<?php
namespace app\common\validate;

use think\Validate;

class Role extends Validate
{
    protected $rule = [
        'name'      =>  'require|min:1',
        'id'        =>  'checkAuth',
    ];

    protected $message = [
        'name.require'      => '角色名必须输入',
        'name.min'          => '角色名字符长度必须超过:rule位',
    ];

    public function checkAuth($value,$rule,$data){
        if($value && in_array($value,\app\admin\middleware\CheckAuth::$ignore_role_id)){
            return '系统指定角色进行操作';
        }
        return true;
    }

}