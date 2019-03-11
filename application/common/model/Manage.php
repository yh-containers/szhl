<?php
namespace app\common\model;

use think\model\concern\SoftDelete;

class Manage extends Base
{
    use SoftDelete;
    protected $name='sys_manage';


    public function setPasswordAttr($value)
    {
        $salt = rand(10000,99999);
        $this->setAttr('slat',$salt);
        return self::entryPwd($value,$salt);
    }

    //加密
    public static function entryPwd($value,$salt)
    {
        return md5($salt.md5($value.$salt).$value);
    }

    //关联角色
    public function linkRole()
    {
        return $this->belongsTo('Role','rid');
    }

    //关联申请--所有
    public function linkProductReq()
    {
        return $this->hasMany('ProductReq','p_auth_mid');
    }
    //关联申请--已完成
    public function linkProductReqComplete()
    {
        return $this->hasMany('ProductReq','p_auth_mid')->where('status',2);
    }
    //关联申请--今日已完成
    public function linkProductReqTodayComplete()
    {
        return $this->hasMany('ProductReq','p_auth_mid')->where([['status','=',2],['complete_time','egt',time()-86400]]);
    }
}