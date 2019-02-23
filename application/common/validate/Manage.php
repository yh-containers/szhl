<?php
namespace app\common\validate;

use think\Validate;

class Manage extends Validate
{
    protected $rule = [
        'name'      =>  'require|min:1',
        'account'   =>  'require|length:11|unique:sys_manage',
        'rid'       =>  'require|gt:0',
        'password'  =>  'requireCallback:checkRequire|min:6',
    ];

    protected $message = [
        'name.require'      => '用户名必须输入',
        'name.min'          => '用户名字符长度必须超过:rule位',
        'account.require'   => '帐号必须输入',
        'account.length'    => '帐号长度必须为:rule位',
        'account.unique'    => '帐号已存在',
        'rid.require'       => '请选择角色',
        'rid.gt'            => '请选择角色',
        'password.requireCallback'=> '密码不能为空',
        'password.min'      => '密码不得低于:rule位',
    ];

    public function sceneAdmin_add_proxy()
    {
        return $this->only(['name','account','password']);
    }

    public function checkRequire($value, $data)
    {
        if(empty($data['id'])){
            return true;
        }
    }
}