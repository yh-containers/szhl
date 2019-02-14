<?php
namespace app\common\validate;

use think\Validate;

class Users extends Validate
{
    protected $rule = [
        'name'          =>  'require',
        'phone'         =>  'require|mobile|unique:users',
        'password'      =>  'require|min:6',
        'qr_password'   => 'require|confirm:password',
        'city'          =>  'require|gt:0',
        'is_protocol'   =>  'require',
        'verify'        =>  'require',
    ];

    protected $message = [
        'name.require'      => '用户名必须输入',
        'phone.require'     => '手机号码必须输入',
        'phone.mobile'      => '请输入正确的手机号码',
        'phone.unique'      => '手机号已被注册',
        'password.require'  => '密码必须输入',
        'password.min'      => '密码不得低于6位',
        'qr_password.require'=> '请输入确认密码',
        'qr_password.confirm'=> '两次密码不一致',
        'city.require'      => '请选择城市',
        'city.gt'           => '请选择城市',
        'is_protocol.require'=> '请同意服务协议',
        'is_protocol.eq'    => '请同意服务协议',
        'verify.require'    => '请输入验证码',

    ];


    public function sceneIndex_reg()
    {
        return $this->only(['name','phone','password','qr_password','city','is_protocol','verify'])
            ->append('is_protocol','eq:1')
            ->append('verify','checkVerify:0')
            ;
    }

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