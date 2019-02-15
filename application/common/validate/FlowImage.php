<?php
namespace app\common\validate;

use think\Validate;

class FlowImage extends Validate
{
    protected $rule = [
        'img'       =>  'require',
    ];

    protected $message = [
        'img.require'      => '请上传图片',
    ];

}