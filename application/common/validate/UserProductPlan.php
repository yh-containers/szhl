<?php
/**
 * Created by PhpStorm.
 * User: Ru
 * Date: 2019/2/15
 * Time: 14:34
 */

namespace app\common\validate;
use think\Validate;

class UserProductPlan extends Validate
{

    protected $rule = [
        'id'            => 'require|gt:0',
        'back_intro'    => 'require',
    ];

    protected $message = [
        'id.require'            => '计划信息异常',
        'id.gt'                 => '计划信息异常',
        'back_intro.require'    => '请输入还款内容',

    ];


    //处理还款计划
    public function scene_admin_handle_plan()
    {
        return $this->only(['id','back_intro']);
    }
}