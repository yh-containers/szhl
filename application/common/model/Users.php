<?php
namespace app\common\model;


class Users extends Base
{
    protected $name='users';

    protected $insert = ['face'=>'/public/uploads/face/toux.png','type'];

    public static $fields_type=['普通用户','合作伙伴','代理用户'];

    //设置用户类型
    public function setTypeAttr($value,$data)
    {
        return $value?$value:0;
    }

    //设置城市
    public function setCityAttr($value,$data)
    {
        if(!isset($data['province'])){
            //获取省份
            $model =new \app\common\model\Location();
            $province = $model->where('area_id',$value)->value('parent_id');
            $this->setAttr('province', $province);
        }
        return $value;
    }

    //设置密码
    public function setPasswordAttr($value)
    {
        $salt = rand(10000,99999);
        $this->setAttr('salt', $salt);
        return self::entryPwd($value,$salt);
    }

    //用户密码加密
    public static function entryPwd($pwd,$salt)
    {
        return md5($pwd.md5($pwd.$salt).$salt);
    }

    /*
     * 验证码登录
     * */
    public function verifyLogin($data)
    {
        empty($data['phone']) && abort(4000,'请输入手机号码');
        empty($data['verify']) && abort(4000,'请输入验证码');
        //验证验证码
        \app\common\model\Sms::checkVerify($data['phone'],$data['verify'],1);
        //验证成功
        $model = $this->where('phone',$data['phone'])->find();
        if(empty($model)) {
            //注册帐号
            $this->save([
                'phone' => $data['phone']
            ]);
            $model = $this;
        }
        //登录成功
        $model->handleLoginInfo();
    }

    /*
     * 帐号密码登录
     * */
    public function pwdLogin($data)
    {
        empty($data['phone']) && abort(4000,'请输入手机号码');
        empty($data['password']) && abort(4000,'请输入密码');

        $model = $this->where('phone','=',$data['phone'])->find();
        empty($model) && abort(4000,'用户名或密码错误');
        $entry_pwd = self::entryPwd($data['password'],$model['salt']);
        $entry_pwd!=$model['password'] && abort(4000,'用户名或密码错误.');
        $model->handleLoginInfo();
    }

    /*
     * web端登录成功处理登录信息
     * */
    public function handleLoginInfo()
    {
        session('user_info',[
            'user_id' => $this->getData('id'),
            'type' => $this->getData('type'),
        ]);
    }

    /*
     * 忘记密码
     * */
    public function forgetPwd($phone,$pwd,$verify)
    {
        //验证验证码
        \app\common\model\Sms::checkVerify($phone,$verify,2);
        //验证成功
        $model = $this->where('phone',$phone)->find();
        $model->save(['password'=>$pwd],['phone'=>$phone]);
    }

}