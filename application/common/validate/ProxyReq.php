<?php
/**
 * Created by PhpStorm.
 * User: Ru
 * Date: 2019/2/15
 * Time: 14:34
 */

namespace app\common\validate;
use think\Validate;

class ProxyReq extends Validate
{

    protected $rule = [
        'phone'              =>  'require|mobile',
        'verify'             =>  'require|checkVerify:3',

    ];

    protected $message = [
        'phone.require'           => '请输入手机号码',
        'phone.mobile'           => '请输入正确的手机号码',
        'verify.require'         => '验证码必须输入',
        'title.min'             => '文章名称长度必须超过:rule位',

    ];

    /*
     * 验证码验证
     * */
    public function checkVerify($value,$rule,$data=[])
    {
        try{
            \app\common\model\Sms::checkVerify($data['phone'],$value,$rule);
            return true;
        }catch (\Exception $e) {
            return $e->getMessage();
        }
    }
}