<?php
namespace app\common\service\sms;


use think\Model;

class UserReg implements IVerify
{
    //短信有效期
    const EXPIRE = 600;
    //发送验证
    public function checkVerify(Model $model=null)
    {
        if(!empty($model)){
            //未达到过期时间且未使用
            if( strtotime($model['create_time']) > time()-self::EXPIRE && $model['status']==1){
                return '短信已发送，请注意查收..';
            }
        }
        return true;
    }

    //获取发送内容
    public function getContent($verify)
    {
        return '您的验证为:'.$verify.' 请在'.intval(self::EXPIRE/60).'分钟内使用';
    }
}