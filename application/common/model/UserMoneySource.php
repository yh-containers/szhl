<?php
namespace app\common\model;



class UserMoneySource extends Base
{
    protected $name='user_money_source';

    protected $json = ['extra'];

    protected $insert = ['status'=>1,'send_time'];

    public static $fields_status=['异常类型','未发放','已发放'];

    /*
     * 发放时间
     * */
    public function setSendTimeAttr($value,$data)
    {
        return $value?$value:time();
    }

    /*
     * 发放
     * */
}