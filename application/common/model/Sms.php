<?php
namespace app\common\model;


class Sms extends Base
{
    protected $name='sys_sms';

    protected $json = [
        'info'
    ];
    public static $SMS_TYPE = [
        ['name'=>'用户注册','class'=>'UserReg','need_verify'=>true],
        ['name'=>'验证码登录','class'=>'UserLogin','need_verify'=>true],
        ['name'=>'忘记密码','class'=>'PwdForget','need_verify'=>true],
        ['name'=>'申请代理','class'=>'ProxyReq','need_verify'=>true],
    ];

    //发送短信验证码
    public static function sendSms($phone,$type)
    {
        empty($phone) && abort(4000,'请输入手机号码');
        !isset(self::$SMS_TYPE[$type]) && abort(4000,'短信类型异常');
        $sms_type = self::$SMS_TYPE[$type];
        $model = new self();
        //获取该手机最后发送的一条消息
        $sms_model = $model->where(['phone'=>$phone,'type'=>$type])->order('id','desc')->find();
        //实例化类
        $class = '\\app\\common\\service\\sms\\'.$sms_type['class'];
        $object = new $class();
        //验证短信信息
        if(method_exists($object,'checkVerify')){
            $check_state = $object->checkVerify($sms_model,$phone);
            //验证信息
            $check_state!==true && abort(4000,$check_state);
        }
        //是否需要验证码
        $verify='';
        $sms_type['need_verify'] && $verify=rand(1000,99999);
        //获取发送内容
        $content = $object->getContent($verify);
        //发送数据
        $send_result = \app\common\service\sms\Sms::send($content,$phone);
        //保存数据
        $save_data = [
            'type'      =>  $type,
            'phone'     =>  $phone,
            'content'   =>  $content,
            'info'      =>  $send_result,
            'status'    =>  1,
        ];
        $verify && $save_data['verify'] = $verify;
        $model->save($save_data);
    }

    //验证短信验证码
    public static function checkVerify($phone,$verify,$type)
    {
        !isset(self::$SMS_TYPE[$type]) && abort(4000,'短信类型异常');
        $model = new self();
        $model = $model->where(['phone'=>$phone,'type'=>$type])->order('id','desc')->find();
        ($model['verify'] != $verify || empty($model)) && abort(4000,'请输入正确的验证码');
        $model['status']!=1 && abort(4000, '验证码已使用,请重新获取');
        //修改验证码为使用状态
        $model->status = 2;
        $model->use_time = time();
        $model->save();
    }

}