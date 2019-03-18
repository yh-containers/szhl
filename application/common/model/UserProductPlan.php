<?php
namespace app\common\model;



class UserProductPlan extends Base
{
    protected $name='user_product_plan';

    protected $insert = ['status'=>0];

    public static $fields_status=['未还款','已还款'];

    /*
     * 还款日期
     *
     * */
    public function setStatusAttr($value,$data)
    {
        if($value==1){
            //已还款
            $this->setAttr('back_time',time());
        }
        return $value;
    }

    /*
     * 获取
     * */
    public function getBackTimeAttr($value)
    {
        return $value?date('Y-m-d H:i:s',$value):'';
    }
    //关联用户
    public function linkUserInfo()
    {
        return $this->belongsTo('Users','uid');
    }
}