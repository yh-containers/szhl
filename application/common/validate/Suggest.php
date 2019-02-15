<?php
namespace app\common\validate;

use think\Validate;

class Suggest extends Validate
{
    protected $rule = [
        'content'       =>  'require|min:5',
    ];

    protected $message = [
        'content.require'       => '请输入建议内容',
        'content.min'           => '投诉建议字数不得低于:rule位',
    ];

}