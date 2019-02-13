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
}