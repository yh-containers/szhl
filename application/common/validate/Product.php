<?php
namespace app\common\validate;

use think\Validate;

class Product extends Validate
{
    protected $rule = [
        'img'               =>  'require',
        'name'              =>  'require|min:3',
        'commission'        =>  'require|between:0.01,1',
        'type'              =>  'require',
        'per'               =>  'require|between:0.01,1',
        'per_unit'          =>  'require',
        'money_start'       =>  'require|gt:0',
        'money_end'         =>  'egt:money_start',
        'money_unit'        =>  'require',
        'auth_time_start'   =>  'require|gt:0',
        'auth_time_end'     =>  'egt:auth_time_start',
        'auth_unit'         =>  'require',
    ];

    protected $message = [
        'img.require'           => '请上传项目图片上传',
        'name.require'          => '项目名必须输入',
        'name.min'              => '项目名称长度必须超过:rule位',
        'commission.require'    => '项目佣金必须输入',
        'commission.between'    => '项目佣金只能在 :1 - :2',
        'type.require'          => '项目类型必须选择',
        'per.require'           => '项目利息必须输入',
        'per.between'           => '项目利息只能在 :1 - :2',
        'per_unit.require'      => '请选择项目利息单位',
        'money_start.require'   => '项目起始金额必须输入',
        'money_start.gt'        => '项目起始金额必须输入',
        'money_end.egt'          => '项目结束金额必须大于开始金额',
        'money_unit.require'    => '请选择项目额度单位',
        'auth_time_start.require'=> '请输入项目起始期限',
        'auth_time_start.gt'    => '请输入项目起始期限',
        'auth_time_end.egt'     => '项目结束期限必须大于开始期限',
        'auth_unit.require'     => '项目期限必须选择',
    ];

}