<?php
/**
 * Created by PhpStorm.
 * User: Ru
 * Date: 2019/2/15
 * Time: 14:34
 */

namespace app\common\validate;
use think\Validate;

class Article extends Validate
{

    protected $rule = [
        'img'               =>  'require',
        'title'             =>  'require|min:2',

    ];

    protected $message = [
        'img.require'           => '请上传项目图片上传',
        'title.require'         => '文章名必须输入',
        'title.min'             => '文章名称长度必须超过:rule位',

    ];

}