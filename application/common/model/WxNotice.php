<?php
namespace app\common\model;

class WxNotice extends Base
{
    protected $name='wx_notice';

    protected $insert = ['status'=>1];

    //微信--生日通知
    public function linkUserBirthday()
    {
        return $this->belongsTo('Users','uid');
    }
}